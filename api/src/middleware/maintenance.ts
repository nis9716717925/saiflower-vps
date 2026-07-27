import { Request, Response, NextFunction } from 'express';
import { config } from '../config';
import { errorResponse } from '../utils/response';

export const maintenanceMode = (_req: Request, res: Response, next: NextFunction) => {
  if (config.app.maintenanceMode) {
    return res.status(503).json(
      errorResponse('Service is under maintenance. Please try again later.'),
    );
  }
  next();
};
