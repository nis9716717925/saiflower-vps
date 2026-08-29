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
    const product = await fetchProduct('gift', slug);
    return productMetadata(product, `/gifts/${slug}`);
  } catch {
    return pageMetadata({
      title: 'Gift | Sai Flower',
      description: 'Shop thoughtful gifts and hampers from Sai Flower with same-day delivery in Delhi NCR.',
    });
  }
}

export default async function GiftDetailPage({ params }: PageProps) {
  const { slug } = await params;
  try {
    const product = await fetchProduct('gift', slug);
    return (
      <>
        <ProductJsonLd product={product} category="gift" pageUrl={`/gifts/${slug}`} />
        <ProductDetailView product={product} listLabel="Gifts" listHref="/gifts" />
      </>
    );
  } catch {
    notFound();
  }
}
