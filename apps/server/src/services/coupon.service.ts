import { prisma, num } from '../db/client';
import { AppError, ValidationError } from '../utils/errors';

export interface CouponRecord {
  code: string;
  discountType: 'flat' | 'percentage' | string;
  discountValue: number;
  minOrderAmount: number;
  usageLimit: number | null;
}

function startOfToday(): Date {
  const d = new Date();
  d.setHours(0, 0, 0, 0);
  return d;
}

function promoExpiryFilter() {
  return {
    OR: [{ expiryDate: null }, { expiryDate: { gte: startOfToday() } }],
  };
}

function mapDiscountType(type: string): string {
  return type === 'PERCENTAGE' ? 'percentage' : 'flat';
}

/** Mirrors cart.php promo_codes lookup. */
export async function validateCoupon(codeRaw: string, currentTotal: number): Promise<CouponRecord> {
  const code = codeRaw.trim().toUpperCase();
  if (!code) throw new ValidationError('Coupon code is required.');

  const row = await prisma.promoCodes.findFirst({
    where: {
      code,
      status: 1,
      ...promoExpiryFilter(),
    },
  });

  if (!row) {
    throw new AppError('Invalid or expired promo code.', 400);
  }

  const coupon: CouponRecord = {
    code: row.code,
    discountType: mapDiscountType(row.discountType),
    discountValue: num(row.discountValue),
    minOrderAmount: num(row.minOrderAmount),
    usageLimit: row.usageLimit == null ? null : Number(row.usageLimit),
  };

  if (currentTotal < coupon.minOrderAmount) {
    throw new AppError(
      `Minimum order amount of ₹${coupon.minOrderAmount} required for this coupon.`,
      400,
    );
  }

  return coupon;
}

export async function listActiveCoupons() {
  const rows = await prisma.promoCodes.findMany({
    where: {
      status: 1,
      ...promoExpiryFilter(),
    },
    orderBy: { id: 'desc' },
    select: {
      code: true,
      discountType: true,
      discountValue: true,
      minOrderAmount: true,
      showOnFlowers: true,
    },
  });
  return rows.map((r) => ({
    code: r.code,
    discountType: mapDiscountType(r.discountType),
    discountValue: num(r.discountValue),
    minOrderAmount: num(r.minOrderAmount),
    showOnFlowers: Number(r.showOnFlowers ?? 0) === 1,
  }));
}
