import type { Metadata } from 'next';
import {
  PersonalizedLandingView,
  personalizedGet,
} from '@/components/landings/PersonalizedLandingView';
import { fetchLandingBouquets } from '@/lib/bouquet';
import { pageMetadata } from '@/lib/site-metadata';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  const entry = personalizedGet(slug);
  if (!entry) {
    const title = slug
      .split('-')
      .filter(Boolean)
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(' ');
    return pageMetadata({
      title: `${title || 'Personalised Gifts'} | Sai Flowers`,
      description:
        'Personalised gifts from Sai Flower — launching soon. Fresh bouquets available today.',
    });
  }
  return pageMetadata({
    title: `${entry.title} | Sai Flowers`,
    description: entry.short,
    canonical: entry.canonical_path,
  });
}

export default async function PersonalizedSlugPage({ params }: PageProps) {
  const { slug } = await params;
  const entry = personalizedGet(slug);

  const search = entry?.bouquet_keywords?.[0] ?? undefined;
  const products = await fetchLandingBouquets({
    sort: 'bestseller',
    limit: 12,
    search,
  });

  if (!entry) {
    const title = slug
      .split('-')
      .filter(Boolean)
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(' ');
    return (
      <PersonalizedLandingView
        entry={{
          slug,
          title: title || 'Personalised Gifts',
          h1: title || 'Personalised Gifts',
          badge: 'Personalised',
          status: 'available_soon',
          status_label: 'Available Soon',
          short:
            'This personalised gift line is launching soon. Explore bouquet ideas below while we finish crafting it.',
          hero: '/assets/images/hero/main-same-day.webp',
          recommend_line: 'Meanwhile, these bouquets make a heartfelt gift today.',
          bouquet_keywords: null,
          canonical_path: `/personalized/${slug}`,
        }}
        products={products}
      />
    );
  }

  return <PersonalizedLandingView entry={entry} products={products} />;
}
