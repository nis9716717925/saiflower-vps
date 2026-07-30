import { ShopListing } from '@/components/shop/ShopListing';
import { fetchProducts } from '@/lib/api';

interface PageProps {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}

function param(value: string | string[] | undefined, fallback = ''): string {
  if (Array.isArray(value)) return value[0] ?? fallback;
  return value ?? fallback;
}

export default async function CakesPage({ searchParams }: PageProps) {
  const params = await searchParams;
  const sort = param(params.sort, 'bestseller');
  const priceMin = param(params.price_min);
  const priceMax = param(params.price_max);
  let items: Awaited<ReturnType<typeof fetchProducts>>['items'] = [];
  let total = 0;
  try {
    const data = await fetchProducts({
      type: 'cake',
      sort,
      limit: 48,
      price_min: priceMin || undefined,
      price_max: priceMax || undefined,
    });
    items = data.items;
    total = data.meta?.total ?? items.length;
  } catch {
    items = [];
  }

  return (
    <ShopListing
      title="Shop All Cakes"
      subtitle="Found {count} cakes · Perfect for every celebration"
      type="cake"
      products={items}
      total={total}
      sort={sort}
      priceMin={priceMin}
      priceMax={priceMax}
      basePath="/cakes"
    />
  );
}
