import { Router } from 'express';
import { body, query } from 'express-validator';
import type { AuthRequest } from '../middleware/auth';
import { guestCart, optionalAuth, authenticate } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import * as orderService from '../services/order.service';
import * as shippingService from '../services/shipping.service';
import * as couponService from '../services/coupon.service';
import { getCart } from '../services/cart.service';
import { NotFoundError } from '../utils/errors';
import { successResponse } from '../utils/response';
import { config } from '../config';

export const checkoutRouter = Router();
export const orderRouter = Router();
export const shippingRouter = Router();
export const couponRouter = Router();

checkoutRouter.use(optionalAuth, guestCart);

checkoutRouter.get('/summary', async (req: AuthRequest, res, next) => {
  try {
    const cart = await getCart(req.user?.id, req.guestId);
    res.json(
      successResponse('Checkout summary', {
        cart,
        checkoutMode: config.checkout.mode,
        shippingRatePerKm: config.shipping.ratePerKm,
        storeAddress: config.shipping.storeAddress,
      }),
    );
  } catch (err) {
    next(err);
  }
});

checkoutRouter.post(
  '/place-order',
  validate([
    body('name').isString().trim().notEmpty(),
    body('phone').isString().trim().notEmpty(),
    body('address').isString().trim().notEmpty(),
  ]),
  async (req: AuthRequest, res, next) => {
    try {
      const result = await orderService.placeOrder({
        name: req.body.name,
        phone: req.body.phone,
        email: req.body.email,
        address: req.body.address,
        date: req.body.date,
        items: req.body.items,
        total: req.body.total != null ? Number(req.body.total) : undefined,
        shipping_fee: req.body.shipping_fee != null ? Number(req.body.shipping_fee) : undefined,
        distance_km: req.body.distance_km != null ? Number(req.body.distance_km) : undefined,
        discount_amount:
          req.body.discount_amount != null ? Number(req.body.discount_amount) : undefined,
        coupon_code: req.body.coupon_code,
        recipient_name: req.body.recipient_name,
        recipient_phone: req.body.recipient_phone,
        delivery_time: req.body.delivery_time ?? req.body.time,
        address_id: req.body.address_id != null ? Number(req.body.address_id) : undefined,
        ordering_for_me: Boolean(req.body.ordering_for_me),
        latitude: req.body.latitude != null ? Number(req.body.latitude) : null,
        longitude: req.body.longitude != null ? Number(req.body.longitude) : null,
        userId: req.user?.id,
        guestId: req.guestId,
      });
      res.status(201).json(successResponse(result.message, result));
    } catch (err) {
      next(err);
    }
  },
);

shippingRouter.post('/calculate', async (req, res, next) => {
  try {
    const result = await shippingService.calculateShippingParts({
      address_line: req.body.address_line,
      city: req.body.city,
      zip: req.body.zip,
    });
    // PHP returns raw { status, ... } — keep that shape under data for clients that expect it
    if (result.status === 'ok') {
      res.json(successResponse('Shipping calculated', result));
    } else {
      res.status(400).json({ success: false, message: result.message, data: result });
    }
  } catch (err) {
    next(err);
  }
});

shippingRouter.get(
  '/address-suggestions',
  optionalAuth,
  guestCart,
  validate([query('input').isString().trim().isLength({ min: 3, max: 200 })]),
  async (req, res, next) => {
    try {
      const suggestions = await shippingService.autocompleteAddress(String(req.query.input));
      res.json(successResponse('Address suggestions retrieved', suggestions));
    } catch (err) {
      next(err);
    }
  },
);

shippingRouter.post(
  '/place-details',
  optionalAuth,
  guestCart,
  validate([body('placeId').isString().trim().notEmpty()]),
  async (req, res, next) => {
    try {
      const address = await shippingService.getPlaceAddress(req.body.placeId);
      res.json(successResponse('Address retrieved', address));
    } catch (err) {
      next(err);
    }
  },
);

shippingRouter.post(
  '/reverse-geocode',
  optionalAuth,
  guestCart,
  validate([
    body('latitude').isFloat({ min: -90, max: 90 }),
    body('longitude').isFloat({ min: -180, max: 180 }),
  ]),
  async (req, res, next) => {
    try {
      const address = await shippingService.reverseGeocode(
        Number(req.body.latitude),
        Number(req.body.longitude),
      );
      res.json(successResponse('Current location detected', address));
    } catch (err) {
      next(err);
    }
  },
);

couponRouter.get('/', async (_req, res, next) => {
  try {
    const coupons = await couponService.listActiveCoupons();
    res.json(successResponse('Coupons retrieved', coupons));
  } catch (err) {
    next(err);
  }
});

couponRouter.post(
  '/validate',
  validate([body('code').isString().notEmpty(), body('subtotal').optional().isFloat({ min: 0 })]),
  async (req, res, next) => {
    try {
      const coupon = await couponService.validateCoupon(
        req.body.code,
        Number(req.body.subtotal ?? 0),
      );
      res.json(successResponse('Coupon valid', coupon));
    } catch (err) {
      next(err);
    }
  },
);

orderRouter.get('/mine', authenticate, async (req: AuthRequest, res, next) => {
  try {
    const profile = req.user!;
    // PHP orders keyed by phone on place; list by customer phone from profile
    const { getProfile } = await import('../services/auth.service');
    const me = await getProfile(profile.id);
    const orders = me.phone ? await orderService.listOrdersByPhone(me.phone) : [];
    res.json(successResponse('Orders retrieved', orders));
  } catch (err) {
    next(err);
  }
});

orderRouter.get('/:id', optionalAuth, async (req, res, next) => {
  try {
    const order = await orderService.getOrderById(Number(req.params.id));
    if (!order) throw new NotFoundError('Order not found');
    res.json(successResponse('Order retrieved', order));
  } catch (err) {
    next(err);
  }
});
