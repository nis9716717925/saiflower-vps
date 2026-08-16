'use client';

import { usePathname } from 'next/navigation';
import { useEffect, useRef } from 'react';
import { ensureStylesheet, externalPageCss } from '@/lib/route-css';

/**
 * Loads route-specific legacy CSS on client navigations only.
 * Initial paint uses bundled CSS + CriticalRouteStyles; this renders no <link> tags in HTML.
 */
export function RouteStyles() {
  const pathname = usePathname() || '/';
  const isFirstRender = useRef(true);

  useEffect(() => {
    if (isFirstRender.current) {
      isFirstRender.current = false;
      return;
    }

    for (const href of externalPageCss(pathname)) {
      ensureStylesheet(href);
    }
  }, [pathname]);

  return null;
}
