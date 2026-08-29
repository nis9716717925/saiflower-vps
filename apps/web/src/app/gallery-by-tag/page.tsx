import type { Metadata } from 'next';
import Link from 'next/link';
import { redirect } from 'next/navigation';
import { fetchGallery } from '@/lib/api';
import { resolveImageSrc } from '@/lib/images';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Gallery by Tag | Sai Flowers',
  description: 'Browse Sai Flowers gallery looks filtered by tag.',
  canonical: '/gallery-by-tag',
});

export const revalidate = 120;

interface PageProps {
  searchParams: Promise<{ tag?: string | string[] }>;
}

function tagParam(value: string | string[] | undefined): string {
  if (Array.isArray(value)) return (value[0] ?? '').trim();
  return (value ?? '').trim();
}

/** PHP: /gallery-by-tag?tag= */
export default async function GalleryByTagPage({ searchParams }: PageProps) {
  const tag = tagParam((await searchParams).tag);
  if (!tag) redirect('/gallery');

  let items: Awaited<ReturnType<typeof fetchGallery>> = [];
  try {
    const all = await fetchGallery(200);
    const needle = tag.toLowerCase();
    items = all.filter((i) => (i.tag || '').toLowerCase() === needle);
  } catch {
    items = [];
  }

  return (
    <div className="cat-page">
      <main className="cat-wrap" style={{ padding: '2rem 1rem 3rem' }}>
        <nav className="cat-crumb" aria-label="Breadcrumb">
          <ol>
            <li>
              <Link href="/">Home</Link>
            </li>
            <li>
              <Link href="/gallery">Gallery</Link>
            </li>
            <li aria-current="page">{tag}</li>
          </ol>
        </nav>
        <h1
          style={{
            fontFamily: "'Cormorant Garamond', Georgia, serif",
            fontSize: 'clamp(2rem, 5vw, 3rem)',
            margin: '1rem 0',
          }}
        >
          Gallery: {tag}
        </h1>
        {items.length === 0 ? (
          <p style={{ color: '#6a6258' }}>
            No looks for this tag yet.{' '}
            <Link href="/gallery" style={{ color: '#2f6f4e', fontWeight: 700 }}>
              Back to gallery
            </Link>
          </p>
        ) : (
          <div className="cat-grid" role="list">
            {items.map((item) => (
              <Link key={item.id} className="cat-card" href={item.url} role="listitem">
                <span className="cat-card__media">
                  <img src={resolveImageSrc(item.image)} alt={item.title} width={320} height={320} loading="lazy" />
                </span>
                <span className="cat-card__body">
                  <span className="cat-card__name">{item.title}</span>
                </span>
              </Link>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
