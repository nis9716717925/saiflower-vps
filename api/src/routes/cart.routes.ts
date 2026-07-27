import { Router } from 'express';
import { cartController } from '../controllers/cart.controller';
import { authenticate, optionalAuth, guestCart } from '../middleware/auth';
import { validate } from '../middleware/errorHandler';
import { cartItemValidator, updateCartItemValidator } from '../validators';

const router = Router();

router.use(optionalAuth, guestCart);

router.get('/', cartController.getCart);
router.post('/items', validate(cartItemValidator), cartController.addItem);
router.patch('/items/:itemId', validate(updateCartItemValidator), cartController.updateItem);
router.delete('/items/:itemId', cartController.removeItem);
router.delete('/', cartController.clearCart);
router.post('/merge', authenticate, cartController.mergeCart);

export default router;
