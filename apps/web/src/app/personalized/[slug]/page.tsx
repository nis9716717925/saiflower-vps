import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import {
  PersonalizedLandingView,
  personalizedGet,
} from '@/components/landings/PersonalizedLandingView';
import { fetchLandingBouquets } from '@/lib/bouquet';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  const entry = personalizedGet(slug);
  if (!entry) return { title: 'Not found' };
  return {
    title: `${entry.title} | Sai Flowers`,
    description: entry.short,
    alternates: { canonical: entry.canonical_path },
  };
}

export default async function PersonalizedSlugPage({ params }: PageProps) {
  const { slug } = await params;
  const entry = personalizedGet(slug);
  if (!entry) notFound();

  const search = entry.bouquet_keywords?.[0];
  const products = await fetchLandingBouquets({
    sort: 'bestseller',
    limit: 12,
    search,
  });

  return <PersonalizedLandingView entry={entry} products={products} />;
}
