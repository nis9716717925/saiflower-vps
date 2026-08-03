'use client';

import { useMemo, useState } from 'react';
import type { FaqItem } from '@/lib/types';

interface FaqHelpCenterProps {
  faqs: FaqItem[];
}

export function FaqHelpCenter({ faqs }: FaqHelpCenterProps) {
  const tags = useMemo(() => {
    const set = new Set<string>();
    for (const f of faqs) {
      const t = (f.page || 'general').trim().toLowerCase();
      if (t) set.add(t);
    }
    return Array.from(set).sort();
  }, [faqs]);

  const [tag, setTag] = useState('all');
  const [query, setQuery] = useState('');

  const visible = faqs.filter((f) => {
    const page = (f.page || 'general').trim().toLowerCase();
    const matchesTag = tag === 'all' || page === tag;
    const hay = `${f.question} ${f.answer} ${page}`.toLowerCase();
    const matchesQuery = !query || hay.includes(query.toLowerCase());
    return matchesTag && matchesQuery;
  });

  return (
    <div className="faq-help">
      <div className="faq-help__inner">
        <header className="faq-help__header">
          <h1>How can we help?</h1>
          <div className="faq-help__search">
            <span className="material-icons-outlined" aria-hidden="true">
              search
            </span>
            <input
              type="search"
              value={query}
              onChange={(e) => setQuery(e.target.value)}
              placeholder="Search keywords..."
              aria-label="Search FAQs"
            />
          </div>
        </header>

        <div className="faq-help__tags" role="tablist" aria-label="FAQ categories">
          <button
            type="button"
            className={`faq-help__tag${tag === 'all' ? ' is-active' : ''}`}
            onClick={() => setTag('all')}
          >
            All
          </button>
          {tags.map((t) => (
            <button
              key={t}
              type="button"
              className={`faq-help__tag${tag === t ? ' is-active' : ''}`}
              onClick={() => setTag(t)}
            >
              {t}
            </button>
          ))}
        </div>

        <div className="faq-help__list">
          {visible.map((f) => (
            <details key={f.id} className="faq-help__item">
              <summary>
                <span>
                  <span className="faq-help__badge">{(f.page || 'general').toUpperCase()}</span>
                  {f.question}
                </span>
              </summary>
              <div className="faq-help__answer">{f.answer}</div>
            </details>
          ))}
        </div>

        {visible.length === 0 ? (
          <p className="faq-help__empty">No matching results.</p>
        ) : null}
      </div>
    </div>
  );
}
