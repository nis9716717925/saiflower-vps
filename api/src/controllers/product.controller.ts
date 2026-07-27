import { Request, Response, NextFunction } from 'express';
import { productService } from '../services/product.service';
import { successResponse } from '../utils/response';
import { paramAsString } from '../utils/params';

export class ProductController {
  list = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const filters = {
        categoryId: req.query.categoryId as string | undefined,
        categorySlug: req.query.category as string | undefined,
        brandId: req.query.brandId as string | undefined,
        brandSlug: req.query.brand as string | undefined,
        minPrice: req.query.minPrice ? parseFloat(req.query.minPrice as string) : undefined,
        maxPrice: req.query.maxPrice ? parseFloat(req.query.maxPrice as string) : undefined,
        minRating: req.query.minRating ? parseFloat(req.query.minRating as string) : undefined,
        isFeatured: req.query.featured === 'true' ? true : undefined,
        inStock: req.query.inStock === 'true' ? true : undefined,
      };
      const result = await productService.list(req.query as Record<string, unknown>, filters);
      res.json(successResponse('Products retrieved', result.items, result.meta));
    } catch (err) {
      next(err);
    }
  };

  getBySlug = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const product = await productService.getBySlug(paramAsString(req.params.slug));
      res.json(successResponse('Product retrieved', product));
    } catch (err) {
      next(err);
    }
  };

  getRelated = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const limit = req.query.limit ? parseInt(req.query.limit as string, 10) : 8;
      const products = await productService.getRelated(paramAsString(req.params.slug), limit);
      res.json(successResponse('Related products retrieved', products));
    } catch (err) {
      next(err);
    }
  };

  checkStock = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const stock = await productService.checkStock(
        paramAsString(req.params.productId),
        req.query.variantId as string | undefined,
      );
      res.json(successResponse('Stock availability', stock));
    } catch (err) {
      next(err);
    }
  };
}

export const productController = new ProductController();
