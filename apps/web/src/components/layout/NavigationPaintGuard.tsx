'use client';

import { usePathname } from 'next/navigation';
import { useEffect, useRef } from 'react';
import { ensureStylesheetAsync, externalPageCss } from '@/lib/route-css';

/**
 * Soft-blocks page content on client navigations until any remaining
 * route stylesheets are attached (most CSS is now bundled).
 */
export function NavigationPaintGuard() {
  const pathname = usePathname() || '/';
  const first = useRef(true);

  useEffect(() => {
    if (first.current) {
      first.current = false;
      return;
    }

    const root = document.documentElement;
    let cancelled = false;
    root.classList.add('sf-nav-loading');

    const hrefs = externalPageCss(pathname);
    void Promise.all(hrefs.map((href) => ensureStylesheetAsync(href)))
      .catch(() => undefined)
      .finally(() => {
        if (cancelled) return;
        requestAnimationFrame(() => {
          requestAnimationFrame(() => {
            root.classList.remove('sf-nav-loading');
          });
        });
      });

    const safety = window.setTimeout(() => {
      root.classList.remove('sf-nav-loading');
    }, 1200);

    return () => {
      cancelled = true;
      window.clearTimeout(safety);
      root.classList.remove('sf-nav-loading');
    };
  }, [pathname]);

  return null;
}
