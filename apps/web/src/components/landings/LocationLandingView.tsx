'use client';

import Link from 'next/link';
import Script from 'next/script';
import { useMemo, useState } from 'react';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { discountPercent, formatInr, productHref } from '@/lib/images';
import type { Product } from '@/lib/types';

export interface LocationInfo {
  area: string;
  local: string;
  nearby: string;
  region: string;
  slug: string;
}

function productFilters(product: Product): string[] {
  const hay = `${product.name} ${product.tag ?? ''}`.toLowerCase();
  const tags = ['all'];
  if (hay.includes('rose')) tags.push('rose');
  if (hay.includes('orchid')) tags.push('orchid');
  if (product.price < 999) tags.push('under-999');
  if (product.price >= 1999 || hay.includes('premium') || hay.includes('luxe')) tags.push('premium');
  if (product.deliverySameday !== false) tags.push('same-day');
  return tags;
}

export function LocationLandingView({
  location,
  products,
}: {
  location: LocationInfo;
  products: Product[];
}) {
  const [filter, setFilter] = useState('all');
  const [sort, setSort] = useState('recommended');

  const visible = useMemo(() => {
    let list = products.filter((p) => productFilters(p).includes(filter) || filter === 'all');
    if (sort === 'price-asc') list = [...list].sort((a, b) => a.price - b.price);
    if (sort === 'price-desc') list = [...list].sort((a, b) => b.price - a.price);
    return list;
  }, [products, filter, sort]);

  const nearbyAreas = location.nearby
    .split(/\s*,\s*|\s+and\s+/i)
    .map((s) => s.trim())
    .filter(Boolean);

  return (
    <div
      className="location-page"
      style={{ paddingBottom: 'calc(5.5rem + env(safe-area-inset-bottom, 0px))' }}
    >
      <div className="loc-showcase">
        <div className="loc-showcase__top">
          <nav className="loc-crumb" aria-label="Breadcrumb">
            <Link href="/">Home</Link>
            <span aria-hidden="true">/</span>
            <Link href="/flowers">Flowers</Link>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{location.area}</span>
          </nav>
          <h1 className="loc-showcase__title">
            Flower Delivery in {location.local} — Same Day
          </h1>
          <p className="loc-showcase__count">
            {visible.length} bouquets · Same-day delivery in {location.area}
          </p>
        </div>

        <div className="loc-toolbar" role="toolbar" aria-label="Filter and sort products">
          <div className="loc-filters hide-scrollbar" id="locFilters">
            {[
              ['all', 'All'],
              ['rose', 'Roses'],
              ['under-999', 'Under ₹999'],
              ['same-day', 'Same Day'],
              ['premium', 'Premium'],
              ['orchid', 'Orchids'],
            ].map(([key, label]) => (
              <button
                key={key}
                type="button"
                className={`loc-filter${filter === key ? ' is-active' : ''}`}
                data-filter={key}
                onClick={() => setFilter(key)}
              >
                {label}
              </button>
            ))}
          </div>
          <label className="loc-sort">
            <span className="sr-only">Sort products</span>
            <select
              id="locSort"
              aria-label="Sort products"
              value={sort}
              onChange={(e) => setSort(e.target.value)}
            >
              <option value="recommended">Sort: Recommended</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
            </select>
          </label>
        </div>

        <div className="loc-results-wrap">
          <div
            className="loc-results-grid"
            id="locProducts"
            role="list"
            aria-label={`Flowers for delivery in ${location.area}`}
          >
            {visible.length === 0 ? (
              <div className="loc-empty">
                <p>
                  Browse our <Link href="/flowers">flowers</Link> for delivery in {location.area}.
                </p>
              </div>
            ) : (
              visible.map((item) => {
                const href = item.url ?? productHref(item.type, item.slug);
                const discount = discountPercent(item.price, item.originalPrice);
                const tags = productFilters(item).join(' ');
                return (
                  <Link
                    key={item.id}
                    href={href}
                    className="loc-card"
                    role="listitem"
                    data-filters={tags}
                    data-price={Math.round(item.price)}
                    data-rating={item.rating ?? 0}
                    title={item.name}
                  >
                    <div className="loc-card__img">
                      <OptimizedImage
                        src={item.image}
                        alt={item.name}
                        width={320}
                        height={320}
                        sizes={IMAGE_SIZE_PRESETS.productGrid}
                      />
                      {discount > 0 ? (
                        <span className="loc-card__badge">{discount}% OFF</span>
                      ) : null}
                    </div>
                    <div className="loc-card__body">
                      <h3 className="loc-card__name">{item.name}</h3>
                      <div className="loc-card__price">
                        {item.originalPrice && item.originalPrice > item.price ? (
                          <span className="loc-card__old">{formatInr(item.originalPrice)}</span>
                        ) : null}
                        <span className="loc-card__now">{formatInr(item.price)}</span>
                      </div>
                    </div>
                  </Link>
                );
              })
            )}
          </div>
        </div>

        <div className="loc-showcase__footer">
          <Link href="/flowers" className="loc-viewall">
            View all flowers in {location.area}{' '}
            <i className="fas fa-arrow-right" aria-hidden="true" />
          </Link>
        </div>

        <section className="loc-trust" aria-label="Delivery highlights">
          <div className="loc-trust__inner">
            <div className="loc-trust__item">
              <span className="loc-trust__icon" aria-hidden="true">
                <i className="fas fa-bolt" />
              </span>
              <span className="loc-trust__text">
                <strong>Same-day delivery</strong> in {location.area}
              </span>
            </div>
            <div className="loc-trust__item">
              <span className="loc-trust__icon" aria-hidden="true">
                <i className="fas fa-star" />
              </span>
              <span className="loc-trust__text">
                <strong>4.8★ rated</strong> by happy customers
              </span>
            </div>
            <div className="loc-trust__item">
              <span className="loc-trust__icon" aria-hidden="true">
                <i className="fas fa-leaf" />
              </span>
              <span className="loc-trust__text">
                <strong>Fresh bouquets</strong> hand-arranged daily
              </span>
            </div>
            <div className="loc-trust__item">
              <span className="loc-trust__icon" aria-hidden="true">
                <i className="fas fa-store" />
              </span>
              <span className="loc-trust__text">
                <strong>Since 1998</strong> · Lodhi Road, Delhi
              </span>
            </div>
          </div>
        </section>
      </div>

      {nearbyAreas.length > 0 ? (
        <section className="loc-nearby" aria-labelledby="loc-nearby-title">
          <h2 id="loc-nearby-title">Also delivering near {location.area}</h2>
          <div className="loc-chip-grid">
            {nearbyAreas.map((area) => (
              <span key={area} className="loc-chip">
                {area}
              </span>
            ))}
          </div>
        </section>
      ) : null}

      <section className="showcase-text loc-seo" aria-label="About flower delivery">
        <p>
          Looking for reliable <strong>flower delivery in {location.local}</strong>? Sai Flowers
          offers same-day bouquets across {location.region}, including {location.nearby}. Choose
          roses, orchids, premium arrangements and more — packaged carefully for doorstep delivery.
        </p>
      </section>

      <Script src="/assets/js/location-landing.js?v=1" strategy="afterInteractive" />
    </div>
  );
}
