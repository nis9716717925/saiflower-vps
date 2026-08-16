import { headers } from 'next/headers';
import { externalPageCss } from '@/lib/route-css';

/** Blocking stylesheets in <head> for route-specific legacy CSS not in the Next.js bundle. */
export async function CriticalRouteStyles() {
  const headerStore = await headers();
  const pathname = headerStore.get('x-pathname') || '/';

  const hrefs = externalPageCss(pathname);

  return (
    <>
      {hrefs.map((href) => (
        <link key={href} rel="stylesheet" href={href} />
      ))}
    </>
  );
}
