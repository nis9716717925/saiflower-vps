import { StubPage } from '@/components/StubPage';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export default async function RelationPage({ params }: PageProps) {
  const { slug } = await params;
  const title = `Gifts for ${slug.charAt(0).toUpperCase()}${slug.slice(1)}`;
  return (
    <StubPage
      title={title}
      description="Relationship landing — full product rails will match PHP collection landings in Phase 7."
    />
  );
}
