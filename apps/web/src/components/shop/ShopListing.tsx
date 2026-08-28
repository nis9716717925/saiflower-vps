'use client';

import Link from 'next/link';
import { useCallback, useEffect, useMemo, useState } from 'react';
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
  /** First paint size — remaining products load as the user scrolls. */
  initialVisible?: number;
  loadMoreStep?: number;
}

const SORT_OPTIONS = [
  { value: 'bestseller', label: 'Popular' },
  { value: 'newest', label: 'Newest' },
  { value: 'rating', label: 'Top rated' },
  { value: 'price_low', label: 'Price ↑' },
  { value: 'price_high', label: 'Price ↓' },
];

const MOBILE_SORT = [
  { value: 'bestseller', label: 'Best Selling' },
  { value: 'newest', label: 'Newest First' },
  { value: 'price_low', label: 'Price: Low to High' },
  { value: 'price_high', label: 'Price: High to Low' },
  { value: 'rating', label: 'Highest Rated' },
];

const PRICE_BANDS = [
  { min: 0, max: 999 as number | '', label: 'Under ₹999', key: 'u999' },
  { min: 999, max: 1999 as number | '', label: '₹999–1999', key: '999-1999' },
  { min: 1999, max: '' as number | '', label: '₹2000+', key: '1999p' },
];

const SIDEBAR_PRICE_BANDS = [
  { min: 0, max: 500 as number | '', label: 'Under ₹500' },
  { min: 500, max: 999 as number | '', label: '₹500 – ₹999' },
  { min: 999, max: 1999 as number | '', label: '₹999 – ₹1999' },
  { min: 1999, max: 4999 as number | '', label: '₹2000 – ₹4999' },
  { min: 4999, max: '' as number | '', label: '₹5000+' },
];

