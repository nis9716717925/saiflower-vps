import type { Metadata } from 'next';
import { AboutFaqList } from '@/components/pages/PageFaqs';
import { NeighborhoodMap } from '@/components/pages/NeighborhoodMap';
import { fetchFaqs } from '@/lib/api';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'About Us | Behind the Blooms — Sai Flowers',
  description:
    'Sai Flowers is a premium floral boutique in New Delhi, specializing in hand-crafted bouquets and luxury event styling since 2015.',
  keywords: ['about Sai Flowers', 'florist New Delhi', 'boutique flowers'],
  canonical: '/about',
});

const FALLBACK_FAQS = [
  {
    id: 1,
    question: 'Where is Sai Flowers based?',
    answer:
      'Our studio is on Lodhi Road, New Delhi. We deliver same-day across Delhi NCR including Gurgaon, Noida, Ghaziabad and Faridabad.',
  },
  {
    id: 2,
    question: 'Do you handle weddings and events?',
    answer:
      'Yes. Beyond everyday bouquets, we style weddings, corporate events and venue décor. WhatsApp our florists to plan your date.',
  },
  {
    id: 3,
    question: 'Are your flowers fresh?',
    answer:
      'Every arrangement is handcrafted with daily-fresh blooms. We guarantee careful packaging and freshness for doorstep delivery.',
  },
];

export default async function AboutPage() {
  let faqs = FALLBACK_FAQS;
  try {
    const rows = await fetchFaqs('about', 6);
    if (rows.length > 0) faqs = rows;
  } catch {
    /* use fallbacks */
  }

  return (
    <div className="about-page">
      <header className="page-header">
        <div className="about-container">
          <h1>Behind the Blooms</h1>
          <p>A decade of passion, creativity, and floral excellence.</p>
        </div>
      </header>

      <section className="about-section about-container">
        <div className="philosophy-grid">
          <div>
            <h2
              style={{
                fontSize: 'clamp(2rem, 5vw, 2.8rem)',
                lineHeight: 1.2,
                marginBottom: 20,
              }}
            >
              We don&apos;t just sell flowers;{' '}
              <span style={{ color: 'var(--about-primary)' }}>we deliver emotions.</span>
            </h2>
            <p style={{ fontSize: '1.1rem', color: '#555' }}>
              Every bouquet that leaves our studio is a hand-crafted masterpiece. We believe that
              nature&apos;s beauty should be accessible and tailored to your unique story.
            </p>
          </div>
          <div className="philosophy-card">
            <h3 style={{ marginTop: 0 }}>Our Mission</h3>
            <p>
              To redefine the floral experience in New Delhi by combining sustainable sourcing with
              avant-garde design. Our focus remains on quality, artistry, and the lasting smile on
              the recipient&apos;s face.
            </p>
          </div>
        </div>
      </section>

      <section className="about-section" style={{ background: '#fafafa', borderTop: '1px solid #f0f0f0' }}>
        <div className="about-container">
          <h2 style={{ textAlign: 'center', marginBottom: 50 }}>Our Evolution</h2>
          <div className="timeline">
            <div className="timeline-item left">
              <div className="date">2015</div>
              <h4>Founding Vision</h4>
              <p>Started as a boutique studio with a dream to bring international floral varieties to Delhi.</p>
            </div>
            <div className="timeline-item right">
              <div className="date">2018</div>
              <h4>Event Styling</h4>
              <p>Recognized as a leading luxury event and wedding floral decor specialist in NCR.</p>
            </div>
            <div className="timeline-item left">
              <div className="date">2024</div>
              <h4>Going Digital</h4>
              <p>Launched online custom bouquet building to reach our clients across the country.</p>
            </div>
            <div className="timeline-item right">
              <div className="date">2026</div>
              <h4>Sai Flowers Online</h4>
              <p>Expanded with a new digital experience at saiflowers.com</p>
            </div>
          </div>
        </div>
      </section>

      <section className="about-section about-container">
        <h2
          style={{
            textAlign: 'center',
            fontSize: 'clamp(1.8rem, 5vw, 2.5rem)',
            marginBottom: 10,
          }}
        >
          The Sai Flowers Promise
        </h2>
        <p style={{ textAlign: 'center', color: '#888', marginBottom: 30 }}>
          Why thousands trust us with their special moments.
        </p>
        <div className="why-grid">
          <div className="why-box">
            <div className="why-icon">
              <i className="fas fa-leaf" aria-hidden="true" />
            </div>
            <h3>Grown with Integrity</h3>
            <p>Direct sourcing from premium farms ensures that your flowers stay fresh for much longer.</p>
          </div>
          <div className="why-box">
            <div className="why-icon">
              <i className="fas fa-magic" aria-hidden="true" />
            </div>
            <h3>Artisan Design</h3>
            <p>Every petal is placed with intent by florists who treat their work as high art.</p>
          </div>
          <div className="why-box">
            <div className="why-icon">
              <i className="fas fa-heart" aria-hidden="true" />
            </div>
            <h3>Customer Love</h3>
            <p>Dedicated tracking and support because we value the trust you place in us.</p>
          </div>
        </div>
        <div className="swipe-indicator">
          <i className="fas fa-hand-pointer" aria-hidden="true" /> Swipe to explore
        </div>
      </section>

      <section className="about-section" style={{ background: '#fff', borderTop: '1px solid #f0f0f0' }}>
        <div className="about-container">
          <h2 style={{ textAlign: 'center', marginBottom: 10 }}>Common Questions</h2>
          <p style={{ textAlign: 'center', color: '#888', marginBottom: 40 }}>
            Find quick answers about our brand and heritage.
          </p>
          <AboutFaqList faqs={faqs} />
        </div>
      </section>

      <NeighborhoodMap />
    </div>
  );
}
