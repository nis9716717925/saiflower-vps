import { StubPage } from '@/components/StubPage';

interface PageProps {
  params: Promise<{ slug: string }>;
}

export default async function CollectionPage({ params }: PageProps) {
  const { slug } = await params;
  const title = slug
    .split('-')
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ');
  return (
    <StubPage
      title={title}
      description={`Collection landing for “${title}”. Full taxonomy + filtered catalog will be wired in Phase 7.`}
    />
  );
}
