import { OrderStatus, PaymentProvider, PaymentStatus } from '@prisma/client';
import prisma from '../config/database';
import { cartService } from './cart.service';
import { couponService } from './coupon.service';
import { paymentService } from './payment.service';
import { addressService } from './address.service';
import { BadRequestError, NotFoundError } from '../utils/errors';
import { generateOrderNumber, decimalToNumber } from '../utils/sanitize';
import { parsePagination, buildPaginationMeta } from '../utils/response';
import { config } from '../config';

const SHIPPING_FLAT_RATE = 49;
const TAX_RATE = 0.05;

export class CheckoutService {
  async getSummary(
    userId: string,
    data: {
      addressId?: string;
      couponCode?: string;
      deliverySlotId?: string;
    },
  ) {
    const cart = await cartService.getOrCreateCart(userId);
    if (!cart.items.length) throw new BadRequestError('Cart is empty');

    let address = null;
    if (data.addressId) {
      address = await addressService.getById(userId, data.addressId);
    }

    let deliverySlot = null;
    if (data.deliverySlotId) {
      deliverySlot = await prisma.deliverySlot.findUnique({
        where: { id: data.deliverySlotId, isActive: true },
      });
      if (!deliverySlot) throw new NotFoundError('Delivery slot not found');
      if (deliverySlot.bookedCount >= deliverySlot.maxOrders) {
        throw new BadRequestError('Delivery slot is fully booked');
      }
    }

    const subtotal = cart.subtotal;
    let discountAmount = 0;
    let coupon = null;

    if (data.couponCode) {
      const validated = await couponService.validate(data.couponCode, userId, subtotal);
      discountAmount = validated.discountAmount;
      coupon = validated;
    }

    const shippingAmount = subtotal > 999 ? 0 : SHIPPING_FLAT_RATE;
    const taxableAmount = subtotal - discountAmount + shippingAmount;
    const taxAmount = Math.round(taxableAmount * TAX_RATE * 100) / 100;
    const totalAmount = taxableAmount + taxAmount;

    return {
      items: cart.items,
      itemCount: cart.itemCount,
      subtotal,
      discountAmount,
      shippingAmount,
      taxAmount,
      totalAmount,
      currency: config.app.currency,
      currencySymbol: config.app.currencySymbol,
      address,
      deliverySlot,
      coupon,
      paymentProviders: paymentService.getAvailableProviders(),
    };
  }

  async getDeliverySlots(fromDate?: string) {
    const start = fromDate ? new Date(fromDate) : new Date();
    start.setHours(0, 0, 0, 0);

    const slots = await prisma.deliverySlot.findMany({
      where: {
        isActive: true,
        date: { gte: start },
      },
      orderBy: [{ date: 'asc' }, { startTime: 'asc' }],
      take: 50,
    });

    return slots.filter((slot) => slot.bookedCount < slot.maxOrders).slice(0, 30);
  }

  async placeOrder(
    userId: string,
    data: {
      addressId: string;
      deliverySlotId?: string;
      couponCode?: string;
      paymentProvider: PaymentProvider;
      paymentRef?: string;
      notes?: string;
    },
  ) {
    const summary = await this.getSummary(userId, {
      addressId: data.addressId,
      couponCode: data.couponCode,
      deliverySlotId: data.deliverySlotId,
    });

    if (!paymentService.isProviderEnabled(data.paymentProvider)) {
      throw new BadRequestError('Selected payment provider is not available');
    }

    const cart = await prisma.cart.findUnique({
      where: { userId },
      include: {
        items: {
          include: { product: true, variant: true },
        },
      },
    });
    if (!cart?.items.length) throw new BadRequestError('Cart is empty');

    for (const item of cart.items) {
      if (item.variant && item.variant.stock < item.quantity) {
        throw new BadRequestError(`Insufficient stock for ${item.product.name}`);
      }
    }

    let paymentStatus: PaymentStatus = PaymentStatus.PENDING;
    if (data.paymentProvider === 'COD') {
      paymentStatus = PaymentStatus.PENDING;
    } else if (data.paymentRef) {
      const verified = await paymentService.verifyPayment(
        data.paymentProvider,
        data.paymentRef,
        summary.totalAmount,
      );
      paymentStatus = verified ? PaymentStatus.PAID : PaymentStatus.FAILED;
    }

    let couponId: string | undefined;
    if (data.couponCode) {
      const c = await prisma.coupon.findUnique({
        where: { code: data.couponCode.toUpperCase() },
      });
      couponId = c?.id;
    }

    const order = await prisma.$transaction(async (tx) => {
      const created = await tx.order.create({
        data: {
          orderNumber: generateOrderNumber(),
          userId,
          addressId: data.addressId,
          deliverySlotId: data.deliverySlotId,
          couponId,
          status: OrderStatus.PENDING,
          paymentStatus,
          paymentProvider: data.paymentProvider,
          paymentRef: data.paymentRef,
          subtotal: summary.subtotal,
          discountAmount: summary.discountAmount,
          shippingAmount: summary.shippingAmount,
          taxAmount: summary.taxAmount,
          totalAmount: summary.totalAmount,
          notes: data.notes,
          items: {
            create: cart.items.map((item) => {
              const unitPrice = item.variant
                ? decimalToNumber(item.variant.price)
                : decimalToNumber(item.product.basePrice);
              return {
                productId: item.productId,
                variantId: item.variantId,
                productName: item.product.name,
                variantName: item.variant?.name,
                sku: item.variant?.sku ?? item.product.sku,
                unitPrice,
                quantity: item.quantity,
                totalPrice: unitPrice * item.quantity,
              };
            }),
          },
          tracking: {
            create: {
              status: OrderStatus.PENDING,
              message: 'Order placed successfully',
            },
          },
        },
        include: { items: true, address: true, tracking: true },
      });

      for (const item of cart.items) {
        if (item.variantId) {
          await tx.productVariant.update({
            where: { id: item.variantId },
            data: { stock: { decrement: item.quantity } },
          });
        }
      }

      if (data.deliverySlotId) {
        await tx.deliverySlot.update({
          where: { id: data.deliverySlotId },
          data: { bookedCount: { increment: 1 } },
        });
      }

      if (couponId) {
        await tx.coupon.update({
          where: { id: couponId },
          data: { usedCount: { increment: 1 } },
        });
        await tx.couponUsage.create({
          data: { couponId, userId, orderId: created.id },
        });
      }

      await tx.cartItem.deleteMany({ where: { cartId: cart.id } });

      return created;
    });

    return order;
  }
}

