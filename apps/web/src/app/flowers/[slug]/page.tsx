import type { Metadata } from 'next';
import { notFound, redirect } from 'next/navigation';
import { ProductDetailView } from '@/components/shop/ProductDetailView';
import { fetchProduct } from '@/lib/api';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  try {
    const { slug } = await params;
    const product = await fetchProduct('flower', slug);
    return {
      title: product.metaTitle ?? `${product.name} | Sai Flower`,
      description: product.metaDescription ?? product.description?.slice(0, 160),
    };
  } catch {
    return { title: 'Flower | Sai Flower' };
  }
}

export default async function FlowerDetailPage({ params }: PageProps) {
  const { slug } = await params;
  try {
    const product = await fetchProduct('flower', slug);
    return <ProductDetailView product={product} listLabel="Flowers" listHref="/flowers" />;
  } catch {
    // Soft-launch: type/occasion-ish slugs (e.g. /flowers/roses) fall back to search
    // instead of a hard 404 while SEO landings remain on PHP.
    if (slug && !slug.includes('.')) {
      redirect(`/search-results?q=${encodeURIComponent(slug.replace(/-/g, ' '))}`);
    }
    notFound();
  }
}
