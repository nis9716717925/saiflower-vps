import type { Metadata } from 'next';
import { CelebrationCalendarPage } from '@/components/landings/CelebrationCalendarView';

export const metadata: Metadata = {
  title: 'Celebrations Calendar 2026 | Flower Gifting Dates — Sai Flowers',
  description:
    'Plan flower gifts for every celebration — Valentine’s, Mother’s Day, festivals, Raksha Bandhan & more. Shop same-day bouquets for Delhi NCR from Sai Flowers.',
  alternates: { canonical: '/celebration-calendar' },
};

export default function Page() {
  return <CelebrationCalendarPage />;
}
