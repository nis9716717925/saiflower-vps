import type { Metadata } from 'next';
import { redirect } from 'next/navigation';
import { buildGalleryDetailMetadata, GalleryDetailView } from '@/components/shop/GalleryDetailView';
import { pageMetadata } from '@/lib/site-metadata';

interface PageProps {
  searchParams: Promise<{ id?: string | string[] }>;
}

function idParam(value: string | string[] | undefined): string | null {
  if (Array.isArray(value)) return value[0] ?? null;
  return value ?? null;
}

export async function generateMetadata({ searchParams }: PageProps): Promise<Metadata> {
  const id = idParam((await searchParams).id);
  if (!id) {
    return pageMetadata({
      title: 'Gallery | Sai Flowers',
      description: 'Browse floral inspiration and event styling from the Sai Flowers gallery.',
      canonical: '/gallery',
    });
  }
  return buildGalleryDetailMetadata(id);
}

/** PHP canonical: /gallery-detail?id= */
export default async function GalleryDetailQueryPage({ searchParams }: PageProps) {
  const id = idParam((await searchParams).id);
  if (!id) redirect('/gallery');
  return <GalleryDetailView id={id} />;
}
