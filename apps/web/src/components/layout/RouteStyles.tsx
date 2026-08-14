'use client';

import { usePathname } from 'next/navigation';
import { useMemo } from 'react';

/** Always needed for chrome (catnav / mobile / search). site-header.css is bundled via layout import. */
const CORE_CSS = [
  '/assets/css/style.css',
  '/assets/css/catnav.css',
  '/assets/css/mobile-nav.css?v=3',
  '/assets/css/search-suggest.css',
  // Must stay last: overrides the opaque header/catnav chrome with the glass pane.
  '/assets/css/header-glass.css?v=2',
] as const;

function pageCss(pathname: string): string[] {
  if (pathname === '/') {
    return [
      '/assets/css/homepage-premium.css?v=4',
      '/assets/css/homepage-firstview.css',
      '/assets/css/homepage-luxe.css?v=5',
      '/assets/css/homepage-mobile.css?v=7',
      '/assets/css/celebrations-calendar.css',
    ];
  }

  if (
    pathname.startsWith('/collection/') ||
    pathname.startsWith('/occasion/') ||
    pathname.startsWith('/relation/') ||
    pathname.startsWith('/personalized')
  ) {
    return ['/assets/css/collection-landing.css?v=6', '/assets/css/category-page.css'];
  }

  // Location landings (canonical + catch-all CMS slugs)
  if (pathname.startsWith('/flower-delivery-in-') || pathname.startsWith('/flower-delivery')) {
    return ['/assets/css/location-landing.css?v=2', '/assets/css/collection-landing.css?v=6'];
  }

  if (pathname === '/flowers' || pathname === '/cakes' || pathname === '/gifts') {
    return ['/assets/css/shop-commerce.css?v=3', '/assets/css/shop-luxe.css', '/assets/css/category-page.css'];
  }

  if (pathname === '/search-results' || pathname.startsWith('/search-results')) {
    return ['/assets/css/shop-commerce.css?v=3'];
  }

  if (
    pathname.startsWith('/flowers/') ||
    pathname.startsWith('/cakes/') ||
    pathname.startsWith('/gifts/')
  ) {
    // Taxonomy landing under /flowers/:slug uses collection CSS; PDP also needs product CSS.
    return [
      '/assets/css/collection-landing.css?v=6',
      '/assets/css/category-page.css',
      '/assets/css/product-detail-premium.css?v=2',
      '/assets/css/shop-luxe.css',
    ];
  }

  if (pathname.startsWith('/events') || pathname.startsWith('/gallery')) {
    return ['/assets/css/category-page.css'];
  }

  if (pathname.startsWith('/celebration-calendar')) {
    return [
      '/assets/css/celebration-calendar-page.css',
      '/assets/css/celebrations-calendar.css',
    ];
  }

  if (pathname.startsWith('/blog')) return ['/assets/css/blog-page.css'];
  if (pathname.startsWith('/about')) return ['/assets/css/about-page.css'];
  if (pathname.startsWith('/contact')) return ['/assets/css/contact-page.css'];
  if (pathname.startsWith('/faq')) return ['/assets/css/faq-page.css'];

  if (
    pathname === '/cart' ||
    pathname.startsWith('/checkout') ||
    pathname.startsWith('/login') ||
    pathname.startsWith('/register')
  ) {
    return ['/assets/css/checkout-commerce.css?v=2'];
  }

  return [];
}

/** Load only CSS needed for the current route (cuts unused homepage/shop CSS on other pages). */
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
