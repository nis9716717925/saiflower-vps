'use client';

import Link from 'next/link';
import { formatInr, productHref, resolveImageSrc } from '@/lib/images';
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
    <div className="cat-page">
      <header className="cat-hero" style={{ ['--cat-hero-image' as string]: `url('${heroImage}')` }}>
        <div className="cat-wrap cat-hero__inner">
          <nav className="cat-crumb" aria-label="Breadcrumb">
            <ol>
              <li>
                <Link href="/">Home</Link>
              </li>
              <li aria-current="page">{badge}</li>
            </ol>
          </nav>
          <p className="cat-badge">{badge}</p>
          <h1>{title}</h1>
          <p>{description}</p>
        </div>
      </header>

      <main>
        <div className="cat-wrap" style={{ paddingTop: '1.25rem' }}>
          {chips && chips.length > 0 ? (
            <div className="cat-chips" aria-label={`${badge} categories`}>
              {chips.map((chip) => (
                <Link
                  key={chip.href}
                  className={`cat-chip${chip.active ? ' is-active' : ''}`}
                  href={chip.href}
                >
                  {chip.label}
                </Link>
              ))}
            </div>
          ) : null}

          {stock.status !== 'in_stock' ? (
            <div
              className={`cat-status cat-status--${stock.status}`}
              role="status"
              style={chips ? { marginTop: '1rem' } : undefined}
            >
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
                {pageKey === 'gifts' ? (
                  <Link className="cat-btn cat-btn--ghost" href="/personalized">
                    Personalised gifts
                  </Link>
                ) : null}
              </div>
            </div>
          ) : null}

          <section className="cat-section" aria-labelledby={`${pageKey}-grid-title`}>
            <div
              className="cat-section__head"
              style={{
                display: 'flex',
                flexWrap: 'wrap',
                alignItems: 'end',
                justifyContent: 'space-between',
                gap: '0.75rem',
              }}
            >
              <div>
                <h2 id={`${pageKey}-grid-title`}>
                  {stock.total > 0
                    ? pageKey === 'cakes'
                      ? 'Cake collection'
                      : 'Gift collection'
                    : pageKey === 'cakes'
                      ? 'Cake menu'
                      : 'Gift menu'}
                </h2>
                <p>
                  {stock.inStock} available
                  {stock.total > stock.inStock
                    ? ` · ${stock.total - stock.inStock} out of stock`
                    : ''}
                </p>
              </div>
              <form method="get" action={basePath}>
                <select
                  name="sort"
                  defaultValue={sort}
                  onChange={(e) => e.currentTarget.form?.submit()}
                  className="cat-chip"
                  style={{ cursor: 'pointer', appearance: 'auto' }}
                >
                  <option value="new">Newest</option>
                  <option value="price_low">Price: Low</option>
                  <option value="price_high">Price: High</option>
                </select>
              </form>
            </div>

            {stock.total === 0 ? (
              <div className="cat-empty-panel">
                <h3 className="cat-serif">Available Soon</h3>
                <p>
                  We’re preparing a fresh {pageKey === 'cakes' ? 'cake' : 'gift'} menu. Meanwhile,
                  surprise them with a handcrafted bouquet.
                </p>
                <a className="cat-btn cat-btn--primary" href="#cat-rec-title">
                  View recommendations
                </a>
              </div>
            ) : (
              <div className="cat-grid" role="list">
                {products.map((product) => {
                  const href = product.url ?? productHref(product.type, product.slug);
                  const img = resolveImageSrc(product.image);
                  const inStock = product.inStock !== false;
                  return (
                    <Link key={product.id} className="cat-card" href={href} role="listitem">
                      <span className="cat-card__media">
                        <img
                          src={img}
                          alt={product.name}
                          width={320}
                          height={320}
                          loading="lazy"
                          decoding="async"
                        />
                        {!inStock ? <span className="cat-card__stock">Out of Stock</span> : null}
                      </span>
                      <span className="cat-card__body">
                        <span className="cat-card__name">{product.name}</span>
                        <span className="cat-card__price">{formatInr(product.price)}</span>
                      </span>
                    </Link>
                  );
                })}
              </div>
            )}
          </section>
        </div>

        {recommendProducts.length > 0 ? (
          <section className="cat-section" aria-labelledby="cat-rec-title">
            <div className="cat-wrap">
              <div className="cat-convert">
                <strong id="cat-rec-title">{recommendTitle}</strong>
                <span>{recommendSub}</span>
              </div>
              <div className="cat-grid" style={{ marginTop: '1rem' }} role="list">
                {recommendProducts.map((item) => {
                  const href = item.url ?? productHref(item.type, item.slug);
                  const img = resolveImageSrc(item.image);
                  const orig = item.originalPrice ?? 0;
                  return (
                    <Link
                      key={item.id}
                      className="cat-card"
                      href={href}
                      role="listitem"
                      title={item.name}
                    >
                      <span className="cat-card__media">
                        <img
                          src={img}
                          alt={item.name}
                          width={320}
                          height={320}
                          loading="lazy"
                          decoding="async"
                        />
                      </span>
                      <span className="cat-card__body">
                        <span className="cat-card__name">{item.name}</span>
                        <span>
                          {orig > item.price ? (
                            <span className="cat-card__mrp">{formatInr(orig)}</span>
                          ) : null}{' '}
                          <span className="cat-card__price">{formatInr(item.price)}</span>
                        </span>
                      </span>
                    </Link>
                  );
                })}
              </div>
              <div
                className="cat-status__actions"
                style={{ marginTop: '1.1rem', justifyContent: 'center' }}
              >
                <Link className="cat-btn cat-btn--primary" href="/flowers">
                  Shop all flower bouquets
                </Link>
                <Link className="cat-btn cat-btn--ghost" href="/collection/best-sellers">
                  Best sellers
                </Link>
              </div>
            </div>
          </section>
        ) : null}
      </main>
    </div>
  );
}
