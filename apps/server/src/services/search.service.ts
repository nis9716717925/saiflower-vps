import { prisma } from '../db/client';
import { mediaUrl, productUrl } from '../utils/catalog';

type SearchHit = {
  id: number;
  name: string;
  slug: string;
  image: string;
  type: string;
  link?: string;
  badge?: string;
};

function searchRank(name: string, slug: string | null | undefined, queryText: string): number {
  const lower = queryText.toLowerCase();
  const n = name.toLowerCase();
  const s = (slug ?? '').toLowerCase();
  if (n.startsWith(lower)) return 0;
  if (s.startsWith(lower)) return 1;
  return 2;
}

async function searchTable(
  type: 'flower' | 'cake' | 'gift',
  queryText: string,
  take: number,
): Promise<SearchHit[]> {
  const param = queryText;
  const where = {
    status: 1,
    OR: [
      { name: { contains: param, mode: 'insensitive' as const } },
      { slug: { contains: param, mode: 'insensitive' as const } },
      { tag: { contains: param, mode: 'insensitive' as const } },
    ],
  };
  const select = { id: true, name: true, slug: true, image: true } as const;

  const rows =
    type === 'flower'
      ? await prisma.flowers.findMany({ where, select, take: Math.max(take, 20) })
      : type === 'cake'
        ? await prisma.cakes.findMany({ where, select, take: Math.max(take, 20) })
        : await prisma.gifts.findMany({ where, select, take: Math.max(take, 20) });

  return rows
    .sort((a, b) => searchRank(a.name, a.slug, queryText) - searchRank(b.name, b.slug, queryText))
    .slice(0, take)
    .map((row) => ({
      id: row.id,
      name: String(row.name ?? 'Product'),
      slug: row.slug != null ? String(row.slug) : '',
      image: mediaUrl(row.image != null ? String(row.image) : null, `${type}s`),
      type,
      link: productUrl(type, row.slug != null ? String(row.slug) : '', row.id),
      badge: type.charAt(0).toUpperCase() + type.slice(1),
    }));
}

/** Product search — used by header typeahead and /search-results. */
export async function searchSuggest(qRaw: string, limitRaw?: number) {
  const queryText = qRaw.trim();
  const limit = Math.min(48, Math.max(1, Number(limitRaw) || 10));
  const results: SearchHit[] = [];
  if (!queryText) {
    return { success: true, query: queryText, results: [] };
  }

  const perType = Math.max(4, Math.ceil(limit / 2));
  for (const type of ['flower', 'cake', 'gift'] as const) {
    try {
      const hits = await searchTable(type, queryText, perType);
      results.push(...hits);
    } catch {
      // table may be missing locally
    }
  }

  if (/celebrat|calendar|festiv|occasion/i.test(queryText)) {
    results.unshift({
      id: 0,
      name: 'Celebrations Calendar',
      slug: 'celebration-calendar',
      image: '/celebrations/valentines-day.jpg',
      type: 'page',
      link: '/celebration-calendar',
      badge: 'Guide',
    });
  }

  const normalized = results
    .sort((a, b) => searchRank(a.name, a.slug, queryText) - searchRank(b.name, b.slug, queryText))
    .slice(0, limit)
    .map((item) => ({
      ...item,
      image: item.image || '',
      link: item.link || `/search-results?q=${encodeURIComponent(queryText)}`,
      badge: item.badge || item.type.charAt(0).toUpperCase() + item.type.slice(1),
    }));

  return { success: true, query: queryText, results: normalized };
}
