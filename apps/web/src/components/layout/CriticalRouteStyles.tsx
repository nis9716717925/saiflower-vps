import { headers } from 'next/headers';
import { CORE_CSS, pageCss } from '@/lib/route-css';

/** Blocking stylesheets in <head> so first paint matches the final layout. */
export async function CriticalRouteStyles() {
  const headerStore = await headers();
  const pathname = headerStore.get('x-pathname') || '/';

  const hrefs = new Set<string>([...CORE_CSS, ...pageCss(pathname)]);

  return (
    <>
      {Array.from(hrefs).map((href) => (
        <link key={href} rel="stylesheet" href={href} />
      ))}
    </>
  );
}
