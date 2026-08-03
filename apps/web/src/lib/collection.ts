import taxonomyJson from './data/collection-taxonomy.json';
import { fetchProducts } from '@/lib/api';
import { filterLandingBouquets, isLandingBouquetProduct } from '@/lib/bouquet';
import type { Product } from '@/lib/types';

export type CollectionKind = 'flower' | 'relation' | 'occasion' | 'collection';

export interface CollectionFilter {
  tables?: string[];
  name_keywords?: string[];
  tags?: string[];
  match?: string;
  price_min?: number;
  price_max?: number;
  sort?: string;
}

export interface CollectionEntry {
  kind: CollectionKind;
  slug: string;
  title: string;
  h1: string;
  short_description: string;
  badge: string;
  cta_label: string;
  hero_image: string;
  filter: CollectionFilter;
  related: string[];
  faqs: { q: string; a: string }[];
  canonical_path: string;
}

type TaxonomyMap = Record<string, Record<string, Omit<CollectionEntry, 'kind' | 'slug' | 'canonical_path'>>>;

const TAXONOMY = taxonomyJson as TaxonomyMap;

export function collectionUrl(kind: CollectionKind, slug: string): string {
  switch (kind) {
    case 'flower':
      return `/flowers/${slug}`;
    case 'relation':
      return `/relation/${slug}`;
    case 'occasion':
      return `/occasion/${slug}`;
    default:
      return `/collection/${slug}`;
  }
}

export function collectionGet(kind: CollectionKind, slug: string): CollectionEntry | null {
  const row = TAXONOMY[kind]?.[slug];
  if (!row) return null;
  return {
    ...row,
    kind,
    slug,
    canonical_path: collectionUrl(kind, slug),
  };
}

export function collectionIsFlowerTypeSlug(slug: string): boolean {
  return Boolean(TAXONOMY.flower?.[slug]);
}

export function collectionList(kind?: CollectionKind): CollectionEntry[] {
  const kinds = kind ? [kind] : (Object.keys(TAXONOMY) as CollectionKind[]);
  const out: CollectionEntry[] = [];
  for (const k of kinds) {
    for (const slug of Object.keys(TAXONOMY[k] ?? {})) {
      const entry = collectionGet(k, slug);
      if (entry) out.push(entry);
    }
  }
  return out;
}

export function collectionResolveRelated(refs: string[]): CollectionEntry[] {
  const out: CollectionEntry[] = [];
  for (const ref of refs) {
    const [kind, slug] = ref.split(':') as [CollectionKind, string];
    const entry = collectionGet(kind, slug);
    if (entry) out.push(entry);
  }
  return out;
}

export function collectionCrossKindLinks(entry: CollectionEntry) {
  return {
    flowers: collectionList('flower').slice(0, 8),
    occasions: collectionList('occasion')
      .filter((e) => e.slug !== 'love')
      .slice(0, 8),
    relations: collectionList('relation').slice(0, 8),
    collections: collectionList('collection').slice(0, 8),
  };
}

export const COLLECTION_CITIES = [
  { name: 'Delhi', href: '/flower-delivery-in-delhi' },
  { name: 'Gurgaon', href: '/flower-delivery-in-gurgaon' },
  { name: 'Noida', href: '/flower-delivery-in-noida' },
  { name: 'Ghaziabad', href: '/flower-delivery-in-ghaziabad' },
  { name: 'Faridabad', href: '/flower-delivery-in-faridabad' },
  { name: 'Greater Noida', href: '/flower-delivery-in-greater-noida' },
];

export const COLLECTION_POPULAR = [
  { label: 'Roses', href: '/flowers/roses' },
  { label: 'Birthday Flowers', href: '/occasion/birthday' },
  { label: 'Anniversary', href: '/occasion/anniversary' },
  { label: 'Same Day Delivery', href: '/collection/same-day-delivery' },
  { label: 'Luxury Flowers', href: '/collection/luxury-flowers' },
  { label: 'For Mother', href: '/relation/mother' },
  { label: 'For Wife', href: '/relation/wife' },
  { label: 'Orchids', href: '/flowers/orchids' },
  { label: 'Budget Under ₹999', href: '/collection/budget-flowers' },
  { label: 'New Arrivals', href: '/collection/new-arrivals' },
];

