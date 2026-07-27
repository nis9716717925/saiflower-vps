import dotenv from 'dotenv';

dotenv.config();

const required = ['DATABASE_URL', 'JWT_ACCESS_SECRET', 'JWT_REFRESH_SECRET'] as const;

for (const key of required) {
  if (!process.env[key]) {
    throw new Error(`Missing required environment variable: ${key}`);
  }
}

export const config = {
  env: process.env.NODE_ENV ?? 'development',
  port: parseInt(process.env.PORT ?? '4000', 10),
  apiPrefix: process.env.API_PREFIX ?? '/api/v1',
  isProduction: process.env.NODE_ENV === 'production',

  database: {
    url: process.env.DATABASE_URL!,
  },

  jwt: {
    accessSecret: process.env.JWT_ACCESS_SECRET!,
    refreshSecret: process.env.JWT_REFRESH_SECRET!,
    accessExpiresIn: process.env.JWT_ACCESS_EXPIRES_IN ?? '15m',
    refreshExpiresIn: process.env.JWT_REFRESH_EXPIRES_IN ?? '7d',
  },

  cors: {
    origins: (process.env.CORS_ORIGINS ?? 'http://localhost:3000')
      .split(',')
      .map((o) => o.trim())
      .filter(Boolean),
  },

  rateLimit: {
    windowMs: parseInt(process.env.RATE_LIMIT_WINDOW_MS ?? '900000', 10),
    max: parseInt(process.env.RATE_LIMIT_MAX ?? '100', 10),
  },

  app: {
    name: process.env.APP_NAME ?? 'Saiflower',
    currency: process.env.APP_CURRENCY ?? 'INR',
    currencySymbol: process.env.APP_CURRENCY_SYMBOL ?? '₹',
    maintenanceMode: process.env.APP_MAINTENANCE_MODE === 'true',
  },

  media: {
    baseUrl: (process.env.MEDIA_BASE_URL ?? 'https://saiflower.com').replace(/\/$/, ''),
  },

  payments: {
    stripe: {
      enabled: process.env.PAYMENT_STRIPE_ENABLED === 'true',
      secretKey: process.env.PAYMENT_STRIPE_SECRET_KEY ?? '',
    },
    paypal: {
      enabled: process.env.PAYMENT_PAYPAL_ENABLED === 'true',
      clientId: process.env.PAYMENT_PAYPAL_CLIENT_ID ?? '',
    },
    razorpay: {
      enabled: process.env.PAYMENT_RAZORPAY_ENABLED === 'true',
      keyId: process.env.PAYMENT_RAZORPAY_KEY_ID ?? '',
      keySecret: process.env.PAYMENT_RAZORPAY_KEY_SECRET ?? '',
    },
    cod: {
      enabled: process.env.PAYMENT_COD_ENABLED !== 'false',
    },
  },

  oauth: {
    google: {
      enabled: process.env.OAUTH_GOOGLE_ENABLED === 'true',
      clientId: process.env.OAUTH_GOOGLE_CLIENT_ID ?? '',
      clientSecret: process.env.OAUTH_GOOGLE_CLIENT_SECRET ?? '',
    },
    facebook: {
      enabled: process.env.OAUTH_FACEBOOK_ENABLED === 'true',
      appId: process.env.OAUTH_FACEBOOK_APP_ID ?? '',
      appSecret: process.env.OAUTH_FACEBOOK_APP_SECRET ?? '',
    },
  },

  passwordReset: {
    tokenExpiresMinutes: parseInt(process.env.PASSWORD_RESET_TOKEN_EXPIRES_MINUTES ?? '30', 10),
    frontendUrl: process.env.FRONTEND_RESET_PASSWORD_URL ?? 'http://localhost:3000/reset-password',
  },
} as const;
