import prisma, { Prisma } from '@saiflower/prisma';

export { prisma, Prisma };
export default prisma;

export async function pingDb(): Promise<boolean> {
  try {
    await prisma.$queryRaw`SELECT 1`;
    return true;
  } catch {
    return false;
  }
}

/** Convert Prisma Decimal | number | null to JS number. */
export function num(value: unknown, fallback = 0): number {
  if (value == null) return fallback;
  if (typeof value === 'number') return value;
  if (typeof value === 'bigint') return Number(value);
  const n = Number(value);
  return Number.isFinite(n) ? n : fallback;
}

export async function tableCounts(): Promise<Record<string, number>> {
  const [
    flowers,
    cakes,
    gifts,
    customers,
    orders,
    dynamicPages,
    blogs,
    categories,
    wishlist,
    promoCodes,
  ] = await Promise.all([
    prisma.flowers.count(),
    prisma.cakes.count(),
    prisma.gifts.count(),
    prisma.customers.count(),
    prisma.orders.count(),
    prisma.dynamicPages.count(),
    prisma.blogs.count(),
    prisma.categories.count(),
    prisma.wishlist.count(),
    prisma.promoCodes.count(),
  ]);
  return {
    flowers,
    cakes,
    gifts,
    customers,
    orders,
    dynamic_pages: dynamicPages,
    blogs,
    categories,
    wishlist,
    promo_codes: promoCodes,
  };
}
