import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { OptimizedImage, IMAGE_SIZE_PRESETS } from '@/components/ui/OptimizedImage';
import { fetchEvent } from '@/lib/api';
import { pageMetadata } from '@/lib/site-metadata';

export const revalidate = 120;

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  try {
    const event = await fetchEvent(slug);
    return pageMetadata({
      title: event.metaTitle || `${event.title} | Sai Flowers Events`,
      description:
        event.metaDescription ||
        (event.description ? event.description.replace(/<[^>]+>/g, '').slice(0, 160) : `Event package: ${event.title}`),
      canonical: event.url,
    });
  } catch {
    return pageMetadata({
      title: 'Events | Sai Flowers',
      description: 'Floral events, workshops, and decor packages from Sai Flowers.',
    });
  }
}

export default async function EventDetailPage({ params }: PageProps) {
  const { slug } = await params;
  let event: Awaited<ReturnType<typeof fetchEvent>>;
  try {
    event = await fetchEvent(slug);
  } catch {
    notFound();
  }

  return (
    <div className="cat-page">
      <div className="relative w-full overflow-hidden" style={{ maxHeight: '70vh' }}>
        <OptimizedImage
          src={event.image}
          alt={event.title}
          style={{ width: '100%', height: '70vh', objectFit: 'cover', display: 'block' }}
          width={1200}
          height={840}
          priority
          sizes={IMAGE_SIZE_PRESETS.hero}
        />
        <div
          style={{
            position: 'absolute',
            inset: 0,
            background: 'linear-gradient(to top, rgba(20,16,12,0.72), transparent 55%)',
            display: 'flex',
            alignItems: 'flex-end',
          }}
        >
          <div className="cat-wrap" style={{ paddingBottom: '2rem', color: '#fff' }}>
            {event.tag ? (
              <p className="cat-badge" style={{ background: 'rgba(255,255,255,0.15)', color: '#fff', border: 0 }}>
                {event.tag}
              </p>
            ) : null}
            <h1
              style={{
                fontFamily: "'Cormorant Garamond', Georgia, serif",
                fontSize: 'clamp(2.2rem, 6vw, 3.6rem)',
                margin: '0.4rem 0 0',
                lineHeight: 1.1,
              }}
            >
              {event.title}
            </h1>
          </div>
        </div>
      </div>

      <main className="cat-wrap" style={{ padding: '2rem 1rem 3rem' }}>
        <nav className="cat-crumb" aria-label="Breadcrumb">
          <ol>
            <li>
              <Link href="/">Home</Link>
            </li>
            <li>
              <Link href="/events">Events</Link>
            </li>
            <li aria-current="page">{event.title}</li>
          </ol>
        </nav>

        <div
          style={{ marginTop: '1.5rem', color: '#4b463e', lineHeight: 1.7, maxWidth: '68ch' }}
          dangerouslySetInnerHTML={{ __html: event.description || '<p>Custom floral styling for your celebration.</p>' }}
        />

        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.65rem', marginTop: '1.75rem' }}>
          <a
            className="cat-btn cat-btn--primary"
            href={`https://wa.me/918802004527?text=${encodeURIComponent(`Hi, I'd like to enquire about "${event.title}"`)}`}
            target="_blank"
            rel="noopener noreferrer"
          >
            Enquire on WhatsApp
          </a>
          <Link className="cat-btn cat-btn--accent" href="/flowers">
            Shop bouquets
          </Link>
        </div>
      </main>
    </div>
  );
}
