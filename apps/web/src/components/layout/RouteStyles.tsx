'use client';

import { usePathname } from 'next/navigation';
import { useEffect, useRef } from 'react';
import { ensureStylesheetAsync, externalPageCss } from '@/lib/route-css';

/**
 * Loads any remaining non-bundled route CSS on client navigations.
 * Most legacy CSS is now in the Next.js bundle via bundled-pages.
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
      void ensureStylesheetAsync(href);
    }
  }, [pathname]);

  return null;
}
