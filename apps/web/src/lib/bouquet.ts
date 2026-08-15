import { fetchProducts, getApiBase } from '@/lib/api';
import type { Product } from '@/lib/types';

/**
 * Mirrors PHP `landing_is_bouquet_product()` in includes/landing_page_sliders.php.
 * Landings sell flower bouquets only — never car/first-night/wedding-event décor.
 */
export function isLandingBouquetProduct(product: Product): boolean {
  const name = (product.name ?? '').trim().toLowerCase();
  const tag = (product.tag ?? '').trim().toLowerCase();
  const hay = `${name} ${tag}`;

  if (!name) return false;

  const blocked = [
    /\bcar\b/,
    /\bevent\b/,
    /\bdecor\b/,
    /\bdecoration\b/,
    /\bworkshop\b/,
    /\bvenue\b/,
    /\bpackage\b/,
    /\bfirst\s*night\b/,
    /\broom\s*decor/,
    /\bstage\b/,
    /\bmandap\b/,
    /\bbackdrop\b/,
    /\bwedding\s*decor/,
    /\bgarland\s*install/,
  ];
  if (blocked.some((re) => re.test(hay))) return false;

  if (name.includes('bouquet')) return true;
  if (name.includes('flower basket') || name.includes('flower box')) return true;
  return false;
}

/** Keep only bouquet merchandise for collection / location / personalized landings. */
export function filterLandingBouquets(products: Product[]): Product[] {
  return products.filter(isLandingBouquetProduct);
}

/** Fetch flower products and keep bouquets only (over-fetches to fill after filter). */
export async function fetchLandingBouquets(
  params: {
    limit?: number;
    sort?: string;
    search?: string;
    price_min?: string | number;
    price_max?: string | number;
  } = {},
): Promise<Product[]> {
  const limit = params.limit ?? 40;
  const fetchLimit = Math.max(limit * 4, 80);
  try {
    const { items } = await fetchProducts({
      type: 'flower',
      sort: params.sort ?? 'bestseller',
      limit: fetchLimit,
      search: params.search,
      price_min: params.price_min,
      price_max: params.price_max,
    });
    const bouquets = filterLandingBouquets(items);
    if (bouquets.length >= Math.min(8, limit)) {
      return bouquets.slice(0, limit);
    }
    const fill = await fetchProducts({
      type: 'flower',
      sort: 'bestseller',
      limit: fetchLimit,
      search: 'bouquet',
    });
    const seen = new Set(bouquets.map((p) => `${p.type}-${p.id}`));
    for (const p of filterLandingBouquets(fill.items)) {
      const key = `${p.type}-${p.id}`;
      if (seen.has(key)) continue;
      seen.add(key);
      bouquets.push(p);
      if (bouquets.length >= limit) break;
    }
    return bouquets.slice(0, limit);
  } catch (error) {
    console.error('[bouquet] product fetch failed', {
      apiBase: getApiBase(),
      params,
      message: error instanceof Error ? error.message : String(error),
    });
    return [];
  }
}
