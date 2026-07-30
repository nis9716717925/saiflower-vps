/**
 * Product type discriminator used across PHP catalog tables.
 * Must stay aligned with session cart `category` values.
 */
export type ProductType = 'flower' | 'cake' | 'gift' | 'addon';

export type CollectionKind = 'relation' | 'occasion' | 'collection' | 'flower-type';

export interface ApiSuccessResponse<T = unknown> {
  success: true;
  message: string;
  data?: T;
  meta?: PaginationMeta;
}

export interface ApiErrorResponse {
  success: false;
  message: string;
  errors?: Record<string, string[]>;
}

export type ApiResponse<T = unknown> = ApiSuccessResponse<T> | ApiErrorResponse;

export interface PaginationMeta {
  page: number;
  limit: number;
  total: number;
  totalPages: number;
  hasNextPage: boolean;
  hasPrevPage: boolean;
}

/** Checkout behavior locked to production PHP until explicitly changed. */
export const CHECKOUT_MODE = 'whatsapp_confirm' as const;

export const SITE = {
  name: 'Sai Flower',
  url: 'https://saiflower.com',
  whatsappE164: '918802004527',
  currency: 'INR',
  currencySymbol: '₹',
} as const;

export const SHIPPING = {
  ratePerKmInr: 25,
  storeAddress:
    'Shop No 1, Sai Mandir, Lodhi Rd, Gokalpuri, Institutional Area, Lodi Colony, New Delhi, Delhi 110003',
  storeLat: 28.5893714,
  storeLng: 77.2289164,
} as const;
