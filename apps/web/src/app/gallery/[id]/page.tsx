import { redirect } from 'next/navigation';
import { pageMetadata } from '@/lib/site-metadata';

export const metadata = pageMetadata({
  title: 'Gallery | Sai Flowers',
  description: 'Browse floral inspiration and event styling from the Sai Flowers gallery.',
  canonical: '/gallery',
});

interface PageProps {
  params: Promise<{ id: string }>;
}

/** Pretty /gallery/:id → PHP canonical /gallery-detail?id= */
export default async function GalleryIdRedirect({ params }: PageProps) {
  const { id } = await params;
  redirect(`/gallery-detail?id=${encodeURIComponent(id)}`);
}
