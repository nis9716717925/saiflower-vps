import { prisma, num } from '../db/client';
import { ValidationError } from '../utils/errors';
import { normalizeProductType, mediaUrl, productUrl } from '../utils/catalog';
import { applySurgePricing } from './pricing.service';

export async function toggleWishlist(userId: number, productId: number, typeRaw?: string) {
  if (productId <= 0) throw new ValidationError('Invalid product');
  const type = normalizeProductType(typeRaw ?? 'flower');

  const existing = await prisma.wishlist.findFirst({
    where: { userId, productId, type },
    select: { id: true },
  });

  if (existing) {
    await prisma.wishlist.deleteMany({
      where: { userId, productId, type },
    });
    return { success: true, action: 'removed' as const, message: 'Removed from wishlist' };
  }

  await prisma.wishlist.create({
    data: { userId, productId, type },
  });
  return { success: true, action: 'added' as const, message: 'Added to wishlist' };
}

async function fetchProductSnapshot(type: string, productId: number) {
  switch (normalizeProductType(type)) {
    case 'flower':
      return prisma.flowers.findUnique({
        where: { id: productId },
        select: { id: true, name: true, slug: true, image: true, price: true, status: true },
      });
    case 'cake':
      return prisma.cakes.findUnique({
        where: { id: productId },
        select: { id: true, name: true, slug: true, image: true, price: true, status: true },
      });
    case 'gift':
      return prisma.gifts.findUnique({
        where: { id: productId },
        select: { id: true, name: true, slug: true, image: true, price: true, status: true },
      });
    case 'addon':
      return prisma.addons.findUnique({
        where: { id: productId },
        select: { id: true, name: true, price: true, status: true },
      });
    default:
      return null;
  }
}

export async function listWishlist(userId: number) {
  const rows = await prisma.wishlist.findMany({
    where: { userId },
    orderBy: { id: 'desc' },
    select: { id: true, productId: true, type: true, createdAt: true },
  });

  const items = [];
  for (const row of rows) {
    const type = normalizeProductType(row.type);
    try {
      const p = await fetchProductSnapshot(type, row.productId);
      if (!p) continue;
      const slug = 'slug' in p && p.slug != null ? String(p.slug) : '';
      const imageField = 'image' in p ? p.image : null;
      const price = await applySurgePricing(num(p.price), type);
      items.push({
        wishlistId: row.id,
        productId: p.id,
        type,
        name: String(p.name ?? ''),
        slug,
        image: mediaUrl(imageField != null ? String(imageField) : null, `${type}s`),
        price,
        url: productUrl(type, slug, p.id),
      });
    } catch {
      // skip missing product
    }
  }
  return items;
}
