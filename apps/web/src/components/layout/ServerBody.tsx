import { headers } from 'next/headers';
import type { ReactNode } from 'react';
import { bodyBackgroundForPath, bodyClassForPath } from '@/lib/route-css';

/** Apply route body class/background on the server to avoid post-hydration flash. */
export async function ServerBody({ children }: { children: ReactNode }) {
  const headerStore = await headers();
  const pathname = headerStore.get('x-pathname') || '/';
  const className = bodyClassForPath(pathname);
  const background = bodyBackgroundForPath(pathname);

  return (
    <body className={className} style={background ? { backgroundColor: background } : undefined}>
      {children}
    </body>
  );
}
