import { ShopListing } from '@/components/shop/ShopListing';
import { fetchCategories, fetchProducts } from '@/lib/api';

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

export default async function FlowersPage({ searchParams }: PageProps) {
  const params = await searchParams;
  const sort = param(params.sort, 'bestseller');
  const priceMin = param(params.price_min);
  const priceMax = param(params.price_max);
  const category = param(params.category);

  let items: Awaited<ReturnType<typeof fetchProducts>>['items'] = [];
  let total = 0;
  let categories: Awaited<ReturnType<typeof fetchCategories>> = [];

  try {
    const [listing, cats] = await Promise.all([
      fetchProducts({
        type: 'flower',
        sort,
        limit: 200,
        price_min: priceMin || undefined,
        price_max: priceMax || undefined,
        category: category || undefined,
      }),
      fetchCategories(),
    ]);
    items = listing.items;
    total = listing.meta?.total ?? items.length;
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
      subtitle="Found {count} bouquets · Flowers first, décor last"
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
    />
  );
}
