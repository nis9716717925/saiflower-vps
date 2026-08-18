import { fetchProducts, getApiBase } from '@/lib/api';
import type { Product } from '@/lib/types';

const EVENT_DECOR_BLOCKED = [
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
  /\bjaimala\b/,
  /\bjaiamal\b/,
  /\bvarmala\b/,
  /\bgarland\b/,
  /\bphoolon\s*ki\s*saree\b/,
  /\bcanopy\b/,
  /\bhaldi\b/,
  /\bentry\s*decor/,
  /\bbed\s*decor/,
  /\bcardecoration\b/,
  /\bweddingdecoration\b/,
  /\bbeddecoration\b/,
];

function productHaystack(product: Product): string {
  return `${product.name ?? ''} ${product.tag ?? ''}`.trim().toLowerCase();
}

/** Event / wedding décor / car / jaimala — never in homepage sliders. */
export function isEventDecorProduct(product: Product): boolean {
  const hay = productHaystack(product);
  if (!hay) return false;
  return EVENT_DECOR_BLOCKED.some((re) => re.test(hay));
}

export function isChocolateBouquetProduct(product: Product): boolean {
  const hay = productHaystack(product);
  return (
    /\bchocolate\b/.test(hay) ||
    /\bferrero\b/.test(hay) ||
    /\bkitkat\b/.test(hay) ||
    /\bcadbury\b/.test(hay) ||
    /\brocher\b/.test(hay)
  );
}

/**
 * Fresh flower variety bouquets for homepage rails & landings.
 * Excludes chocolate, car décor, jaimala, first-night, pots, etc.
 */
export function isSliderFlowerBouquet(product: Product): boolean {
  const name = (product.name ?? '').trim().toLowerCase();
  if (!name) return false;
  if (isEventDecorProduct(product)) return false;
  if (isChocolateBouquetProduct(product)) return false;
  if (/\bcrochet\b/.test(name) || /\bflower\s*pot\b/.test(name)) return false;
  if (name.includes('bouquet')) return true;
  if (name.includes('flower basket') || name.includes('flower box')) return true;
  if (/\bwreath\b/.test(name) && !isEventDecorProduct(product)) return true;
  return false;
}

/**
 * Mirrors PHP `landing_is_bouquet_product()` — gift-ready bouquets for landings.
 * Chocolate bouquets are allowed on landings but not homepage sliders.
 */
export function isLandingBouquetProduct(product: Product): boolean {
  const name = (product.name ?? '').trim().toLowerCase();
  if (!name) return false;
  if (isEventDecorProduct(product)) return false;
  if (name.includes('bouquet')) return true;
  if (name.includes('flower basket') || name.includes('flower box')) return true;
  return false;
}

export function filterLandingBouquets(products: Product[]): Product[] {
  return products.filter(isLandingBouquetProduct);
}

export function filterSliderFlowerBouquets(products: Product[]): Product[] {
  return products.filter(isSliderFlowerBouquet);
}

/** Conversion-oriented score for merchandising (higher = show earlier). */
export function conversionScore(product: Product): number {
  const price = Number(product.price) || 0;
  const rating = Number(product.rating) || 4.5;
  const orig = Number(product.originalPrice) || 0;
  const discount =
    orig > price && price > 0 ? Math.min(55, Math.round(((orig - price) / orig) * 100)) : 0;
  const hay = productHaystack(product);

  let score = rating * 18;
  // Sweet spot gifting prices convert best
  if (price >= 699 && price <= 2499) score += 40;
  else if (price >= 499 && price <= 3499) score += 22;
  else if (price > 4999) score -= 12;

  score += Math.min(28, discount * 0.55);

  if (product.inStock !== false) score += 8;
  if (/\bsameday\b/.test(hay) || product.deliverySameday === true) score += 16;
  if (/\brose\b/.test(hay) || /\bredroses\b/.test(hay)) score += 6;
  if (/\bbirthday\b/.test(hay) || /\baniversary\b/.test(hay) || /\baniversary\b/.test(hay)) {
    score += 5;
  }
  if (isChocolateBouquetProduct(product)) score -= 4;
  if (isEventDecorProduct(product)) score -= 40;

  // Prefer fresher catalog entries slightly
  score += Math.min(10, (product.id % 40) / 4);

  return score;
}

export function sortByConversion(products: Product[]): Product[] {
  return [...products].sort((a, b) => {
    const diff = conversionScore(b) - conversionScore(a);
    if (diff !== 0) return diff;
    return b.id - a.id;
  });
}

/**
 * Shop merchandising tiers (flowers page shows ALL, ordered for conversion):
 * 0 = flower bouquets, 1 = chocolate bouquets, 2 = other giftables, 3 = décor last.
 */
export function shopMerchTier(product: Product): number {
  if (isEventDecorProduct(product)) return 3;
  if (isChocolateBouquetProduct(product)) return 1;
  if (isSliderFlowerBouquet(product) || isLandingBouquetProduct(product)) return 0;
  return 2;
}

export function orderShopProductsForConversion(
  products: Product[],
  sort = 'bestseller',
): Product[] {
  return [...products].sort((a, b) => {
    const tierDiff = shopMerchTier(a) - shopMerchTier(b);
    if (tierDiff !== 0) return tierDiff;
    switch (sort) {
      case 'price_low':
        return a.price - b.price;
      case 'price_high':
        return b.price - a.price;
      case 'newest':
      case 'new':
        return b.id - a.id;
      case 'rating':
        return (b.rating ?? 0) - (a.rating ?? 0) || b.id - a.id;
      default:
        return conversionScore(b) - conversionScore(a) || b.id - a.id;
    }
  });
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
      return sortByConversion(bouquets).slice(0, limit);
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
    return sortByConversion(bouquets).slice(0, limit);
  } catch (error) {
    console.error('[bouquet] product fetch failed', {
      apiBase: getApiBase(),
      params,
      message: error instanceof Error ? error.message : String(error),
    });
    return [];
  }
}
