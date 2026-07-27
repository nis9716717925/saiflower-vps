import { Request, Response, NextFunction } from 'express';
import { sanitizeObject } from '../utils/sanitize';

export const sanitizeBody = (req: Request, _res: Response, next: NextFunction) => {
  if (req.body && typeof req.body === 'object') {
    req.body = sanitizeObject(req.body);
  }
  next();
};
