import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

/**
 * Legacy detail URLs with ?slug= → canonical prefixed paths (from .htaccess).
 * Also maps gallery-detail / event-detail bare paths.
 */
const SLUG_REDIRECTS: Record<string, string> = {
  '/flower-detail': '/flowers',
  '/cake-detail': '/cakes',
  '/gift-detail': '/gifts',
  '/event-detail': '/events',
};

export function middleware(request: NextRequest) {
  const { pathname, searchParams } = request.nextUrl;
  const prefix = SLUG_REDIRECTS[pathname];
  const slug = searchParams.get('slug');

  if (prefix && slug) {
    const url = request.nextUrl.clone();
    url.pathname = `${prefix}/${slug}`;
    url.search = '';
    return NextResponse.redirect(url, 301);
  }

  // Bare legacy detail pages without slug → listing
  if (pathname === '/event-detail' && !slug) {
    const url = request.nextUrl.clone();
    url.pathname = '/events';
    url.search = '';
    return NextResponse.redirect(url, 302);
  }

  const response = NextResponse.next();
  if (process.env.ALLOW_INDEXING !== 'true') {
    response.headers.set('X-Robots-Tag', 'noindex, nofollow');
  }
  return response;
}

export const config = {
  matcher: [
    '/flower-detail',
    '/cake-detail',
    '/gift-detail',
    '/event-detail',
    '/((?!_next/static|_next/image|favicon.png|assets/|uploads/).*)',
  ],
};
