import type { Metadata } from 'next';
import {
  PersonalizedLandingView,
  personalizedGet,
} from '@/components/landings/PersonalizedLandingView';
import { fetchLandingBouquets } from '@/lib/bouquet';

export const metadata: Metadata = {
  title: 'Personalised Gifts | Sai Flowers',
  description:
    'Photo frames, engraved keepsakes and custom message cards — coming soon. Meanwhile, surprise them with a fresh bouquet today.',
  alternates: { canonical: '/personalized' },
};

export default async function PersonalizedHubPage() {
  const entry = personalizedGet('');
  if (!entry) return null;

  const products = await fetchLandingBouquets({ sort: 'bestseller', limit: 12 });

  return <PersonalizedLandingView entry={entry} products={products} />;
}
