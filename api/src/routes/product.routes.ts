import { Router } from 'express';
import { productController } from '../controllers/product.controller';
import { validate } from '../middleware/errorHandler';
import { productListValidator, uuidParam } from '../validators';

const router = Router();

router.get('/', validate(productListValidator), productController.list);
router.get('/:productId/stock', validate(uuidParam('productId')), productController.checkStock);
router.get('/:slug/related', productController.getRelated);
router.get('/:slug', productController.getBySlug);

export default router;
