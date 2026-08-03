import type { Metadata } from 'next';
import { redirect } from 'next/navigation';
import { buildGalleryDetailMetadata, GalleryDetailView } from '@/components/shop/GalleryDetailView';

interface PageProps {
  searchParams: Promise<{ id?: string | string[] }>;
}

function idParam(value: string | string[] | undefined): string | null {
  if (Array.isArray(value)) return value[0] ?? null;
  return value ?? null;
}

export async function generateMetadata({ searchParams }: PageProps): Promise<Metadata> {
  const id = idParam((await searchParams).id);
  if (!id) return { title: 'Gallery | Sai Flowers' };
  return buildGalleryDetailMetadata(id);
}

/** PHP canonical: /gallery-detail?id= */
export default async function GalleryDetailQueryPage({ searchParams }: PageProps) {
  const id = idParam((await searchParams).id);
  if (!id) redirect('/gallery');
  return <GalleryDetailView id={id} />;
}
