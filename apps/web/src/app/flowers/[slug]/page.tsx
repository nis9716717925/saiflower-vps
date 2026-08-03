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

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  if (collectionIsFlowerTypeSlug(slug)) {
    const entry = collectionGet('flower', slug);
    if (entry) {
      return {
        title: `${entry.h1} | Sai Flower`,
        description: entry.short_description,
        alternates: { canonical: entry.canonical_path },
      };
    }
  }
  try {
    const product = await fetchProduct('flower', slug);
    return {
      title: product.metaTitle ?? `${product.name} | Sai Flower`,
      description: product.metaDescription ?? product.description?.slice(0, 160),
    };
  } catch {
    return { title: 'Flower | Sai Flower' };
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
    return <ProductDetailView product={product} listLabel="Flowers" listHref="/flowers" />;
  } catch {
    notFound();
  }
}
