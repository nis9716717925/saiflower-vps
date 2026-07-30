import { Router } from 'express';
import { body } from 'express-validator';
import type { AuthRequest } from '../middleware/auth';
import { guestCart, optionalAuth } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import * as cartService from '../services/cart.service';
import { AppError } from '../utils/errors';
import { successResponse } from '../utils/response';

const router = Router();

router.use(optionalAuth, guestCart);

router.get('/', async (req: AuthRequest, res, next) => {
  try {
    const cart = await cartService.getCart(req.user?.id, req.guestId);
    if (cart.guestId) res.setHeader('X-Guest-Id', cart.guestId);
    res.json(successResponse('Cart retrieved', cart));
  } catch (err) {
    next(err);
  }
});

router.post(
  '/items',
  validate([
    body('productId').isInt({ min: 1 }),
    body('quantity').optional().isInt({ min: 1 }),
  ]),
  async (req: AuthRequest, res, next) => {
    try {
      const cart = await cartService.addToCart(
        {
          productId: Number(req.body.productId ?? req.body.product_id),
          category: req.body.category,
          quantity: Number(req.body.quantity ?? req.body.qty ?? 1),
          name: req.body.name,
          price: req.body.price != null ? Number(req.body.price) : undefined,
          image: req.body.image,
        },
        req.user?.id,
        req.guestId,
      );
      if (cart.guestId) res.setHeader('X-Guest-Id', cart.guestId);
      res.status(201).json(successResponse('Added to cart', cart));
    } catch (err) {
      next(err);
    }
  },
);

router.patch('/items/:index', async (req: AuthRequest, res, next) => {
  try {
    const index = Number(req.params.index);
    let cart;
    if (req.body.change != null) {
      cart = await cartService.updateQty(index, Number(req.body.change), req.user?.id, req.guestId);
    } else if (req.body.qty != null || req.body.quantity != null) {
      cart = await cartService.setQty(
        index,
        Number(req.body.qty ?? req.body.quantity),
        req.user?.id,
        req.guestId,
      );
    } else {
      throw new AppError('Provide change or qty', 400);
    }
    res.json(successResponse('Cart updated', cart));
  } catch (err) {
    next(err);
  }
});

router.delete('/items/:index', async (req: AuthRequest, res, next) => {
  try {
    const cart = await cartService.removeItem(
      Number(req.params.index),
      req.user?.id,
      req.guestId,
    );
    res.json(successResponse('Item removed', cart));
  } catch (err) {
    next(err);
  }
});

router.delete('/', async (req: AuthRequest, res, next) => {
  try {
    const cart = await cartService.clearCart(req.user?.id, req.guestId);
    res.json(successResponse('Cart cleared', cart));
  } catch (err) {
    next(err);
  }
});

router.post('/coupon', validate([body('code').isString().notEmpty()]), async (req: AuthRequest, res, next) => {
  try {
    const cart = await cartService.applyCouponToCart(req.body.code, req.user?.id, req.guestId);
    res.json(successResponse('Coupon applied successfully!', cart));
  } catch (err) {
    next(err);
  }
});

router.delete('/coupon', async (req: AuthRequest, res, next) => {
  try {
    const cart = await cartService.removeCouponFromCart(req.user?.id, req.guestId);
    res.json(successResponse('Promo code removed.', cart));
  } catch (err) {
    next(err);
  }
});

router.post('/merge', async (req: AuthRequest, res, next) => {
  try {
    if (!req.user) throw new AppError('Login required to merge cart', 401);
    const guestId = String(req.body.guestId ?? req.guestId ?? '');
    if (!guestId) throw new AppError('guestId required', 400);
    const cart = await cartService.mergeGuestCart(req.user.id, guestId);
    res.json(successResponse('Cart merged', cart));
  } catch (err) {
    next(err);
  }
});

export default router;
