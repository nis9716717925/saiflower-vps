import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

/**
 * Legacy detail URLs with ?slug= or ?id= → canonical prefixed paths (from .htaccess).
 * Also maps gallery-detail / event-detail bare paths.
 */
const SLUG_REDIRECTS: Record<string, string> = {
  '/flower-detail': '/flowers',
  '/cake-detail': '/cakes',
  '/gift-detail': '/gifts',
  '/event-detail': '/events',
};

const ID_REDIRECTS: Record<string, string> = {
  '/flower-detail': 'flower',
  '/cake-detail': 'cake',
  '/gift-detail': 'gift',
};

export function middleware(request: NextRequest) {
  const { pathname, searchParams } = request.nextUrl;
  const prefix = SLUG_REDIRECTS[pathname];
  const slug = searchParams.get('slug');
  const id = searchParams.get('id');

  if (prefix && slug) {
    const url = request.nextUrl.clone();
    url.pathname = `${prefix}/${slug}`;
    url.search = '';
    return NextResponse.redirect(url, 301);
  }

  const productType = ID_REDIRECTS[pathname];
  if (productType && id && /^\d+$/.test(id)) {
    const url = request.nextUrl.clone();
    url.pathname = `/api/legacy-product/${productType}/${id}`;
    url.search = '';
    return NextResponse.rewrite(url);
  }

  // Bare legacy detail pages without slug → listing
  if (pathname === '/event-detail' && !slug && !id) {
    const url = request.nextUrl.clone();
    url.pathname = '/events';
    url.search = '';
    return NextResponse.redirect(url, 302);
  }

  if (prefix && !slug && !id) {
    const url = request.nextUrl.clone();
    url.pathname = prefix;
    url.search = '';
    return NextResponse.redirect(url, 302);
  }

  const requestHeaders = new Headers(request.headers);
  requestHeaders.set('x-pathname', pathname);

  const response = NextResponse.next({
    request: { headers: requestHeaders },
  });
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
