import { Router } from 'express';
import { authController } from '../controllers/auth.controller';
import { authenticate } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import { authRateLimiter } from '../middleware/rateLimiter';
import {
  registerValidator,
  loginValidator,
  refreshTokenValidator,
  forgotPasswordValidator,
  resetPasswordValidator,
  socialLoginValidator,
} from '../validators';

const router = Router();

router.post('/register', authRateLimiter, validate(registerValidator), authController.register);
router.post('/login', authRateLimiter, validate(loginValidator), authController.login);
router.post('/social', authRateLimiter, validate(socialLoginValidator), authController.socialLogin);
router.post('/refresh', validate(refreshTokenValidator), authController.refresh);
router.post('/logout', validate(refreshTokenValidator), authController.logout);
router.post('/forgot-password', authRateLimiter, validate(forgotPasswordValidator), authController.forgotPassword);
router.post('/reset-password', authRateLimiter, validate(resetPasswordValidator), authController.resetPassword);

router.get('/profile', authenticate, authController.profile);
router.post('/logout-all', authenticate, authController.logoutAll);

export default router;
