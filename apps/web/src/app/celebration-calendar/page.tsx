import type { Metadata } from 'next';
import { CelebrationCalendarPage } from '@/components/landings/CelebrationCalendarView';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Celebrations Calendar 2026 | Flower Gifting Dates — Sai Flowers',
  description:
    'Plan flower gifts for every celebration — Valentine’s, Mother’s Day, festivals, Raksha Bandhan & more. Shop same-day bouquets for Delhi NCR from Sai Flowers.',
  keywords: ['celebrations calendar', 'gifting dates', 'festivals', 'Valentine flowers'],
  canonical: '/celebration-calendar',
});

export default function Page() {
  return <CelebrationCalendarPage />;
}
