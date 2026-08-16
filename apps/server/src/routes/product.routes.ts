import { Router } from 'express';
import * as productService from '../services/product.service';
import { successResponse, buildPaginationMeta } from '../utils/response';

const router = Router();

router.get('/', async (req, res, next) => {
  try {
    const q = {
      type: typeof req.query.type === 'string' ? req.query.type : 'flower',
      page: req.query.page ? Number(req.query.page) : 1,
      limit: req.query.limit ? Number(req.query.limit) : 24,
      sort: typeof req.query.sort === 'string' ? req.query.sort : undefined,
      search: typeof req.query.search === 'string' ? req.query.search : undefined,
      category: typeof req.query.category === 'string' ? req.query.category : undefined,
      price_min: req.query.price_min != null ? Number(req.query.price_min) : undefined,
      price_max: req.query.price_max != null ? Number(req.query.price_max) : undefined,
      in_stock: req.query.in_stock != null ? Number(req.query.in_stock) : undefined,
    };
    const { items, total, page, limit } = await productService.listProducts(q);
    res.json(
      successResponse('Products retrieved', items, buildPaginationMeta(page, limit, total)),
    );
  } catch (err) {
    next(err);
  }
});

router.get('/:type/id/:id/stock', async (req, res, next) => {
  try {
    const stock = await productService.checkStock(req.params.type, Number(req.params.id));
    res.json(successResponse('Stock checked', stock));
  } catch (err) {
    next(err);
  }
});

router.get('/:type/id/:id', async (req, res, next) => {
  try {
    const product = await productService.getProductById(req.params.type, Number(req.params.id));
    res.json(successResponse('Product retrieved', product));
  } catch (err) {
    next(err);
  }
});

router.get('/:type/:slug', async (req, res, next) => {
  try {
    const product = await productService.getProductBySlug(req.params.type, req.params.slug);
    res.json(successResponse('Product retrieved', product));
  } catch (err) {
    next(err);
  }
});

export default router;
