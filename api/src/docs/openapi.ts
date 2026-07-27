export const openApiSpec = {
  openapi: '3.0.3',
  info: {
    title: 'Saiflower eCommerce API',
    version: '1.0.0',
    description:
      'Production-ready REST API for Saiflower eCommerce platform. Serves web and mobile clients with JWT authentication, full cart/checkout flow, and multi-provider payments.',
    contact: { name: 'Saiflower API Support', email: 'support@saiflower.com' },
  },
  servers: [
    { url: 'http://localhost:4000/api/v1', description: 'Local development' },
    { url: 'https://api.saiflower.com/api/v1', description: 'Production' },
  ],
  tags: [
    { name: 'Auth', description: 'Authentication & user management' },
    { name: 'Products', description: 'Product catalog' },
    { name: 'Categories', description: 'Categories & brands' },
    { name: 'Cart', description: 'Shopping cart (guest & authenticated)' },
    { name: 'Checkout', description: 'Checkout flow' },
    { name: 'Orders', description: 'Order management' },
    { name: 'Wishlist', description: 'User wishlist' },
    { name: 'Reviews', description: 'Product reviews & ratings' },
    { name: 'Addresses', description: 'Address book' },
    { name: 'Coupons', description: 'Coupon validation' },
    { name: 'Settings', description: 'App settings' },
  ],
  components: {
    securitySchemes: {
      bearerAuth: { type: 'http', scheme: 'bearer', bearerFormat: 'JWT' },
      guestId: { type: 'apiKey', in: 'header', name: 'X-Guest-Id' },
    },
    schemas: {
      ApiResponse: {
        type: 'object',
        properties: {
          success: { type: 'boolean' },
          message: { type: 'string' },
          data: { type: 'object' },
          errors: { type: 'object', additionalProperties: { type: 'array', items: { type: 'string' } } },
          meta: {
            type: 'object',
            properties: {
              page: { type: 'integer' },
              limit: { type: 'integer' },
              total: { type: 'integer' },
              totalPages: { type: 'integer' },
              hasNextPage: { type: 'boolean' },
              hasPrevPage: { type: 'boolean' },
            },
          },
        },
      },
      RegisterRequest: {
        type: 'object',
        required: ['email', 'password', 'firstName', 'lastName'],
        properties: {
          email: { type: 'string', format: 'email' },
          password: { type: 'string', minLength: 8 },
          firstName: { type: 'string' },
          lastName: { type: 'string' },
          phone: { type: 'string' },
        },
      },
      LoginRequest: {
        type: 'object',
        required: ['email', 'password'],
        properties: {
          email: { type: 'string', format: 'email' },
          password: { type: 'string' },
        },
      },
      CartItemRequest: {
        type: 'object',
        required: ['productId', 'quantity'],
        properties: {
          productId: { type: 'string', format: 'uuid' },
          variantId: { type: 'string', format: 'uuid' },
          quantity: { type: 'integer', minimum: 1 },
        },
      },
      PlaceOrderRequest: {
        type: 'object',
        required: ['addressId', 'paymentProvider'],
        properties: {
          addressId: { type: 'string', format: 'uuid' },
          deliverySlotId: { type: 'string', format: 'uuid' },
          couponCode: { type: 'string' },
          paymentProvider: { type: 'string', enum: ['STRIPE', 'PAYPAL', 'RAZORPAY', 'COD'] },
          paymentRef: { type: 'string' },
          notes: { type: 'string' },
        },
      },
      AddressRequest: {
        type: 'object',
        required: ['fullName', 'phone', 'addressLine1', 'city', 'state', 'postalCode'],
        properties: {
          label: { type: 'string' },
          fullName: { type: 'string' },
          phone: { type: 'string' },
          addressLine1: { type: 'string' },
          addressLine2: { type: 'string' },
          city: { type: 'string' },
          state: { type: 'string' },
          postalCode: { type: 'string' },
          country: { type: 'string', default: 'IN' },
          isDefault: { type: 'boolean' },
        },
      },
      ReviewRequest: {
        type: 'object',
        required: ['rating'],
        properties: {
          rating: { type: 'integer', minimum: 1, maximum: 5 },
          title: { type: 'string' },
          comment: { type: 'string' },
        },
      },
    },
  },
  paths: {
    '/auth/register': {
      post: {
        tags: ['Auth'],
        summary: 'Register a new user',
        requestBody: { required: true, content: { 'application/json': { schema: { $ref: '#/components/schemas/RegisterRequest' } } } },
        responses: { 201: { description: 'User registered', content: { 'application/json': { schema: { $ref: '#/components/schemas/ApiResponse' } } } } },
      },
    },
    '/auth/login': {
      post: {
        tags: ['Auth'],
        summary: 'Login with email and password',
        requestBody: { required: true, content: { 'application/json': { schema: { $ref: '#/components/schemas/LoginRequest' } } } },
        responses: { 200: { description: 'Login successful' } },
      },
    },
    '/auth/social': {
      post: {
        tags: ['Auth'],
        summary: 'Social login (Google/Facebook)',
        requestBody: {
          required: true,
          content: {
            'application/json': {
              schema: {
                type: 'object',
                required: ['provider', 'providerId', 'email', 'firstName', 'lastName'],
                properties: {
                  provider: { type: 'string', enum: ['GOOGLE', 'FACEBOOK'] },
                  providerId: { type: 'string' },
                  email: { type: 'string' },
                  firstName: { type: 'string' },
                  lastName: { type: 'string' },
                },
              },
            },
          },
        },
        responses: { 200: { description: 'Social login successful' } },
      },
    },
    '/auth/refresh': {
      post: {
        tags: ['Auth'],
        summary: 'Refresh access token',
        requestBody: { content: { 'application/json': { schema: { type: 'object', properties: { refreshToken: { type: 'string' } } } } } },
        responses: { 200: { description: 'Token refreshed' } },
      },
    },
    '/auth/logout': {
      post: { tags: ['Auth'], summary: 'Logout (revoke refresh token)', responses: { 200: { description: 'Logged out' } } },
    },
    '/auth/logout-all': {
      post: { tags: ['Auth'], summary: 'Logout from all devices', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Logged out' } } },
    },
    '/auth/forgot-password': {
      post: { tags: ['Auth'], summary: 'Request password reset', responses: { 200: { description: 'Reset email sent' } } },
    },
    '/auth/reset-password': {
      post: { tags: ['Auth'], summary: 'Reset password with token', responses: { 200: { description: 'Password reset' } } },
    },
    '/auth/profile': {
      get: { tags: ['Auth'], summary: 'Get current user profile', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Profile' } } },
    },
    '/products': {
      get: {
        tags: ['Products'],
        summary: 'List products with search, filter, sort, pagination',
        parameters: [
          { name: 'page', in: 'query', schema: { type: 'integer' } },
          { name: 'limit', in: 'query', schema: { type: 'integer' } },
          { name: 'search', in: 'query', schema: { type: 'string' } },
          { name: 'category', in: 'query', schema: { type: 'string' } },
          { name: 'brand', in: 'query', schema: { type: 'string' } },
          { name: 'minPrice', in: 'query', schema: { type: 'number' } },
          { name: 'maxPrice', in: 'query', schema: { type: 'number' } },
          { name: 'minRating', in: 'query', schema: { type: 'number' } },
          { name: 'featured', in: 'query', schema: { type: 'boolean' } },
          { name: 'inStock', in: 'query', schema: { type: 'boolean' } },
          { name: 'sortBy', in: 'query', schema: { type: 'string', enum: ['createdAt', 'basePrice', 'ratingAvg', 'name'] } },
          { name: 'sortOrder', in: 'query', schema: { type: 'string', enum: ['asc', 'desc'] } },
        ],
        responses: { 200: { description: 'Product list' } },
      },
    },
    '/products/{slug}': {
      get: { tags: ['Products'], summary: 'Get product details', parameters: [{ name: 'slug', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Product detail' } } },
    },
    '/products/{slug}/related': {
      get: { tags: ['Products'], summary: 'Get related products', parameters: [{ name: 'slug', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Related products' } } },
    },
    '/products/{productId}/stock': {
      get: { tags: ['Products'], summary: 'Check stock availability', parameters: [{ name: 'productId', in: 'path', required: true, schema: { type: 'string' } }, { name: 'variantId', in: 'query', schema: { type: 'string' } }], responses: { 200: { description: 'Stock info' } } },
    },
    '/categories': {
      get: { tags: ['Categories'], summary: 'List all categories with subcategories', responses: { 200: { description: 'Category tree' } } },
    },
    '/categories/{slug}': {
      get: { tags: ['Categories'], summary: 'Get category by slug', parameters: [{ name: 'slug', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Category' } } },
    },
    '/categories/brands': {
      get: { tags: ['Categories'], summary: 'List all brands', responses: { 200: { description: 'Brands' } } },
    },
    '/categories/brands/{slug}': {
      get: { tags: ['Categories'], summary: 'Get brand by slug', parameters: [{ name: 'slug', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Brand' } } },
    },
    '/cart': {
      get: { tags: ['Cart'], summary: 'Get cart', security: [{ bearerAuth: [] }, { guestId: [] }], responses: { 200: { description: 'Cart' } } },
      delete: { tags: ['Cart'], summary: 'Clear cart', security: [{ bearerAuth: [] }, { guestId: [] }], responses: { 200: { description: 'Cart cleared' } } },
    },
    '/cart/items': {
      post: { tags: ['Cart'], summary: 'Add item to cart', security: [{ bearerAuth: [] }, { guestId: [] }], requestBody: { content: { 'application/json': { schema: { $ref: '#/components/schemas/CartItemRequest' } } } }, responses: { 201: { description: 'Item added' } } },
    },
    '/cart/items/{itemId}': {
      patch: { tags: ['Cart'], summary: 'Update cart item quantity', parameters: [{ name: 'itemId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Updated' } } },
      delete: { tags: ['Cart'], summary: 'Remove cart item', parameters: [{ name: 'itemId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Removed' } } },
    },
    '/cart/merge': {
      post: { tags: ['Cart'], summary: 'Merge guest cart into user cart after login', security: [{ bearerAuth: [] }, { guestId: [] }], responses: { 200: { description: 'Merged' } } },
    },
    '/checkout/summary': {
      post: { tags: ['Checkout'], summary: 'Get checkout summary', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Summary' } } },
    },
    '/checkout/delivery-slots': {
      get: { tags: ['Checkout'], summary: 'List available delivery slots', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Slots' } } },
    },
    '/checkout/payment-providers': {
      get: { tags: ['Checkout'], summary: 'List enabled payment providers', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Providers' } } },
    },
    '/checkout/place-order': {
      post: { tags: ['Checkout'], summary: 'Place order', security: [{ bearerAuth: [] }], requestBody: { content: { 'application/json': { schema: { $ref: '#/components/schemas/PlaceOrderRequest' } } } }, responses: { 201: { description: 'Order placed' } } },
    },
    '/orders': {
      get: { tags: ['Orders'], summary: 'Order history', security: [{ bearerAuth: [] }], parameters: [{ name: 'page', in: 'query', schema: { type: 'integer' } }, { name: 'status', in: 'query', schema: { type: 'string' } }], responses: { 200: { description: 'Orders' } } },
    },
    '/orders/{orderId}': {
      get: { tags: ['Orders'], summary: 'Order detail', security: [{ bearerAuth: [] }], parameters: [{ name: 'orderId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Order' } } },
    },
    '/orders/{orderId}/cancel': {
      post: { tags: ['Orders'], summary: 'Cancel order', security: [{ bearerAuth: [] }], parameters: [{ name: 'orderId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Cancelled' } } },
    },
    '/orders/{orderId}/track': {
      get: { tags: ['Orders'], summary: 'Track order', security: [{ bearerAuth: [] }], parameters: [{ name: 'orderId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Tracking' } } },
    },
    '/orders/{orderId}/invoice': {
      get: { tags: ['Orders'], summary: 'Generate invoice', security: [{ bearerAuth: [] }], parameters: [{ name: 'orderId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Invoice' } } },
    },
    '/wishlist': {
      get: { tags: ['Wishlist'], summary: 'View wishlist', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Wishlist' } } },
    },
    '/wishlist/{productId}': {
      post: { tags: ['Wishlist'], summary: 'Add to wishlist', security: [{ bearerAuth: [] }], parameters: [{ name: 'productId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 201: { description: 'Added' } } },
      delete: { tags: ['Wishlist'], summary: 'Remove from wishlist', security: [{ bearerAuth: [] }], parameters: [{ name: 'productId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Removed' } } },
    },
    '/reviews/product/{productId}': {
      get: { tags: ['Reviews'], summary: 'List product reviews', parameters: [{ name: 'productId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Reviews' } } },
      post: { tags: ['Reviews'], summary: 'Create review', security: [{ bearerAuth: [] }], parameters: [{ name: 'productId', in: 'path', required: true, schema: { type: 'string' } }], requestBody: { content: { 'application/json': { schema: { $ref: '#/components/schemas/ReviewRequest' } } } }, responses: { 201: { description: 'Created' } } },
    },
    '/reviews/{reviewId}': {
      patch: { tags: ['Reviews'], summary: 'Update review', security: [{ bearerAuth: [] }], parameters: [{ name: 'reviewId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Updated' } } },
      delete: { tags: ['Reviews'], summary: 'Delete review', security: [{ bearerAuth: [] }], parameters: [{ name: 'reviewId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Deleted' } } },
    },
    '/addresses': {
      get: { tags: ['Addresses'], summary: 'List addresses', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Addresses' } } },
      post: { tags: ['Addresses'], summary: 'Add address', security: [{ bearerAuth: [] }], requestBody: { content: { 'application/json': { schema: { $ref: '#/components/schemas/AddressRequest' } } } }, responses: { 201: { description: 'Created' } } },
    },
    '/addresses/{addressId}': {
      get: { tags: ['Addresses'], summary: 'Get address', security: [{ bearerAuth: [] }], parameters: [{ name: 'addressId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Address' } } },
      patch: { tags: ['Addresses'], summary: 'Update address', security: [{ bearerAuth: [] }], parameters: [{ name: 'addressId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Updated' } } },
      delete: { tags: ['Addresses'], summary: 'Delete address', security: [{ bearerAuth: [] }], parameters: [{ name: 'addressId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Deleted' } } },
    },
    '/addresses/{addressId}/default': {
      patch: { tags: ['Addresses'], summary: 'Set default address', security: [{ bearerAuth: [] }], parameters: [{ name: 'addressId', in: 'path', required: true, schema: { type: 'string' } }], responses: { 200: { description: 'Default set' } } },
    },
    '/coupons/validate': {
      post: { tags: ['Coupons'], summary: 'Validate coupon code', security: [{ bearerAuth: [] }], responses: { 200: { description: 'Valid coupon' } } },
    },
    '/settings': {
      get: { tags: ['Settings'], summary: 'Get public app settings', responses: { 200: { description: 'Settings' } } },
    },
  },
};
