'use client';

import Link from 'next/link';
import { useSearchParams } from 'next/navigation';
import { Suspense, useEffect, useState } from 'react';
import { apiUrl } from '@/lib/api';
import { resolveImageSrc } from '@/lib/images';
import type { SearchHit, SearchResponse } from '@/lib/types';

function SearchResultsContent() {
  const searchParams = useSearchParams();
  const q = searchParams.get('q')?.trim() ?? '';
  const [results, setResults] = useState<SearchHit[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    void (async () => {
      setLoading(true);
      if (!q) {
        setResults([]);
        setLoading(false);
        return;
      }
      try {
        const res = await fetch(apiUrl(`/search?q=${encodeURIComponent(q)}`));
        const data = (await res.json()) as SearchResponse;
        setResults(data.results ?? []);
      } catch {
        setResults([]);
      } finally {
        setLoading(false);
      }
    })();
  }, [q]);

  return (
    <main className="container mx-auto px-4 py-8 md:py-12 max-w-4xl">
      <h1 className="text-3xl font-bold mb-2">Search Results</h1>
      {q ? (
        <p className="text-slate-500 mb-8">
          Showing results for <strong>&ldquo;{q}&rdquo;</strong>
        </p>
      ) : (
        <p className="text-slate-500 mb-8">Enter a search term to find flowers, cakes and gifts.</p>
      )}

      {loading ? (
        <p className="text-slate-500">Searching…</p>
      ) : results.length === 0 ? (
        <div className="text-center py-12 text-slate-500">
          <span className="material-icons-outlined text-5xl text-slate-300 mb-4 block">search_off</span>
          <p>No results found. Try a different keyword.</p>
          <Link href="/flowers" className="inline-block mt-6 text-primary font-bold hover:underline">
            Browse all flowers
          </Link>
        </div>
      ) : (
        <div className="space-y-3">
          {results.map((hit) => (
            <Link
              key={`${hit.type}-${hit.id}-${hit.slug}`}
              href={hit.link ?? '/flowers'}
              className="flex items-center gap-4 bg-white rounded-2xl border border-slate-100 p-4 hover:border-primary/30 hover:shadow-md transition-all"
            >
              <div className="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 flex-shrink-0">
                <img src={resolveImageSrc(hit.image)} alt={hit.name} className="w-full h-full object-cover" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="font-bold text-slate-900 truncate">{hit.name}</p>
                {hit.badge && <span className="text-xs text-primary font-semibold uppercase">{hit.badge}</span>}
              </div>
              <span className="material-icons-outlined text-slate-400">chevron_right</span>
            </Link>
          ))}
        </div>
      )}
    </main>
  );
}

export default function SearchResultsPage() {
  return (
    <Suspense fallback={<main className="container mx-auto px-4 py-16 text-slate-500">Loading…</main>}>
      <SearchResultsContent />
    </Suspense>
  );
}
