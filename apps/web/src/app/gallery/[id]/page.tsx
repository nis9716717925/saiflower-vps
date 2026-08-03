import { redirect } from 'next/navigation';

interface PageProps {
  params: Promise<{ id: string }>;
}

/** Pretty /gallery/:id → PHP canonical /gallery-detail?id= */
export default async function GalleryIdRedirect({ params }: PageProps) {
  const { id } = await params;
  redirect(`/gallery-detail?id=${encodeURIComponent(id)}`);
}
