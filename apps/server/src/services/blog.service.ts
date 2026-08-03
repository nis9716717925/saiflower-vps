import { prisma } from '../db/client';
import { mediaUrl } from '../utils/catalog';
import { NotFoundError } from '../utils/errors';

function excerptFrom(content: string, max = 140): string {
  const plain = content
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '$1')
    .replace(/<[^>]+>/g, '')
    .replace(/\s+/g, ' ')
    .trim();
  return plain.length > max ? `${plain.slice(0, max)}…` : plain;
}

function mapBlogListItem(row: {
  id: number;
  title: string;
  slug: string | null;
  content: string;
  image: string;
  createdAt: Date | null;
}) {
  return {
    id: row.id,
    title: row.title,
    slug: row.slug || String(row.id),
    image: mediaUrl(row.image),
    excerpt: excerptFrom(row.content),
    createdAt: row.createdAt?.toISOString() ?? null,
    url: `/blog/${row.slug || row.id}`,
  };
}

export async function listBlogs(limit = 100) {
  const rows = await prisma.blogs.findMany({
    where: { status: 1 },
    orderBy: { id: 'desc' },
    take: Math.min(200, Math.max(1, limit)),
    select: {
      id: true,
      title: true,
      slug: true,
      content: true,
      image: true,
      createdAt: true,
    },
  });
  return rows.map(mapBlogListItem);
}

export async function getBlogBySlug(slug: string) {
  const trimmed = slug.trim();
  if (!trimmed) throw new NotFoundError('Blog not found');

  const asId = /^\d+$/.test(trimmed) ? Number(trimmed) : null;
  const row =
    (await prisma.blogs.findFirst({
      where: {
        status: 1,
        OR: [{ slug: trimmed }, ...(asId != null ? [{ id: asId }] : [])],
      },
    })) ?? null;

  if (!row) throw new NotFoundError('Blog not found');

  return {
    id: row.id,
    title: row.title,
    slug: row.slug || String(row.id),
    content: row.content,
    image: mediaUrl(row.image),
    createdAt: row.createdAt?.toISOString() ?? null,
    metaTitle: row.metaTitle,
    metaDescription: row.metaDescription,
    metaKeywords: row.metaKeywords,
    url: `/blog/${row.slug || row.id}`,
  };
}

export async function listFaqs(page: string, limit = 6) {
  const all = page === 'all' || page === '*';
  const pages = all
    ? undefined
    : page === 'about' || page === 'contact'
      ? [page, 'general']
      : [page];
  const rows = await prisma.faqs.findMany({
    where: {
      status: 1,
      ...(pages ? { page: { in: pages } } : {}),
    },
    take: Math.min(all ? 200 : 20, Math.max(1, limit)),
    orderBy: all ? [{ page: 'asc' }, { id: 'asc' }] : { id: 'asc' },
    select: {
      id: true,
      question: true,
      answer: true,
      page: true,
    },
  });
  return rows.map((r) => ({
    id: r.id,
    question: r.question,
    answer: r.answer,
    page: (r.page ?? 'general').trim() || 'general',
  }));
}
