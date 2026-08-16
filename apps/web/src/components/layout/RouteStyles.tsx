'use client';

import { usePathname } from 'next/navigation';
import { useMemo } from 'react';
import { CORE_CSS, pageCss } from '@/lib/route-css';

/** Client-side route CSS for navigations after first paint (head preloads critical sheets). */
export function RouteStyles() {
  const pathname = usePathname() || '/';
  const hrefs = useMemo(() => {
    const set = new Set<string>([...CORE_CSS, ...pageCss(pathname)]);
    return Array.from(set);
  }, [pathname]);

  return (
    <>
      {hrefs.map((href) => (
        <link key={href} rel="stylesheet" href={href} />
      ))}
    </>
  );
}
