import type { Metadata } from 'next';
import Link from 'next/link';
import { redirect } from 'next/navigation';
import { fetchGalleryItem } from '@/lib/api';
import { fetchLandingBouquets } from '@/lib/bouquet';
import { OptimizedImage } from '@/components/ui/OptimizedImage';
import { formatInr, productHref } from '@/lib/images';
import { pageMetadata } from '@/lib/site-metadata';

export async function buildGalleryDetailMetadata(id: string): Promise<Metadata> {
  try {
    const item = await fetchGalleryItem(Number(id));
    return pageMetadata({
      title: item.metaTitle || `${item.title} | Floral Gallery — Sai Flowers`,
      description: item.metaDescription || `Inspiration look: ${item.title} from Sai Flowers gallery.`,
      canonical: `/gallery-detail?id=${item.id}`,
    });
  } catch {
    return pageMetadata({
      title: 'Gallery | Sai Flowers',
      description: 'Browse floral inspiration and event styling from the Sai Flowers gallery.',
    });
  }
}

export async function GalleryDetailView({ id }: { id: string }) {
  const numericId = Number(id);
  if (!Number.isFinite(numericId)) redirect('/gallery');

  let item: Awaited<ReturnType<typeof fetchGalleryItem>>;
  try {
    item = await fetchGalleryItem(numericId);
  } catch {
    redirect('/gallery');
  }

  let recommend: Awaited<ReturnType<typeof fetchLandingBouquets>> = [];
  try {
    recommend = await fetchLandingBouquets({ sort: 'bestseller', limit: 8 });
  } catch {
    recommend = [];
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
            <li aria-current="page">{item.title}</li>
          </ol>
        </nav>

        <article style={{ display: 'grid', gap: '1.5rem', marginTop: '1.25rem' }}>
          <div
            style={{
              borderRadius: '1.25rem',
              overflow: 'hidden',
              background: '#fff',
              border: '1px solid rgba(28,24,20,0.08)',
              boxShadow: '0 12px 32px rgba(28,24,20,0.06)',
            }}
          >
            <OptimizedImage
              src={item.image}
              alt={item.title}
              priority
              style={{ width: '100%', maxHeight: '70vh', objectFit: 'cover', display: 'block' }}
            />
          </div>
          <div>
            {item.tag ? <p className="cat-badge">{item.tag}</p> : null}
            <h1
              style={{
                fontFamily: "'Cormorant Garamond', Georgia, serif",
                fontSize: 'clamp(2rem, 5vw, 3rem)',
                margin: '0.5rem 0',
              }}
            >
              {item.title}
            </h1>
            <p style={{ color: '#6a6258', maxWidth: '42ch' }}>
              Love this look? Order a matching handcrafted bouquet for same-day delivery across Delhi NCR.
            </p>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.65rem', marginTop: '1.25rem' }}>
              <Link className="cat-btn cat-btn--primary" href="/flowers">
                Shop flowers
              </Link>
              <a
                className="cat-btn cat-btn--accent"
                href={`https://wa.me/918802004527?text=${encodeURIComponent(`Hi, I love the gallery look "${item.title}" — can you recreate this?`)}`}
                target="_blank"
                rel="noopener noreferrer"
              >
                Recreate on WhatsApp
              </a>
            </div>
          </div>
        </article>

        {recommend.length > 0 ? (
          <section className="cat-section" style={{ marginTop: '2.5rem' }} aria-labelledby="gal-rec">
            <div className="cat-section__head">
              <strong id="gal-rec">Shop similar bouquets</strong>
              <span>Fresh picks ready for same-day gifting.</span>
            </div>
            <div className="cat-grid">
              {recommend.map((p) => (
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
      </main>
    </div>
  );
}
