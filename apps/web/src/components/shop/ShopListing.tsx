'use client';

import Link from 'next/link';
import { useCallback, useEffect, useState } from 'react';
import { FlowerShopCard } from '@/components/shop/FlowerShopCard';
import type { Product, ShopCategory } from '@/lib/types';

interface ShopListingProps {
  title: string;
  subtitle: string;
  type: 'flower' | 'cake' | 'gift';
  products: Product[];
  total: number;
  sort: string;
  priceMin?: string;
  priceMax?: string;
  categoryId?: string;
  categories?: ShopCategory[];
  basePath: string;
  faqs?: { question: string; answer: string }[];
}

const SORT_OPTIONS = [
  { value: 'bestseller', label: 'Best Selling' },
  { value: 'newest', label: 'Newest' },
  { value: 'rating', label: 'Highest Rated' },
  { value: 'price_low', label: 'Price: Low' },
  { value: 'price_high', label: 'Price: High' },
];

const MOBILE_SORT = [
  { value: 'bestseller', label: 'Best Selling' },
  { value: 'newest', label: 'Newest First' },
  { value: 'price_low', label: 'Price: Low to High' },
  { value: 'price_high', label: 'Price: High to Low' },
];

function orderCategoriesFlowerFirst(categories: ShopCategory[]): ShopCategory[] {
  const decor: ShopCategory[] = [];
  const floral: ShopCategory[] = [];
  for (const cat of categories) {
    if (/decor|car|wedding décor|wedding decor|event|stage|first night|room decor/i.test(cat.name)) {
      decor.push(cat);
    } else {
      floral.push(cat);
    }
  }
  return [...floral, ...decor];
}

function priceChecked(
  priceMin: string,
  priceMax: string,
  min: number,
  max: number | '',
): boolean {
  if (max === '') return Number(priceMin) === min && !priceMax;
  return Number(priceMin) === min && Number(priceMax) === max;
}

