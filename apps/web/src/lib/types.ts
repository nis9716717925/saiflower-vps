import type { ProductType } from '@saiflower/shared';

export interface Product {
  id: number;
  type: ProductType;
  name: string;
  slug: string;
  description?: string | null;
  price: number;
  originalPrice?: number | null;
  image: string;
  rating?: number | null;
  inStock: boolean;
  status?: number;
  tag?: string;
  categoryIds?: string;
  metaTitle?: string | null;
  metaDescription?: string | null;
  faqs?: string | null;
  imagesGallery?: string | null;
  galleryImages?: string[];
  variants?: unknown[];
  related?: Product[];
  url?: string;
  deliverySameday?: boolean;
  deliveryNextday?: boolean;
}

export interface ShopCategory {
  id: number;
  name: string;
  slug?: string | null;
  image?: string | null;
  sortOrder?: number;
  status?: number;
}

export interface BlogListItem {
  id: number;
  title: string;
  slug: string;
  image: string;
  excerpt: string;
  createdAt?: string | null;
  url: string;
}

export interface BlogPost extends BlogListItem {
  content: string;
  metaTitle?: string | null;
  metaDescription?: string | null;
  metaKeywords?: string | null;
}

export interface FaqItem {
  id: number;
  question: string;
  answer: string;
  page?: string | null;
}

export interface CartItem {
  id: number;
  category: ProductType;
  name: string;
  price: number;
  image: string;
  qty: number;
}

export interface CartData {
  guestId?: string;
  items: CartItem[];
  count: number;
  subtotal: number;
  discountAmount: number;
  grandTotal: number;
  coupon?: unknown;
}

export interface ApiSuccess<T> {
  success: true;
  message: string;
  data?: T;
  meta?: PaginationMeta;
}

export interface ApiError {
  success: false;
  message: string;
  errors?: Record<string, string[]>;
}

export type ApiResponse<T> = ApiSuccess<T> | ApiError;

export interface PaginationMeta {
  page: number;
  limit: number;
  total: number;
  totalPages: number;
  hasNextPage: boolean;
  hasPrevPage: boolean;
}

export interface CustomerProfile {
  id: number;
  name: string;
  email: string;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  pincode?: string | null;
}

export interface AuthPayload {
  accessToken: string;
  refreshToken: string;
  customer: CustomerProfile;
}

export interface SearchHit {
  id: number;
  name: string;
  slug: string;
  image: string;
  type: string;
  link?: string;
  badge?: string;
}

export interface SearchResponse {
  success: boolean;
  query: string;
  results: SearchHit[];
}

export interface ShippingResult {
  status: 'ok' | 'error';
  distance_km?: number;
  distance_text?: string;
  shipping_fee?: number;
  rate_per_km?: number;
  store_address?: string;
  message?: string;
}

export interface AddressSuggestion {
  description: string;
  placeId: string;
}

export interface GoogleAddressDetails {
  flatHouseNo: string;
  apartmentStreetLocality: string;
  pincode: string;
  formattedAddress: string;
  latitude?: number | null;
  longitude?: number | null;
}

export interface PlaceOrderResult {
  order_id: number;
  message: string;
  whatsappUrl: string;
  status?: string;
}

export type AddressType = 'Home' | 'Work' | 'Other';

export interface CustomerAddress {
  id: number;
  customerId: number;
  recipientName: string;
  mobile: string;
  email?: string | null;
  flatHouseNo: string;
  apartmentStreetLocality: string;
  pincode: string;
  addressType: AddressType;
  isDefault: boolean;
  addressLine?: string;
  city?: string;
  formattedAddress?: string;
  createdAt?: string | Date | null;
  updatedAt?: string | Date | null;
}

export interface AuthSession {
  authenticated: boolean;
  customer?: CustomerProfile;
}
