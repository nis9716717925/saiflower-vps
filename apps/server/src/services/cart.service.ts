import { randomUUID } from 'crypto';
import type { ProductType } from '@saiflower/shared';
import { normalizeProductType } from '../utils/catalog';
import { AppError } from '../utils/errors';
import { getProductById } from './product.service';
import { applySurgePricing } from './pricing.service';
import { validateCoupon, type CouponRecord } from './coupon.service';

export interface CartItem {
  id: number;
  category: ProductType;
  name: string;
  price: number;
  image: string;
  qty: number;
}

export interface CartState {
  items: CartItem[];
  coupon: CouponRecord | null;
}

const carts = new Map<string, CartState>();

function emptyCart(): CartState {
  return { items: [], coupon: null };
}

function cartKey(userId?: number, guestId?: string): string {
  if (userId) return `user:${userId}`;
  if (guestId) return `guest:${guestId}`;
  return `guest:${randomUUID()}`;
}

export function resolveCartId(userId?: number, guestId?: string): { key: string; guestId?: string } {
  if (userId) return { key: cartKey(userId) };
  const gid = guestId?.trim() || randomUUID();
  return { key: cartKey(undefined, gid), guestId: gid };
}

function getOrCreate(key: string): CartState {
  if (!carts.has(key)) carts.set(key, emptyCart());
  return carts.get(key)!;
}

async function refreshPrices(state: CartState): Promise<number> {
  let total = 0;
  for (const item of state.items) {
    try {
      const live = await getProductById(item.category, item.id);
      item.price = live.price;
      item.name = live.name || item.name;
      item.image = live.image || item.image;
      total += item.price * item.qty;
    } catch {
      const surged = await applySurgePricing(item.price, item.category);
      item.price = surged;
      total += item.price * item.qty;
    }
  }
  return total;
}

function discountFor(total: number, coupon: CouponRecord | null): number {
  if (!coupon) return 0;
  if (total < coupon.minOrderAmount) return 0;
  let discount = 0;
  if (coupon.discountType === 'flat') discount = coupon.discountValue;
  if (coupon.discountType === 'percentage') {
    discount = (total * coupon.discountValue) / 100;
  }
  if (discount > total) discount = total;
  return discount;
}

export async function getCart(userId?: number, guestId?: string) {
  const { key, guestId: gid } = resolveCartId(userId, guestId);
  const state = getOrCreate(key);
  const subtotal = await refreshPrices(state);
  if (state.coupon && subtotal < state.coupon.minOrderAmount) {
    state.coupon = null;
  }
  const discountAmount = discountFor(subtotal, state.coupon);
  const count = state.items.reduce((sum, item) => sum + (item.qty ?? 0), 0);
  return {
    guestId: gid,
    items: state.items,
    count,
    subtotal,
    discountAmount,
    grandTotal: subtotal - discountAmount,
    coupon: state.coupon,
  };
}

export async function addToCart(
  input: {
    productId: number;
    category?: string;
    quantity?: number;
    name?: string;
    price?: number;
    image?: string;
  },
  userId?: number,
  guestId?: string,
) {
  const { key, guestId: gid } = resolveCartId(userId, guestId);
  const state = getOrCreate(key);
  const category = normalizeProductType(input.category);
  const qty = Math.max(1, input.quantity ?? 1);

  let name = input.name ?? '';
  let price = input.price ?? 0;
  let image = input.image ?? '';
  try {
    const live = await getProductById(category, input.productId);
    name = live.name;
    price = live.price;
    image = live.image;
  } catch {
    // Allow add with client snapshot when product lookup fails (PHP accepts POST snapshot)
    price = await applySurgePricing(price, category);
  }

  const existing = state.items.find(
    (i) => i.id === input.productId && i.category === category,
  );
  if (existing) {
    existing.qty += qty;
    existing.price = price;
    existing.name = name || existing.name;
    existing.image = image || existing.image;
  } else {
    state.items.push({ id: input.productId, category, name, price, image, qty });
  }

  const cart = await getCart(userId, gid);
  return cart;
}

export async function updateQty(
  index: number,
  change: number,
  userId?: number,
  guestId?: string,
) {
  const { key, guestId: gid } = resolveCartId(userId, guestId);
  const state = getOrCreate(key);
  if (!state.items[index]) throw new AppError('Cart item not found', 404);
  state.items[index].qty += change;
  if (state.items[index].qty <= 0) {
    state.items.splice(index, 1);
  }
  return getCart(userId, gid);
}

export async function setQty(
  index: number,
  qty: number,
  userId?: number,
  guestId?: string,
) {
  const { key, guestId: gid } = resolveCartId(userId, guestId);
  const state = getOrCreate(key);
  if (!state.items[index]) throw new AppError('Cart item not found', 404);
  if (qty <= 0) state.items.splice(index, 1);
  else state.items[index].qty = qty;
  return getCart(userId, gid);
}

export async function removeItem(index: number, userId?: number, guestId?: string) {
  const { key, guestId: gid } = resolveCartId(userId, guestId);
  const state = getOrCreate(key);
  if (state.items[index]) state.items.splice(index, 1);
  return getCart(userId, gid);
}

export async function clearCart(userId?: number, guestId?: string) {
  const { key, guestId: gid } = resolveCartId(userId, guestId);
  carts.set(key, emptyCart());
  return getCart(userId, gid);
}

export async function applyCouponToCart(code: string, userId?: number, guestId?: string) {
  const cart = await getCart(userId, guestId);
  const coupon = await validateCoupon(code, cart.subtotal);
  const { key } = resolveCartId(userId, guestId ?? cart.guestId);
  const state = getOrCreate(key);
  state.coupon = coupon;
  return getCart(userId, guestId ?? cart.guestId);
}

export async function removeCouponFromCart(userId?: number, guestId?: string) {
  const { key, guestId: gid } = resolveCartId(userId, guestId);
  const state = getOrCreate(key);
  state.coupon = null;
  return getCart(userId, gid);
}

/** Merge guest cart into user cart on login (PHP session is single; API needs merge). */
export async function mergeGuestCart(userId: number, guestId: string) {
  const guestKey = cartKey(undefined, guestId);
  const userKey = cartKey(userId);
  const guest = carts.get(guestKey);
  if (!guest?.items.length) return getCart(userId);

  const userState = getOrCreate(userKey);
  for (const item of guest.items) {
    const existing = userState.items.find(
      (i) => i.id === item.id && i.category === item.category,
    );
    if (existing) existing.qty += item.qty;
    else userState.items.push({ ...item });
  }
  if (guest.coupon && !userState.coupon) userState.coupon = guest.coupon;
  carts.delete(guestKey);
  return getCart(userId);
}
