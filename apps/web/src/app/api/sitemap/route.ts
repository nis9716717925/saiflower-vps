import { fetchBlogs, fetchCmsPages, fetchEvents, fetchGallery, fetchProducts } from '@/lib/api';
import taxonomyJson from '@/lib/data/collection-taxonomy.json';
import { LOCATION_REGISTRY } from '@/lib/locations';

export const dynamic = 'force-dynamic';

const SITE = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com';

type TaxMap = Record<string, Record<string, unknown>>;

function xmlEscape(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

function urlTag(path: string, priority = '0.5', changefreq = 'weekly'): string {
  const loc = path.startsWith('http') ? path : `${SITE}${path.startsWith('/') ? path : `/${path}`}`;
  return `  <url>
    <loc>${xmlEscape(loc)}</loc>
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`;
}

/** Internal handler; public URL is /sitemap.xml via next.config rewrite. */
export async function GET() {
  const urls: string[] = [];
  const add = (path: string, priority = '0.5', changefreq = 'weekly') => {
    urls.push(urlTag(path, priority, changefreq));
  };

  add('/', '1.0', 'daily');
  add('/flowers', '0.9', 'daily');
  add('/cakes', '0.9', 'daily');
  add('/gifts', '0.9', 'daily');
  add('/events', '0.8');
  add('/gallery', '0.7');
  add('/blog', '0.8');
  add('/about', '0.7', 'monthly');
  add('/contact', '0.7', 'monthly');
  add('/celebration-calendar', '0.85');
  add('/personalized', '0.8');
  add('/faq', '0.6', 'monthly');
  add('/custom-pages', '0.5');
  add('/sitemap', '0.4');
  add('/privacy', '0.3', 'yearly');
  add('/terms', '0.3', 'yearly');
  add('/refund-policy', '0.3', 'yearly');
  add('/delivery-policy', '0.3', 'yearly');
  add('/grievnce', '0.3', 'yearly');
  add('/legal', '0.3', 'yearly');

  const tax = taxonomyJson as TaxMap;
  for (const slug of Object.keys(tax.occasion || {})) add(`/occasion/${slug}`, '0.8');
  for (const slug of Object.keys(tax.relation || {})) add(`/relation/${slug}`, '0.75');
  for (const slug of Object.keys(tax.collection || {})) add(`/collection/${slug}`, '0.8');
  for (const slug of Object.keys(tax.flower || {})) add(`/flowers/${slug}`, '0.8');
  for (const loc of Object.values(LOCATION_REGISTRY)) add(`/${loc.slug}`, '0.7');

  try {
    const [blogs, events, gallery, pages, flowers] = await Promise.all([
      fetchBlogs(200),
      fetchEvents(100),
      fetchGallery(100),
      fetchCmsPages(400),
      fetchProducts({ type: 'flower', limit: 200, sort: 'new' }),
    ]);
    for (const b of blogs) add(b.url, '0.6');
    for (const e of events) add(e.url, '0.55');
    for (const g of gallery) add(g.url.includes('?') ? g.url : `/gallery-detail?id=${g.id}`, '0.5');
    for (const p of pages) add(p.url, '0.65');
    for (const f of flowers.items) {
      if (f.slug) add(`/flowers/${f.slug}`, '0.7', 'daily');
    }
  } catch {
    /* keep static */
  }

  const body = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls.join('\n')}
</urlset>
`;

  return new Response(body, {
    headers: {
      'Content-Type': 'application/xml; charset=utf-8',
      'Cache-Control': 'public, max-age=3600',
    },
  });
}
