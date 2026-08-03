import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { CollectionLandingView } from '@/components/landings/CollectionLandingView';
import {
  collectionFetchProducts,
  collectionGet,
  type CollectionKind,
} from '@/lib/collection';

export async function renderCollectionLanding(kind: CollectionKind, slug: string) {
  const entry = collectionGet(kind, slug);
  if (!entry) notFound();
  const products = await collectionFetchProducts(entry, 40);
  return <CollectionLandingView entry={entry} products={products} />;
}

export async function collectionLandingMetadata(
  kind: CollectionKind,
  slug: string,
): Promise<Metadata> {
  const entry = collectionGet(kind, slug);
  if (!entry) return { title: 'Not found' };
  const title = `${entry.h1} | Sai Flower`;
  const description = entry.short_description;
  return {
    title,
    description,
    alternates: { canonical: entry.canonical_path },
    openGraph: {
      title: entry.h1,
      description,
      url: entry.canonical_path,
      images: entry.hero_image ? [{ url: entry.hero_image }] : undefined,
    },
  };
}
