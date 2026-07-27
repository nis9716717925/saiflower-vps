import { Response, NextFunction } from 'express';
import { checkoutService, orderService } from '../services/order.service';
import { paymentService } from '../services/payment.service';
import { successResponse } from '../utils/response';
import { AuthRequest } from '../middleware/auth';
import { paramAsString } from '../utils/params';

export class CheckoutController {
  summary = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const summary = await checkoutService.getSummary(req.user!.id, req.body);
      res.json(successResponse('Checkout summary', summary));
    } catch (err) {
      next(err);
    }
  };

  deliverySlots = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const slots = await checkoutService.getDeliverySlots(req.query.from as string);
      res.json(successResponse('Delivery slots retrieved', slots));
    } catch (err) {
      next(err);
    }
  };

  placeOrder = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const order = await checkoutService.placeOrder(req.user!.id, req.body);
      res.status(201).json(successResponse('Order placed successfully', order));
    } catch (err) {
      next(err);
    }
  };

  paymentProviders = async (_req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const providers = paymentService.getAvailableProviders();
      res.json(successResponse('Payment providers', providers));
    } catch (err) {
      next(err);
    }
  };
}

export class OrderController {
  list = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const result = await orderService.list(req.user!.id, req.query as Record<string, unknown>);
      res.json(successResponse('Orders retrieved', result.items, result.meta));
    } catch (err) {
      next(err);
    }
  };

  getById = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const order = await orderService.getById(req.user!.id, paramAsString(req.params.orderId));
      res.json(successResponse('Order retrieved', order));
    } catch (err) {
      next(err);
    }
  };

  cancel = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const order = await orderService.cancel(req.user!.id, paramAsString(req.params.orderId), req.body.reason);
      res.json(successResponse('Order cancelled', order));
    } catch (err) {
      next(err);
    }
  };

  track = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const tracking = await orderService.track(req.user!.id, paramAsString(req.params.orderId));
      res.json(successResponse('Order tracking', tracking));
    } catch (err) {
      next(err);
    }
  };

  invoice = async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const invoice = await orderService.generateInvoice(req.user!.id, paramAsString(req.params.orderId));
      res.json(successResponse('Invoice generated', invoice));
    } catch (err) {
      next(err);
    }
  };
}

export const checkoutController = new CheckoutController();
export const orderController = new OrderController();
