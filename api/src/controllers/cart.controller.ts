import { Response, NextFunction } from 'express';
import { cartService } from '../services/cart.service';
import { successResponse } from '../utils/response';
import { AuthRequest } from '../middleware/auth';
import { BadRequestError } from '../utils/errors';
import { paramAsString } from '../utils/params';

export class CartController {
  getCart = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const cart = await cartService.getOrCreateCart(req.user?.id, req.guestId);
      res.json(successResponse('Cart retrieved', cart));
    } catch (err) {
      next(err);
    }
  };

  mergeCart = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      if (!req.guestId) throw new BadRequestError('Guest ID required');
      const cart = await cartService.mergeGuestCart(req.user!.id, req.guestId);
      res.json(successResponse('Cart merged', cart));
    } catch (err) {
      next(err);
    }
  };

  addItem = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const cartData = await cartService.getOrCreateCart(req.user?.id, req.guestId);
      const cart = await cartService.addItem(
        cartData.id as string,
        req.body.productId,
        req.body.quantity,
        req.body.variantId,
      );
      res.status(201).json(successResponse('Item added to cart', cart));
    } catch (err) {
      next(err);
    }
  };

  updateItem = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const cartData = await cartService.getOrCreateCart(req.user?.id, req.guestId);
      const cart = await cartService.updateItem(
        paramAsString(req.params.itemId),
        req.body.quantity,
        cartData.id as string,
      );
      res.json(successResponse('Cart updated', cart));
    } catch (err) {
      next(err);
    }
  };

  removeItem = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const cartData = await cartService.getOrCreateCart(req.user?.id, req.guestId);
      const cart = await cartService.removeItem(paramAsString(req.params.itemId), cartData.id as string);
      res.json(successResponse('Item removed', cart));
    } catch (err) {
      next(err);
    }
  };

  clearCart = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const cartData = await cartService.getOrCreateCart(req.user?.id, req.guestId);
      const cart = await cartService.clearCart(cartData.id as string);
      res.json(successResponse('Cart cleared', cart));
    } catch (err) {
      next(err);
    }
  };
}

export const cartController = new CartController();
