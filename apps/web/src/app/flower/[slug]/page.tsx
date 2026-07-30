import { StubPage } from '@/components/StubPage';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export default async function FlowerTypePage({ params }: PageProps) {
  const { slug } = await params;
  const title = slug.charAt(0).toUpperCase() + slug.slice(1);
  return (
    <StubPage
      title={`${title} Flowers`}
      description={`Shop ${title.toLowerCase()} bouquets. This type landing will filter the flowers catalog in Phase 7.`}
    />
  );
}
