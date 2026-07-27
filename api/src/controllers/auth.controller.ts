import { Response, NextFunction } from 'express';
import { authService } from '../services/auth.service';
import { successResponse } from '../utils/response';
import { AuthRequest } from '../middleware/auth';

export class AuthController {
  register = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await authService.register(req.body);
      res.status(201).json(successResponse('Registration successful', result));
    } catch (err) {
      next(err);
    }
  };

  login = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await authService.login(req.body.email, req.body.password);
      res.json(successResponse('Login successful', result));
    } catch (err) {
      next(err);
    }
  };

  socialLogin = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await authService.socialLogin(req.body);
      res.json(successResponse('Social login successful', result));
    } catch (err) {
      next(err);
    }
  };

  refresh = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await authService.refresh(req.body.refreshToken);
      res.json(successResponse('Token refreshed', result));
    } catch (err) {
      next(err);
    }
  };

  logout = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      if (req.body.refreshToken) {
        await authService.logout(req.body.refreshToken);
      }
      res.json(successResponse('Logged out successfully'));
    } catch (err) {
      next(err);
    }
  };

  logoutAll = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      await authService.logoutAll(req.user!.id);
      res.json(successResponse('Logged out from all devices'));
    } catch (err) {
      next(err);
    }
  };

  forgotPassword = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await authService.forgotPassword(req.body.email);
      res.json(successResponse(result.message, result));
    } catch (err) {
      next(err);
    }
  };

  resetPassword = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await authService.resetPassword(req.body.token, req.body.password);
      res.json(successResponse(result.message));
    } catch (err) {
      next(err);
    }
  };

  profile = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const user = await authService.getProfile(req.user!.id);
      res.json(successResponse('Profile retrieved', user));
    } catch (err) {
      next(err);
    }
  };
}

export const authController = new AuthController();
