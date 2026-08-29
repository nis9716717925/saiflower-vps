import type { Metadata } from 'next';
import { FaqHelpCenter } from '@/components/pages/FaqHelpCenter';
import { fetchFaqs } from '@/lib/api';
import { pageMetadata } from '@/lib/site-metadata';
import type { FaqItem } from '@/lib/types';

export const metadata = pageMetadata({
  title: 'Help Center | Sai Flowers',
  description: 'Answers about delivery, ordering, payments, and floral services from Sai Flowers.',
  canonical: '/faq',
});

const FALLBACK = [
  {
    id: 1,
    question: 'Do you offer same-day flower delivery in Delhi?',
    answer:
      'Yes. Order before the daily cutoff and we deliver fresh bouquets across Delhi NCR including Gurgaon, Noida, Ghaziabad and Faridabad.',
    page: 'delivery',
  },
  {
    id: 2,
    question: 'How do I track my order?',
    answer: 'After checkout you receive confirmation on WhatsApp/SMS. Message us anytime with your order details for live updates.',
    page: 'orders',
  },
  {
    id: 3,
    question: 'Can I add a personal message card?',
    answer: 'Yes — add your note at checkout and we include a complimentary message card with your bouquet.',
    page: 'general',
  },
];

export default async function FaqPage() {
  let faqs: FaqItem[] = FALLBACK;
  try {
    const rows = await fetchFaqs('all', 200);
    if (rows.length > 0) faqs = rows;
  } catch {
    /* fallbacks */
  }

  return <FaqHelpCenter faqs={faqs} />;
}
