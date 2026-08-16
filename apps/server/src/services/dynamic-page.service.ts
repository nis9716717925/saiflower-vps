import { prisma } from '../db/client';
import { mediaUrl } from '../utils/catalog';
import { NotFoundError } from '../utils/errors';

function formatContent(content: string | null | undefined): string {
  if (!content) return '';
  let decoded = content;
  try {
    decoded = content
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&quot;/g, '"')
      .replace(/&#39;/g, "'")
      .replace(/&amp;/g, '&');
  } catch {
    decoded = content;
  }
  if (/<(p|h[1-6]|ul|ol|img|div|span|strong)\b/i.test(decoded)) return decoded;
  return decoded
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>')
    .replace(/\n/g, '<br/>');
}

function mapPage(row: {
  id: number;
  title: string;
  shortDescription: string | null;
  slug: string;
  layoutType: string | null;
  pageTag: string | null;
  heroImage: string | null;
  extraImages: string | null;
  content: string | null;
  metaTitle: string | null;
  metaDescription: string | null;
  metaKeywords: string | null;
  faqs: string | null;
  midgridImage: string | null;
  midgridImageAlt: string | null;
}) {
  let extraImages: string[] = [];
  if (row.extraImages) {
    try {
      const parsed = JSON.parse(row.extraImages) as unknown;
      if (Array.isArray(parsed)) {
        extraImages = parsed
          .map((item) => (typeof item === 'string' ? mediaUrl(item) : null))
          .filter((item): item is string => Boolean(item));
      }
    } catch {
      extraImages = [];
    }
  }

  return {
    id: row.id,
    title: row.title,
    shortDescription: row.shortDescription,
    slug: row.slug,
    layoutType: row.layoutType || 'event_info',
    pageTag: row.pageTag?.trim() || null,
    heroImage: row.heroImage ? mediaUrl(row.heroImage) : null,
    extraImages,
    midgridImage: row.midgridImage ? mediaUrl(row.midgridImage) : null,
    midgridImageAlt: row.midgridImageAlt,
    contentHtml: formatContent(row.content),
    metaTitle: row.metaTitle,
    metaDescription: row.metaDescription,
    metaKeywords: row.metaKeywords,
    faqs: row.faqs,
    url: `/${row.slug}`,
  };
}

export async function getDynamicPageBySlug(slug: string) {
  const row = await prisma.dynamicPages.findFirst({
    where: { slug, status: 1 },
  });
  if (!row) throw new NotFoundError('Page not found');
  return mapPage(row);
}

export async function listDynamicPages(limit = 200) {
  const rows = await prisma.dynamicPages.findMany({
    where: { status: 1 },
    orderBy: { title: 'asc' },
    take: Math.min(500, Math.max(1, limit)),
    select: {
      id: true,
      title: true,
      shortDescription: true,
      slug: true,
      layoutType: true,
      pageTag: true,
      heroImage: true,
      metaTitle: true,
      metaDescription: true,
    },
  });
  return rows.map((row) => ({
    id: row.id,
    title: row.title,
    shortDescription: row.shortDescription,
    slug: row.slug,
    layoutType: row.layoutType || 'event_info',
    pageTag: row.pageTag,
    heroImage: row.heroImage ? mediaUrl(row.heroImage) : null,
    url: `/${row.slug}`,
  }));
}
