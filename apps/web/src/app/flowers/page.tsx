import { ShopListing } from '@/components/shop/ShopListing';
import { fetchProducts } from '@/lib/api';

interface PageProps {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}

function param(value: string | string[] | undefined, fallback = ''): string {
  if (Array.isArray(value)) return value[0] ?? fallback;
  return value ?? fallback;
}

async function loadListing(
  type: 'flower' | 'cake' | 'gift',
  searchParams: Record<string, string | string[] | undefined>,
) {
  const sort = param(searchParams.sort, 'bestseller');
  const priceMin = param(searchParams.price_min);
  const priceMax = param(searchParams.price_max);
  const { items, meta } = await fetchProducts({
    type,
    sort,
    limit: 48,
    price_min: priceMin || undefined,
    price_max: priceMax || undefined,
  });
  return { items, total: meta?.total ?? items.length, sort, priceMin, priceMax };
}

export default async function FlowersPage({ searchParams }: PageProps) {
  const params = await searchParams;
  let listing = { items: [] as Awaited<ReturnType<typeof loadListing>>['items'], total: 0, sort: 'bestseller', priceMin: '', priceMax: '' };
  try {
    listing = await loadListing('flower', params);
  } catch {
    listing = { items: [], total: 0, sort: param(params.sort, 'bestseller'), priceMin: param(params.price_min), priceMax: param(params.price_max) };
  }

  return (
    <ShopListing
      title="Shop All Flowers"
      subtitle="Found {count} bouquets · Flowers first, décor last"
      type="flower"
      products={listing.items}
      total={listing.total}
      sort={listing.sort}
      priceMin={listing.priceMin}
      priceMax={listing.priceMax}
      basePath="/flowers"
    />
  );
}
