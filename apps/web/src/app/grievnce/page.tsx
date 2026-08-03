import type { Metadata } from 'next';
import Link from 'next/link';

export const metadata: Metadata = {
  title: 'Grievance Redressal Mechanism | Sai Flowers',
  description:
    'Contact Sai Flower’s Grievance Officer for order, delivery, or service complaints under the Consumer Protection (E-Commerce) Rules, 2020.',
  alternates: { canonical: '/grievnce' },
};

export default function GrievancePage() {
  return (
    <main style={{ background: '#fff1f5', padding: '2.5rem 1rem 3.5rem', minHeight: '60vh' }}>
      <h1
        style={{
          textAlign: 'center',
          color: '#2f6f4e',
          fontFamily: "'Cormorant Garamond', Georgia, serif",
          fontSize: 'clamp(2rem, 5vw, 2.75rem)',
          margin: '0 0 1.5rem',
        }}
      >
        Grievance Redressal Mechanism
      </h1>
      <div
        style={{
          maxWidth: 900,
          margin: '0 auto',
          background: '#fff',
          padding: '1.5rem',
          borderRadius: 16,
          boxShadow: '0 10px 30px rgba(0,0,0,0.05)',
          color: '#555',
          lineHeight: 1.65,
        }}
      >
        <p>
          In accordance with the Information Technology Act, 2000 and Consumer Protection (E-Commerce)
          Rules, 2020, Sai Flower is committed to resolving customer grievances in a timely and transparent
          manner.
        </p>
        <p>
          If you have any complaints regarding your order, delivery, or our services, please reach out to
          our Grievance Officer directly using the contact information below:
        </p>
        <div
          style={{
            background: '#f9f9f9',
            padding: '1.25rem',
            borderRadius: 8,
            marginTop: '1.25rem',
            borderLeft: '4px solid #2f6f4e',
          }}
        >
          <p>
            <strong>Name:</strong> Krishan Kumar
          </p>
          <p>
            <strong>Designation:</strong> Grievance Officer
          </p>
          <p>
            <strong>Email Address:</strong>{' '}
            <a href="mailto:saiflower03@gmail.com" style={{ color: '#2f6f4e' }}>
              saiflower03@gmail.com
            </a>
          </p>
          <p>
            <strong>Contact Number:</strong>{' '}
            <a href="tel:+918802004527" style={{ color: '#2f6f4e' }}>
              +91-8802004527
            </a>
          </p>
          <p>
            <strong>Operating Address:</strong> Shop No. 1, Lodhi Road, Sai Baba Mandir, New Delhi
          </p>
        </div>
        <h2 style={{ color: '#2f6f4e', fontSize: '1.25rem', marginTop: '1.75rem' }}>Resolution Time</h2>
        <p>
          We will acknowledge your complaint within <strong>48 hours</strong> and strive to resolve it
          within <strong>3 to 5 business days</strong>.
        </p>
        <p style={{ marginTop: '1.5rem' }}>
          <Link href="/legal" style={{ color: '#2f6f4e', fontWeight: 700 }}>
            ← Back to Legal &amp; Compliance
          </Link>
        </p>
      </div>
    </main>
  );
}
