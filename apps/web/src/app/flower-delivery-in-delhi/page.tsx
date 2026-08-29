import type { Metadata } from 'next';
import { LocationLandingView } from '@/components/landings/LocationLandingView';
import { fetchLandingBouquets } from '@/lib/bouquet';
import { locationGet } from '@/lib/locations';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Flower Delivery in Delhi | Same Day — Sai Flower',
  description:
    'Order fresh flowers for same-day delivery in Delhi NCR. Handcrafted bouquets from Sai Flowers.',
  keywords: ['flower delivery Delhi', 'same day flowers', 'Delhi NCR'],
  canonical: '/flower-delivery-in-delhi',
});

export default async function DelhiDeliveryPage() {
  const location = locationGet('flower-delivery-in-delhi') ?? {
    area: 'Delhi',
    local: 'Delhi',
    nearby: 'Gurgaon, Noida, and Ghaziabad',
    region: 'Delhi NCR',
    slug: 'flower-delivery-in-delhi',
  };

  const products = await fetchLandingBouquets({ sort: 'bestseller', limit: 40 });

  return <LocationLandingView location={location} products={products} />;
}
