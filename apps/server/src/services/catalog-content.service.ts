import { prisma } from '../db/client';
import { mediaUrl } from '../utils/catalog';
import { NotFoundError } from '../utils/errors';

export async function listGallery(limit = 100) {
  const rows = await prisma.gallery.findMany({
    where: { status: 1 },
    orderBy: { id: 'desc' },
    take: Math.min(200, Math.max(1, limit)),
    select: {
      id: true,
      title: true,
      tag: true,
      image: true,
    },
  });

  return rows.map((row) => ({
    id: row.id,
    title: row.title,
    tag: row.tag?.trim() || null,
    image: mediaUrl(row.image, 'gallery/'),
    url: `/gallery-detail?id=${row.id}`,
  }));
}

export async function getGalleryItem(id: number) {
  const row = await prisma.gallery.findFirst({
    where: { id, status: 1 },
    select: {
      id: true,
      title: true,
      tag: true,
      image: true,
      metaTitle: true,
      metaDescription: true,
    },
  });
  if (!row) throw new NotFoundError('Gallery item not found');

  return {
    id: row.id,
    title: row.title,
    tag: row.tag?.trim() || null,
    image: mediaUrl(row.image, 'gallery/'),
    metaTitle: row.metaTitle,
    metaDescription: row.metaDescription,
    url: `/gallery-detail?id=${row.id}`,
  };
}

export async function listEvents(limit = 100) {
  const rows = await prisma.events.findMany({
    where: { status: 1 },
    orderBy: { id: 'desc' },
    take: Math.min(200, Math.max(1, limit)),
    select: {
      id: true,
      title: true,
      slug: true,
      tag: true,
      description: true,
      coverImage: true,
    },
  });

  return rows.map((row) => {
    const slug = row.slug?.trim() || String(row.id);
    return {
      id: row.id,
      title: row.title,
      slug,
      tag: row.tag?.trim() || null,
      description: row.description,
      image: mediaUrl(row.coverImage, 'events/'),
      url: `/events/${slug}`,
    };
  });
}

export async function getEventBySlug(slug: string) {
  const bySlug = await prisma.events.findFirst({
    where: { status: 1, slug },
  });
  const row =
    bySlug ??
    (Number.isFinite(Number(slug))
      ? await prisma.events.findFirst({ where: { status: 1, id: Number(slug) } })
      : null);

  if (!row) throw new NotFoundError('Event not found');

  const finalSlug = row.slug?.trim() || String(row.id);
  return {
    id: row.id,
    title: row.title,
    slug: finalSlug,
    tag: row.tag?.trim() || null,
    description: row.description,
    image: mediaUrl(row.coverImage, 'events/'),
    metaTitle: row.metaTitle,
    metaDescription: row.metaDescription,
    url: `/events/${finalSlug}`,
  };
}
