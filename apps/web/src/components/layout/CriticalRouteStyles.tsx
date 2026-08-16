import { headers } from 'next/headers';
import { CORE_CSS, HOMEPAGE_CRITICAL_CSS, pageCss } from '@/lib/route-css';

/** Blocking stylesheets in <head> so first paint matches the final layout. */
export async function CriticalRouteStyles() {
  const headerStore = await headers();
  const pathname = headerStore.get('x-pathname') || '/';

  const hrefs = new Set<string>([...CORE_CSS]);
  if (pathname === '/') {
    for (const href of HOMEPAGE_CRITICAL_CSS) hrefs.add(href);
  } else {
    for (const href of pageCss(pathname)) hrefs.add(href);
  }

  return (
    <>
      {Array.from(hrefs).map((href) => (
        <link key={href} rel="stylesheet" href={href} />
      ))}
    </>
  );
}
