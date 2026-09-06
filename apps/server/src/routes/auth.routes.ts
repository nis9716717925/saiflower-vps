import { Router } from 'express';
import { body } from 'express-validator';
import type { AuthRequest } from '../middleware/auth';
import { authenticate, optionalAuth } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import * as authService from '../services/auth.service';
import { mergeGuestCart } from '../services/cart.service';
import { successResponse } from '../utils/response';

const router = Router();

router.post(
  '/register',
  validate([
    body('name').isString().trim().notEmpty(),
    body('email').isEmail(),
    body('password').isString().isLength({ min: 6 }),
    body('confirmPassword').isString(),
  ]),
  async (req, res, next) => {
    try {
      const data = await authService.registerCustomer({
        name: req.body.name,
        email: req.body.email,
        phone: req.body.phone,
        password: req.body.password,
        confirmPassword: req.body.confirmPassword ?? req.body.confirm_password,
      });
      res.status(201).json(successResponse(data.message, data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/login',
  validate([body('email').isEmail(), body('password').isString().notEmpty()]),
  async (req, res, next) => {
    try {
      const data = await authService.loginCustomer(req.body.email, req.body.password);
      const guestId = typeof req.headers['x-guest-id'] === 'string' ? req.headers['x-guest-id'] : '';
      if (guestId) {
        await mergeGuestCart(data.customer.id, guestId);
      }
      res.json(successResponse('Login successful', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post('/guest', async (req, res, next) => {
  try {
    const data = await authService.createGuestSession();
    const guestId = typeof req.headers['x-guest-id'] === 'string' ? req.headers['x-guest-id'] : '';
    if (guestId) {
      await mergeGuestCart(data.customer.id, guestId);
    }
    res.status(201).json(successResponse('Guest session started', data));
  } catch (err) {
    next(err);
  }
});

router.post(
  '/google',
  validate([body('credential').isString().notEmpty()]),
  async (req, res, next) => {
    try {
      const data = await authService.loginWithGoogle(req.body.credential);
      const guestId = typeof req.headers['x-guest-id'] === 'string' ? req.headers['x-guest-id'] : '';
      if (guestId) {
        await mergeGuestCart(data.customer.id, guestId);
      }
      res.json(successResponse('Google sign-in successful', data));
    } catch (err) {
      next(err);
    }
  },
);

router.post(
  '/verify',
  validate([body('token').isString().notEmpty()]),
  async (req, res, next) => {
    try {
      const data = await authService.verifyEmail(req.body.token ?? req.query.token);
      res.json(successResponse(data.message, data));
    } catch (err) {
      next(err);
    }
  },
);

router.get('/verify', async (req, res, next) => {
  try {
    const token = String(req.query.token ?? '');
    const data = await authService.verifyEmail(token);
    res.json(successResponse(data.message, data));
  } catch (err) {
    next(err);
  }
});

router.post(
  '/refresh',
  validate([body('refreshToken').isString().notEmpty()]),
  async (req, res, next) => {
    try {
      const data = await authService.refreshSession(req.body.refreshToken);
      res.json(successResponse('Token refreshed', data));
    } catch (err) {
      next(err);
    }
  },
);

router.get('/me', authenticate, async (req: AuthRequest, res, next) => {
  try {
    const profile = await authService.getProfile(req.user!.id);
    res.json(successResponse('Profile retrieved', profile));
  } catch (err) {
    next(err);
  }
});

router.patch(
  '/me',
  authenticate,
  validate([body('name').isString().trim().notEmpty()]),
  async (req: AuthRequest, res, next) => {
    try {
      const profile = await authService.updateProfile(req.user!.id, {
        name: req.body.name,
        phone: req.body.phone,
        address: req.body.address,
        city: req.body.city,
        pincode: req.body.pincode,
      });
      res.json(successResponse('Your details have been updated successfully.', profile));
    } catch (err) {
      next(err);
    }
  },
);

router.get('/session', optionalAuth, async (req: AuthRequest, res) => {
  if (!req.user) {
    res.json(successResponse('Guest', { authenticated: false }));
    return;
  }
  const profile = await authService.getProfile(req.user.id);
  res.json(successResponse('Authenticated', { authenticated: true, customer: profile }));
});

export default router;
