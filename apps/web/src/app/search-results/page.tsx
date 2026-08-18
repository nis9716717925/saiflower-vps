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
        const res = await fetch(apiUrl(`/search?q=${encodeURIComponent(q)}&limit=36`));
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
    <div className="sf-shop bg-[#f3f6f4] min-h-screen">
      <div className="sf-shop__sticky">
        <div className="sf-shop__sticky-inner">
          <div className="sf-shop__heading">
            <h1>Search results</h1>
            <p>
              {loading
                ? 'Searching…'
                : q
                  ? `${results.length} result${results.length === 1 ? '' : 's'} for “${q}”`
                  : 'Enter a keyword to find flowers, cakes & gifts'}
            </p>
          </div>
          <Link href="/flowers" className="sf-shop__filter-btn">
            Browse flowers
          </Link>
        </div>
        <div className="sf-shop__chips hide-scrollbar" aria-label="Quick links">
          <Link href="/flowers?sort=bestseller" className="sf-chip">
            <i className="fas fa-fire" aria-hidden="true" /> Popular
          </Link>
          <Link href="/flowers?price_min=0&price_max=999" className="sf-chip">
            Under ₹999
          </Link>
          <Link href="/occasion/birthday" className="sf-chip">
            Birthday
          </Link>
          <Link href="/cakes" className="sf-chip">
            Cakes
          </Link>
        </div>
      </div>

      <main className="sf-shop__main sf-shop__main--single">
        {loading ? (
          <p className="sf-shop__loading">Searching…</p>
        ) : !q ? (
          <div className="sf-shop__empty">
            <i className="fas fa-magnifying-glass" aria-hidden="true" />
            <h3>Start searching</h3>
            <p>Try “roses”, “birthday”, or “same day”.</p>
            <Link href="/flowers" className="sf-shop__empty-cta">
              Shop flowers
            </Link>
          </div>
        ) : results.length === 0 ? (
          <div className="sf-shop__empty">
            <i className="fas fa-search" aria-hidden="true" />
            <h3>No results</h3>
            <p>Try a different keyword or browse bestsellers.</p>
            <Link href="/flowers" className="sf-shop__empty-cta">
              Browse all flowers
            </Link>
          </div>
        ) : (
          <div className="sf-search-grid">
            {results.map((hit) => (
              <Link
                key={`${hit.type}-${hit.id}-${hit.slug}`}
                href={hit.link ?? '/flowers'}
                className="sf-search-card"
              >
                <span className="sf-search-card__media">
                  <img
                    src={resolveImageSrc(hit.image)}
                    alt={hit.name}
                    width={320}
                    height={400}
                    loading="lazy"
                    decoding="async"
                  />
                </span>
                <span className="sf-search-card__body">
                  {hit.badge ? <span className="sf-search-card__badge">{hit.badge}</span> : null}
                  <span className="sf-search-card__name">{hit.name}</span>
                  <span className="sf-search-card__cta">
                    View <i className="fas fa-arrow-right" aria-hidden="true" />
                  </span>
                </span>
              </Link>
            ))}
          </div>
        )}
      </main>
    </div>
  );
}

export default function SearchResultsPage() {
  return (
    <Suspense fallback={<main className="sf-shop__loading p-8 text-center text-slate-500">Loading…</main>}>
      <SearchResultsContent />
    </Suspense>
  );
}
