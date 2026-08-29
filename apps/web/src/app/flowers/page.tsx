import { ShopListing } from '@/components/shop/ShopListing';
import { orderShopProductsForConversion } from '@/lib/bouquet';
import { fetchCategories, fetchProducts } from '@/lib/api';
import { pageMetadataForPath } from '@/lib/site-metadata';
import type { Product } from '@/lib/types';

export const metadata = pageMetadataForPath('/flowers', {
  title: 'Fresh Flowers & Bouquets Online | Sai Flower Delhi',
  description:
    'Order fresh flower bouquets online from Sai Flower. Same-day delivery in Delhi. Roses, orchids, wedding & event flowers.',
});

export const revalidate = 120;

interface PageProps {
  searchParams: Promise<Record<string, string | string[] | undefined>>;
}

function param(value: string | string[] | undefined, fallback = ''): string {
  if (Array.isArray(value)) return value[0] ?? fallback;
  return value ?? fallback;
}

const FLOWER_FAQS = [
  {
    question: 'Do you offer same-day flower delivery in Delhi NCR?',
    answer:
      'Yes — place your order before 6 PM and we deliver the same day across Delhi NCR. Express and midnight delivery slots are also available on select products.',
  },
  {
    question: 'How do you keep the flowers fresh during delivery?',
    answer:
      'Every bouquet is made to order with freshly cut blooms, hydrated right up to dispatch and packaged carefully so it arrives looking its best. Freshness is guaranteed on every order.',
  },
  {
    question: 'Can I customise a bouquet or add a personal message?',
    answer:
      'Absolutely. Many arrangements can be customised, and every order includes a free personalised message card. Contact us for larger custom or wedding requests.',
  },
  {
    question: 'What payment methods do you accept?',
    answer:
      'We accept all major UPI apps, cards and net banking through secure payment gateways. You can also confirm orders via WhatsApp after checkout.',
  },
];

async function fetchAllFlowerProducts(params: {
  sort: string;
  price_min?: string;
  price_max?: string;
  category?: string;
}): Promise<{ items: Product[]; total: number }> {
  const merged: Product[] = [];
  const seen = new Set<number>();
  let total = 0;

  for (let page = 1; page <= 4; page += 1) {
    const listing = await fetchProducts({
      type: 'flower',
      sort: params.sort,
      limit: 100,
      page,
      price_min: params.price_min || undefined,
      price_max: params.price_max || undefined,
      category: params.category || undefined,
    });
    total = listing.meta?.total ?? total;
    for (const item of listing.items) {
      if (seen.has(item.id)) continue;
      seen.add(item.id);
      merged.push(item);
    }
    if (listing.items.length < 100) break;
    if (total > 0 && merged.length >= total) break;
  }

  return {
    items: orderShopProductsForConversion(merged, params.sort),
    total: total || merged.length,
  };
}

export default async function FlowersPage({ searchParams }: PageProps) {
  const params = await searchParams;
  const sort = param(params.sort, 'bestseller');
  const priceMin = param(params.price_min);
  const priceMax = param(params.price_max);
  const category = param(params.category);

  let items: Product[] = [];
  let total = 0;
  let categories: Awaited<ReturnType<typeof fetchCategories>> = [];

  try {
    const [listing, cats] = await Promise.all([
      fetchAllFlowerProducts({
        sort,
        price_min: priceMin || undefined,
        price_max: priceMax || undefined,
        category: category || undefined,
      }),
      fetchCategories(),
    ]);
    items = listing.items;
    total = listing.total;
    categories = cats;
  } catch {
    try {
      categories = await fetchCategories();
    } catch {
      categories = [];
    }
  }

  return (
    <ShopListing
      title="Shop All Flowers"
      subtitle="Found {count} items · Flower bouquets first, then chocolates & more"
      type="flower"
      products={items}
      total={total}
      sort={sort}
      priceMin={priceMin}
      priceMax={priceMax}
      categoryId={category}
      categories={categories}
      basePath="/flowers"
      faqs={FLOWER_FAQS}
      initialVisible={24}
      loadMoreStep={24}
    />
  );
}
