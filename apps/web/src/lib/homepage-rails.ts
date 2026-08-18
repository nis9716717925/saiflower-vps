import {
  filterSliderFlowerBouquets,
  sortByConversion,
} from '@/lib/bouquet';
import { fetchProducts } from '@/lib/api';
import type { Product } from '@/lib/types';

export type HomepageRails = {
  bestSellers: Product[];
  sameDay: Product[];
  onDemand: Product[];
  newlyAdded: Product[];
  occasions: Product[];
  birthday: Product[];
};

type RailKey = keyof HomepageRails;

const RAIL_SIZES: Record<RailKey, number> = {
  bestSellers: 12,
  sameDay: 10,
  onDemand: 10,
  newlyAdded: 10,
  occasions: 10,
  birthday: 10,
};

/** Soft theme match — never forces décor / chocolate into rails. */
function themeScore(product: Product, rail: RailKey): number {
  const hay = `${product.name} ${product.tag ?? ''}`.toLowerCase();
  switch (rail) {
    case 'bestSellers':
      return 1;
    case 'sameDay':
      return /\bsameday\b|\bsame\s*day\b|\bexpress\b/.test(hay) ? 8 : 0;
    case 'onDemand':
      return /\bexpress\b|\bsameday\b|\bsame\s*day\b|\bon\s*demand\b/.test(hay) ? 8 : 1;
    case 'newlyAdded':
      return product.id;
    case 'occasions':
      return /\bbirthday\b|\banniversary\b|\baniversary\b|\bwedding\b|\blove\b/.test(hay)
        ? 8
        : 1;
    case 'birthday':
      return /\bbirthday\b/.test(hay) ? 10 : /\brose\b/.test(hay) ? 3 : 0;
    default:
      return 0;
  }
}

function productKey(p: Product): string {
  return `${p.type}-${p.id}`;
}

/**
 * Build homepage rails from one bouquet pool.
 * Products may appear at most twice across all rails (usually once).
 */
export function allocateHomepageRails(
  pool: Product[],
  maxAppearances = 2,
): HomepageRails {
  const eligible = sortByConversion(filterSliderFlowerBouquets(pool));
  const counts = new Map<string, number>();
  const rails: HomepageRails = {
    bestSellers: [],
    sameDay: [],
    onDemand: [],
    newlyAdded: [],
    occasions: [],
    birthday: [],
  };

  const order: RailKey[] = [
    'bestSellers',
    'sameDay',
    'onDemand',
    'newlyAdded',
    'occasions',
    'birthday',
  ];

  function canUse(p: Product): boolean {
    return (counts.get(productKey(p)) ?? 0) < maxAppearances;
  }

  function take(p: Product, rail: RailKey) {
    rails[rail].push(p);
    const key = productKey(p);
    counts.set(key, (counts.get(key) ?? 0) + 1);
  }

  for (const rail of order) {
    const size = RAIL_SIZES[rail];
    const ranked = [...eligible].sort((a, b) => {
      const aCount = counts.get(productKey(a)) ?? 0;
      const bCount = counts.get(productKey(b)) ?? 0;
      // Prefer unused products first
      if (aCount !== bCount) return aCount - bCount;
      if (rail === 'newlyAdded') return b.id - a.id;
      const themeDiff = themeScore(b, rail) - themeScore(a, rail);
      if (themeDiff !== 0) return themeDiff;
      return 0; // already conversion-sorted in eligible
    });

    for (const p of ranked) {
      if (rails[rail].length >= size) break;
      if (!canUse(p)) continue;
      // Avoid repeating inside the same rail
      if (rails[rail].some((x) => productKey(x) === productKey(p))) continue;
      take(p, rail);
    }
  }

  // Soft fill gaps from unused products only (still flower bouquets)
  for (const rail of order) {
    if (rails[rail].length >= RAIL_SIZES[rail]) continue;
    for (const p of eligible) {
      if (rails[rail].length >= RAIL_SIZES[rail]) break;
      if ((counts.get(productKey(p)) ?? 0) !== 0) continue;
      if (rails[rail].some((x) => productKey(x) === productKey(p))) continue;
      take(p, rail);
    }
  }

  return rails;
}

/** Fetch a wide flower catalog and allocate distinct homepage rails. */
export async function loadHomepageRails(): Promise<HomepageRails> {
  const pages: Product[] = [];
  const seen = new Set<string>();

  for (const page of [1, 2, 3]) {
    try {
      const { items } = await fetchProducts({
        type: 'flower',
        sort: page === 3 ? 'newest' : 'bestseller',
        limit: 100,
        page,
      });
      for (const p of items) {
        const key = productKey(p);
        if (seen.has(key)) continue;
        seen.add(key);
        pages.push(p);
      }
    } catch {
      /* keep what we have */
    }
  }

  // Extra bouquet-focused pull if pool is thin
  if (filterSliderFlowerBouquets(pages).length < 40) {
    try {
      const { items } = await fetchProducts({
        type: 'flower',
        sort: 'bestseller',
        limit: 100,
        search: 'bouquet',
      });
      for (const p of items) {
        const key = productKey(p);
        if (seen.has(key)) continue;
        seen.add(key);
        pages.push(p);
      }
    } catch {
      /* ignore */
    }
  }

  return allocateHomepageRails(pages, 2);
}
