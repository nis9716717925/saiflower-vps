import { CategoryShopListing } from '@/components/shop/CategoryShopListing';
import { fetchProducts } from '@/lib/api';
import { fetchLandingBouquets } from '@/lib/bouquet';

interface PageProps {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}

function param(value: string | string[] | undefined, fallback = ''): string {
  if (Array.isArray(value)) return value[0] ?? fallback;
  return value ?? fallback;
}

export default async function GiftsPage({ searchParams }: PageProps) {
  const params = await searchParams;
  const sortRaw = param(params.sort, 'new');
  const sort = sortRaw === 'bestseller' || sortRaw === 'newest' ? 'new' : sortRaw;
  const priceMin = param(params.price_min);
  const priceMax = param(params.price_max);

  let items: Awaited<ReturnType<typeof fetchProducts>>['items'] = [];
  let recommend: Awaited<ReturnType<typeof fetchLandingBouquets>> = [];

  try {
    const [gifts, flowers] = await Promise.all([
      fetchProducts({
        type: 'gift',
        sort,
        limit: 100,
        price_min: priceMin || undefined,
        price_max: priceMax || undefined,
      }),
      fetchLandingBouquets({ sort: 'bestseller', limit: 12, search: 'gift' }),
    ]);
    items = gifts.items;
    recommend = flowers;
  } catch {
    items = [];
  }

  return (
    <CategoryShopListing
      pageKey="gifts"
      badge="Gifts"
      title="Thoughtful Gifts"
      description="Curated gift picks for every relationship. When stock runs low, fresh flower bouquets are ready for same-day delivery."
      heroImage="https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=1600&q=80"
      products={items}
      sort={sort}
      recommendProducts={recommend}
      recommendTitle="Bouquet recommendations"
      recommendSub="Fresh flower bouquets ready for same-day gifting across Delhi NCR."
      chips={[
        { label: 'All Gifts', href: '/gifts', active: true },
        { label: 'Personalised', href: '/personalized' },
        { label: 'Photo Frames', href: '/personalized/photo-frames' },
        { label: 'Engraved', href: '/personalized/engraved-gifts' },
        { label: 'Hampers', href: '/collection/hampers' },
      ]}
    />
  );
}
