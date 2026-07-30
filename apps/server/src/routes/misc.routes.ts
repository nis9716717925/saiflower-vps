import { Router } from 'express';
import { body } from 'express-validator';
import type { AuthRequest } from '../middleware/auth';
import { authenticate } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import * as wishlistService from '../services/wishlist.service';
import * as reviewService from '../services/review.service';
import * as categoryService from '../services/category.service';
import * as searchService from '../services/search.service';
import { successResponse } from '../utils/response';

export const wishlistRouter = Router();
export const reviewRouter = Router();
export const categoryRouter = Router();
export const searchRouter = Router();
export const settingsRouter = Router();

wishlistRouter.use(authenticate);

wishlistRouter.get('/', async (req: AuthRequest, res, next) => {
  try {
    const items = await wishlistService.listWishlist(req.user!.id);
    res.json(successResponse('Wishlist retrieved', items));
  } catch (err) {
    next(err);
  }
});

wishlistRouter.post(
  '/toggle',
  validate([body('product_id').isInt({ min: 1 })]),
  async (req: AuthRequest, res, next) => {
    try {
      const result = await wishlistService.toggleWishlist(
        req.user!.id,
        Number(req.body.product_id ?? req.body.productId),
        req.body.type,
      );
      res.json(successResponse(result.message, result));
    } catch (err) {
      next(err);
    }
  },
);

reviewRouter.get('/', async (req, res, next) => {
  try {
    const limit = req.query.limit ? Number(req.query.limit) : 50;
    const reviews = await reviewService.listReviews(limit);
    res.json(successResponse('Reviews retrieved', reviews));
  } catch (err) {
    next(err);
  }
});

reviewRouter.post(
  '/',
  validate([body('comment').isString().trim().notEmpty()]),
  async (req, res, next) => {
    try {
      const result = await reviewService.submitReview({
        name: req.body.name,
        rating: req.body.rating != null ? Number(req.body.rating) : 5,
        comment: req.body.comment,
      });
      res.status(201).json(successResponse(result.message, result));
    } catch (err) {
      next(err);
    }
  },
);

categoryRouter.get('/', async (_req, res, next) => {
  try {
    const categories = await categoryService.listCategories();
    res.json(successResponse('Categories retrieved', categories));
  } catch (err) {
    next(err);
  }
});

searchRouter.get('/', async (req, res, next) => {
  try {
    const q = typeof req.query.q === 'string' ? req.query.q : '';
    const data = await searchService.searchSuggest(q);
    // Match ajax_search.php top-level shape for drop-in clients
    res.json(data);
  } catch (err) {
    next(err);
  }
});

settingsRouter.get('/', async (_req, res, next) => {
  try {
    const settings = await categoryService.getSettings();
    res.json(successResponse('Settings retrieved', settings));
  } catch (err) {
    next(err);
  }
});
