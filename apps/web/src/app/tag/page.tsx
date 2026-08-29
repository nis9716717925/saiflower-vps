import type { Metadata } from 'next';
import Link from 'next/link';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { fetchProducts } from '@/lib/api';
import { formatInr, productHref } from '@/lib/images';
import { pageMetadata } from '@/lib/site-metadata';
import type { Product } from '@/lib/types';

export const metadata = pageMetadata({
  title: 'Shop by Tag | Sai Flowers',
  description: 'Find flowers, cakes and gifts by tag.',
  canonical: '/tag',
});

interface PageProps {
  searchParams: Promise<{ name?: string | string[] }>;
}

function nameParam(value: string | string[] | undefined): string {
  if (Array.isArray(value)) return (value[0] ?? '').trim();
  return (value ?? '').trim();
}

/** PHP: /tag?name= */
export default async function TagSearchPage({ searchParams }: PageProps) {
  const name = nameParam((await searchParams).name);
  let results: Product[] = [];

  if (name) {
    try {
      const [flowers, cakes, gifts] = await Promise.all([
        fetchProducts({ type: 'flower', search: name, limit: 40 }),
        fetchProducts({ type: 'cake', search: name, limit: 20 }),
        fetchProducts({ type: 'gift', search: name, limit: 20 }),
      ]);
      results = [...flowers.items, ...cakes.items, ...gifts.items];
    } catch {
      results = [];
    }
  }

  return (
    <div className="cat-page">
      <main className="cat-wrap" style={{ padding: '2rem 1rem 3rem' }}>
        <nav className="cat-crumb" aria-label="Breadcrumb">
          <ol>
            <li>
              <Link href="/">Home</Link>
            </li>
            <li aria-current="page">Tag</li>
          </ol>
        </nav>
        <h1
          style={{
            fontFamily: "'Cormorant Garamond', Georgia, serif",
            fontSize: 'clamp(2rem, 5vw, 3rem)',
            margin: '1rem 0 0.5rem',
          }}
        >
          {name ? `Tag: ${name}` : 'Shop by tag'}
        </h1>
        {!name ? (
          <p style={{ color: '#6a6258' }}>
            Add <code>?name=</code> to search products by tag, or{' '}
            <Link href="/flowers" style={{ color: '#2f6f4e', fontWeight: 700 }}>
              browse flowers
            </Link>
            .
          </p>
        ) : results.length === 0 ? (
          <p style={{ color: '#6a6258' }}>No products matched this tag.</p>
        ) : (
          <div className="cat-grid" role="list">
            {results.map((p) => (
              <Link key={`${p.type}-${p.id}`} className="cat-card" href={productHref(p.type, p.slug)} role="listitem">
                <span className="cat-card__media">
                  <OptimizedImage src={p.image} alt={p.name} width={320} height={320} sizes={IMAGE_SIZE_PRESETS.productGrid} />
                </span>
                <span className="cat-card__body">
                  <span className="cat-card__name">{p.name}</span>
                  <span className="cat-card__price">{formatInr(p.price)}</span>
                </span>
              </Link>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}
