import { Router } from 'express';
import {
  wishlistController,
  reviewController,
  addressController,
  couponController,
  settingsController,
} from '../controllers/misc.controller';
import { authenticate } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import {
  addressValidator,
  reviewValidator,
  couponValidator,
  uuidParam,
} from '../validators';

const wishlistRouter = Router();
wishlistRouter.use(authenticate);
wishlistRouter.get('/', wishlistController.list);
wishlistRouter.post('/:productId', validate(uuidParam('productId')), wishlistController.add);
wishlistRouter.delete('/:productId', validate(uuidParam('productId')), wishlistController.remove);

const reviewRouter = Router();
reviewRouter.get('/product/:productId', validate(uuidParam('productId')), reviewController.list);
reviewRouter.post(
  '/product/:productId',
  authenticate,
  validate([...uuidParam('productId'), ...reviewValidator]),
  reviewController.create,
);
reviewRouter.patch(
  '/:reviewId',
  authenticate,
  validate([...uuidParam('reviewId'), ...reviewValidator]),
  reviewController.update,
);
reviewRouter.delete('/:reviewId', authenticate, validate(uuidParam('reviewId')), reviewController.delete);

const addressRouter = Router();
addressRouter.use(authenticate);
addressRouter.get('/', addressController.list);
addressRouter.post('/', validate(addressValidator), addressController.create);
addressRouter.get('/:addressId', validate(uuidParam('addressId')), addressController.getById);
addressRouter.patch('/:addressId', validate([...uuidParam('addressId'), ...addressValidator]), addressController.update);
addressRouter.delete('/:addressId', validate(uuidParam('addressId')), addressController.delete);
addressRouter.patch('/:addressId/default', validate(uuidParam('addressId')), addressController.setDefault);

const couponRouter = Router();
couponRouter.post('/validate', authenticate, validate(couponValidator), couponController.validate);

const settingsRouter = Router();
settingsRouter.get('/', settingsController.get);

export {
  wishlistRouter,
  reviewRouter,
  addressRouter,
  couponRouter,
  settingsRouter,
};
