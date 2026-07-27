import prisma from '../config/database';
import { BadRequestError } from '../utils/errors';
import { decimalToNumber } from '../utils/sanitize';

export class CouponService {
  async validate(code: string, userId: string, orderAmount: number) {
    const coupon = await prisma.coupon.findUnique({ where: { code: code.toUpperCase() } });

    if (!coupon || !coupon.isActive) {
      throw new BadRequestError('Invalid coupon code');
    }

    const now = new Date();
    if (coupon.startsAt && coupon.startsAt > now) {
      throw new BadRequestError('Coupon is not yet active');
    }
    if (coupon.expiresAt && coupon.expiresAt < now) {
      throw new BadRequestError('Coupon has expired');
    }
    if (coupon.usageLimit && coupon.usedCount >= coupon.usageLimit) {
      throw new BadRequestError('Coupon usage limit reached');
    }

    const minAmount = coupon.minOrderAmount
      ? decimalToNumber(coupon.minOrderAmount)
      : 0;
    if (orderAmount < minAmount) {
      throw new BadRequestError(`Minimum order amount of ${minAmount} required`);
    }

    const userUsageCount = await prisma.couponUsage.count({
      where: { couponId: coupon.id, userId },
    });
    if (userUsageCount >= coupon.perUserLimit) {
      throw new BadRequestError('Coupon usage limit per user reached');
    }

    const discount = this.calculateDiscount(coupon, orderAmount);

    return {
      code: coupon.code,
      discountType: coupon.discountType,
      discountValue: decimalToNumber(coupon.discountValue),
      discountAmount: discount,
      finalAmount: Math.max(0, orderAmount - discount),
    };
  }

  calculateDiscount(
    coupon: { discountType: string; discountValue: { toNumber?: () => number } | number; maxDiscount?: { toNumber?: () => number } | number | null },
    orderAmount: number,
  ): number {
    const value = decimalToNumber(coupon.discountValue);
    let discount =
      coupon.discountType === 'PERCENTAGE'
        ? (orderAmount * value) / 100
        : value;

    if (coupon.maxDiscount) {
      discount = Math.min(discount, decimalToNumber(coupon.maxDiscount));
    }

    return Math.round(discount * 100) / 100;
  }
}

export const couponService = new CouponService();
