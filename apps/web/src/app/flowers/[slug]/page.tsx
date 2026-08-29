import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { CollectionLandingView } from '@/components/landings/CollectionLandingView';
import { ProductDetailView } from '@/components/shop/ProductDetailView';
import {
  collectionFetchProducts,
  collectionGet,
  collectionIsFlowerTypeSlug,
} from '@/lib/collection';
import { fetchProduct } from '@/lib/api';
import { pageMetadata, productMetadata } from '@/lib/site-metadata';
import { ProductJsonLd } from '@/components/seo/ProductJsonLd';

export const revalidate = 120;

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  if (collectionIsFlowerTypeSlug(slug)) {
    const entry = collectionGet('flower', slug);
    if (entry) {
      return pageMetadata({
        title: `${entry.h1} | Sai Flower`,
        description: entry.short_description,
        canonical: entry.canonical_path,
      });
    }
  }
  try {
    const product = await fetchProduct('flower', slug);
    return productMetadata(product, `/flowers/${slug}`);
  } catch {
    return pageMetadata({
      title: 'Flower | Sai Flower',
      description: 'Shop fresh flowers and bouquets from Sai Flower with same-day delivery in Delhi NCR.',
    });
  }
}

export default async function FlowerSlugPage({ params }: PageProps) {
  const { slug } = await params;

  // PHP flower-router.php: taxonomy landing wins over PDP for type slugs
  if (collectionIsFlowerTypeSlug(slug)) {
    const entry = collectionGet('flower', slug);
    if (entry) {
      const products = await collectionFetchProducts(entry, 40);
      return <CollectionLandingView entry={entry} products={products} />;
    }
  }

  try {
    const product = await fetchProduct('flower', slug);
    return (
      <>
        <ProductJsonLd product={product} category="flower" pageUrl={`/flowers/${slug}`} />
        <ProductDetailView product={product} listLabel="Flowers" listHref="/flowers" />
      </>
    );
  } catch {
    notFound();
  }
}
