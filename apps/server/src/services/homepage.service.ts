import { prisma } from '../db/client';

/** Admin uploads land on VPS disk; serve /uploads/* locally (not Supabase). */
function localUploadUrl(path?: string | null): string {
  if (!path?.trim()) return '';
  const raw = path.trim();
  if (/^https?:\/\//i.test(raw)) return raw.replace(/ /g, '%20');
  const normalized = raw.startsWith('/') ? raw.slice(1) : raw;
  if (normalized.startsWith('uploads/')) return `/${normalized}`.replace(/ /g, '%20');
  return `/uploads/slides/${normalized}`.replace(/ /g, '%20');
}

export async function listHomepageSlides() {
  const rows = await prisma.homepageSlides.findMany({
    where: { status: 1 },
    orderBy: [{ sortOrder: 'asc' }, { id: 'asc' }],
    select: {
      id: true,
      image: true,
      mobileImage: true,
      link: true,
      sortOrder: true,
    },
  });

  return rows.map((row) => ({
    id: row.id,
    image: localUploadUrl(row.image),
    mobileImage: row.mobileImage ? localUploadUrl(row.mobileImage) : null,
    link: row.link?.trim() || null,
    sortOrder: row.sortOrder ?? 0,
  }));
}
