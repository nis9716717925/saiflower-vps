import crypto from 'crypto';
import bcrypt from 'bcryptjs';
import { OAuth2Client } from 'google-auth-library';
import { config } from '../config';
import { prisma } from '../db/client';
import { AppError, UnauthorizedError, ValidationError } from '../utils/errors';
import { publicCustomer } from '../utils/catalog';
import { signAccessToken, signRefreshToken, verifyRefreshToken } from '../utils/jwt';

const googleClient = new OAuth2Client(config.oauth.google.clientId);

const customerSelect = {
  id: true,
  name: true,
  email: true,
  phone: true,
  address: true,
  city: true,
  pincode: true,
  isVerified: true,
  avatarUrl: true,
  authProvider: true,
} as const;

function tokensForCustomer(row: {
  id: number;
  name: string;
  email: string;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  pincode?: string | null;
  isVerified?: number | null;
  avatarUrl?: string | null;
  authProvider?: string | null;
}) {
  const payload = {
    userId: row.id,
    email: row.email,
    name: row.name ?? '',
    role: 'CUSTOMER' as const,
  };
  return {
    accessToken: signAccessToken(payload),
    refreshToken: signRefreshToken(payload),
    customer: publicCustomer(row),
  };
}

export async function registerCustomer(input: {
  name: string;
  email: string;
  phone?: string;
  password: string;
  confirmPassword: string;
}) {
  const name = input.name.trim();
  const email = input.email.trim().toLowerCase();
  const phone = (input.phone ?? '').trim();
  const password = input.password;
  const confirm = input.confirmPassword;

  if (!name || !email || !password) {
    throw new ValidationError('All fields are required.');
  }
  if (password !== confirm) {
    throw new ValidationError('Passwords do not match.');
  }

  const existing = await prisma.customers.findUnique({
    where: { email },
    select: { id: true },
  });
  if (existing) {
    throw new AppError('Email already registered. Please login.', 409);
  }

  const hashed = await bcrypt.hash(password, 10);
  const token = crypto.randomBytes(32).toString('hex');

  const customer = await prisma.customers.create({
    data: {
      name,
      email,
      phone: phone || null,
      password: hashed,
      isVerified: 0,
      verificationToken: token,
      authProvider: 'local',
    },
  });

  const verifyLink = `${config.app.publicUrl}/verify?token=${token}`;
  console.info(`[auth] Verification link for ${email}: ${verifyLink}`);

  return {
    customerId: customer.id,
    message:
      'Registration successful! Please check your email to verify your account.',
    verificationToken: config.isProduction ? undefined : token,
  };
}

export async function loginCustomer(emailRaw: string, password: string) {
  const email = emailRaw.trim().toLowerCase();
  if (!email || !password) {
    throw new ValidationError('Email and password are required.');
  }

  const row = await prisma.customers.findUnique({
    where: { email },
    select: { ...customerSelect, password: true },
  });
  if (!row) {
    throw new UnauthorizedError('No account found with this email.');
  }

  const hash = row.password ?? '';
  const ok = hash ? await bcrypt.compare(password, hash) : false;
  if (!ok) {
    throw new UnauthorizedError('Invalid password.');
  }
  if (Number(row.isVerified) !== 1) {
    throw new AppError(
      'Please verify your email address before logging in. Check your inbox.',
      403,
    );
  }

  const { password: _pw, ...customer } = row;
  return tokensForCustomer(customer);
}

export async function verifyEmail(token: string) {
  const trimmed = token.trim();
  if (!trimmed) throw new ValidationError('Invalid token provided.');

  const row = await prisma.customers.findFirst({
    where: { verificationToken: trimmed, isVerified: 0 },
    select: customerSelect,
  });
  if (!row) {
    throw new AppError(
      'Invalid or expired verification link. Your account may already be verified.',
      400,
    );
  }

  const updated = await prisma.customers.update({
    where: { id: row.id },
    data: { isVerified: 1, verificationToken: null },
    select: customerSelect,
  });

  return {
    message: 'Your email has been successfully verified! You can now log in.',
    ...tokensForCustomer(updated),
  };
}

export async function refreshSession(refreshToken: string) {
  try {
    const payload = verifyRefreshToken(refreshToken);
    const row = await prisma.customers.findUnique({
      where: { id: payload.userId },
      select: customerSelect,
    });
    if (!row) throw new UnauthorizedError('User not found');
    return tokensForCustomer(row);
  } catch {
    throw new UnauthorizedError('Invalid refresh token');
  }
}

export async function getProfile(userId: number) {
  const row = await prisma.customers.findUnique({
    where: { id: userId },
    select: customerSelect,
  });
  if (!row) throw new AppError('Profile not found', 404);
  return publicCustomer(row);
}

export async function updateProfile(
  userId: number,
  input: { name: string; phone?: string; address?: string; city?: string; pincode?: string },
) {
  const name = input.name.trim();
  if (!name) throw new ValidationError('Name is required.');

  await prisma.customers.update({
    where: { id: userId },
    data: {
      name,
      phone: (input.phone ?? '').trim() || null,
      address: (input.address ?? '').trim() || null,
      city: (input.city ?? '').trim() || null,
      pincode: (input.pincode ?? '').trim() || null,
    },
  });
  return getProfile(userId);
}

export async function loginWithGoogle(idToken: string) {
  if (!config.oauth.google.enabled || !config.oauth.google.clientId) {
    throw new AppError('Google OAuth is not configured', 503);
  }

  const ticket = await googleClient.verifyIdToken({
    idToken,
    audience: config.oauth.google.clientId,
  });
  const payload = ticket.getPayload();
  if (!payload?.email || !payload.sub) {
    throw new UnauthorizedError('Invalid Google credential.');
  }

  const email = payload.email.toLowerCase();
  const name = payload.name || payload.given_name || email.split('@')[0];
  const picture = payload.picture ?? null;
  const googleId = payload.sub;

  let customer = await prisma.customers.findFirst({
    where: { googleId },
    select: customerSelect,
  });

  if (!customer) {
    const byEmail = await prisma.customers.findUnique({
      where: { email },
      select: { id: true, name: true, avatarUrl: true },
    });
    if (byEmail) {
      customer = await prisma.customers.update({
        where: { id: byEmail.id },
        data: {
          googleId,
          authProvider: 'google',
          avatarUrl: picture ?? byEmail.avatarUrl,
          isVerified: 1,
          name: byEmail.name?.trim() ? byEmail.name : name,
        },
        select: customerSelect,
      });
    } else {
      customer = await prisma.customers.create({
        data: {
          name,
          email,
          phone: null,
          password: '',
          isVerified: 1,
          verificationToken: null,
          googleId,
          authProvider: 'google',
          avatarUrl: picture,
        },
        select: customerSelect,
      });
    }
  } else {
    customer = await prisma.customers.update({
      where: { id: customer.id },
      data: { name, avatarUrl: picture, isVerified: 1 },
      select: customerSelect,
    });
  }

  return tokensForCustomer(customer);
}
