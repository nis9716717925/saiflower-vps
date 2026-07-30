import type { NextFunction, Request, Response } from 'express';
import jwt from 'jsonwebtoken';
import { prisma } from '../db/client';
import { UnauthorizedError } from '../utils/errors';
import { verifyAccessToken, type TokenPayload } from '../utils/jwt';

export interface AuthRequest extends Request {
  user?: TokenPayload & { id: number };
  guestId?: string;
}

export const authenticate = async (req: AuthRequest, _res: Response, next: NextFunction) => {
  try {
    const authHeader = req.headers.authorization;
    if (!authHeader?.startsWith('Bearer ')) {
      throw new UnauthorizedError('Access token required');
    }
    const payload = verifyAccessToken(authHeader.slice(7));
    const customer = await prisma.customers.findUnique({
      where: { id: payload.userId },
      select: { id: true, email: true, name: true },
    });
    if (!customer) throw new UnauthorizedError('User account not found');
    req.user = { ...payload, id: customer.id };
    next();
  } catch (err) {
    if (err instanceof jwt.JsonWebTokenError) {
      return next(new UnauthorizedError('Invalid or expired access token'));
    }
    next(err);
  }
};

export const optionalAuth = async (req: AuthRequest, _res: Response, next: NextFunction) => {
  const authHeader = req.headers.authorization;
  if (!authHeader?.startsWith('Bearer ')) return next();
  try {
    const payload = verifyAccessToken(authHeader.slice(7));
    const customer = await prisma.customers.findUnique({
      where: { id: payload.userId },
      select: { id: true },
    });
    if (customer) req.user = { ...payload, id: customer.id };
  } catch {
    // ignore
  }
  next();
};

export const guestCart = (req: AuthRequest, _res: Response, next: NextFunction) => {
  const guestId = req.headers['x-guest-id'];
  if (typeof guestId === 'string' && guestId.trim()) {
    req.guestId = guestId.trim();
  }
  next();
};
