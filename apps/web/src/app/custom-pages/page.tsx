import type { Metadata } from 'next';
import Link from 'next/link';
import { fetchCmsPages } from '@/lib/api';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Custom Pages & Local Delivery | Sai Flowers',
  description: 'Browse Sai Flowers SEO and local flower delivery pages across Delhi NCR.',
  canonical: '/custom-pages',
});

export default async function CustomPagesIndex() {
  let pages: Awaited<ReturnType<typeof fetchCmsPages>> = [];
  try {
    pages = await fetchCmsPages(500);
  } catch {
    pages = [];
  }

  const locations = pages.filter((p) => p.slug.startsWith('flower-delivery-in-'));
  const other = pages.filter((p) => !p.slug.startsWith('flower-delivery-in-'));

  return (
    <main className="cat-wrap" style={{ padding: '2rem 1rem 3rem' }}>
      <nav className="cat-crumb" aria-label="Breadcrumb">
        <ol>
          <li>
            <Link href="/">Home</Link>
          </li>
          <li aria-current="page">Custom pages</li>
        </ol>
      </nav>
      <h1
        style={{
          fontFamily: "'Cormorant Garamond', Georgia, serif",
          fontSize: 'clamp(2rem, 5vw, 3rem)',
          margin: '1rem 0 0.5rem',
        }}
      >
        Custom pages
      </h1>
      <p style={{ color: '#6a6258', maxWidth: '48ch', marginBottom: '2rem' }}>
        Local delivery landings and special occasion pages from our catalogue.
      </p>

      {locations.length > 0 ? (
        <section style={{ marginBottom: '2.5rem' }}>
          <h2 style={{ fontSize: '1.25rem', marginBottom: '0.85rem' }}>Flower delivery areas</h2>
          <ul style={{ display: 'grid', gap: '0.5rem', gridTemplateColumns: 'repeat(auto-fill,minmax(220px,1fr))', listStyle: 'none', padding: 0 }}>
            {locations.map((p) => (
              <li key={p.id}>
                <Link href={p.url} style={{ color: '#2f6f4e', fontWeight: 600 }}>
                  {p.title}
                </Link>
              </li>
            ))}
          </ul>
        </section>
      ) : null}

      {other.length > 0 ? (
        <section>
          <h2 style={{ fontSize: '1.25rem', marginBottom: '0.85rem' }}>More pages</h2>
          <ul style={{ display: 'grid', gap: '0.5rem', gridTemplateColumns: 'repeat(auto-fill,minmax(220px,1fr))', listStyle: 'none', padding: 0 }}>
            {other.map((p) => (
              <li key={p.id}>
                <Link href={p.url} style={{ color: '#2f6f4e', fontWeight: 600 }}>
                  {p.title}
                </Link>
              </li>
            ))}
          </ul>
        </section>
      ) : null}

      {pages.length === 0 ? (
        <p style={{ color: '#6a6258' }}>
          No custom pages found yet. Try our{' '}
          <Link href="/flower-delivery-in-delhi" style={{ color: '#2f6f4e', fontWeight: 700 }}>
            Delhi delivery
          </Link>{' '}
          landing or{' '}
          <Link href="/flowers" style={{ color: '#2f6f4e', fontWeight: 700 }}>
            shop flowers
          </Link>
          .
        </p>
      ) : null}
    </main>
  );
}
