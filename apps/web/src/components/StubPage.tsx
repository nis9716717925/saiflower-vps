import Link from 'next/link';

interface StubPageProps {
  title: string;
  description?: string;
}

export function StubPage({ title, description }: StubPageProps) {
  return (
    <main className="container mx-auto px-4 py-16 max-w-3xl">
      <h1 className="text-3xl font-bold text-slate-900 mb-4">{title}</h1>
      <p className="text-slate-600 leading-relaxed mb-8">
        {description ??
          'This page uses the same Sai Flower header and footer as our production site. Full content will be ported in a later phase.'}
      </p>
      <Link href="/flowers" className="inline-flex items-center gap-2 text-primary font-bold hover:underline">
        Shop flowers <i className="fas fa-arrow-right" aria-hidden="true" />
      </Link>
    </main>
  );
}