export function collectionBuildSeoHtml(entry: CollectionEntry): string {
  return `<p>Looking for <strong>${entry.title}</strong> online in Delhi NCR? Sai Flowers handcrafts fresh arrangements for same-day delivery across Delhi, Gurgaon, Noida and nearby areas. ${entry.short_description}</p><p>Browse our curated ${entry.title.toLowerCase()} collection, add a free message card, and checkout securely with UPI or cards. Need help choosing? WhatsApp our florists — we have been arranging blooms since 1998.</p>`;
}

/** Fetch bouquets only for taxonomy landings. Never returns car/first-night décor. */
export async function collectionFetchProducts(
  entry: CollectionEntry,
  limit = 40,
): Promise<Product[]> {
  const filter = entry.filter ?? {};
  // Landings always sell flower bouquets only (ignore cakes/gifts/décor tables).
  const tables = ['flowers'] as const;
  const typeMap: Record<string, 'flower' | 'cake' | 'gift'> = {
    flowers: 'flower',
    cakes: 'cake',
    gifts: 'gift',
  };
  const keywords = [
    ...(filter.name_keywords ?? []),
    ...(filter.tags ?? []),
  ].filter(Boolean);
  const search = keywords[0] ?? entry.slug.replace(/-/g, ' ');
  const sort = (filter.sort as string) || 'bestseller';
  // Over-fetch so décor filtering still leaves a full grid
  const fetchLimit = Math.max(limit * 4, 80);

  const batches = await Promise.all(
    tables.map(async (table) => {
      const type = typeMap[table] ?? 'flower';
      try {
        const { items } = await fetchProducts({
          type,
          sort: sort === 'new' ? 'newest' : sort,
          limit: fetchLimit,
          search: keywords.length ? search : undefined,
          price_min: filter.price_min,
          price_max: filter.price_max,
        });
        return items;
      } catch {
        return [] as Product[];
      }
    }),
  );

  let merged = filterLandingBouquets(batches.flat());
  if (merged.length === 0) {
    try {
      const { items } = await fetchProducts({
        type: 'flower',
        sort: 'bestseller',
        limit: fetchLimit,
        search: 'bouquet',
      });
      merged = filterLandingBouquets(items);
    } catch {
      merged = [];
    }
  }

  // Prefer keyword matches when available (still bouquet-only)
  if (keywords.length > 0) {
    const lower = keywords.map((k) => k.toLowerCase());
    const scored = merged.map((p) => {
      const hay = `${p.name} ${p.tag ?? ''}`.toLowerCase();
      const hit = lower.some((k) => hay.includes(k));
      return { p, hit };
    });
    const hits = scored.filter((s) => s.hit).map((s) => s.p);
    if (hits.length >= 8) merged = hits;
  }

  if (filter.price_min != null) {
    merged = merged.filter((p) => p.price >= Number(filter.price_min));
  }
  if (filter.price_max != null) {
    merged = merged.filter((p) => p.price <= Number(filter.price_max));
  }

  const seen = new Set<string>();
  const unique: Product[] = [];
  for (const p of merged) {
    if (!isLandingBouquetProduct(p)) continue;
    const key = `${p.type}-${p.id}`;
    if (seen.has(key)) continue;
    seen.add(key);
    unique.push(p);
    if (unique.length >= limit) break;
  }

  // Backfill with popular bouquets so the landing is never empty / sparse
  if (unique.length < Math.min(36, limit)) {
    try {
      const { items } = await fetchProducts({
        type: 'flower',
        sort: 'bestseller',
        limit: fetchLimit,
        search: 'bouquet',
      });
      for (const p of filterLandingBouquets(items)) {
        const key = `${p.type}-${p.id}`;
        if (seen.has(key)) continue;
        seen.add(key);
        unique.push(p);
        if (unique.length >= limit) break;
      }
    } catch {
      /* keep what we have */
    }
  }

  return unique;
}

export function collectionSplitGroups(products: Product[]) {
  const all = products;
  const featured = products.filter((p) => (p.rating ?? 0) >= 4.7).slice(0, 12);
  const bestsellers = [...products]
    .sort((a, b) => (b.rating ?? 0) - (a.rating ?? 0))
    .slice(0, 12);
  const recent = [...products].reverse().slice(0, 12);
  const sameday = products.filter((p) => p.deliverySameday !== false).slice(0, 12);
  return { all, featured, bestsellers, recent, sameday };
}
