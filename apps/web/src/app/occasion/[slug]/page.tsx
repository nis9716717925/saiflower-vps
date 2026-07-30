import { StubPage } from '@/components/StubPage';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export default async function OccasionPage({ params }: PageProps) {
  const { slug } = await params;
  const title = slug
    .split('-')
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ');
  return (
    <StubPage
      title={title}
      description={`Occasion gifts for ${title}. Catalog filtering by occasion tags lands in Phase 7.`}
    />
  );
}
