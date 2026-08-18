import type { Metadata } from 'next';
import { CollectionLandingView } from '@/components/landings/CollectionLandingView';
import {
  collectionFetchProducts,
  collectionGet,
  type CollectionKind,
} from '@/lib/collection';
import { fetchLandingBouquets } from '@/lib/bouquet';

export async function renderCollectionLanding(kind: CollectionKind, slug: string) {
  const entry = collectionGet(kind, slug);
  if (entry) {
    const products = await collectionFetchProducts(entry, 40);
    return <CollectionLandingView entry={entry} products={products} />;
  }

  // Soft landing instead of hard 404 for unknown taxonomy slugs linked from nav.
  const products = await fetchLandingBouquets({ sort: 'bestseller', limit: 24 });
  const title = slug
    .split('-')
    .filter(Boolean)
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');

  return (
    <CollectionLandingView
      entry={{
        kind,
        slug,
        title: title || 'Collection',
        h1: title || 'Collection',
        short_description:
          'This collection is being curated. Browse our bestselling bouquets below, or explore all flowers.',
        badge: 'Coming soon',
        cta_label: 'Shop flowers',
        hero_image: '/assets/images/hero/main-same-day.jpg',
        filter: {},
        related: [],
        faqs: [],
        canonical_path:
          kind === 'flower'
            ? `/flowers/${slug}`
            : kind === 'occasion'
              ? `/occasion/${slug}`
              : kind === 'relation'
                ? `/relation/${slug}`
                : `/collection/${slug}`,
      }}
      products={products}
    />
  );
}

export async function collectionLandingMetadata(
  kind: CollectionKind,
  slug: string,
): Promise<Metadata> {
  const entry = collectionGet(kind, slug);
  if (!entry) {
    const title = slug
      .split('-')
      .filter(Boolean)
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(' ');
    return {
      title: `${title || 'Collection'} | Sai Flower`,
      description: 'Explore handcrafted flowers from Sai Flower for same-day delivery in Delhi NCR.',
    };
  }
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
