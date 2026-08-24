import { config } from '../config';
import { prisma, num } from '../db/client';
import { AppError, ValidationError } from '../utils/errors';
import { clearCart, getCart } from './cart.service';
import { getProductById } from './product.service';
import {
  assertShippingReady,
  calculateShippingFromAddress,
  geocodeAddress,
  requireAddressFields,
} from './shipping.service';

function mapsLinkForLocation(opts: {
  address: string;
  latitude?: number | null;
  longitude?: number | null;
}): string {
  const lat = opts.latitude;
  const lng = opts.longitude;
  if (typeof lat === 'number' && typeof lng === 'number' && Number.isFinite(lat) && Number.isFinite(lng)) {
    return `https://www.google.com/maps?q=${lat},${lng}`;
  }
  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(opts.address.trim())}`;
}

async function buildCartItemsTextWithUrls(
  items: Array<{ qty: number; name: string; category: string; id: number }>,
): Promise<string> {
  if (!items.length) return '';
  const lines: string[] = [];
  for (const item of items) {
    let productPage = '';
    try {
      const product = await getProductById(item.category, item.id);
      if (product.url) {
        productPage = product.url.startsWith('http')
          ? product.url
          : `${config.app.publicUrl}${product.url.startsWith('/') ? '' : '/'}${product.url}`;
      }
    } catch {
      // Keep order text even if a catalog lookup fails.
    }
    lines.push(`${item.qty}x ${item.name}`);
    if (productPage) lines.push(`🔗 ${productPage}`);
  }
  return lines.join('\n');
}

function mapOrderRow(o: {
  id: number;
  customerName: string;
  customerPhone: string | null;
  customerEmail: string | null;
  deliveryAddress: string;
  deliveryDate: Date | null;
  orderItems: string;
  totalAmount: unknown;
  status: string | null;
  couponCode: string | null;
  createdAt: Date | null;
}) {
  return {
    id: o.id,
    customer_name: o.customerName,
    customer_phone: o.customerPhone,
    customer_email: o.customerEmail,
    delivery_address: o.deliveryAddress,
    delivery_date: o.deliveryDate,
    order_items: o.orderItems,
    total_amount: num(o.totalAmount),
    status: o.status,
    coupon_code: o.couponCode,
    created_at: o.createdAt,
  };
}

export async function placeOrder(input: {
  name: string;
  phone: string;
  email?: string;
  address: string;
  date?: string;
  items?: string;
  total?: number;
  shipping_fee?: number;
  distance_km?: number;
  discount_amount?: number;
  coupon_code?: string;
  recipient_name?: string;
  recipient_phone?: string;
  delivery_time?: string;
  address_id?: number;
  ordering_for_me?: boolean;
  latitude?: number | null;
  longitude?: number | null;
  userId?: number;
  guestId?: string;
}) {
  requireAddressFields(input.name, input.phone, input.address);

  if (!input.userId && !input.guestId) {
    throw new AppError('Please log in or continue as guest to place your order.', 401);
  }

  if (input.address_id && !input.userId) {
    throw new ValidationError('Saved addresses require a logged-in account.');
  }

  if (input.address_id && input.userId) {
    const saved = await prisma.customerAddress.findFirst({
      where: { id: input.address_id, customerId: input.userId },
      select: { id: true },
    });
    if (!saved) {
      throw new ValidationError('Saved address not found for this account.');
    }
  }

  const shippingResult = await calculateShippingFromAddress(input.address);
  assertShippingReady(shippingResult);

  const clientShippingFee = Number(input.shipping_fee ?? 0);
  if (Math.abs(shippingResult.shipping_fee - clientShippingFee) > 1) {
    throw new AppError('Shipping amount changed. Please refresh and try again.', 400);
  }

  const cart = await getCart(input.userId, input.guestId);
  const discountAmount = Number(input.discount_amount ?? cart.discountAmount ?? 0);
  const couponCode = (input.coupon_code ?? cart.coupon?.code ?? '').trim();

  if (couponCode) {
    const promo = await prisma.promoCodes.findFirst({
      where: { code: couponCode, status: 1 },
      select: { usageLimit: true },
    });
    if (promo?.usageLimit) {
      const limit = Number(promo.usageLimit);
      const used = await prisma.orders.count({
        where: { customerPhone: input.phone, couponCode },
      });
      if (used >= limit) {
        throw new AppError(`You've reached the usage limit for (${couponCode}).`, 400);
      }
    }
  }

  let itemsText = (input.items ?? '').trim();
  if (!itemsText && cart.items.length) {
    itemsText = cart.items
      .map((i) => `${i.qty}x ${i.name} (₹${i.price}) [${i.category}]`)
      .join('\n');
  }
  if (!itemsText) {
    throw new ValidationError('Cart is empty.');
  }

  itemsText += `\n--------------------------------\n`;
  itemsText += `Shipping (${shippingResult.distance_km.toFixed(2)} km @ ₹${config.shipping.ratePerKm}/km): ₹${shippingResult.shipping_fee.toFixed(2)}`;
  if (discountAmount > 0) {
    itemsText += `\nDiscount (${couponCode}): -₹${discountAmount.toFixed(2)}`;
  }

  const computedTotal =
    cart.subtotal + shippingResult.shipping_fee - (discountAmount || cart.discountAmount);
  const total = Number(input.total ?? computedTotal);

  const dateStr = (input.date ?? '').trim();
  const deliveryDate = dateStr ? new Date(dateStr) : null;

  const order = await prisma.orders.create({
    data: {
      customerName: input.name.trim(),
      customerPhone: input.phone.trim(),
      customerEmail: (input.email ?? '').trim(),
      deliveryAddress: input.address.trim(),
      deliveryDate,
      orderItems: itemsText,
      totalAmount: total,
      status: 'Pending',
      couponCode: couponCode || null,
    },
  });

  const orderId = order.id;

  console.info(
    `[order] NEW ORDER #${orderId} ₹${total} — ${input.name} ${input.phone} → ${config.app.adminOrderEmail}`,
  );

  await clearCart(input.userId, input.guestId);

  const itemsWithUrls =
    (await buildCartItemsTextWithUrls(cart.items)) ||
    cart.items.map((i) => `${i.qty}x ${i.name}`).join('\n') ||
    itemsText;

  let latitude = input.latitude ?? null;
  let longitude = input.longitude ?? null;
  if (
    (typeof latitude !== 'number' || typeof longitude !== 'number') &&
    input.address.trim()
  ) {
    const geocoded = await geocodeAddress(input.address);
    if (geocoded) {
      latitude = geocoded.latitude;
      longitude = geocoded.longitude;
    }
  }

  const orderingForMe = Boolean(input.ordering_for_me);
  const recipientName = orderingForMe
    ? input.name
    : (input.recipient_name ?? input.name);
  const recipientPhone = orderingForMe
    ? input.phone
    : (input.recipient_phone ?? input.phone);

  const whatsappMessage = buildWhatsAppMessage({
    itemsText: itemsWithUrls,
    total,
    shippingFee: shippingResult.shipping_fee,
    distanceText: shippingResult.distance_text,
    date: input.date ?? '',
    time: input.delivery_time ?? '',
    senderName: input.name,
    senderPhone: input.phone,
    recipientName,
    recipientPhone,
    address: input.address,
    orderingForMe,
    mapsUrl: mapsLinkForLocation({
      address: input.address,
      latitude,
      longitude,
    }),
  });

  const whatsappUrl = `https://wa.me/${config.checkout.whatsappE164}?text=${encodeURIComponent(whatsappMessage)}`;

  return {
    status: 'success' as const,
    message: 'Order placed successfully',
    order_id: orderId,
    checkoutMode: config.checkout.mode,
    whatsappUrl,
    redirect: `/?order_success=1&oid=${orderId}`,
  };
}

