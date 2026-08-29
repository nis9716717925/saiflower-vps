import {
  collectionLandingMetadata,
  renderCollectionLanding,
} from '@/lib/collection-page';

export const revalidate = 120;

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps) {
  const { slug } = await params;
  return collectionLandingMetadata('occasion', slug);
}

export default async function OccasionPage({ params }: PageProps) {
  const { slug } = await params;
  return renderCollectionLanding('occasion', slug);
}
