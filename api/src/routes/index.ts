import { Router } from 'express';
import authRoutes from './auth.routes';
import productRoutes from './product.routes';
import categoryRoutes from './category.routes';
import cartRoutes from './cart.routes';
import { checkoutRouter, orderRouter } from './order.routes';
import {
  wishlistRouter,
  reviewRouter,
  addressRouter,
  couponRouter,
  settingsRouter,
} from './misc.routes';

const router = Router();

router.use('/auth', authRoutes);
router.use('/products', productRoutes);
router.use('/categories', categoryRoutes);
router.use('/cart', cartRoutes);
router.use('/checkout', checkoutRouter);
router.use('/orders', orderRouter);
router.use('/wishlist', wishlistRouter);
router.use('/reviews', reviewRouter);
router.use('/addresses', addressRouter);
router.use('/coupons', couponRouter);
router.use('/settings', settingsRouter);

export default router;