export function ShopListing({
  title,
  subtitle,
  products,
  total,
  sort,
  priceMin = '',
  priceMax = '',
  categoryId = '',
  categories = [],
  basePath,
  faqs = [],
}: ShopListingProps) {
  const [filterOpen, setFilterOpen] = useState(false);
  const orderedCats = orderCategoriesFlowerFirst(categories);
  const activeCategory = categoryId ? Number(categoryId) : null;
  const hasPriceOrCat = Boolean(priceMin || priceMax || activeCategory);

  const closeFilter = useCallback(() => setFilterOpen(false), []);

  useEffect(() => {
    document.body.style.overflow = filterOpen ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [filterOpen]);

  function hrefWith(params: Record<string, string | undefined>) {
    const q = new URLSearchParams();
    const merged: Record<string, string | undefined> = {
      sort,
      category: categoryId || undefined,
      price_min: priceMin || undefined,
      price_max: priceMax || undefined,
      ...params,
    };
    for (const [k, v] of Object.entries(merged)) {
      if (v !== undefined && v !== '') q.set(k, v);
    }
    const qs = q.toString();
    return qs ? `${basePath}?${qs}` : basePath;
  }

  return (
    <div className="bg-[#f7f4ee] text-slate-900 font-sans antialiased min-h-screen">
      {/* Mobile horizontal filter chips — matches flowers.php */}
      <div className="md:hidden bg-white border-b-2 border-slate-200 py-3 px-3 flex items-center overflow-x-auto gap-3 hide-scrollbar shadow-[0_4px_12px_rgba(0,0,0,0.08)] relative z-40">
        <button
          type="button"
          onClick={() => setFilterOpen(true)}
          className="flex items-center gap-1.5 bg-slate-100 border-2 border-slate-200 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex-shrink-0 uppercase tracking-tight text-slate-800"
        >
          <span className="material-icons-outlined text-base">tune</span> Filters
        </button>
        <div className="h-6 w-[2px] bg-slate-200 flex-shrink-0 rounded-full" />
        <button
          type="button"
          onClick={() => setFilterOpen(true)}
          className="flex items-center gap-1 bg-slate-100 border-2 border-slate-200 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex-shrink-0 uppercase tracking-tight text-slate-800"
        >
          Sort
          <span className="material-icons-outlined text-base text-slate-400">expand_more</span>
        </button>
        <button
          type="button"
          onClick={() => setFilterOpen(true)}
          className="flex items-center gap-1 bg-slate-100 border-2 border-slate-200 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex-shrink-0 uppercase tracking-tight text-slate-800"
        >
          Price
          <span className="material-icons-outlined text-base text-slate-400">expand_more</span>
        </button>
        {hasPriceOrCat ? (
          <Link
            href={basePath}
            className="bg-red-50 text-red-600 border-2 border-red-100 px-4 py-2 rounded-full text-[12px] font-black active:scale-95 transition-transform flex items-center gap-1 flex-shrink-0 uppercase tracking-tight"
          >
            <span className="material-icons-outlined text-sm">close</span> Clear
          </Link>
        ) : null}
      </div>

      <main className="container mx-auto px-2 md:px-4 pt-2 md:pt-8 pb-8 relative flex flex-row gap-4 md:gap-8 justify-center">
        <aside
          className="sticky top-[80px] md:top-28 z-30 h-[calc(100vh-100px)] md:h-[calc(100vh-120px)] overflow-y-auto w-16 md:w-64 flex-shrink-0 bg-transparent md:bg-white md:rounded-2xl md:border border-slate-100 md:shadow-sm"
          style={{ WebkitOverflowScrolling: 'touch' }}
        >
          {/* Mobile icon category rail */}
          <div className="md:hidden flex flex-col gap-4 pt-4 pb-32 items-center bg-white/80 backdrop-blur-md rounded-r-2xl border-r-2 border-y-2 border-slate-100 shadow-md">
            <Link href={hrefWith({ category: undefined })} className="flex flex-col items-center gap-1 group">
              <div
                className={`w-11 h-11 rounded-full bg-slate-50 border-2 flex items-center justify-center transition-all bg-white ${
                  !activeCategory ? 'border-primary' : 'border-transparent shadow-sm'
                }`}
              >
                <span
                  className={`material-icons-outlined text-base ${
                    !activeCategory ? 'text-primary' : 'text-slate-400'
                  }`}
                >
                  all_inclusive
                </span>
              </div>
              <span
                className={`text-[10px] font-black text-center leading-tight ${
                  !activeCategory ? 'text-primary' : 'text-slate-500'
                }`}
              >
                All
              </span>
            </Link>
            {orderedCats.map((cat) => {
              const active = activeCategory === cat.id;
              return (
                <Link
                  key={cat.id}
                  href={hrefWith({ category: String(cat.id) })}
                  className="flex flex-col items-center gap-1 group"
                >
                  <div
                    className={`w-11 h-11 rounded-full bg-slate-50 border-2 flex items-center justify-center overflow-hidden transition-all bg-white ${
                      active ? 'border-primary' : 'border-transparent shadow-sm'
                    }`}
                  >
                    {cat.image ? (
                      <img
                        src={cat.image}
                        className="w-full h-full object-cover"
                        alt={`${cat.name} category`}
                      />
                    ) : (
                      <span
                        className={`material-icons-outlined text-base ${
                          active ? 'text-primary' : 'text-slate-400'
                        }`}
                      >
                        local_florist
                      </span>
                    )}
                  </div>
                  <span
                    className={`text-[10px] font-black text-center leading-tight ${
                      active ? 'text-primary' : 'text-slate-500'
                    }`}
                  >
                    {cat.name}
                  </span>
                </Link>
              );
            })}
          </div>

          {/* Desktop sidebar */}
          <div className="hidden md:block space-y-6">
            <div className="p-6">
              <h2 className="text-lg font-bold mb-6 text-slate-900 border-b pb-2">Categories</h2>
              <div className="grid grid-cols-2 lg:grid-cols-2 gap-4">
                <Link href={hrefWith({ category: undefined })} className="flex flex-col items-center gap-2 group">
                  <div
                    className={`w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center border-2 group-hover:border-primary transition-all overflow-hidden ${
                      !activeCategory ? 'border-primary' : 'border-transparent'
                    }`}
                  >
                    <span
                      className={`material-icons-outlined text-2xl group-hover:text-primary ${
                        !activeCategory ? 'text-primary' : 'text-slate-400'
                      }`}
                    >
                      all_inclusive
                    </span>
                  </div>
                  <span
                    className={`text-xs font-black group-hover:text-primary ${
                      !activeCategory ? 'text-primary' : 'text-slate-600'
                    }`}
                  >
                    All
                  </span>
                </Link>
                {orderedCats.map((cat) => {
                  const active = activeCategory === cat.id;
                  return (
                    <Link
                      key={cat.id}
                      href={hrefWith({ category: String(cat.id) })}
                      className="flex flex-col items-center gap-2 group"
                    >
                      <div
                        className={`w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center border-2 group-hover:border-primary transition-all overflow-hidden ${
                          active ? 'border-primary' : 'border-transparent'
                        }`}
                      >
                        {cat.image ? (
                          <img
                            src={cat.image}
                            className="w-full h-full object-cover"
                            alt={`${cat.name} category`}
                          />
                        ) : (
                          <span
                            className={`material-icons-outlined text-2xl group-hover:text-primary ${
                              active ? 'text-primary' : 'text-slate-400'
                            }`}
                          >
                            local_florist
                          </span>
                        )}
                      </div>
                      <span
                        className={`text-xs font-black text-center group-hover:text-primary ${
                          active ? 'text-primary' : 'text-slate-600'
                        }`}
                      >
                        {cat.name}
                      </span>
                    </Link>
                  );
                })}
              </div>
            </div>

            <div className="p-6 border-t border-slate-100">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-lg font-bold">Filters</h2>
                {priceMin || priceMax ? (
                  <Link
                    href={hrefWith({ price_min: undefined, price_max: undefined })}
                    className="text-xs bg-red-50 text-red-500 px-2 py-1 rounded-md font-bold hover:bg-red-100 transition-colors"
                  >
                    Clear All
                  </Link>
                ) : null}
              </div>

              <form action={basePath} method="GET" id="desktopFilterForm">
                <input type="hidden" name="sort" value={sort} />
                {activeCategory ? (
                  <input type="hidden" name="category" value={activeCategory} />
                ) : null}
                <div className="mb-6">
                  <h3 className="text-xs font-bold uppercase tracking-wider mb-4 text-slate-400">
                    Price Range
                  </h3>
                  <div className="space-y-3">
                    {[
                      { min: 0, max: 500 as number | '', label: 'Under ₹500' },
                      { min: 500, max: 1000 as number | '', label: '₹500 - ₹1000' },
                      { min: 1000, max: 2000 as number | '', label: '₹1000 - ₹2000' },
                      { min: 2000, max: '' as number | '', label: 'Over ₹2000' },
                    ].map((band) => (
                      <label key={band.label} className="flex items-center gap-3 cursor-pointer group">
                        <input
                          type="radio"
                          name="price_range"
                          className="text-primary focus:ring-primary border-slate-300"
                          defaultChecked={priceChecked(priceMin, priceMax, band.min, band.max)}
                          onChange={(e) => {
                            const form = e.currentTarget.form;
                            if (!form) return;
                            const minEl = form.querySelector('#d_min') as HTMLInputElement;
                            const maxEl = form.querySelector('#d_max') as HTMLInputElement;
                            if (minEl) minEl.value = String(band.min);
                            if (maxEl) maxEl.value = band.max === '' ? '' : String(band.max);
                            form.submit();
                          }}
                        />
                        <span className="text-sm text-slate-600 group-hover:text-primary transition-colors">
                          {band.label}
                        </span>
                      </label>
                    ))}
                  </div>
                  <input type="hidden" name="price_min" id="d_min" defaultValue={priceMin} />
                  <input type="hidden" name="price_max" id="d_max" defaultValue={priceMax} />
                </div>
              </form>
            </div>
          </div>
        </aside>

        <section className="flex-1 min-w-0" id="product-grid">
          <div className="flex flex-row items-center justify-between gap-4 mb-6 hidden md:flex">
            <div>
              <h1 className="text-3xl font-bold mb-1 text-slate-900">{title}</h1>
              <p className="text-slate-500 text-sm">{subtitle.replace('{count}', String(total))}</p>
            </div>
            <form action={basePath} method="GET">
              {activeCategory ? (
                <input type="hidden" name="category" value={activeCategory} />
              ) : null}
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
            <div className="grid grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6">
              {products.map((product) => (
                <FlowerShopCard key={product.id} product={product} />
              ))}
            </div>
          ) : (
            <div className="text-center py-20 bg-white rounded-3xl border border-dashed border-slate-300">
              <span className="material-icons-outlined text-4xl text-slate-300 mb-4">search_off</span>
              <h3 className="text-xl font-bold text-slate-800">No flowers found</h3>
              <p className="text-slate-500 mt-2">Try adjusting your filters.</p>
              <Link
                href={basePath}
                className="inline-block mt-6 px-6 py-2 bg-primary text-white rounded-full font-bold text-sm"
              >
                Clear Filters
              </Link>
            </div>
          )}
        </section>
      </main>

      {faqs.length > 0 ? (
        <section className="mt-8 md:mt-20 border-t border-slate-100 pt-10 md:pt-16 pb-10 container mx-auto">
          <div className="max-w-3xl mx-auto px-4">
            <h2 className="text-2xl font-bold text-center mb-10 text-slate-900">
              Frequently Asked Questions
            </h2>
            <div className="space-y-4">
              {faqs.map((item) => (
                <details
                  key={item.question}
                  className="faq-item-box bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-primary/30 transition-colors group"
                >
                  <summary className="w-full flex items-center justify-between p-5 text-left font-bold text-slate-800 bg-transparent cursor-pointer list-none">
                    <span>{item.question}</span>
                    <span className="material-icons-outlined text-slate-400 transition-transform group-open:rotate-180">
                      expand_more
                    </span>
                  </summary>
                  <div className="px-5 text-sm text-slate-600 bg-slate-50/50">
                    <div className="pb-5 pt-2 border-t border-slate-100 whitespace-pre-line">
                      {item.answer}
                    </div>
                  </div>
                </details>
              ))}
            </div>
          </div>
        </section>
      ) : null}

      {/* Mobile filter drawer */}
      <div
        id="filterOverlay"
        onClick={closeFilter}
        className={`fixed inset-0 bg-black/50 z-40 backdrop-blur-sm transition-opacity ${
          filterOpen ? 'block' : 'hidden'
        }`}
      />
      <aside
        id="mobileFilter"
        className={`fixed top-0 left-0 h-full w-[85%] max-w-xs bg-white z-50 shadow-2xl p-6 flex flex-col transition-transform duration-300 ease-in-out ${
          filterOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex items-center justify-between mb-8">
          <h2 className="text-xl font-bold text-slate-900">Filter Flowers</h2>
          <button
            type="button"
            onClick={closeFilter}
            className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-red-50 hover:text-red-500 transition-colors"
          >
            <span className="material-icons-outlined text-lg">close</span>
          </button>
        </div>
        <form action={basePath} method="GET" className="flex-1 flex flex-col overflow-y-auto">
          {activeCategory ? <input type="hidden" name="category" value={activeCategory} /> : null}
          <h3 className="text-xs font-bold uppercase tracking-wider mb-4 text-slate-400">Sort By</h3>
          <div className="space-y-3 mb-8">
            {MOBILE_SORT.map((opt) => (
              <label
                key={opt.value}
                className="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary"
              >
                <input
                  type="radio"
                  name="sort"
                  value={opt.value}
                  className="text-primary focus:ring-primary border-slate-300 w-5 h-5"
                  defaultChecked={sort === opt.value}
                  onChange={(e) => e.currentTarget.form?.submit()}
                />
                <span className="font-bold text-slate-700">{opt.label}</span>
              </label>
            ))}
          </div>

          <h3 className="text-xs font-bold uppercase tracking-wider mb-4 text-slate-400">
            Price Range
          </h3>
          <div className="space-y-4 mb-8">
            {[
              { min: 0, max: 500 as number | '', label: 'Under ₹500' },
              { min: 500, max: 1000 as number | '', label: '₹500 - ₹1000' },
              { min: 1000, max: 2000 as number | '', label: '₹1000 - ₹2000' },
              { min: 2000, max: '' as number | '', label: 'Over ₹2000' },
            ].map((band) => (
              <label
                key={band.label}
                className="flex items-center gap-3 cursor-pointer bg-slate-50 p-3 rounded-xl border border-slate-100 active:border-primary"
              >
                <input
                  type="radio"
                  name="m_price"
                  className="text-primary focus:ring-primary border-slate-300 w-5 h-5"
                  defaultChecked={priceChecked(priceMin, priceMax, band.min, band.max)}
                  onChange={(e) => {
                    const form = e.currentTarget.form;
                    if (!form) return;
                    const minEl = form.querySelector('#m_min') as HTMLInputElement;
                    const maxEl = form.querySelector('#m_max') as HTMLInputElement;
                    if (minEl) minEl.value = String(band.min);
                    if (maxEl) maxEl.value = band.max === '' ? '' : String(band.max);
                  }}
                />
                <span className="font-bold text-slate-700">{band.label}</span>
              </label>
            ))}
          </div>

          <input type="hidden" name="price_min" id="m_min" defaultValue={priceMin} />
          <input type="hidden" name="price_max" id="m_max" defaultValue={priceMax} />

          <div className="mt-auto space-y-3 pt-4">
            <button
              type="submit"
              className="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/30 active:scale-95 transition-transform"
            >
              Apply Filters
            </button>
            <Link
              href={basePath}
              className="block w-full text-center text-slate-500 font-bold py-3 hover:text-red-500"
            >
              Reset All
            </Link>
          </div>
        </form>
      </aside>
    </div>
  );
}
