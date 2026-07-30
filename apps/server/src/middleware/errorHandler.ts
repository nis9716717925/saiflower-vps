import type { NextFunction, Request, Response } from 'express';
import { validationResult, type ValidationChain } from 'express-validator';
import { config } from '../config';
import { AppError, ValidationError } from '../utils/errors';
import { errorResponse } from '../utils/response';

export const validate = (validations: ValidationChain[]) => {
  return async (req: Request, _res: Response, next: NextFunction) => {
    await Promise.all(validations.map((v) => v.run(req)));

    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      const formatted: Record<string, string[]> = {};
      for (const err of errors.array()) {
        const field = 'path' in err ? String(err.path) : 'general';
        if (!formatted[field]) formatted[field] = [];
        formatted[field].push(err.msg);
      }
      return next(new ValidationError('Validation failed', formatted));
    }
    next();
  };
};

export const errorHandler = (
  err: Error,
  _req: Request,
  res: Response,
  _next: NextFunction,
) => {
  if (err instanceof AppError) {
    return res.status(err.statusCode).json(errorResponse(err.message, err.errors));
  }

  if (!config.isProduction) {
    console.error(err);
  }

  return res.status(500).json(errorResponse('Internal server error'));
};

export const notFoundHandler = (_req: Request, res: Response) => {
  res.status(404).json(errorResponse('Route not found'));
};
