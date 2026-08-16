'use client';

import { usePathname } from 'next/navigation';
import { useEffect } from 'react';
import { bodyBackgroundForPath, bodyClassForPath } from '@/lib/route-css';

/** Sync body class/background on client navigations (server sets initial paint). */
export function BodyClass() {
  const pathname = usePathname() || '/';

  useEffect(() => {
    const body = document.body;
    const classes = bodyClassForPath(pathname).split(/\s+/).filter(Boolean);
    const known = new Set([
      'text-gray-800',
      'homepage-premium',
      'cl-page-body',
      'shop-listing-body',
      'checkout-funnel-body',
    ]);

    for (const name of known) {
      body.classList.toggle(name, classes.includes(name));
    }

    const background = bodyBackgroundForPath(pathname);
    body.style.backgroundColor = background ?? '#fdfcf9';
  }, [pathname]);

  return null;
}
