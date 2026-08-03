import {
  collectionLandingMetadata,
  renderCollectionLanding,
} from '@/lib/collection-page';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PageProps) {
  const { slug } = await params;
  return collectionLandingMetadata('relation', slug);
}

export default async function RelationPage({ params }: PageProps) {
  const { slug } = await params;
  return renderCollectionLanding('relation', slug);
}
