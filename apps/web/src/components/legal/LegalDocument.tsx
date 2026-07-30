import type { ReactNode } from 'react';

interface LegalDocumentProps {
  title: string;
  updated?: string;
  children: ReactNode;
}

export function LegalDocument({ title, updated = 'January 2026', children }: LegalDocumentProps) {
  return (
    <main className="bg-[#fafafa] pb-16">
      <h1 className="text-center text-primary pt-10 pb-6 text-3xl md:text-4xl font-bold px-4">
        {title}
      </h1>
      <div className="content-box max-w-3xl mx-auto bg-white rounded-2xl shadow-lg px-6 py-8 md:px-12 md:py-12 text-slate-700 leading-relaxed">
        <p className="font-semibold text-slate-800 mb-6">Last Updated: {updated}</p>
        <div className="legal-prose space-y-4 [&_h2]:text-primary [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mt-8 [&_h2]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mb-2 [&_a]:text-primary [&_a]:font-semibold">
          {children}
        </div>
      </div>
    </main>
  );
}
