import prisma from '../config/database';
import { ForbiddenError, NotFoundError } from '../utils/errors';
import { parsePagination, buildPaginationMeta } from '../utils/response';

export class ReviewService {
  async listByProduct(productId: string, query: Record<string, unknown>) {
    const { page, limit } = parsePagination(query);
    const skip = (page - 1) * limit;

    const [reviews, total] = await Promise.all([
      prisma.review.findMany({
        where: { productId, isApproved: true },
        skip,
        take: limit,
        orderBy: { createdAt: 'desc' },
        include: {
          user: { select: { firstName: true, lastName: true } },
        },
      }),
      prisma.review.count({ where: { productId, isApproved: true } }),
    ]);

    return { items: reviews, meta: buildPaginationMeta(page, limit, total) };
  }

  async create(
    userId: string,
    productId: string,
    data: { rating: number; title?: string; comment?: string },
  ) {
    const product = await prisma.product.findUnique({ where: { id: productId } });
    if (!product) throw new NotFoundError('Product not found');

    const review = await prisma.review.upsert({
      where: { productId_userId: { productId, userId } },
      create: { userId, productId, ...data },
      update: { ...data },
      include: { user: { select: { firstName: true, lastName: true } } },
    });

    await this.updateProductRating(productId);
    return review;
  }

  async update(userId: string, reviewId: string, data: { rating?: number; title?: string; comment?: string }) {
    const review = await prisma.review.findUnique({ where: { id: reviewId } });
    if (!review) throw new NotFoundError('Review not found');
    if (review.userId !== userId) throw new ForbiddenError('Cannot update this review');

    const updated = await prisma.review.update({
      where: { id: reviewId },
      data,
      include: { user: { select: { firstName: true, lastName: true } } },
    });

    await this.updateProductRating(review.productId);
    return updated;
  }

  async delete(userId: string, reviewId: string) {
    const review = await prisma.review.findUnique({ where: { id: reviewId } });
    if (!review) throw new NotFoundError('Review not found');
    if (review.userId !== userId) throw new ForbiddenError('Cannot delete this review');

    await prisma.review.delete({ where: { id: reviewId } });
    await this.updateProductRating(review.productId);
    return { message: 'Review deleted' };
  }

  private async updateProductRating(productId: string) {
    const agg = await prisma.review.aggregate({
      where: { productId, isApproved: true },
      _avg: { rating: true },
      _count: { rating: true },
    });

    await prisma.product.update({
      where: { id: productId },
      data: {
        ratingAvg: agg._avg.rating ?? 0,
        ratingCount: agg._count.rating,
      },
    });
  }
}

export const reviewService = new ReviewService();
