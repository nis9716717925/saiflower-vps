import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { ProductDetailView } from '@/components/shop/ProductDetailView';
import { fetchProduct } from '@/lib/api';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  try {
    const { slug } = await params;
    const product = await fetchProduct('gift', slug);
    return {
      title: product.metaTitle ?? `${product.name} | Sai Flower`,
      description: product.metaDescription ?? product.description?.slice(0, 160),
    };
  } catch {
    return { title: 'Gift | Sai Flower' };
  }
}

export default async function GiftDetailPage({ params }: PageProps) {
  const { slug } = await params;
  try {
    const product = await fetchProduct('gift', slug);
    return <ProductDetailView product={product} listLabel="Gifts" listHref="/gifts" />;
  } catch {
    notFound();
  }
}
