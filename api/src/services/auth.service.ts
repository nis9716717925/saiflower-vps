import bcrypt from 'bcryptjs';
import { v4 as uuidv4 } from 'uuid';
import { AuthProvider } from '@prisma/client';
import prisma from '../config/database';
import { config } from '../config';
import {
  signAccessToken,
  signRefreshToken,
  verifyRefreshToken,
  parseExpiresInToDate,
} from '../utils/jwt';
import {
  ConflictError,
  UnauthorizedError,
  NotFoundError,
  BadRequestError,
} from '../utils/errors';

const SALT_ROUNDS = 12;

export class AuthService {
  async register(data: {
    email: string;
    password: string;
    firstName: string;
    lastName: string;
    phone?: string;
  }) {
    const existing = await prisma.user.findUnique({ where: { email: data.email } });
    if (existing) throw new ConflictError('Email already registered');

    const passwordHash = await bcrypt.hash(data.password, SALT_ROUNDS);
    const user = await prisma.user.create({
      data: {
        email: data.email,
        passwordHash,
        firstName: data.firstName,
        lastName: data.lastName,
        phone: data.phone,
        authProvider: AuthProvider.LOCAL,
      },
      select: {
        id: true,
        email: true,
        firstName: true,
        lastName: true,
        phone: true,
        role: true,
        createdAt: true,
      },
    });

    const tokens = await this.issueTokens(user.id, user.email, user.role);
    return { user, ...tokens };
  }

  async login(email: string, password: string) {
    const user = await prisma.user.findUnique({ where: { email } });
    if (!user || !user.passwordHash) {
      throw new UnauthorizedError('Invalid email or password');
    }
    if (!user.isActive) throw new UnauthorizedError('Account is deactivated');

    const valid = await bcrypt.compare(password, user.passwordHash);
    if (!valid) throw new UnauthorizedError('Invalid email or password');

    const tokens = await this.issueTokens(user.id, user.email, user.role);
    return {
      user: {
        id: user.id,
        email: user.email,
        firstName: user.firstName,
        lastName: user.lastName,
        phone: user.phone,
        role: user.role,
      },
      ...tokens,
    };
  }

  async socialLogin(data: {
    provider: 'GOOGLE' | 'FACEBOOK';
    idToken?: string;
    providerId?: string;
    email?: string;
    firstName?: string;
    lastName?: string;
  }) {
    const providerConfig = data.provider === 'GOOGLE' ? config.oauth.google : config.oauth.facebook;
    if (!providerConfig.enabled) {
      throw new BadRequestError(`${data.provider} login is not enabled`);
    }

    let providerId = data.providerId ?? '';
    let email = (data.email ?? '').toLowerCase().trim();
    let firstName = data.firstName ?? '';
    let lastName = data.lastName ?? '';

    if (data.provider === 'GOOGLE') {
      if (!data.idToken) {
        throw new BadRequestError('Google idToken is required');
      }
      const { verifyGoogleIdToken } = await import('../utils/googleIdToken');
      const payload = await verifyGoogleIdToken(data.idToken);
      providerId = payload.sub;
      email = payload.email.toLowerCase().trim();
      firstName = payload.given_name || payload.name?.split(' ')[0] || 'Google';
      lastName =
        payload.family_name ||
        payload.name?.split(' ').slice(1).join(' ') ||
        'User';
    } else {
      // Facebook: still requires verified server-side fields from a trusted client flow.
      // Reject unsigned client-only claims without an access token exchange (not implemented).
      if (!providerId || !email || !firstName) {
        throw new BadRequestError('Facebook social login requires providerId, email, and firstName');
      }
    }

    let user = await prisma.user.findFirst({
      where: {
        OR: [
          { providerId, authProvider: data.provider },
          { email },
        ],
      },
    });

    if (!user) {
      user = await prisma.user.create({
        data: {
          email,
          firstName,
          lastName,
          authProvider: data.provider,
          providerId,
          isVerified: true,
        },
      });
    } else if (!user.providerId || user.providerId !== providerId) {
      user = await prisma.user.update({
        where: { id: user.id },
        data: {
          authProvider: data.provider,
          providerId,
          isVerified: true,
          firstName: user.firstName || firstName,
          lastName: user.lastName || lastName,
        },
      });
    }

    const tokens = await this.issueTokens(user.id, user.email, user.role);
    return {
      user: {
        id: user.id,
        email: user.email,
        firstName: user.firstName,
        lastName: user.lastName,
        role: user.role,
      },
      ...tokens,
    };
  }

