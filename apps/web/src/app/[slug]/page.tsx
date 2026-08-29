import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { DynamicCmsPage } from '@/components/pages/DynamicCmsPage';
import { LocationLandingView } from '@/components/landings/LocationLandingView';
import { fetchCmsPage, fetchProducts } from '@/lib/api';
import { fetchLandingBouquets } from '@/lib/bouquet';
import { isLocationSlug, locationGet } from '@/lib/locations';
import { pageMetadata } from '@/lib/site-metadata';

export const revalidate = 120;

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps): Promise<Metadata> {
  const { slug } = await params;
  const location = locationGet(slug);
  if (location) {
    return pageMetadata({
      title: `Flower Delivery in ${location.local} | Same Day — Sai Flower`,
      description: `Order fresh flowers for same-day delivery in ${location.local}, ${location.region}. Handcrafted bouquets from Sai Flowers.`,
      canonical: `/${slug}`,
    });
  }

  try {
    const page = await fetchCmsPage(slug);
    return pageMetadata({
      title: page.metaTitle || `${page.title} | Sai Flowers`,
      description: page.metaDescription || page.shortDescription || `${page.title} — Sai Flowers.`,
      canonical: page.url,
    });
  } catch {
    return pageMetadata({
      title: 'Page Not Found | Sai Flowers',
      description: 'The page you are looking for could not be found on Sai Flowers.',
      noIndex: true,
    });
  }
}

export default async function CatchAllLandingPage({ params }: PageProps) {
  const { slug } = await params;

  if (isLocationSlug(slug)) {
    const location = locationGet(slug);
    if (!location) notFound();
    const products = await fetchLandingBouquets({ sort: 'bestseller', limit: 40 });
    return <LocationLandingView location={location} products={products} />;
  }

  let page: Awaited<ReturnType<typeof fetchCmsPage>>;
  try {
    page = await fetchCmsPage(slug);
  } catch {
    notFound();
  }

  let products: Awaited<ReturnType<typeof fetchLandingBouquets>> = [];
  if (page.layoutType === 'product_showcase') {
    try {
      if (page.pageTag) {
        const tagged = await fetchProducts({
          type: 'flower',
          search: page.pageTag,
          sort: 'bestseller',
          limit: 40,
        });
        products = tagged.items;
      }
      if (products.length < 12) {
        const bouquets = await fetchLandingBouquets({
          sort: 'bestseller',
          limit: 40,
          search: page.pageTag || undefined,
        });
        products = bouquets;
      }
    } catch {
      products = [];
    }
  }

  return <DynamicCmsPage page={page} products={products} />;
}
