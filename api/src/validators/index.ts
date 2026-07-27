import { body, param, query } from 'express-validator';

export const registerValidator = [
  body('email').isEmail().normalizeEmail().withMessage('Valid email is required'),
  body('password').isLength({ min: 8 }).withMessage('Password must be at least 8 characters'),
  body('firstName').trim().notEmpty().withMessage('First name is required'),
  body('lastName').trim().notEmpty().withMessage('Last name is required'),
  body('phone').optional().isMobilePhone('any'),
];

export const loginValidator = [
  body('email').isEmail().normalizeEmail(),
  body('password').notEmpty(),
];

export const refreshTokenValidator = [
  body('refreshToken').notEmpty().withMessage('Refresh token is required'),
];

export const forgotPasswordValidator = [
  body('email').isEmail().normalizeEmail(),
];

export const resetPasswordValidator = [
  body('token').notEmpty(),
  body('password').isLength({ min: 8 }),
];

export const socialLoginValidator = [
  body('provider').isIn(['GOOGLE', 'FACEBOOK']),
  body('idToken').optional().isString().notEmpty(),
  body('providerId').optional().isString().notEmpty(),
  body('email').optional().isEmail().normalizeEmail(),
  body('firstName').optional().trim().notEmpty(),
  body('lastName').optional().trim().notEmpty(),
  body().custom((_, { req }) => {
    if (req.body.provider === 'GOOGLE' && !req.body.idToken) {
      throw new Error('idToken is required for Google login');
    }
    if (req.body.provider === 'FACEBOOK') {
      if (!req.body.providerId || !req.body.email || !req.body.firstName) {
        throw new Error('providerId, email, and firstName are required for Facebook login');
      }
    }
    return true;
  }),
];

export const productListValidator = [
  query('page').optional().isInt({ min: 1 }),
  query('limit').optional().isInt({ min: 1, max: 100 }),
  query('sortBy').optional().isIn(['createdAt', 'basePrice', 'ratingAvg', 'name']),
  query('sortOrder').optional().isIn(['asc', 'desc']),
  query('minPrice').optional().isFloat({ min: 0 }),
  query('maxPrice').optional().isFloat({ min: 0 }),
  query('minRating').optional().isFloat({ min: 0, max: 5 }),
];

export const cartItemValidator = [
  body('productId').isUUID(),
  body('variantId').optional().isUUID(),
  body('quantity').isInt({ min: 1 }).withMessage('Quantity must be at least 1'),
];

export const updateCartItemValidator = [
  param('itemId').isUUID(),
  body('quantity').isInt({ min: 1 }),
];

export const addressValidator = [
  body('fullName').trim().notEmpty(),
  body('phone').trim().notEmpty(),
  body('addressLine1').trim().notEmpty(),
  body('city').trim().notEmpty(),
  body('state').trim().notEmpty(),
  body('postalCode').trim().notEmpty(),
  body('country').optional().trim(),
  body('label').optional().trim(),
  body('addressLine2').optional().trim(),
  body('isDefault').optional().isBoolean(),
];

export const reviewValidator = [
  body('rating').isInt({ min: 1, max: 5 }),
  body('title').optional().trim().isLength({ max: 200 }),
  body('comment').optional().trim().isLength({ max: 2000 }),
];

export const couponValidator = [
  body('code').trim().notEmpty(),
  body('orderAmount').optional().isFloat({ min: 0 }),
];

export const checkoutSummaryValidator = [
  body('addressId').optional().isUUID(),
  body('couponCode').optional().trim(),
  body('deliverySlotId').optional().isUUID(),
];

export const placeOrderValidator = [
  body('addressId').isUUID(),
  body('deliverySlotId').optional().isUUID(),
  body('couponCode').optional().trim(),
  body('paymentProvider').isIn(['STRIPE', 'PAYPAL', 'RAZORPAY', 'COD']),
  body('paymentRef').optional().trim(),
  body('notes').optional().trim().isLength({ max: 500 }),
];

export const cancelOrderValidator = [
  body('reason').optional().trim().isLength({ max: 500 }),
];

export const uuidParam = (name: string) => [param(name).isUUID()];
