'use client';

import Link from 'next/link';
import { ProductCard } from '@/components/shop/ProductCard';
import type { Product } from '@/lib/types';

interface ShopListingProps {
  title: string;
  subtitle: string;
  type: 'flower' | 'cake' | 'gift';
  products: Product[];
  total: number;
  sort: string;
  priceMin?: string;
  priceMax?: string;
  basePath: string;
}

const SORT_OPTIONS = [
  { value: 'bestseller', label: 'Best Selling' },
  { value: 'newest', label: 'Newest' },
  { value: 'rating', label: 'Highest Rated' },
  { value: 'price_low', label: 'Price: Low' },
  { value: 'price_high', label: 'Price: High' },
];

export function ShopListing({
  title,
  subtitle,
  products,
  total,
  sort,
  priceMin = '',
  priceMax = '',
  basePath,
}: ShopListingProps) {
  const hasFilters = Boolean(priceMin || priceMax);

  return (
    <main className="container mx-auto px-2 md:px-4 pt-2 md:pt-8 pb-8 relative flex flex-row gap-4 md:gap-8 justify-center">
      <aside className="hidden lg:block w-64 flex-shrink-0">
        <div className="bg-white rounded-2xl border border-slate-100 p-6 sticky top-24 shadow-sm">
          <h2 className="font-bold text-lg mb-6">Filters</h2>
          <form action={basePath} method="GET" className="space-y-6">
            <div>
              <h3 className="text-xs font-bold uppercase tracking-wider mb-3 text-slate-400">Sort By</h3>
              <select
                name="sort"
                defaultValue={sort}
                onChange={(e) => e.currentTarget.form?.submit()}
                className="w-full bg-slate-50 border-slate-200 rounded-lg text-sm font-semibold focus:ring-primary py-2 px-3"
              >
                {SORT_OPTIONS.map((opt) => (
                  <option key={opt.value} value={opt.value}>
                    {opt.label}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <h3 className="text-xs font-bold uppercase tracking-wider mb-3 text-slate-400">Price Range</h3>
              <div className="space-y-2">
                <Link href={`${basePath}?sort=${sort}`} className="block text-sm hover:text-primary">
                  All prices
                </Link>
                <Link
                  href={`${basePath}?sort=${sort}&price_min=0&price_max=500`}
                  className="block text-sm hover:text-primary"
                >
                  Under ₹500
                </Link>
                <Link
                  href={`${basePath}?sort=${sort}&price_min=500&price_max=1000`}
                  className="block text-sm hover:text-primary"
                >
                  ₹500 – ₹1000
                </Link>
                <Link
                  href={`${basePath}?sort=${sort}&price_min=1000&price_max=2000`}
                  className="block text-sm hover:text-primary"
                >
                  ₹1000 – ₹2000
                </Link>
                <Link href={`${basePath}?sort=${sort}&price_min=2000`} className="block text-sm hover:text-primary">
                  Over ₹2000
                </Link>
              </div>
            </div>

            {hasFilters && (
              <Link href={basePath} className="block text-center text-sm text-red-500 font-bold">
                Clear filters
              </Link>
            )}
          </form>
        </div>
      </aside>

      <section className="flex-1 min-w-0" id="product-grid">
        <div className="flex flex-row items-center justify-between gap-4 mb-6 hidden md:flex">
          <div>
            <h1 className="text-3xl font-bold mb-1 text-slate-900">{title}</h1>
            <p className="text-slate-500 text-sm">{subtitle.replace('{count}', String(total))}</p>
          </div>
          <form action={basePath} method="GET">
            {priceMin ? <input type="hidden" name="price_min" value={priceMin} /> : null}
            {priceMax ? <input type="hidden" name="price_max" value={priceMax} /> : null}
            <select
              name="sort"
              defaultValue={sort}
              onChange={(e) => e.currentTarget.form?.submit()}
              className="bg-white border-slate-200 rounded-lg text-sm font-semibold focus:ring-primary py-2 pl-3 pr-8 shadow-sm cursor-pointer"
            >
              {SORT_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </select>
          </form>
        </div>

        {total > 0 ? (
          <div className="sp-grid">
            {products.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        ) : (
          <div className="text-center py-16 text-slate-500">
            <p className="text-lg font-semibold mb-2">No products found</p>
            <Link href={basePath} className="text-primary font-bold hover:underline">
              View all
            </Link>
          </div>
        )}
      </section>
    </main>
  );
}
