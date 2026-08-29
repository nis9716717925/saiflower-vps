'use client';

import Link from 'next/link';
import { useEffect } from 'react';
import { usePathname } from 'next/navigation';

const SHORTCUTS = [
  { label: 'Shop Flowers', href: '/flowers', icon: 'local_florist', hint: 'Fresh bouquets' },
  { label: 'Birthday Gifts', href: '/occasion/birthday', icon: 'cake', hint: 'Celebrate today' },
  { label: 'Roses', href: '/flowers/roses', icon: 'favorite', hint: 'Classic romance' },
  { label: 'Same Day', href: '/collection/same-day-delivery', icon: 'bolt', hint: 'Delhi NCR fast' },
  { label: 'Celebrations', href: '/celebration-calendar', icon: 'calendar_month', hint: 'Plan ahead' },
  { label: 'Contact', href: '/contact', icon: 'chat', hint: "We're here" },
] as const;

const WA_LINK = 'https://wa.me/918802004527';

/** Branded 404 — matches PHP 404.php (n4-*), tightened for first-viewport clarity. */
export function NotFoundView() {
  const pathname = usePathname() || '/';

  useEffect(() => {
    const body = document.body;
    body.classList.add('n4-body');
    const prev = body.style.backgroundColor;
    body.style.backgroundColor = '#f6f2ea';
    return () => {
      body.classList.remove('n4-body');
      body.style.backgroundColor = prev;
    };
  }, []);

  return (
    <div className="n4-page">
      <section className="n4-hero" aria-labelledby="n4-title">
        <div className="n4-bloom n4-bloom--a" aria-hidden="true" />
        <div className="n4-bloom n4-bloom--b" aria-hidden="true" />
        <div className="n4-wrap">
          <p className="n4-brand">Sai Flower</p>
          <p className="n4-code">
            <span className="material-icons-outlined" style={{ fontSize: '1rem' }} aria-hidden="true">
              spa
            </span>{' '}
            Error 404
          </p>
          <h1 id="n4-title" className="n4-title">
            This bouquet took a wrong turn
          </h1>
          <p className="n4-lead">
            The page you&apos;re looking for isn&apos;t blooming here. Let&apos;s get you back to fresh
            flowers, celebrations, and same-day delivery.
          </p>

          <div className="n4-actions">
            <Link className="n4-btn n4-btn--primary" href="/">
              Go to homepage
            </Link>
            <Link className="n4-btn n4-btn--accent" href="/flowers">
              Shop flowers
            </Link>
            <a
              className="n4-btn n4-btn--ghost"
              href={WA_LINK}
              target="_blank"
              rel="noopener noreferrer"
            >
              WhatsApp us
            </a>
          </div>

          <p className="n4-path" title="Requested URL">
            Missing: {pathname}
          </p>

          <div className="n4-grid" aria-label="Popular destinations">
            {SHORTCUTS.map((s) => (
              <Link key={s.href} className="n4-card" href={s.href}>
                <span className="material-icons-outlined" aria-hidden="true">
                  {s.icon}
                </span>
                <span>
                  <strong>{s.label}</strong>
                  <span>{s.hint}</span>
                </span>
              </Link>
            ))}
          </div>

          <p className="n4-foot">
            Still stuck? <Link href="/contact">Contact support</Link> or browse the{' '}
            <Link href="/celebration-calendar">celebrations calendar</Link>.
          </p>
        </div>
      </section>
    </div>
  );
}
