'use client';

import Link from 'next/link';
import { useCallback, useEffect, useId, useRef, useState, type CSSProperties } from 'react';
import { OptimizedImage } from '@/components/ui/OptimizedImage';
import { apiUrl } from '@/lib/api';
import { SiteIcon } from '@/components/icons/SiteIcon';
import type { SearchHit, SearchResponse } from '@/lib/types';

type HeaderSearchBoxProps = {
  inputId: string;
  formClassName?: string;
  inputClassName?: string;
  placeholder?: string;
  inputStyle?: CSSProperties;
};

export function HeaderSearchBox({
  inputId,
  formClassName,
  inputClassName,
  placeholder = 'Search flowers, occasions, gifts...',
  inputStyle,
}: HeaderSearchBoxProps) {
  const listId = useId();
  const wrapRef = useRef<HTMLDivElement>(null);
  const [query, setQuery] = useState('');
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [items, setItems] = useState<SearchHit[]>([]);
  const [active, setActive] = useState(-1);
  const seqRef = useRef(0);

  const hide = useCallback(() => {
    setOpen(false);
    setActive(-1);
  }, []);

  useEffect(() => {
    const q = query.trim();
    if (q.length < 1) {
      setItems([]);
      setOpen(false);
      setLoading(false);
      return;
    }

    const seq = ++seqRef.current;
    setLoading(true);
    setOpen(true);
    const timer = window.setTimeout(() => {
      void (async () => {
        try {
          const res = await fetch(
            apiUrl(`/search?q=${encodeURIComponent(q)}&limit=8`),
            { headers: { Accept: 'application/json' } },
          );
          const data = (await res.json()) as SearchResponse;
          if (seq !== seqRef.current) return;
          setItems(Array.isArray(data.results) ? data.results : []);
        } catch {
          if (seq !== seqRef.current) return;
          setItems([]);
        } finally {
          if (seq === seqRef.current) setLoading(false);
        }
      })();
    }, 160);

    return () => window.clearTimeout(timer);
  }, [query]);

  useEffect(() => {
    const onDoc = (e: MouseEvent) => {
      if (!wrapRef.current?.contains(e.target as Node)) hide();
    };
    document.addEventListener('mousedown', onDoc);
    return () => document.removeEventListener('mousedown', onDoc);
  }, [hide]);

  function onKeyDown(e: React.KeyboardEvent<HTMLInputElement>) {
    if (!open) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActive((i) => (items.length ? (i + 1) % items.length : -1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActive((i) => (items.length ? (i - 1 + items.length) % items.length : -1));
    } else if (e.key === 'Escape') {
      hide();
    } else if (e.key === 'Enter' && active >= 0 && items[active]?.link) {
      e.preventDefault();
      window.location.href = items[active].link!;
    }
  }

  const trimmed = query.trim();
  const showPanel = open && trimmed.length > 0;

  return (
    <div className={`sf-header-search${formClassName ? ` ${formClassName}` : ''}`} ref={wrapRef}>
      <form action="/search-results" method="GET" role="search" className="sf-header-search__form">
        <input
          name="q"
          id={inputId}
          type="search"
          autoComplete="off"
          enterKeyHint="search"
          placeholder={placeholder}
          className={inputClassName}
          style={inputStyle}
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          onFocus={() => {
            if (trimmed.length > 0) setOpen(true);
          }}
          onKeyDown={onKeyDown}
          role="combobox"
          aria-autocomplete="list"
          aria-expanded={showPanel}
          aria-controls={listId}
          aria-activedescendant={active >= 0 ? `${listId}-opt-${active}` : undefined}
        />
        <button type="submit" aria-label="Search">
          <SiteIcon name="search" size={20} />
        </button>
      </form>

      {showPanel ? (
        <div
          id={listId}
          className="sf-header-search__panel search-suggestions"
          role="listbox"
          aria-label="Search suggestions"
        >
          {loading && items.length === 0 ? (
            <div className="sf-suggest-msg">Searching “{trimmed}”…</div>
          ) : items.length === 0 ? (
            <>
              <div className="sf-suggest-msg">
                No matches for “{trimmed}”. Try roses, birthday, or cakes.
              </div>
              <Link className="sf-suggest-foot" href={`/search-results?q=${encodeURIComponent(trimmed)}`}>
                Search all results
              </Link>
            </>
          ) : (
            <>
              {items.map((item, i) => (
                <a
                  key={`${item.type}-${item.id}-${item.slug}`}
                  id={`${listId}-opt-${i}`}
                  role="option"
                  aria-selected={active === i}
                  href={item.link ?? `/search-results?q=${encodeURIComponent(trimmed)}`}
                  className={`sf-suggest-item${active === i ? ' is-active' : ''}`}
                  onMouseEnter={() => setActive(i)}
                >
                  <OptimizedImage
                    src={item.image}
                    alt=""
                    width={40}
                    height={40}
                    sizes="40px"
                  />
                  <span className="sf-suggest-copy">
                    <span className="sf-suggest-name">{item.name}</span>
                    <span className="sf-suggest-type">{item.badge || item.type}</span>
                  </span>
                </a>
              ))}
              <Link
                className="sf-suggest-foot"
                href={`/search-results?q=${encodeURIComponent(trimmed)}`}
              >
                View all results for “{trimmed}”
              </Link>
            </>
          )}
        </div>
      ) : null}
    </div>
  );
}
