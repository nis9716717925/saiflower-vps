import { prisma } from '../db/client';
import { ValidationError } from '../utils/errors';

export async function submitReview(input: { name?: string; rating?: number; comment?: string }) {
  const name = (input.name ?? 'Anonymous').trim() || 'Anonymous';
  const rating = Math.min(5, Math.max(1, Number(input.rating ?? 5) || 5));
  const comment = (input.comment ?? '').trim();
  if (!comment) throw new ValidationError('Comment is required.');

  const review = await prisma.reviews.create({
    data: { name, rating, reviewText: comment },
  });
  return { id: review.id, message: 'Review submitted successfully' };
}

export async function listReviews(limit = 50) {
  const take = Math.min(100, Math.max(1, limit));
  const rows = await prisma.reviews.findMany({
    orderBy: { id: 'desc' },
    take,
    select: {
      id: true,
      name: true,
      rating: true,
      reviewText: true,
      platform: true,
      status: true,
      createdAt: true,
    },
  });
  return rows.map((r) => ({
    id: r.id,
    name: r.name,
    rating: r.rating,
    comment: r.reviewText,
    platform: r.platform,
    status: r.status,
    created_at: r.createdAt,
  }));
}