export class OrderService {
  async list(userId: string, query: Record<string, unknown>) {
    const { page, limit } = parsePagination(query);
    const skip = (page - 1) * limit;
    const status = query.status as OrderStatus | undefined;

    const where = { userId, ...(status ? { status } : {}) };

    const [orders, total] = await Promise.all([
      prisma.order.findMany({
        where,
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        include: {
          items: { take: 3 },
          address: true,
        },
      }),
      prisma.order.count({ where }),
    ]);

    return {
      items: orders.map(this.formatOrder),
      meta: buildPaginationMeta(page, limit, total),
    };
  }

  async getById(userId: string, orderId: string) {
    const order = await prisma.order.findFirst({
      where: { id: orderId, userId },
      include: {
        items: true,
        address: true,
        deliverySlot: true,
        tracking: { orderBy: { createdAt: 'asc' } },
        coupon: { select: { code: true, discountType: true, discountValue: true } },
      },
    });
    if (!order) throw new NotFoundError('Order not found');
    return this.formatOrder(order);
  }

  async cancel(userId: string, orderId: string, reason?: string) {
    const order = await prisma.order.findFirst({
      where: { id: orderId, userId },
      include: { items: true },
    });
    if (!order) throw new NotFoundError('Order not found');

    const cancellable: OrderStatus[] = [OrderStatus.PENDING, OrderStatus.CONFIRMED];
    if (!cancellable.includes(order.status)) {
      throw new BadRequestError('Order cannot be cancelled at this stage');
    }

    const updated = await prisma.$transaction(async (tx) => {
      const result = await tx.order.update({
        where: { id: orderId },
        data: {
          status: OrderStatus.CANCELLED,
          cancelledAt: new Date(),
          cancelReason: reason,
        },
        include: { tracking: true },
      });

      await tx.orderTracking.create({
        data: {
          orderId,
          status: OrderStatus.CANCELLED,
          message: reason ?? 'Order cancelled by customer',
        },
      });

      for (const item of order.items) {
        if (item.variantId) {
          await tx.productVariant.update({
            where: { id: item.variantId },
            data: { stock: { increment: item.quantity } },
          });
        }
      }

      return result;
    });

    return this.formatOrder(updated);
  }

  async track(userId: string, orderId: string) {
    const order = await prisma.order.findFirst({
      where: { id: orderId, userId },
      select: {
        id: true,
        orderNumber: true,
        status: true,
        tracking: { orderBy: { createdAt: 'asc' } },
      },
    });
    if (!order) throw new NotFoundError('Order not found');
    return order;
  }

  async generateInvoice(userId: string, orderId: string) {
    const order = await this.getById(userId, orderId);
    return {
      invoiceNumber: `INV-${order.orderNumber}`,
      issuedAt: new Date().toISOString(),
      seller: {
        name: config.app.name,
        currency: config.app.currency,
      },
      order,
    };
  }

  private formatOrder(order: Record<string, unknown>) {
    const numericFields = [
      'subtotal', 'discountAmount', 'shippingAmount', 'taxAmount', 'totalAmount',
    ];
    const formatted = { ...order };
    for (const field of numericFields) {
      if (formatted[field] != null) {
        formatted[field] = decimalToNumber(formatted[field] as never);
      }
    }
    if (Array.isArray(formatted.items)) {
      formatted.items = (formatted.items as Record<string, unknown>[]).map((item) => ({
        ...item,
        unitPrice: decimalToNumber(item.unitPrice as never),
        totalPrice: decimalToNumber(item.totalPrice as never),
      }));
    }
    return formatted;
  }
}

export const checkoutService = new CheckoutService();
export const orderService = new OrderService();