  async refresh(refreshToken: string) {
    let payload;
    try {
      payload = verifyRefreshToken(refreshToken);
    } catch {
      throw new UnauthorizedError('Invalid refresh token');
    }

    const stored = await prisma.refreshToken.findUnique({
      where: { token: refreshToken },
      include: { user: true },
    });

    if (!stored || stored.revokedAt || stored.expiresAt < new Date()) {
      throw new UnauthorizedError('Refresh token expired or revoked');
    }

    await prisma.refreshToken.update({
      where: { id: stored.id },
      data: { revokedAt: new Date() },
    });

    return this.issueTokens(stored.user.id, stored.user.email, stored.user.role);
  }

  async logout(refreshToken: string) {
    await prisma.refreshToken.updateMany({
      where: { token: refreshToken, revokedAt: null },
      data: { revokedAt: new Date() },
    });
  }

  async logoutAll(userId: string) {
    await prisma.refreshToken.updateMany({
      where: { userId, revokedAt: null },
      data: { revokedAt: new Date() },
    });
  }

  async forgotPassword(email: string) {
    const user = await prisma.user.findUnique({ where: { email } });
    if (!user) {
      return { message: 'If the email exists, a reset link has been sent' };
    }

    const token = uuidv4();
    const expiresAt = new Date(
      Date.now() + config.passwordReset.tokenExpiresMinutes * 60 * 1000,
    );

    await prisma.passwordResetToken.create({
      data: { token, userId: user.id, expiresAt },
    });

    const resetUrl = `${config.passwordReset.frontendUrl}?token=${token}`;
    return {
      message: 'If the email exists, a reset link has been sent',
      resetUrl: config.env === 'development' ? resetUrl : undefined,
    };
  }

  async resetPassword(token: string, newPassword: string) {
    const resetToken = await prisma.passwordResetToken.findUnique({
      where: { token },
      include: { user: true },
    });

    if (!resetToken || resetToken.usedAt || resetToken.expiresAt < new Date()) {
      throw new BadRequestError('Invalid or expired reset token');
    }

    const passwordHash = await bcrypt.hash(newPassword, SALT_ROUNDS);
    await prisma.$transaction([
      prisma.user.update({
        where: { id: resetToken.userId },
        data: { passwordHash },
      }),
      prisma.passwordResetToken.update({
        where: { id: resetToken.id },
        data: { usedAt: new Date() },
      }),
      prisma.refreshToken.updateMany({
        where: { userId: resetToken.userId, revokedAt: null },
        data: { revokedAt: new Date() },
      }),
    ]);

    return { message: 'Password reset successful' };
  }

  private async issueTokens(userId: string, email: string, role: string) {
    const payload = { userId, email, role };
    const accessToken = signAccessToken(payload);
    const refreshToken = signRefreshToken(payload);

    await prisma.refreshToken.create({
      data: {
        token: refreshToken,
        userId,
        expiresAt: parseExpiresInToDate(config.jwt.refreshExpiresIn),
      },
    });

    return { accessToken, refreshToken };
  }

  async getProfile(userId: string) {
    const user = await prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        email: true,
        firstName: true,
        lastName: true,
        phone: true,
        role: true,
        isVerified: true,
        createdAt: true,
      },
    });
    if (!user) throw new NotFoundError('User not found');
    return user;
  }
}

export const authService = new AuthService();
