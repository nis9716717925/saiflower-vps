import Link from 'next/link';
import { formatInr, productHref, resolveImageSrc } from '@/lib/images';
import type { Product } from '@/lib/types';

export interface PersonalizedEntry {
  slug: string;
  title: string;
  h1: string;
  badge: string;
  status: string;
  status_label: string;
  short: string;
  hero: string;
  recommend_line: string;
  bouquet_keywords: string[] | null;
  canonical_path: string;
}

export const PERSONALIZED_TAXONOMY: Record<string, Omit<PersonalizedEntry, 'canonical_path'>> = {
  '': {
    slug: '',
    title: 'Personalised Gifts',
    h1: 'Personalised Gifts Online — Coming Soon',
    badge: 'Personalised',
    status: 'available_soon',
    status_label: 'Available Soon',
    short:
      'Photo frames, engraved keepsakes and custom message cards — crafting something special. Meanwhile, surprise them with a fresh bouquet today.',
    hero: 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?auto=format&fit=crop&w=1600&q=80',
    recommend_line:
      'While we finish personalised keepsakes, these bouquets make a heartfelt gift right now.',
    bouquet_keywords: null,
  },
  'photo-frames': {
    slug: 'photo-frames',
    title: 'Photo Frames & Keepsakes',
    h1: 'Personalised Photo Frames & Keepsakes',
    badge: 'Photo Gifts',
    status: 'available_soon',
    status_label: 'Available Soon',
    short:
      'Custom photo frames and memory keepsakes are on the way. Send fresh blooms while you wait — they never go out of style.',
    hero: 'https://images.unsplash.com/photo-1513519245088-0e12902e35a6?auto=format&fit=crop&w=1600&q=80',
    recommend_line: 'Pair a future photo gift with flowers they’ll love today.',
    bouquet_keywords: ['rose', 'mixed', 'premium'],
  },
  'custom-message-cards': {
    slug: 'custom-message-cards',
    title: 'Custom Message Cards',
    h1: 'Custom Message Cards with Flower Gifts',
    badge: 'Message Cards',
    status: 'available_soon',
    status_label: 'Available Soon',
    short:
      'Personal notes and premium message cards are launching soon. Every Sai Flowers bouquet already includes a free handwritten-style card at checkout.',
    hero: 'https://images.unsplash.com/photo-1456735190827-d1262f71b8a3?auto=format&fit=crop&w=1600&q=80',
    recommend_line: 'Add your note at checkout — these bouquets deliver with a free message card.',
    bouquet_keywords: ['birthday', 'love', 'thank'],
  },
  'engraved-gifts': {
    slug: 'engraved-gifts',
    title: 'Engraved Gifts',
    h1: 'Engraved Personalised Gifts',
    badge: 'Engraved',
    status: 'available_soon',
    status_label: 'Available Soon',
    short:
      'Name-engraved gifts are in production. Until then, curated flower bouquets are ready for same-day delivery across Delhi NCR.',
    hero: 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=1600&q=80',
    recommend_line: 'Looking for something memorable today? These signature bouquets convert beautifully.',
    bouquet_keywords: ['premium', 'orchid', 'designer'],
  },
  'photo-gifts': {
    slug: 'photo-gifts',
    title: 'Photo Gifts',
    h1: 'Personalised Photo Gifts',
    badge: 'Photo Gifts',
    status: 'available_soon',
    status_label: 'Available Soon',
    short: 'Photo mugs, frames and prints are almost here. Fresh rose and mixed bouquets ship today.',
    hero: 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?auto=format&fit=crop&w=1600&q=80',
    recommend_line: 'You may also love these romantic bouquet picks.',
    bouquet_keywords: ['rose', 'valentine', 'red'],
  },
  'name-plates': {
    slug: 'name-plates',
    title: 'Name Plates & Custom Tags',
    h1: 'Custom Name Plates & Gift Tags',
    badge: 'Custom',
    status: 'available_soon',
    status_label: 'Available Soon',
    short:
      'Custom name plates are coming soon. Personalise any flower order with a free card note for now.',
    hero: 'https://images.unsplash.com/photo-1487530811176-3780da8112fd?auto=format&fit=crop&w=1600&q=80',
    recommend_line: 'Go for these bestselling bouquets while custom tags roll out.',
    bouquet_keywords: null,
  },
};

export function personalizedGet(slug: string): PersonalizedEntry | null {
  const key = slug.toLowerCase().replace(/^\/+|\/+$/g, '');
  const row = PERSONALIZED_TAXONOMY[key];
  if (!row) return null;
  return {
    ...row,
    canonical_path: key === '' ? '/personalized' : `/personalized/${key}`,
  };
}

export function PersonalizedLandingView({
  entry,
  products,
}: {
  entry: PersonalizedEntry;
  products: Product[];
}) {
  const children = Object.values(PERSONALIZED_TAXONOMY)
    .filter((c) => c.slug !== '')
    .map((c) => ({
      ...c,
      canonical_path: `/personalized/${c.slug}`,
    }));

  return (
    <div className="cat-page">
      <header className="cat-hero" style={{ ['--cat-hero-image' as string]: `url('${entry.hero}')` }}>
        <div className="cat-wrap cat-hero__inner">
          <nav className="cat-crumb" aria-label="Breadcrumb">
            <ol>
              <li>
                <Link href="/">Home</Link>
              </li>
              {entry.slug !== '' ? (
                <li>
                  <Link href="/personalized">Personalised</Link>
                </li>
              ) : null}
              <li aria-current="page">{entry.title}</li>
            </ol>
          </nav>
          <p className="cat-badge">{entry.badge}</p>
          <h1>{entry.h1}</h1>
          <p>{entry.short}</p>
        </div>
      </header>

      <main>
        <div className="cat-wrap" style={{ paddingTop: '1.25rem' }}>
          <div className={`cat-status cat-status--${entry.status}`} role="status">
            <span className="cat-status__pill">
              <i className="fas fa-clock" aria-hidden="true" /> {entry.status_label}
            </span>
            <p className="cat-status__msg">{entry.recommend_line}</p>
            <div className="cat-status__actions">
              <a className="cat-btn cat-btn--accent" href="#cat-rec-title">
                See bouquet recommendations
              </a>
              <Link className="cat-btn cat-btn--primary" href="/gifts">
                Shop gifts
              </Link>
              <Link className="cat-btn cat-btn--ghost" href="/flowers">
                Shop flowers
              </Link>
            </div>
          </div>

          {children.length > 0 ? (
            <div className="cat-chips" aria-label="Personalised categories">
              <Link
                className={`cat-chip${entry.slug === '' ? ' is-active' : ''}`}
                href="/personalized"
              >
                All Personalised
              </Link>
              {children.map((child) => (
                <Link
                  key={child.slug}
                  className={`cat-chip${child.slug === entry.slug ? ' is-active' : ''}`}
                  href={child.canonical_path}
                >
                  {child.title}
                </Link>
              ))}
            </div>
          ) : null}
        </div>

        {products.length > 0 ? (
          <section className="cat-section" aria-labelledby="cat-rec-title">
            <div className="cat-wrap">
              <div className="cat-convert">
                <strong id="cat-rec-title">You may also check these bouquets</strong>
                <span>{entry.recommend_line}</span>
              </div>
              <div className="cat-grid" style={{ marginTop: '1rem' }} role="list">
                {products.map((item) => {
                  const href = item.url ?? productHref(item.type, item.slug);
                  const img = resolveImageSrc(item.image);
                  const orig = item.originalPrice ?? 0;
                  return (
                    <Link key={item.id} className="cat-card" href={href} role="listitem" title={item.name}>
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
