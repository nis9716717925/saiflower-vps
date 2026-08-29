'use client';

import Link from 'next/link';
import { useMemo, useState } from 'react';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { formatInr, productHref } from '@/lib/images';
import type { Product } from '@/lib/types';

export interface LookbookItem {
  id: number;
  title: string;
  tag?: string | null;
  image: string;
  url: string;
  priceLabel?: string | null;
  badge?: string | null;
}

interface LookbookPageProps {
  badge: string;
  title: string;
  description: string;
  heroImage: string;
  crumbLabel: string;
  emptyMessage: string;
  emptyWaHref?: string;
  sectionTitle: string;
  sectionSub: string;
  items: LookbookItem[];
  recommendProducts: Product[];
  recommendTitle?: string;
  recommendSub?: string;
}

export function LookbookPage({
  badge,
  title,
  description,
  heroImage,
  crumbLabel,
  emptyMessage,
  emptyWaHref,
  sectionTitle,
  sectionSub,
  items,
  recommendProducts,
  recommendTitle = 'Bouquet recommendations',
  recommendSub = 'Handcrafted flowers ready for same-day delivery across Delhi NCR.',
}: LookbookPageProps) {
  const tags = useMemo(() => {
    const set = new Set<string>();
    for (const item of items) {
      const t = item.tag?.trim();
      if (t) set.add(t);
    }
    return Array.from(set);
  }, [items]);

  const [filter, setFilter] = useState('all');
  const visible = filter === 'all' ? items : items.filter((i) => (i.tag || '').toLowerCase() === filter);

  return (
    <div className="cat-page">
      <header className="cat-hero" style={{ ['--cat-hero-image' as string]: `url('${heroImage}')` }}>
        <div className="cat-wrap cat-hero__inner">
          <nav className="cat-crumb" aria-label="Breadcrumb">
            <ol>
              <li>
                <Link href="/">Home</Link>
              </li>
              <li aria-current="page">{crumbLabel}</li>
            </ol>
          </nav>
          <p className="cat-badge">{badge}</p>
          <h1>{title}</h1>
          <p>{description}</p>
        </div>
      </header>

      <main>
        <div className="cat-wrap" style={{ paddingTop: '1.25rem' }}>
          {items.length === 0 ? (
            <div className="cat-status cat-status--available_soon" role="status">
              <span className="cat-status__pill">Available Soon</span>
              <p className="cat-status__msg">{emptyMessage}</p>
              <div className="cat-status__actions">
                <a className="cat-btn cat-btn--accent" href="#cat-rec-title">
                  See recommendations
                </a>
                {emptyWaHref ? (
                  <a className="cat-btn cat-btn--primary" href={emptyWaHref} target="_blank" rel="noopener noreferrer">
                    Enquire on WhatsApp
                  </a>
                ) : (
                  <Link className="cat-btn cat-btn--primary" href="/flowers">
                    Shop flowers
                  </Link>
                )}
              </div>
            </div>
          ) : (
            <>
              {tags.length > 0 ? (
                <div className="cat-chips" aria-label={`Filter ${crumbLabel.toLowerCase()}`}>
                  <button
                    type="button"
                    className={`cat-chip${filter === 'all' ? ' is-active' : ''}`}
                    onClick={() => setFilter('all')}
                  >
                    All
                  </button>
                  {tags.map((tag) => {
                    const key = tag.toLowerCase();
                    return (
                      <button
                        key={tag}
                        type="button"
                        className={`cat-chip${filter === key ? ' is-active' : ''}`}
                        onClick={() => setFilter(key)}
                      >
                        {tag}
                      </button>
                    );
                  })}
                </div>
              ) : null}

              <section className="cat-section" aria-labelledby="lookbook-title">
                <div className="cat-section__head">
                  <h2 id="lookbook-title">{sectionTitle}</h2>
                  <p>{sectionSub}</p>
                </div>
                <div className="cat-grid" role="list">
                  {visible.map((item) => (
                    <Link
                      key={item.id}
                      className="cat-card"
                      href={item.url}
                      data-tag={(item.tag || '').toLowerCase()}
                      role="listitem"
                    >
                      <span className="cat-card__media">
                        <OptimizedImage
                          src={item.image}
                          alt={item.title}
                          width={320}
                          height={320}
                          sizes={IMAGE_SIZE_PRESETS.gallery}
                        />
                        {item.badge ? <span className="cat-card__stock cat-card__stock--soon">{item.badge}</span> : null}
                      </span>
                      <span className="cat-card__body">
                        <span className="cat-card__name">{item.title}</span>
                        {item.priceLabel ? (
                          <span className="cat-card__price">{item.priceLabel}</span>
                        ) : item.tag ? (
                          <span style={{ fontSize: '0.72rem', color: '#6a6258', fontWeight: 700 }}>{item.tag}</span>
                        ) : null}
                      </span>
                    </Link>
                  ))}
                </div>
              </section>
            </>
          )}
        </div>

        {recommendProducts.length > 0 ? (
          <section className="cat-section cat-recs" aria-labelledby="cat-rec-title">
            <div className="cat-wrap">
              <div className="cat-section__head">
                <strong id="cat-rec-title">{recommendTitle}</strong>
                <span>{recommendSub}</span>
              </div>
              <div className="cat-grid">
                {recommendProducts.map((item) => (
                  <Link key={`${item.type}-${item.id}`} className="cat-card" href={productHref(item.type, item.slug)}>
                    <span className="cat-card__media">
                      <OptimizedImage
                        src={item.image}
                        alt={item.name}
                        width={320}
                        height={320}
                        sizes={IMAGE_SIZE_PRESETS.productGrid}
                      />
                    </span>
                    <span className="cat-card__body">
                      <span className="cat-card__name">{item.name}</span>
                      <span className="cat-card__price">{formatInr(item.price)}</span>
                    </span>
                  </Link>
                ))}
              </div>
            </div>
          </section>
        ) : null}
      </main>
    </div>
  );
}
