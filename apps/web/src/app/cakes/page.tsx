import { CategoryShopListing } from '@/components/shop/CategoryShopListing';
import { fetchProducts } from '@/lib/api';
import { fetchLandingBouquets } from '@/lib/bouquet';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Order Cakes Online | Birthday & Celebration Cakes — Sai Flower',
  description:
    'Celebrate with designer cakes and sweet treats from Sai Flower, delivered same-day across Delhi NCR.',
  canonical: '/cakes',
});

interface PageProps {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}

function param(value: string | string[] | undefined, fallback = ''): string {
  if (Array.isArray(value)) return value[0] ?? fallback;
  return value ?? fallback;
}

export default async function CakesPage({ searchParams }: PageProps) {
  const params = await searchParams;
  const sortRaw = param(params.sort, 'new');
  const sort = sortRaw === 'bestseller' || sortRaw === 'newest' ? 'new' : sortRaw;
  const priceMin = param(params.price_min);
  const priceMax = param(params.price_max);

  let items: Awaited<ReturnType<typeof fetchProducts>>['items'] = [];
  let recommend: Awaited<ReturnType<typeof fetchLandingBouquets>> = [];

  try {
    const [cakes, flowers] = await Promise.all([
      fetchProducts({
        type: 'cake',
        sort,
        limit: 100,
        price_min: priceMin || undefined,
        price_max: priceMax || undefined,
      }),
      fetchLandingBouquets({ sort: 'bestseller', limit: 12, search: 'birthday' }),
    ]);
    items = cakes.items;
    recommend = flowers;
  } catch {
    items = [];
  }

  return (
    <CategoryShopListing
      pageKey="cakes"
      badge="Cakes"
      title="Celebration Cakes"
      description="Birthday, anniversary and party cakes — when stock is limited, our flower bouquets are always ready for same-day gifting."
      heroImage="https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1600&q=80"
      products={items}
      sort={sort}
      recommendProducts={recommend}
      recommendTitle="Bouquet recommendations"
      recommendSub="Fresh flower bouquets ready for same-day gifting across Delhi NCR."
      chips={[
        { label: 'All Cakes', href: '/cakes', active: true },
        { label: 'Birthday', href: '/occasion/birthday' },
        { label: 'Anniversary', href: '/occasion/anniversary' },
        { label: 'Flowers', href: '/flowers' },
      ]}
    />
  );
}
