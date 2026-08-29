import { Router } from 'express';
import { CHECKOUT_MODE, SITE } from '@saiflower/shared';
import { successResponse } from '../utils/response';
import authRoutes from './auth.routes';
import productRoutes from './product.routes';
import cartRoutes from './cart.routes';
import addressRoutes from './address.routes';
import {
  checkoutRouter,
  orderRouter,
  shippingRouter,
  couponRouter,
} from './checkout.routes';
import {
  wishlistRouter,
  reviewRouter,
  categoryRouter,
  searchRouter,
  settingsRouter,
} from './misc.routes';
import { blogRouter, faqRouter } from './blog.routes';
import { galleryRouter, eventsRouter } from './catalog-content.routes';
import { homepageRouter } from './homepage.routes';
import { pagesRouter } from './pages.routes';
import { publicCatalogCache } from '../middleware/cacheHeaders';

const router = Router();
const catalogCache = publicCatalogCache(120);

router.get('/', (_req, res) => {
  res.json(
    successResponse('SaiFlower API', {
      version: 'v1',
      phase: 3,
      checkoutMode: CHECKOUT_MODE,
      site: SITE.name,
      resources: [
        'POST /auth/register',
        'POST /auth/login',
        'POST /auth/google',
        'GET|POST /auth/verify',
        'POST /auth/refresh',
        'GET|PATCH /auth/me',
        'GET /products?type=flower|cake|gift',
        'GET /products/:type/:slug',
        'GET /categories',
        'GET /search?q=',
        'GET|POST|PATCH|DELETE /cart',
        'GET|POST|PATCH|DELETE /addresses',
        'POST /shipping/calculate',
        'GET /coupons',
        'POST /checkout/place-order',
        'GET /orders/mine',
        'GET|POST /wishlist',
        'GET|POST /reviews',
        'GET /settings',
        'GET /blogs',
        'GET /blogs/:slug',
        'GET /faqs?page=',
        'GET /gallery',
        'GET /gallery/:id',
        'GET /events',
        'GET /events/:slug',
        'GET /homepage/slides',
        'GET /pages',
        'GET /pages/:slug',
      ],
    }),
  );
});

router.use('/auth', authRoutes);
router.use('/products', catalogCache, productRoutes);
router.use('/categories', catalogCache, categoryRouter);
router.use('/search', searchRouter);
router.use('/cart', cartRoutes);
router.use('/addresses', addressRoutes);
router.use('/shipping', shippingRouter);
router.use('/coupons', couponRouter);
router.use('/checkout', checkoutRouter);
router.use('/orders', orderRouter);
router.use('/wishlist', wishlistRouter);
router.use('/reviews', reviewRouter);
router.use('/settings', catalogCache, settingsRouter);
router.use('/blogs', catalogCache, blogRouter);
router.use('/faqs', catalogCache, faqRouter);
router.use('/gallery', catalogCache, galleryRouter);
router.use('/events', catalogCache, eventsRouter);
router.use('/homepage', catalogCache, homepageRouter);
router.use('/pages', catalogCache, pagesRouter);

export default router;
