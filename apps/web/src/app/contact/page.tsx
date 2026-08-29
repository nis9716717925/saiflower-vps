import type { Metadata } from 'next';
import Link from 'next/link';
import { ContactFaqList } from '@/components/pages/PageFaqs';
import { NeighborhoodMap } from '@/components/pages/NeighborhoodMap';
import { fetchFaqs } from '@/lib/api';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Contact Us | Sai Flowers Delhi',
  description:
    'Reach Sai Flowers on Lodhi Road, New Delhi by phone, email or WhatsApp for bespoke floral arrangements and same-day delivery.',
  keywords: ['contact Sai Flowers', 'florist Delhi', 'WhatsApp flowers'],
  canonical: '/contact',
});

const FALLBACK_FAQS = [
  {
    id: 1,
    question: 'How do I place an order?',
    answer:
      'Browse flowers, cakes or gifts, then use Add to Cart or Buy Now on any product. Every order appears in our order desk for confirmation.',
  },
  {
    id: 2,
    question: 'What are your delivery areas?',
    answer:
      'We offer same-day delivery across Delhi NCR — Delhi, Gurgaon, Noida, Ghaziabad, Faridabad and nearby areas. Order before 6 PM for same-day slots.',
  },
  {
    id: 3,
    question: 'Can I customise a bouquet?',
    answer:
      'Yes. WhatsApp us at +91 88020 04527 with your colour preference, budget and occasion — our florists will craft something special.',
  },
  {
    id: 4,
    question: 'Where is your studio?',
    answer:
      'Shop No 1, Sai Mandir, Lodhi Rd, Gokalpuri, Institutional Area, Lodi Colony, New Delhi, Delhi 110003.',
  },
];

export default async function ContactPage() {
  let faqs = FALLBACK_FAQS;
  try {
    const rows = await fetchFaqs('contact', 4);
    if (rows.length > 0) faqs = rows;
  } catch {
    /* use fallbacks */
  }

  return (
    <div className="contact-page">
      <div className="contact-container">
        <header className="page-header-simple">
          <h1>Let&apos;s Start a Conversation</h1>
          <p>Find us on social media or reach out directly for bespoke floral arrangements.</p>
        </header>

        <div className="contact-wrapper">
          <div className="info-box">
            <h3 style={{ color: 'var(--contact-primary)', marginTop: 0 }}>Official Channels</h3>
            <p style={{ fontSize: '0.95rem', color: '#666', marginTop: 10 }}>
              Reach us by phone, email, or WhatsApp. To place an order, use Add to Cart or Buy Now on
              any product — every order appears in our order desk.
            </p>

            <div style={{ marginTop: 30 }}>
              <p style={{ fontSize: '0.95rem', color: '#666' }}>
                <strong>Studio:</strong> Shop No 1, Sai Mandir, Lodhi Rd, Gokalpuri, Institutional
                Area, Lodi Colony, New Delhi, Delhi 110003
              </p>
              <p style={{ fontSize: '0.95rem', color: '#666' }}>
                <strong>Phone:</strong>{' '}
                <a href="tel:+918802004527" style={{ color: 'inherit', textDecoration: 'none' }}>
                  +91 88020 04527
                </a>
              </p>
              <p style={{ fontSize: '0.95rem', color: '#666' }}>
                <strong>Support:</strong>{' '}
                <a
                  href="mailto:saiflower03@gmail.com"
                  style={{ color: 'inherit', textDecoration: 'none' }}
                >
                  saiflower03@gmail.com
                </a>
              </p>
            </div>

            <div className="social-grid">
              <a
                href="https://www.instagram.com/saiflowerofficial?igsh=MWUwM3UwY3Q4bWc5bg%3D%3D"
                className="social-card"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-instagram" style={{ color: '#E1306C' }} aria-hidden="true" />
                <span style={{ fontSize: '0.75rem', fontWeight: 700 }}>Instagram</span>
              </a>
              <a
                href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/"
                className="social-card"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-facebook" style={{ color: '#1877F2' }} aria-hidden="true" />
                <span style={{ fontSize: '0.75rem', fontWeight: 700 }}>Facebook</span>
              </a>
              <a
                href="https://wa.me/918802004527"
                className="social-card"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-whatsapp" style={{ color: '#25D366' }} aria-hidden="true" />
                <span style={{ fontSize: '0.75rem', fontWeight: 700 }}>WhatsApp</span>
              </a>
              <a
                href="https://x.com/saiflower03"
                className="social-card"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-twitter" style={{ color: '#1DA1F2' }} aria-hidden="true" />
                <span style={{ fontSize: '0.75rem', fontWeight: 700 }}>Twitter</span>
              </a>
            </div>
          </div>
        </div>

        <div className="directory-section">
          <h3 style={{ textAlign: 'center', marginBottom: 40, color: 'var(--contact-primary)' }}>
            Explore our Universe
          </h3>
          <div className="dir-grid">
            <div className="dir-group">
              <h4>Collections</h4>
              <ul>
                <li>
                  <Link href="/flowers">Shop Flowers</Link>
                </li>
                <li>
                  <Link href="/cakes">Shop Cakes</Link>
                </li>
                <li>
                  <Link href="/gifts">Shop Gifts</Link>
                </li>
                <li>
                  <Link href="/events">Event Portfolios</Link>
                </li>
                <li>
                  <Link href="/gallery">Floral Art Gallery</Link>
                </li>
              </ul>
            </div>
            <div className="dir-group">
              <h4>Information</h4>
              <ul>
                <li>
                  <Link href="/about">Our Heritage</Link>
                </li>
                <li>
                  <Link href="/blog">Bloom Blog</Link>
                </li>
                <li>
                  <Link href="/sitemap">Sitemap</Link>
                </li>
              </ul>
            </div>
            <div className="dir-group">
              <h4>Customer Care</h4>
              <ul>
                <li>
                  <Link href="/delivery-policy">Delivery Policy</Link>
                </li>
                <li>
                  <Link href="/terms">Terms of Service</Link>
                </li>
                <li>
                  <Link href="/privacy">Privacy Policy</Link>
                </li>
                <li>
                  <Link href="/grievnce">Grievance</Link>
                </li>
                <li>
                  <Link href="/refund-policy">Refund Policy</Link>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div className="faq-section">
          <h2 style={{ textAlign: 'center', color: 'var(--contact-primary)', marginBottom: 40 }}>
            Common Questions
          </h2>
          <ContactFaqList faqs={faqs} />
        </div>
      </div>

      <NeighborhoodMap />
    </div>
  );
}
