import { Router } from 'express';
import { checkoutController, orderController } from '../controllers/order.controller';
import { authenticate } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import {
  checkoutSummaryValidator,
  placeOrderValidator,
  cancelOrderValidator,
  uuidParam,
} from '../validators';

const checkoutRouter = Router();
checkoutRouter.use(authenticate);
checkoutRouter.post('/summary', validate(checkoutSummaryValidator), checkoutController.summary);
checkoutRouter.get('/delivery-slots', checkoutController.deliverySlots);
checkoutRouter.post('/place-order', validate(placeOrderValidator), checkoutController.placeOrder);
checkoutRouter.get('/payment-providers', checkoutController.paymentProviders);

const orderRouter = Router();
orderRouter.use(authenticate);
orderRouter.get('/', orderController.list);
orderRouter.get('/:orderId', validate(uuidParam('orderId')), orderController.getById);
orderRouter.post('/:orderId/cancel', validate([...uuidParam('orderId'), ...cancelOrderValidator]), orderController.cancel);
orderRouter.get('/:orderId/track', validate(uuidParam('orderId')), orderController.track);
orderRouter.get('/:orderId/invoice', validate(uuidParam('orderId')), orderController.invoice);

export { checkoutRouter, orderRouter };
