'use client';

import Link from 'next/link';
import { formatInr, productHref, resolveImageSrc } from '@/lib/images';
import type { Product } from '@/lib/types';

export interface CmsPageData {
  id: number;
  title: string;
  shortDescription?: string | null;
  slug: string;
  layoutType: string;
  pageTag?: string | null;
  heroImage?: string | null;
  contentHtml: string;
  url: string;
}

interface DynamicCmsPageProps {
  page: CmsPageData;
  products?: Product[];
}

export function DynamicCmsPage({ page, products = [] }: DynamicCmsPageProps) {
  const hero =
    page.heroImage ||
    'https://images.unsplash.com/photo-1490750967868-88aa4486c946?q=80&w=1400';
  const isShowcase = page.layoutType === 'product_showcase';

  return (
    <div className="cat-page">
      <header className="cat-hero" style={{ ['--cat-hero-image' as string]: `url('${hero}')` }}>
        <div className="cat-wrap cat-hero__inner">
          <nav className="cat-crumb" aria-label="Breadcrumb">
            <ol>
              <li>
                <Link href="/">Home</Link>
              </li>
              <li aria-current="page">{page.title}</li>
            </ol>
          </nav>
          {page.pageTag ? <p className="cat-badge">{page.pageTag}</p> : null}
          <h1>{page.title}</h1>
          {page.shortDescription ? <p>{page.shortDescription}</p> : null}
        </div>
      </header>

      <main className="cat-wrap" style={{ padding: '1.75rem 1rem 3rem' }}>
        {page.contentHtml ? (
          <article
            className="cms-content"
            style={{ color: '#4b463e', lineHeight: 1.7, maxWidth: '72ch', marginBottom: '2rem' }}
            dangerouslySetInnerHTML={{ __html: page.contentHtml }}
          />
        ) : null}

        {isShowcase && products.length > 0 ? (
          <section className="cat-section" aria-labelledby="cms-products">
            <div className="cat-section__head">
              <strong id="cms-products">Shop {page.title}</strong>
              <span>Handcrafted bouquets ready for same-day delivery.</span>
            </div>
            <div className="cat-grid">
              {products.map((p) => (
                <Link key={`${p.type}-${p.id}`} className="cat-card" href={productHref(p.type, p.slug)}>
                  <span className="cat-card__media">
                    <img src={resolveImageSrc(p.image)} alt={p.name} width={320} height={320} loading="lazy" />
                  </span>
                  <span className="cat-card__body">
                    <span className="cat-card__name">{p.name}</span>
                    <span className="cat-card__price">{formatInr(p.price)}</span>
                  </span>
                </Link>
              ))}
            </div>
          </section>
        ) : null}

        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.65rem', marginTop: '1.5rem' }}>
          <Link className="cat-btn cat-btn--primary" href="/flowers">
            Shop flowers
          </Link>
          <a
            className="cat-btn cat-btn--accent"
            href="https://wa.me/918802004527"
            target="_blank"
            rel="noopener noreferrer"
          >
            WhatsApp us
          </a>
        </div>
      </main>
    </div>
  );
}