const OCCASION_FILTERS = [
  { label: 'Birthday', href: '/occasion/birthday' },
  { label: 'Anniversary', href: '/occasion/anniversary' },
  { label: 'Wedding', href: '/occasion/wedding' },
  { label: 'Love & Romance', href: '/occasion/love-romance' },
  { label: 'Congratulations', href: '/occasion/congratulations' },
  { label: 'Thank You', href: '/occasion/thank-you' },
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

/** Homepage-style Font Awesome icons for shop category badges. */
function shopCategoryFaIcon(name: string): string {
  const n = name.toLowerCase();
  if (/same.?day|express|bolt/.test(n)) return 'fa-bolt';
  if (/chocolate|choco/.test(n)) return 'fa-candy-cane';
  if (/sympath|condolen|funeral/.test(n)) return 'fa-dove';
  if (/rose/.test(n)) return 'fa-spa';
  if (/love|romance|valentine/.test(n)) return 'fa-heart';
  if (/wedding|jai.?mala|mala/.test(n)) return 'fa-ring';
  if (/car/.test(n)) return 'fa-car';
  if (/jewell?ery|jewel/.test(n)) return 'fa-gem';
  if (/decor|decoration|stage|event/.test(n)) return 'fa-wand-magic-sparkles';
  if (/first.?night|honeymoon|room/.test(n)) return 'fa-moon';
  if (/crochet|plant/.test(n)) return 'fa-leaf';
  if (/birthday|cake/.test(n)) return 'fa-cake-candles';
  if (/annivers/.test(n)) return 'fa-heart';
  if (/gift|hamper/.test(n)) return 'fa-gift';
  return 'fa-spa';
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
  initialVisible = 24,
  loadMoreStep = 24,
}: ShopListingProps) {
  const [filterOpen, setFilterOpen] = useState(false);
  const [sameDayOnly, setSameDayOnly] = useState(false);
  const [visibleCount, setVisibleCount] = useState(initialVisible);
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

  useEffect(() => {
    setVisibleCount(initialVisible);
  }, [products, sameDayOnly, sort, priceMin, priceMax, categoryId, initialVisible]);

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

  const filteredProducts = useMemo(() => {
    if (!sameDayOnly) return products;
    return products.filter((p) => p.inStock !== false && p.deliverySameday !== false);
  }, [products, sameDayOnly]);

  const visibleProducts = useMemo(
    () => filteredProducts.slice(0, visibleCount),
    [filteredProducts, visibleCount],
  );

  const hasMore = visibleCount < filteredProducts.length;
  const shownCount = sameDayOnly ? filteredProducts.length : total;

  useEffect(() => {
    if (!hasMore) return;
    const sentinel = document.getElementById('sf-shop-load-more');
    if (!sentinel) return;
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries.some((e) => e.isIntersecting)) {
          setVisibleCount((n) => Math.min(n + loadMoreStep, filteredProducts.length));
        }
      },
      { rootMargin: '480px 0px' },
    );
    observer.observe(sentinel);
    return () => observer.disconnect();
  }, [hasMore, loadMoreStep, filteredProducts.length, visibleCount]);

  return (
    <div className="sf-shop bg-[#f3f6f4] text-slate-900 antialiased min-h-screen">
      {/* Sticky commerce chrome */}
      <div className="sf-shop__sticky">
        <div className="sf-shop__sticky-inner">
          <div className="sf-shop__heading">
            <h1>{title}</h1>
            <p>
              {shownCount} item{shownCount === 1 ? '' : 's'}
              {sameDayOnly ? ' · same day' : ''}
            </p>
          </div>

          <div className="sf-shop__sticky-actions">
            <form action={basePath} method="GET" className="sf-shop__sort-form hidden sm:flex">
              {activeCategory ? <input type="hidden" name="category" value={activeCategory} /> : null}
              {priceMin ? <input type="hidden" name="price_min" value={priceMin} /> : null}
              {priceMax ? <input type="hidden" name="price_max" value={priceMax} /> : null}
              <label className="sr-only" htmlFor="sf-shop-sort">
                Sort
              </label>
              <select
                id="sf-shop-sort"
                name="sort"
                defaultValue={sort}
                onChange={(e) => e.currentTarget.form?.submit()}
              >
                {SORT_OPTIONS.map((opt) => (
                  <option key={opt.value} value={opt.value}>
                    {opt.label}
                  </option>
                ))}
              </select>
            </form>

            <button
              type="button"
              className="sf-shop__filter-btn md:hidden"
              onClick={() => setFilterOpen(true)}
            >
              <i className="fas fa-sliders" aria-hidden="true" /> Filters
            </button>
          </div>
        </div>

        {/* Quick commerce chips — real filters */}
        <div className="sf-shop__chips hide-scrollbar" aria-label="Quick filters">
          <Link
            href={hrefWith({ sort: 'bestseller', price_min: undefined, price_max: undefined })}
            className={`sf-chip${sort === 'bestseller' && !priceMin && !priceMax && !sameDayOnly ? ' is-active' : ''}`}
            onClick={() => setSameDayOnly(false)}
          >
            <i className="fas fa-fire" aria-hidden="true" /> Popular
          </Link>
          <button
            type="button"
            className={`sf-chip${sameDayOnly ? ' is-active' : ''}`}
            onClick={() => setSameDayOnly((v) => !v)}
          >
            <i className="fas fa-bolt" aria-hidden="true" /> Same day
          </button>
          {PRICE_BANDS.map((band) => {
            const active = priceChecked(priceMin, priceMax, band.min, band.max);
            return (
              <Link
                key={band.key}
                href={hrefWith({
                  price_min: String(band.min),
                  price_max: band.max === '' ? undefined : String(band.max),
                  sort,
                })}
                className={`sf-chip${active ? ' is-active' : ''}`}
                onClick={() => setSameDayOnly(false)}
              >
                {band.label}
              </Link>
            );
          })}
          <Link
            href={hrefWith({ sort: 'newest', price_min: undefined, price_max: undefined })}
            className={`sf-chip${sort === 'newest' && !priceMin && !priceMax ? ' is-active' : ''}`}
            onClick={() => setSameDayOnly(false)}
          >
            New
          </Link>
          <Link
            href={hrefWith({ sort: 'rating', price_min: undefined, price_max: undefined })}
            className={`sf-chip${sort === 'rating' && !priceMin && !priceMax && !sameDayOnly ? ' is-active' : ''}`}
            onClick={() => setSameDayOnly(false)}
          >
            <i className="fas fa-chart-line" aria-hidden="true" /> Trending
          </Link>
          <Link
            href="/occasion/birthday"
            className="sf-chip sf-chip--occasion"
            onClick={() => setSameDayOnly(false)}
          >
            <i className="fas fa-calendar-days" aria-hidden="true" /> Occasions
          </Link>
          {hasPriceOrCat || sameDayOnly ? (
            <Link
              href={basePath}
              className="sf-chip sf-chip--clear"
              onClick={() => setSameDayOnly(false)}
            >
              Clear
            </Link>
          ) : null}
        </div>

        {/* Homepage-style circular category badges (mobile) */}
        <div className="sf-shop__cat-badges hide-scrollbar md:hidden" aria-label="Categories">
          <Link
            href={hrefWith({ category: undefined })}
            className={`sf-shop__cat-badge${!activeCategory ? ' is-active' : ''}`}
          >
            <span className="sf-shop__cat-badge-icon">
              <i className="fas fa-border-all" aria-hidden="true" />
            </span>
            <span className="sf-shop__cat-badge-label">All</span>
          </Link>
          {orderedCats.map((cat) => {
            const active = activeCategory === cat.id;
            return (
              <Link
                key={cat.id}
                href={hrefWith({ category: String(cat.id) })}
                className={`sf-shop__cat-badge${active ? ' is-active' : ''}`}
              >
                <span className="sf-shop__cat-badge-icon">
                  <i className={`fas ${shopCategoryFaIcon(cat.name)}`} aria-hidden="true" />
                </span>
                <span className="sf-shop__cat-badge-label">{cat.name}</span>
              </Link>
            );
          })}
        </div>
      </div>

      <main className="sf-shop__main">
        <aside className="sf-shop__aside" aria-label="Filters">
          <div className="sf-shop__filters">
            <div className="sf-shop__filters-head">
              <h2>Filters</h2>
              {hasPriceOrCat || sameDayOnly ? (
                <Link href={basePath} className="sf-shop__filters-clear" onClick={() => setSameDayOnly(false)}>
                  Clear all
                </Link>
              ) : null}
            </div>

            <details className="sf-shop__filter-group" open>
              <summary>Categories</summary>
              <ul className="sf-shop__filter-list">
                <li>
                  <Link
                    href={hrefWith({ category: undefined })}
                    className={`sf-shop__filter-opt${!activeCategory ? ' is-active' : ''}`}
                  >
                    <span className="sf-shop__filter-check" aria-hidden="true" />
                    All items
                  </Link>
                </li>
                {orderedCats.map((cat) => {
                  const active = activeCategory === cat.id;
                  return (
                    <li key={cat.id}>
                      <Link
                        href={hrefWith({ category: String(cat.id) })}
                        className={`sf-shop__filter-opt${active ? ' is-active' : ''}`}
                      >
                        <span className="sf-shop__filter-check" aria-hidden="true" />
                        {cat.name}
                      </Link>
                    </li>
                  );
                })}
              </ul>
            </details>

            <details className="sf-shop__filter-group" open>
              <summary>Price</summary>
              <ul className="sf-shop__filter-list">
                {SIDEBAR_PRICE_BANDS.map((band) => {
                  const active = priceChecked(priceMin, priceMax, band.min, band.max);
                  return (
                    <li key={band.label}>
                      <Link
                        href={hrefWith({
                          price_min: String(band.min),
                          price_max: band.max === '' ? undefined : String(band.max),
                        })}
                        className={`sf-shop__filter-opt${active ? ' is-active' : ''}`}
                      >
                        <span className="sf-shop__filter-check" aria-hidden="true" />
                        {band.label}
                      </Link>
                    </li>
                  );
                })}
              </ul>
            </details>

            <details className="sf-shop__filter-group" open>
              <summary>Customer rating</summary>
              <ul className="sf-shop__filter-list">
                <li>
                  <Link
                    href={hrefWith({ sort: 'rating', price_min: undefined, price_max: undefined })}
                    className={`sf-shop__filter-opt${sort === 'rating' && !priceMin && !priceMax ? ' is-active' : ''}`}
                  >
                    <span className="sf-shop__filter-check" aria-hidden="true" />
                    4★ &amp; above
                  </Link>
                </li>
                <li>
                  <Link
                    href={hrefWith({ sort: 'bestseller', price_min: undefined, price_max: undefined })}
                    className={`sf-shop__filter-opt${sort === 'bestseller' && !priceMin && !priceMax ? ' is-active' : ''}`}
                  >
                    <span className="sf-shop__filter-check" aria-hidden="true" />
                    Popular picks
                  </Link>
                </li>
              </ul>
            </details>

            <details className="sf-shop__filter-group" open>
              <summary>Occasions</summary>
              <ul className="sf-shop__filter-list">
                {OCCASION_FILTERS.map((occ) => (
                  <li key={occ.href}>
                    <Link href={occ.href} className="sf-shop__filter-opt sf-shop__filter-opt--link">
                      <span className="sf-shop__filter-check" aria-hidden="true" />
                      {occ.label}
                    </Link>
                  </li>
                ))}
              </ul>
            </details>

            <details className="sf-shop__filter-group" open>
              <summary>Delivery</summary>
              <ul className="sf-shop__filter-list">
                <li>
                  <button
                    type="button"
                    className={`sf-shop__filter-opt sf-shop__filter-opt--btn${sameDayOnly ? ' is-active' : ''}`}
                    onClick={() => setSameDayOnly((v) => !v)}
                  >
                    <span className="sf-shop__filter-check" aria-hidden="true" />
                    Same-day delivery
                  </button>
                </li>
              </ul>
            </details>
          </div>
        </aside>

        <section className="sf-shop__grid-wrap" id="product-grid" aria-live="polite">
          <p className="sf-shop__sub md:block hidden">{subtitle.replace('{count}', String(shownCount))}</p>

          {visibleProducts.length > 0 ? (
            <>
              <div className="sf-shop__grid">
                {visibleProducts.map((product) => (
                  <FlowerShopCard key={product.id} product={product} />
                ))}
              </div>
              {hasMore ? (
                <div id="sf-shop-load-more" className="sf-shop__load-more" aria-hidden="true">
                  <button
                    type="button"
                    className="sf-shop__load-more-btn"
                    onClick={() =>
                      setVisibleCount((n) => Math.min(n + loadMoreStep, filteredProducts.length))
                    }
                  >
                    Load more flowers
                  </button>
                </div>
              ) : null}
            </>
          ) : (
            <div className="sf-shop__empty">
              <i className="fas fa-search" aria-hidden="true" />
              <h3>No products found</h3>
              <p>Try clearing filters or browse bestsellers.</p>
              <Link href={basePath} className="sf-shop__empty-cta" onClick={() => setSameDayOnly(false)}>
                Clear filters
              </Link>
            </div>
          )}
        </section>
      </main>

      {faqs.length > 0 ? (
        <section className="sf-shop__faq">
          <h2>Frequently asked questions</h2>
          <div className="sf-shop__faq-list">
            {faqs.map((item) => (
              <details key={item.question}>
                <summary>{item.question}</summary>
                <div className="sf-shop__faq-a">{item.answer}</div>
              </details>
            ))}
          </div>
        </section>
      ) : null}

      <div
        className={`sf-shop__overlay${filterOpen ? ' is-open' : ''}`}
        onClick={closeFilter}
        aria-hidden={!filterOpen}
      />
      <aside className={`sf-shop__drawer${filterOpen ? ' is-open' : ''}`} aria-label="Filters">
        <div className="sf-shop__drawer-head">
          <h2>Sort & filters</h2>
          <button type="button" onClick={closeFilter} aria-label="Close filters">
            <i className="fas fa-xmark" aria-hidden="true" />
          </button>
        </div>
        <form action={basePath} method="GET" className="sf-shop__drawer-form">
          {activeCategory ? <input type="hidden" name="category" value={activeCategory} /> : null}
          <h3>Sort by</h3>
          <div className="sf-shop__drawer-opts">
            {MOBILE_SORT.map((opt) => (
              <label key={opt.value}>
                <input
                  type="radio"
                  name="sort"
                  value={opt.value}
                  defaultChecked={sort === opt.value}
                  onChange={(e) => e.currentTarget.form?.submit()}
                />
                <span>{opt.label}</span>
              </label>
            ))}
          </div>

          <h3>Price range</h3>
          <div className="sf-shop__drawer-opts">
            {[
              { min: 0, max: 500 as number | '', label: 'Under ₹500' },
              { min: 500, max: 1000 as number | '', label: '₹500 – ₹1000' },
              { min: 1000, max: 2000 as number | '', label: '₹1000 – ₹2000' },
              { min: 2000, max: '' as number | '', label: 'Over ₹2000' },
            ].map((band) => (
              <label key={band.label}>
                <input
                  type="radio"
                  name="m_price"
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
                <span>{band.label}</span>
              </label>
            ))}
          </div>

          <input type="hidden" name="price_min" id="m_min" defaultValue={priceMin} />
          <input type="hidden" name="price_max" id="m_max" defaultValue={priceMax} />

          <div className="sf-shop__drawer-actions">
            <button type="submit">Apply</button>
            <Link href={basePath} onClick={closeFilter}>
              Reset
            </Link>
          </div>
        </form>
      </aside>
    </div>
  );
}
