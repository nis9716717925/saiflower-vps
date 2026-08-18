import dotenv from 'dotenv';
import path from 'path';

dotenv.config({ path: path.resolve(__dirname, '../../.env') });
dotenv.config({ path: path.resolve(__dirname, '../../../packages/prisma/.env') });
dotenv.config();

function requiredInProduction(key: string): string | undefined {
  const value = process.env[key];
  if (!value && process.env.NODE_ENV === 'production') {
    throw new Error(`Missing required environment variable: ${key}`);
  }
  return value;
}

export const config = {
  env: process.env.NODE_ENV ?? 'development',
  port: parseInt(process.env.PORT ?? '4000', 10),
  apiPrefix: process.env.API_PREFIX ?? '/api/v1',
  isProduction: process.env.NODE_ENV === 'production',

  database: {
    /** Supabase PostgreSQL — Prisma pooled runtime datasource */
    url: process.env.DATABASE_URL ?? '',
  },

  jwt: {
    accessSecret:
      requiredInProduction('JWT_ACCESS_SECRET') ??
      'dev-access-secret-change-me-min-32-chars',
    refreshSecret:
      requiredInProduction('JWT_REFRESH_SECRET') ??
      'dev-refresh-secret-change-me-min-32-chars',
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
    max: parseInt(process.env.RATE_LIMIT_MAX ?? '1000', 10),
  },

  app: {
    name: process.env.APP_NAME ?? 'Sai Flower',
    currency: process.env.APP_CURRENCY ?? 'INR',
    currencySymbol: process.env.APP_CURRENCY_SYMBOL ?? '₹',
    maintenanceMode: process.env.APP_MAINTENANCE_MODE === 'true',
    publicUrl: (process.env.PUBLIC_SITE_URL ?? 'https://saiflower.com').replace(/\/$/, ''),
    adminOrderEmail: process.env.ADMIN_ORDER_EMAIL ?? 'searchlifterhexa@gmail.com',
  },

  checkout: {
    mode: (process.env.CHECKOUT_MODE ?? 'whatsapp_confirm') as 'whatsapp_confirm',
    whatsappE164: process.env.WHATSAPP_E164 ?? '918802004527',
  },

  shipping: {
    ratePerKm: parseInt(process.env.SHIPPING_RATE_PER_KM ?? '25', 10),
    storeLat: parseFloat(process.env.STORE_LAT ?? '28.5893714'),
    storeLng: parseFloat(process.env.STORE_LNG ?? '77.2289164'),
    storeAddress:
      process.env.STORE_ADDRESS ??
      'Shop No 1, Sai Mandir, Lodhi Rd, Gokalpuri, Institutional Area, Lodi Colony, New Delhi, Delhi 110003',
    googleMapsApiKey: process.env.GOOGLE_MAPS_API_KEY ?? '',
  },

  media: {
    baseUrl: (process.env.MEDIA_BASE_URL ?? 'https://saiflower.com').replace(/\/$/, ''),
  },

  oauth: {
    google: {
      enabled: process.env.OAUTH_GOOGLE_ENABLED !== 'false',
      clientId: process.env.OAUTH_GOOGLE_CLIENT_ID ?? '',
    },
  },

  mail: {
    from: process.env.MAIL_FROM ?? 'no-reply@saiflower.com',
  },
} as const;
