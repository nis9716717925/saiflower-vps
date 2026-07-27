import { Request, Response, NextFunction } from 'express';
import { wishlistService } from '../services/wishlist.service';
import { reviewService } from '../services/review.service';
import { addressService } from '../services/address.service';
import { couponService } from '../services/coupon.service';
import { settingsService } from '../services/settings.service';
import { successResponse } from '../utils/response';
import { AuthRequest } from '../middleware/auth';
import { paramAsString } from '../utils/params';

export class WishlistController {
  list = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await wishlistService.list(req.user!.id, req.query as Record<string, unknown>);
      res.json(successResponse('Wishlist retrieved', result.items, result.meta));
    } catch (err) {
      next(err);
    }
  };

  add = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const item = await wishlistService.add(req.user!.id, paramAsString(req.params.productId));
      res.status(201).json(successResponse('Added to wishlist', item));
    } catch (err) {
      next(err);
    }
  };

  remove = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await wishlistService.remove(req.user!.id, paramAsString(req.params.productId));
      res.json(successResponse(result.message));
    } catch (err) {
      next(err);
    }
  };
}

export class ReviewController {
  list = async (req: Request, res: Response, next: NextFunction) => {
    try {
      const result = await reviewService.listByProduct(
        paramAsString(req.params.productId),
        req.query as Record<string, unknown>,
      );
      res.json(successResponse('Reviews retrieved', result.items, result.meta));
    } catch (err) {
      next(err);
    }
  };

  create = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const review = await reviewService.create(req.user!.id, paramAsString(req.params.productId), req.body);
      res.status(201).json(successResponse('Review submitted', review));
    } catch (err) {
      next(err);
    }
  };

  update = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const review = await reviewService.update(req.user!.id, paramAsString(req.params.reviewId), req.body);
      res.json(successResponse('Review updated', review));
    } catch (err) {
      next(err);
    }
  };

  delete = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await reviewService.delete(req.user!.id, paramAsString(req.params.reviewId));
      res.json(successResponse(result.message));
    } catch (err) {
      next(err);
    }
  };
}

export class AddressController {
  list = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const addresses = await addressService.list(req.user!.id);
      res.json(successResponse('Addresses retrieved', addresses));
    } catch (err) {
      next(err);
    }
  };

  getById = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const address = await addressService.getById(req.user!.id, paramAsString(req.params.addressId));
      res.json(successResponse('Address retrieved', address));
    } catch (err) {
      next(err);
    }
  };

  create = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const address = await addressService.create(req.user!.id, req.body);
      res.status(201).json(successResponse('Address created', address));
    } catch (err) {
      next(err);
    }
  };

  update = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const address = await addressService.update(req.user!.id, paramAsString(req.params.addressId), req.body);
      res.json(successResponse('Address updated', address));
    } catch (err) {
      next(err);
    }
  };

  delete = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await addressService.delete(req.user!.id, paramAsString(req.params.addressId));
      res.json(successResponse(result.message));
    } catch (err) {
      next(err);
    }
  };

  setDefault = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const address = await addressService.setDefault(req.user!.id, paramAsString(req.params.addressId));
      res.json(successResponse('Default address updated', address));
    } catch (err) {
      next(err);
    }
  };
}

export class CouponController {
  validate = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const orderAmount = req.body.orderAmount ?? 0;
      const result = await couponService.validate(req.body.code, req.user!.id, orderAmount);
      res.json(successResponse('Coupon valid', result));
    } catch (err) {
      next(err);
    }
  };
}

export class SettingsController {
  get = async (_req: Request, res: Response, next: NextFunction) => {
    try {
      const settings = await settingsService.getPublicSettings();
      res.json(successResponse('Settings retrieved', settings));
    } catch (err) {
      next(err);
    }
  };
}

export const wishlistController = new WishlistController();
export const reviewController = new ReviewController();
export const addressController = new AddressController();
export const couponController = new CouponController();
export const settingsController = new SettingsController();
