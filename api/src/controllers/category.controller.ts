import { Request, Response, NextFunction } from 'express';
import { categoryService } from '../services/category.service';
import { successResponse } from '../utils/response';
import { paramAsString } from '../utils/params';

export class CategoryController {
  list = async (_req: Request, res: Response, next: NextFunction) => {
    try {
      const categories = await categoryService.listAll();
      res.json(successResponse('Categories retrieved', categories));
    } catch (err) {
      next(err);
    }
  };

  getBySlug = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const category = await categoryService.getBySlug(paramAsString(req.params.slug));
      res.json(successResponse('Category retrieved', category));
    } catch (err) {
      next(err);
    }
  };

  listBrands = async (_req: Request, res: Response, next: NextFunction) => {
    try {
      const brands = await categoryService.listBrands();
      res.json(successResponse('Brands retrieved', brands));
    } catch (err) {
      next(err);
    }
  };

  getBrandBySlug = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const brand = await categoryService.getBrandBySlug(paramAsString(req.params.slug));
      res.json(successResponse('Brand retrieved', brand));
    } catch (err) {
      next(err);
    }
  };
}

export const categoryController = new CategoryController();
