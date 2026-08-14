'use client';

import Link from 'next/link';
import { FlowerShopCard } from '@/components/shop/FlowerShopCard';
import type { Product } from '@/lib/types';

interface CategoryShopListingProps {
  pageKey: 'cakes' | 'gifts';
  title: string;
  badge: string;
  description: string;
  heroImage: string;
  products: Product[];
  sort: string;
  recommendProducts?: Product[];
  recommendTitle?: string;
  recommendSub?: string;
  chips?: { label: string; href: string; active?: boolean }[];
}

function stockSummary(products: Product[]) {
  const total = products.length;
  const inStock = products.filter((p) => p.inStock !== false).length;
  if (total === 0) {
    return {
      status: 'empty' as const,
      label: 'Coming soon',
      message: 'We’re preparing a fresh menu. Meanwhile, surprise them with a handcrafted bouquet.',
      total: 0,
      inStock: 0,
    };
  }
  if (inStock === 0) {
    return {
      status: 'out' as const,
      label: 'Temporarily out of stock',
      message: 'These items are restocking. Fresh flower bouquets are ready for same-day delivery.',
      total,
      inStock: 0,
    };
  }
  if (inStock < total) {
    return {
      status: 'limited' as const,
      label: 'Limited stock',
      message: 'Some items are low — flower bouquets make a perfect same-day alternative.',
      total,
      inStock,
    };
  }
  return {
    status: 'in_stock' as const,
    label: 'In stock',
    message: '',
    total,
    inStock,
  };
}

export function CategoryShopListing({
  pageKey,
  title,
  badge,
  description,
  heroImage,
  products,
  sort,
  recommendProducts = [],
  recommendTitle = 'Bouquet recommendations',
  recommendSub = 'Handcrafted flowers ready for same-day delivery across Delhi NCR.',
  chips,
}: CategoryShopListingProps) {
  const stock = stockSummary(products);
  const basePath = `/${pageKey}`;

  return (
    <div className="sf-shop cat-page cat-page--commerce">
      <header
        className="sf-shop-hero"
        style={{ ['--cat-hero-image' as string]: `url('${heroImage}')` }}
      >
        <div className="sf-shop-hero__inner">
          <nav className="sf-shop-hero__crumb" aria-label="Breadcrumb">
            <Link href="/">Home</Link>
            <span>/</span>
            <span>{badge}</span>
          </nav>
          <div className="sf-shop-hero__copy">
            <p className="sf-shop-hero__badge">{badge}</p>
            <h1>{title}</h1>
            <p>{description}</p>
          </div>
        </div>
      </header>

      <div className="sf-shop__sticky sf-shop__sticky--cat">
        <div className="sf-shop__sticky-inner">
          <div className="sf-shop__heading">
            <h2>
              {pageKey === 'cakes' ? 'Cakes' : 'Gifts'} · {stock.inStock} available
            </h2>
            <p>{stock.total > stock.inStock ? `${stock.total - stock.inStock} out of stock` : 'Ready to gift'}</p>
          </div>
          <form method="get" action={basePath} className="sf-shop__sort-form">
            <select
              name="sort"
              defaultValue={sort}
              onChange={(e) => e.currentTarget.form?.submit()}
              aria-label="Sort products"
            >
              <option value="new">Newest</option>
              <option value="price_low">Price ↑</option>
              <option value="price_high">Price ↓</option>
            </select>
          </form>
        </div>

        {chips && chips.length > 0 ? (
          <div className="sf-shop__chips hide-scrollbar" aria-label={`${badge} categories`}>
            {chips.map((chip) => (
              <Link
                key={chip.href}
                href={chip.href}
                className={`sf-chip${chip.active ? ' is-active' : ''}`}
              >
                {chip.label}
              </Link>
            ))}
          </div>
        ) : null}
      </div>

      <main className="sf-shop__main sf-shop__main--single">
        {stock.status !== 'in_stock' ? (
          <div className={`cat-status cat-status--${stock.status}`} role="status">
            <span className="cat-status__pill">{stock.label}</span>
            <p className="cat-status__msg">{stock.message}</p>
            <div className="cat-status__actions">
              {recommendProducts.length > 0 ? (
                <a className="cat-btn cat-btn--accent" href="#cat-rec-title">
                  See bouquet recommendations
                </a>
              ) : null}
              <Link className="cat-btn cat-btn--primary" href="/flowers">
                Shop flowers
              </Link>
            </div>
          </div>
        ) : null}

        {stock.total === 0 ? (
          <div className="sf-shop__empty">
            <i className="fas fa-box-open" aria-hidden="true" />
            <h3>Available soon</h3>
            <p>
              We’re preparing a fresh {pageKey === 'cakes' ? 'cake' : 'gift'} menu. Meanwhile,
              surprise them with a handcrafted bouquet.
            </p>
            <a className="sf-shop__empty-cta" href="#cat-rec-title">
              View recommendations
            </a>
          </div>
        ) : (
          <div className="sf-shop__grid">
            {products.map((product) => (
              <FlowerShopCard key={product.id} product={product} />
            ))}
          </div>
        )}

        {recommendProducts.length > 0 ? (
          <section className="sf-shop__recs" aria-labelledby="cat-rec-title">
            <div className="sf-shop__recs-head">
              <h2 id="cat-rec-title">{recommendTitle}</h2>
              <p>{recommendSub}</p>
            </div>
            <div className="sf-shop__grid">
              {recommendProducts.map((item) => (
                <FlowerShopCard key={item.id} product={item} />
              ))}
            </div>
            <div className="sf-shop__recs-actions">
              <Link href="/flowers" className="cat-btn cat-btn--primary">
                Shop all flower bouquets
              </Link>
              <Link href="/collection/best-sellers" className="cat-btn cat-btn--ghost">
                Best sellers
              </Link>
            </div>
          </section>
        ) : null}
      </main>
    </div>
  );
}
