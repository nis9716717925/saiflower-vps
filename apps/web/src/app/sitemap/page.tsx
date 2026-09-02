import type { Metadata } from 'next';
import Link from 'next/link';
import { fetchBlogs, fetchCmsPages, fetchEvents, fetchGallery } from '@/lib/api';
import taxonomyJson from '@/lib/data/collection-taxonomy.json';
import { LOCATION_REGISTRY } from '@/lib/locations';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Sitemap | Sai Flowers',
  description: 'Explore the full structure of Sai Flowers website. Find all our pages, products, and categories easily.',
  canonical: '/sitemap',
});

const STATIC_MAIN = [
  { path: '/', title: 'Home' },
  { path: '/flowers', title: 'Flowers' },
  { path: '/cakes', title: 'Cakes' },
  { path: '/gifts', title: 'Gifts' },
  { path: '/events', title: 'Events & Decor' },
  { path: '/gallery', title: 'Gallery' },
  { path: '/blog', title: 'Blog' },
  { path: '/about', title: 'About Us' },
  { path: '/contact', title: 'Contact' },
  { path: '/celebration-calendar', title: 'Celebrations Calendar' },
  { path: '/personalized', title: 'Personalised Gifts' },
  { path: '/faq', title: 'FAQ' },
  { path: '/custom-pages', title: 'Custom Pages' },
  { path: '/cart', title: 'Cart' },
  { path: '/checkout', title: 'Checkout' },
  { path: '/wishlist', title: 'Wishlist' },
  { path: '/login', title: 'Login' },
  { path: '/register', title: 'Register' },
];

const STATIC_LEGAL = [
  { path: '/privacy', title: 'Privacy Policy' },
  { path: '/terms', title: 'Terms & Conditions' },
  { path: '/refund-policy', title: 'Refund Policy' },
  { path: '/delivery-policy', title: 'Delivery Policy' },
  { path: '/grievnce', title: 'Grievance' },
  { path: '/legal', title: 'Legal' },
];

type TaxMap = Record<string, Record<string, { title?: string; h1?: string }>>;

function Section({
  title,
  links,
}: {
  title: string;
  links: Array<{ path: string; title: string }>;
}) {
  if (links.length === 0) return null;
  return (
    <section className="sitemap-section" style={{ marginBottom: '2.5rem' }}>
      <h2
        style={{
          color: '#2f6f4e',
          borderBottom: '2px solid #d4af37',
          paddingBottom: 10,
          marginBottom: 16,
          fontSize: '1.35rem',
          fontWeight: 700,
        }}
      >
        {title}
        <span style={{ fontSize: '0.75rem', color: '#94a3b8', marginLeft: 8, fontWeight: 600 }}>
          ({links.length})
        </span>
      </h2>
      <div style={{ maxHeight: 420, overflowY: 'auto' }}>
        {links.map((l) => (
          <Link
            key={l.path}
            href={l.path}
            style={{
              display: 'block',
              padding: '8px 0',
              color: '#555',
              textDecoration: 'none',
              borderBottom: '1px dashed #eee',
            }}
          >
            {l.title}
          </Link>
        ))}
      </div>
    </section>
  );
}

export default async function SitemapPage() {
  const tax = taxonomyJson as TaxMap;
  const occasion = Object.entries(tax.occasion || {}).map(([slug, e]) => ({
    path: `/occasion/${slug}`,
    title: e.h1 || e.title || slug,
  }));
  const relation = Object.entries(tax.relation || {}).map(([slug, e]) => ({
    path: `/relation/${slug}`,
    title: e.h1 || e.title || slug,
  }));
  const collection = Object.entries(tax.collection || {}).map(([slug, e]) => ({
    path: `/collection/${slug}`,
    title: e.h1 || e.title || slug,
  }));
  const flowerTypes = Object.entries(tax.flower || {}).map(([slug, e]) => ({
    path: `/flowers/${slug}`,
    title: e.h1 || e.title || slug,
  }));
  const locations = Object.values(LOCATION_REGISTRY).map((l) => ({
    path: `/${l.slug}`,
    title: `Flower Delivery in ${l.local}`,
  }));

  let blogs: Array<{ path: string; title: string }> = [];
  let events: Array<{ path: string; title: string }> = [];
  let gallery: Array<{ path: string; title: string }> = [];
  let cms: Array<{ path: string; title: string }> = [];

  try {
    const [b, e, g, p] = await Promise.all([
      fetchBlogs(100),
      fetchEvents(100),
      fetchGallery(100),
      fetchCmsPages(500),
    ]);
    blogs = b.map((x) => ({ path: x.url, title: x.title }));
    events = e.map((x) => ({ path: x.url, title: x.title }));
    gallery = g.map((x) => ({ path: x.url, title: x.title }));
    cms = p.map((x) => ({ path: x.url, title: x.title }));
  } catch {
    /* ignore */
  }

  return (
    <main style={{ maxWidth: 1100, margin: '0 auto', padding: '2rem 1rem 3.5rem' }}>
      <h1
        style={{
          fontFamily: "'Cormorant Garamond', Georgia, serif",
          fontSize: 'clamp(2rem, 5vw, 3rem)',
          color: '#2f6f4e',
          marginBottom: '0.5rem',
        }}
      >
        Sitemap
      </h1>
      <p style={{ color: '#6a6258', marginBottom: '2rem' }}>
        XML sitemap for search engines:{' '}
        <Link href="/sitemap.xml" style={{ color: '#2f6f4e', fontWeight: 700 }}>
          /sitemap.xml
        </Link>
      </p>

      <div
        style={{
          display: 'grid',
          gap: '1rem',
          gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))',
        }}
      >
        <Section title="Main pages" links={STATIC_MAIN} />
        <Section title="Legal" links={STATIC_LEGAL} />
        <Section title="Occasions" links={occasion} />
        <Section title="Relations" links={relation} />
        <Section title="Collections" links={collection} />
        <Section title="Flower types" links={flowerTypes} />
        <Section title="Locations" links={locations} />
        <Section title="Custom pages" links={cms} />
        <Section title="Blog" links={blogs} />
        <Section title="Events" links={events} />
        <Section title="Gallery" links={gallery} />
      </div>
    </main>
  );
}
