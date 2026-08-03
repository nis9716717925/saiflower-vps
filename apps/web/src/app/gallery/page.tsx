import type { Metadata } from 'next';
import { LookbookPage } from '@/components/shop/LookbookPage';
import { fetchGallery } from '@/lib/api';
import { fetchLandingBouquets } from '@/lib/bouquet';

export const metadata: Metadata = {
  title: 'Floral Gallery | Sai Flowers',
  description:
    'Browse our portfolio of wedding décor, event styling and bespoke floral installations — then order a matching bouquet for same-day delivery.',
  alternates: { canonical: '/gallery' },
};

export default async function GalleryPage() {
  let items: Awaited<ReturnType<typeof fetchGallery>> = [];
  let recommend: Awaited<ReturnType<typeof fetchLandingBouquets>> = [];

  try {
    const [gallery, flowers] = await Promise.all([
      fetchGallery(100),
      fetchLandingBouquets({ sort: 'bestseller', limit: 12 }),
    ]);
    items = gallery;
    recommend = flowers;
  } catch {
    items = [];
  }

  return (
    <LookbookPage
      badge="Lookbook"
      crumbLabel="Gallery"
      title="Floral Gallery"
      description="Real arrangements from Sai Flowers — get inspired, then order a matching bouquet for same-day delivery."
      heroImage="https://images.unsplash.com/photo-1487530811176-3780da8112fd?auto=format&fit=crop&w=1600&q=80"
      emptyMessage="New gallery photos are being curated. Explore ready-to-order bouquets below."
      sectionTitle="Inspiration board"
      sectionSub="Tap a look you love — then shop the bouquet recommendations."
      items={items}
      recommendProducts={recommend}
      recommendTitle="Bouquet recommendations"
      recommendSub="Handcrafted flowers ready for same-day delivery across Delhi NCR."
    />
  );
}
