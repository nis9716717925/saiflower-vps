import type { Metadata } from 'next';
import Link from 'next/link';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Legal & Compliance | Sai Flower',
  description: 'Sai Flower policies for terms, delivery, privacy, and refunds.',
  canonical: '/legal',
});

const CARDS = [
  {
    href: '/terms',
    icon: 'fa-file-invoice',
    title: 'Terms of Service',
    blurb: 'Rules and regulations for using saiflower.com and purchasing our floral arrangements.',
  },
  {
    href: '/delivery-policy',
    icon: 'fa-truck-fast',
    title: 'Delivery Policy',
    blurb: 'Details on Delhi NCR delivery slots, midnight shipping, and timing buffers.',
  },
  {
    href: '/privacy',
    icon: 'fa-user-shield',
    title: 'Privacy Policy',
    blurb: 'How we handle your personal data and protect your shopping experience.',
  },
  {
    href: '/refund-policy',
    icon: 'fa-hand-holding-dollar',
    title: 'Refund Policy',
    blurb: 'Our guidelines for order cancellations, replacements, and quality issues.',
  },
  {
    href: '/grievnce',
    icon: 'fa-scale-balanced',
    title: 'Grievance Redressal',
    blurb: 'How to reach our Grievance Officer and expected resolution timelines.',
  },
  {
    href: '/faq',
    icon: 'fa-circle-question',
    title: 'Help Center / FAQ',
    blurb: 'Answers about delivery, ordering, payments, and floral services.',
  },
];

export default function LegalHubPage() {
  return (
    <main className="container mx-auto px-4 py-12 max-w-5xl">
      <header className="text-center mb-12">
        <h1 className="text-3xl md:text-4xl font-bold text-primary mb-3">Legal &amp; Compliance</h1>
        <p className="text-slate-600 max-w-2xl mx-auto">
          Transparency is the root of our relationship. Find all our policies regarding your orders and
          data here.
        </p>
      </header>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-12">
        {CARDS.map((card) => (
          <Link
            key={card.href}
            href={card.href}
            className="bg-white border border-slate-100 rounded-3xl p-8 text-center shadow-sm hover:-translate-y-1 hover:border-primary transition-all"
          >
            <i className={`fas ${card.icon} text-4xl text-primary mb-4`} aria-hidden="true" />
            <h2 className="text-xl font-bold text-primary mb-2">{card.title}</h2>
            <p className="text-slate-500 text-sm">{card.blurb}</p>
          </Link>
        ))}
      </div>
    </main>
  );
}
