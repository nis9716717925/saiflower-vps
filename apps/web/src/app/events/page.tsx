import type { Metadata } from 'next';
import { LookbookPage } from '@/components/shop/LookbookPage';
import { fetchEvents } from '@/lib/api';
import { fetchLandingBouquets } from '@/lib/bouquet';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Events & Workshops | Sai Flowers',
  description:
    'Weddings, stage décor and celebrations — book florists for big moments, or send a bouquet today for smaller ones.',
  canonical: '/events',
});

export default async function EventsPage() {
  let items: Awaited<ReturnType<typeof fetchEvents>> = [];
  let recommend: Awaited<ReturnType<typeof fetchLandingBouquets>> = [];

  try {
    const [events, flowers] = await Promise.all([
      fetchEvents(100),
      fetchLandingBouquets({ sort: 'bestseller', limit: 12, search: 'wedding' }),
    ]);
    items = events.map((ev) => ({
      ...ev,
      priceLabel: 'Custom quote',
      badge: 'Enquire',
    }));
    recommend = flowers;
  } catch {
    items = [];
  }

  return (
    <LookbookPage
      badge="Events & Decor"
      crumbLabel="Events"
      title="Events & Workshops"
      description="Weddings, stage décor and celebrations — book florists for big moments, or send a bouquet today for smaller ones."
      heroImage="https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=1600&q=80"
      emptyMessage="Event packages are being updated. For smaller celebrations, these flower bouquets are ready now."
      emptyWaHref="https://wa.me/918802004527?text=Hi%2C%20I%27d%20like%20to%20enquire%20about%20event%20packages"
      sectionTitle="Event packages"
      sectionSub="Browse services — then check bouquet recommendations for gifting."
      items={items}
      recommendProducts={recommend}
      recommendTitle="Bouquet recommendations"
      recommendSub="Wedding and celebration flower picks ready for same-day delivery."
    />
  );
}
