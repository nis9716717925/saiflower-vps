'use client';

import type { FaqItem } from '@/lib/types';

export function AboutFaqList({ faqs }: { faqs: FaqItem[] }) {
  if (faqs.length === 0) {
    return (
      <p style={{ textAlign: 'center', color: '#999' }}>
        Explore our collections to see our quality in action.
      </p>
    );
  }

  return (
    <div className="faq-wrapper">
      {faqs.map((f) => (
        <div
          key={f.id}
          className="faq-item"
          onClick={(e) => (e.currentTarget as HTMLElement).classList.toggle('active')}
          onKeyDown={(e) => {
            if (e.key === 'Enter' || e.key === ' ') {
              e.preventDefault();
              (e.currentTarget as HTMLElement).classList.toggle('active');
            }
          }}
          role="button"
          tabIndex={0}
        >
          <div className="faq-question">
            {f.question}
            <i className="fas fa-plus faq-icon" style={{ fontSize: '0.8rem' }} aria-hidden="true" />
          </div>
          <div className="faq-answer" style={{ whiteSpace: 'pre-line' }}>
            {f.answer}
          </div>
        </div>
      ))}
    </div>
  );
}

export function ContactFaqList({ faqs }: { faqs: FaqItem[] }) {
  if (faqs.length === 0) return null;

  return (
    <div style={{ maxWidth: 850, margin: '0 auto' }}>
      {faqs.map((f) => (
        <div
          key={f.id}
          className="faq-item"
          onClick={(e) => (e.currentTarget as HTMLElement).classList.toggle('active')}
          onKeyDown={(e) => {
            if (e.key === 'Enter' || e.key === ' ') {
              e.preventDefault();
              (e.currentTarget as HTMLElement).classList.toggle('active');
            }
          }}
          role="button"
          tabIndex={0}
        >
          <div className="faq-q">
            {f.question}
            <i
              className="fas fa-plus"
              style={{ fontSize: '0.8rem', color: 'var(--contact-accent, #d4af37)' }}
              aria-hidden="true"
            />
          </div>
          <div className="faq-a" style={{ whiteSpace: 'pre-line' }}>
            {f.answer}
          </div>
        </div>
      ))}
    </div>
  );
}
