'use client';

import Link from 'next/link';
import { useMemo } from 'react';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { formatInr, productHref } from '@/lib/images';
import type { Product } from '@/lib/types';

export interface CmsPageData {
  id: number;
  title: string;
  shortDescription?: string | null;
  slug: string;
  layoutType: string;
  pageTag?: string | null;
  heroImage?: string | null;
  extraImages?: string[];
  midgridImage?: string | null;
  midgridImageAlt?: string | null;
  contentHtml: string;
  faqs?: string | null;
  url: string;
}

interface DynamicCmsPageProps {
  page: CmsPageData;
  products?: Product[];
}

function parseFaqs(raw?: string | null): { question: string; answer: string }[] {
  if (!raw?.trim()) return [];
  try {
    const parsed = JSON.parse(raw) as unknown;
    if (!Array.isArray(parsed)) return [];
    return parsed
      .map((item) => {
        if (!item || typeof item !== 'object') return null;
        const row = item as Record<string, unknown>;
        const question =
          typeof row.question === 'string'
            ? row.question
            : typeof row.q === 'string'
              ? row.q
              : '';
        const answer =
          typeof row.answer === 'string' ? row.answer : typeof row.a === 'string' ? row.a : '';
        if (!question || !answer) return null;
        return { question, answer };
      })
      .filter((item): item is { question: string; answer: string } => Boolean(item));
  } catch {
    return [];
  }
}

export function DynamicCmsPage({ page, products = [] }: DynamicCmsPageProps) {
  const hero =
    page.heroImage ||
    'https://images.unsplash.com/photo-1490750967868-88aa4486c946?q=80&w=1400';
  const isShowcase = page.layoutType === 'product_showcase';
  const faqs = useMemo(() => parseFaqs(page.faqs), [page.faqs]);

  return (
    <div className="cat-page cl-page">
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
        {page.midgridImage ? (
          <figure className="cat-midgrid" style={{ margin: '0 0 2rem' }}>
            <OptimizedImage
              src={page.midgridImage}
              alt={page.midgridImageAlt || page.title}
              width={1200}
              height={640}
              sizes={IMAGE_SIZE_PRESETS.gallery}
              style={{ width: '100%', borderRadius: '1rem', display: 'block' }}
            />
          </figure>
        ) : null}

        {page.contentHtml ? (
          <article
            className="cms-content"
            style={{ color: '#4b463e', lineHeight: 1.7, maxWidth: '72ch', marginBottom: '2rem' }}
            dangerouslySetInnerHTML={{ __html: page.contentHtml }}
          />
        ) : null}

        {page.extraImages && page.extraImages.length > 0 ? (
          <section className="cat-section" aria-label="Gallery">
            <div
              className="cat-grid"
              style={{ gridTemplateColumns: 'repeat(auto-fill, minmax(10rem, 1fr))' }}
            >
              {page.extraImages.map((src) => (
                <OptimizedImage
                  key={src}
                  src={src}
                  alt=""
                  width={320}
                  height={320}
                  sizes={IMAGE_SIZE_PRESETS.gallery}
                  style={{ width: '100%', borderRadius: '0.75rem', aspectRatio: '1', objectFit: 'cover' }}
                />
              ))}
            </div>
          </section>
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
                    <OptimizedImage src={p.image} alt={p.name} width={320} height={320} />
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

        {faqs.length > 0 ? (
          <section className="cat-section" aria-labelledby="cms-faq-title">
            <div className="cat-section__head">
              <strong id="cms-faq-title">{page.title} — FAQs</strong>
            </div>
            <div className="cl-faq">
              {faqs.map((faq, i) => (
                <details key={faq.question} className="cl-faq__item" open={i === 0}>
                  <summary>{faq.question}</summary>
                  <p>{faq.answer}</p>
                </details>
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