function buildWhatsAppMessage(p: {
  itemsText: string;
  total: number;
  shippingFee: number;
  distanceText: string;
  date: string;
  time: string;
  senderName: string;
  senderPhone: string;
  recipientName: string;
  recipientPhone: string;
  address: string;
  orderingForMe?: boolean;
  mapsUrl?: string;
}) {
  let msg = `*✨ NEW ORDER ✨*\n\n`;
  msg += `*🛍️ ITEMS:*\n${p.itemsText}\n\n`;
  msg += `*💰 TOTAL: ₹${p.total}*\n`;
  msg += `*(Incl. Shipping: ₹${p.shippingFee} for ${p.distanceText})*\n\n`;
  msg += `*📍 DELIVERY:* ${p.date} | ${p.time}\n`;
  if (p.orderingForMe) {
    msg += `*👤 CUSTOMER (ordering for self):* ${p.senderName} (${p.senderPhone})\n`;
  } else {
    msg += `*👤 SENDER:* ${p.senderName} (${p.senderPhone})\n`;
    msg += `*🎁 RECIPIENT:* ${p.recipientName} (${p.recipientPhone})\n`;
  }
  msg += `*📍 ADDRESS:* ${p.address}`;
  if (p.mapsUrl) {
    msg += `\n*🗺 GOOGLE MAPS:* ${p.mapsUrl}`;
  }
  return msg;
}

export async function listOrdersByPhone(phone: string) {
  const rows = await prisma.orders.findMany({
    where: { customerPhone: phone },
    orderBy: { id: 'desc' },
    take: 50,
  });
  return rows.map(mapOrderRow);
}

export async function getOrderById(id: number) {
  const row = await prisma.orders.findUnique({ where: { id } });
  return row ? mapOrderRow(row) : null;
}
