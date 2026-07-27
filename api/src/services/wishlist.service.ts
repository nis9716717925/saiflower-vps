import prisma from '../config/database';
import { ConflictError, NotFoundError } from '../utils/errors';
import { parsePagination, buildPaginationMeta } from '../utils/response';
import { decimalToNumber } from '../utils/sanitize';

export class WishlistService {
  async list(userId: string, query: Record<string, unknown>) {
    const { page, limit } = parsePagination(query);
    const skip = (page - 1) * limit;

    const [items, total] = await Promise.all([
      prisma.wishlistItem.findMany({
        where: { userId },
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        include: {
          product: {
            include: {
              images: { orderBy: { sortOrder: 'asc' }, take: 1 },
              category: { select: { name: true, slug: true } },
            },
          },
        },
      }),
      prisma.wishlistItem.count({ where: { userId } }),
    ]);

    return {
      items: items.map((item) => ({
        id: item.id,
        addedAt: item.createdAt,
        product: {
          ...item.product,
          basePrice: decimalToNumber(item.product.basePrice),
          compareAtPrice: item.product.compareAtPrice
            ? decimalToNumber(item.product.compareAtPrice)
            : null,
        },
      })),
      meta: buildPaginationMeta(page, limit, total),
    };
  }

  async add(userId: string, productId: string) {
    const product = await prisma.product.findUnique({
      where: { id: productId, isActive: true },
    });
    if (!product) throw new NotFoundError('Product not found');

    const existing = await prisma.wishlistItem.findUnique({
      where: { userId_productId: { userId, productId } },
    });
    if (existing) throw new ConflictError('Product already in wishlist');

    return prisma.wishlistItem.create({
      data: { userId, productId },
      include: { product: { include: { images: { take: 1 } } } },
    });
  }

  async remove(userId: string, productId: string) {
    const item = await prisma.wishlistItem.findUnique({
      where: { userId_productId: { userId, productId } },
    });
    if (!item) throw new NotFoundError('Wishlist item not found');

    await prisma.wishlistItem.delete({ where: { id: item.id } });
    return { message: 'Removed from wishlist' };
  }
}

export const wishlistService = new WishlistService();
