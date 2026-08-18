import type { Request } from 'express';
import rateLimit from 'express-rate-limit';
import { config } from '../config';

function isLoopback(req: Request): boolean {
  const ip = req.ip || req.socket.remoteAddress || '';
  return (
    ip === '127.0.0.1' ||
    ip === '::1' ||
    ip === '::ffff:127.0.0.1' ||
    ip.endsWith('/127.0.0.1')
  );
}

export const globalRateLimiter = rateLimit({
  windowMs: config.rateLimit.windowMs,
  max: config.rateLimit.max,
  standardHeaders: true,
  legacyHeaders: false,
  message: { success: false, message: 'Too many requests, please try again later.' },
  // Next.js SSR + nginx both hit the API from loopback. Without skipping,
  // every visitor shares one 127.0.0.1 bucket and products disappear (429).
  skip: (req) => {
    if (!config.isProduction) return true;
    if (req.path === '/health' || req.path.startsWith('/health')) return true;
    return isLoopback(req);
  },
});
