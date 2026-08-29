import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { ProductDetailView } from '@/components/shop/ProductDetailView';
import { ProductJsonLd } from '@/components/seo/ProductJsonLd';
import { fetchProduct } from '@/lib/api';
import { pageMetadata, productMetadata } from '@/lib/site-metadata';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  try {
    const { slug } = await params;
    const product = await fetchProduct('cake', slug);
    return productMetadata(product, `/cakes/${slug}`);
  } catch {
    return pageMetadata({
      title: 'Cake | Sai Flower',
      description: 'Order celebration cakes from Sai Flower with same-day delivery in Delhi NCR.',
    });
  }
}

export default async function CakeDetailPage({ params }: PageProps) {
  const { slug } = await params;
  try {
    const product = await fetchProduct('cake', slug);
    return (
      <>
        <ProductJsonLd product={product} category="cake" pageUrl={`/cakes/${slug}`} />
        <ProductDetailView product={product} listLabel="Cakes" listHref="/cakes" />
      </>
    );
  } catch {
    notFound();
  }
}
