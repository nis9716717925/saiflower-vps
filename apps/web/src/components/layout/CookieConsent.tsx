'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';
import {
  getCookieConsent,
  setCookieConsent,
  type CookieConsentChoice,
} from '@/lib/cookie-consent';

export function CookieConsent() {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const existing = getCookieConsent();
    if (!existing) {
      document.documentElement.classList.add('sf-cookie-pending');
      setVisible(true);
    }
  }, []);

  function choose(choice: CookieConsentChoice) {
    setCookieConsent(choice);
    setVisible(false);
  }

  if (!visible) return null;

  return (
    <div
      className="sf-cookie-banner"
      role="dialog"
      aria-live="polite"
      aria-label="Cookie consent"
    >
      <div className="sf-cookie-banner__inner">
        <div className="sf-cookie-banner__text">
          <p className="sf-cookie-banner__title">
            <i className="fas fa-cookie-bite" aria-hidden="true" /> We use cookies
          </p>
          <p className="sf-cookie-banner__desc sf-cookie-banner__desc--desktop">
            Sai Flower uses cookies to keep your cart and sign-in working, remember your
            preferences, and improve our website. See our{' '}
            <Link href="/privacy">Privacy Policy</Link> for details.
          </p>
          <p className="sf-cookie-banner__desc sf-cookie-banner__desc--mobile">
            For cart, sign-in &amp; preferences.{' '}
            <Link href="/privacy">Privacy</Link>
          </p>
        </div>
        <div className="sf-cookie-banner__actions">
          <button
            type="button"
            className="sf-cookie-banner__btn sf-cookie-banner__btn--ghost"
            onClick={() => choose('essential')}
          >
            <span className="sf-cookie-banner__btn-label sf-cookie-banner__btn-label--desktop">Essential only</span>
            <span className="sf-cookie-banner__btn-label sf-cookie-banner__btn-label--mobile">Essential</span>
          </button>
          <button
            type="button"
            className="sf-cookie-banner__btn sf-cookie-banner__btn--primary"
            onClick={() => choose('all')}
          >
            Accept all
          </button>
        </div>
      </div>
    </div>
  );
}
