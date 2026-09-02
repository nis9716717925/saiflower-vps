/** Shared route → stylesheet mapping for RouteStyles and server head preloads. */

export const CORE_CSS = [
  '/assets/css/style.css',
  '/assets/css/catnav.css',
  '/assets/css/mobile-nav.css?v=3',
  '/assets/css/search-suggest.css',
  '/assets/css/header-glass.css?v=2',
] as const;

/** Legacy sheets shipped inside the Next.js CSS bundle (not loaded via <link>). */
export const BUNDLED_CSS_FILES = new Set([
  'style.css',
  'catnav.css',
  'mobile-nav.css',
  'search-suggest.css',
  'header-glass.css',
  'homepage-premium.css',
  'homepage-firstview.css',
  'homepage-luxe.css',
  'homepage-mobile.css',
  'celebrations-calendar.css',
  'celebration-calendar-page.css',
  'collection-landing.css',
  'category-page.css',
  'shop-commerce.css',
  'shop-luxe.css',
  'product-detail-premium.css',
  'location-landing.css',
  'blog-page.css',
  'about-page.css',
  'contact-page.css',
  'faq-page.css',
  'checkout-commerce.css',
]);

export function cssFileName(href: string): string {
  return href.split('/').pop()?.split('?')[0] ?? href;
}

export function isBundledCss(href: string): boolean {
  return BUNDLED_CSS_FILES.has(cssFileName(href));
}

/** Stylesheets that still need a blocking <link> (route-specific, not in the JS bundle). */
export function externalPageCss(pathname: string): string[] {
  const seen = new Set<string>();
  const hrefs: string[] = [];

  for (const href of [...CORE_CSS, ...pageCss(pathname)]) {
    if (isBundledCss(href)) continue;
    const key = cssFileName(href);
    if (seen.has(key)) continue;
    seen.add(key);
    hrefs.push(href);
  }

  return hrefs;
}

export function ensureStylesheet(href: string): void {
  void ensureStylesheetAsync(href);
}

/** Attach a stylesheet and resolve when it is loaded (or already present). */
export function ensureStylesheetAsync(href: string): Promise<void> {
  if (typeof document === 'undefined') return Promise.resolve();
  const path = cssFileName(href);
  const existing = document.querySelector<HTMLLinkElement>(
    `link[rel="stylesheet"][href*="${path}"]`,
  );
  if (existing) {
    try {
      if (existing.sheet) return Promise.resolve();
    } catch {
      /* cross-origin sheet access can throw — treat as loaded */
      return Promise.resolve();
    }
    return new Promise((resolve) => {
      existing.addEventListener('load', () => resolve(), { once: true });
      existing.addEventListener('error', () => resolve(), { once: true });
    });
  }

  return new Promise((resolve) => {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = href;
    link.addEventListener('load', () => resolve(), { once: true });
    link.addEventListener('error', () => resolve(), { once: true });
    document.head.appendChild(link);
  });
}

/** Homepage sheets that must paint before first view (avoid square→circle icon flash). */
export const HOMEPAGE_CRITICAL_CSS = [
  '/assets/css/homepage-premium.css?v=5',
  '/assets/css/homepage-firstview.css?v=2',
  '/assets/css/homepage-luxe.css?v=7',
] as const;

const RESERVED_ROOT_SEGMENTS = new Set([
  'about',
  'admin',
  'ajax',
  'api',
  'assets',
  'blog',
  'cakes',
  'cart',
  'celebration-calendar',
  'checkout',
  'collection',
  'contact',
  'custom-pages',
  'delivery-policy',
  'events',
  'faq',
  'flower-delivery-in-delhi',
  'flowers',
  'gallery',
  'gallery-by-tag',
  'gallery-detail',
  'gifts',
  'grievnce',
  'health',
  'legal',
  'login',
  'logout',
  'occasion',
  'personalized',
  'privacy',
  'profile',
  'refund-policy',
  'register',
  'relation',
  'search-results',
  'sitemap',
  'tag',
  'terms',
  'uploads',
  'verify',
  'wishlist',
]);

const LANDING_PAGE_CSS = ['/assets/css/collection-landing.css?v=6', '/assets/css/category-page.css'] as const;

export function pageCss(pathname: string): string[] {
  if (pathname === '/') {
    return [
      ...HOMEPAGE_CRITICAL_CSS,
      '/assets/css/homepage-mobile.css?v=8',
      '/assets/css/celebrations-calendar.css',
    ];
  }

  if (pathname === '/custom-pages') {
    return ['/assets/css/category-page.css'];
  }

  if (
    pathname.startsWith('/collection/') ||
    pathname.startsWith('/occasion/') ||
    pathname.startsWith('/relation/') ||
    pathname.startsWith('/personalized')
  ) {
    return [...LANDING_PAGE_CSS];
  }

  if (pathname.startsWith('/flower-delivery-in-') || pathname.startsWith('/flower-delivery')) {
    return ['/assets/css/location-landing.css?v=2', ...LANDING_PAGE_CSS];
  }

  if (pathname === '/flowers' || pathname === '/cakes' || pathname === '/gifts') {
    return ['/assets/css/shop-commerce.css?v=6', '/assets/css/shop-luxe.css', '/assets/css/category-page.css'];
  }

  if (pathname === '/search-results' || pathname.startsWith('/search-results')) {
    return ['/assets/css/shop-commerce.css?v=6'];
  }

  if (
    pathname.startsWith('/flowers/') ||
    pathname.startsWith('/cakes/') ||
    pathname.startsWith('/gifts/')
  ) {
    return [
      ...LANDING_PAGE_CSS,
      '/assets/css/product-detail-premium.css?v=2',
      '/assets/css/shop-luxe.css',
    ];
  }

  if (pathname.startsWith('/events') || pathname.startsWith('/gallery')) {
    return ['/assets/css/category-page.css'];
  }

  if (pathname.startsWith('/celebration-calendar')) {
    return ['/assets/css/celebration-calendar-page.css', '/assets/css/celebrations-calendar.css'];
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
    return ['/assets/css/checkout-commerce.css?v=6'];
  }

  // CMS + location catch-all pages at /:slug
  const root = pathname.replace(/^\/+|\/+$/g, '').split('/')[0];
  if (root && !pathname.includes('/', 1) && !RESERVED_ROOT_SEGMENTS.has(root)) {
    return [...LANDING_PAGE_CSS];
  }

  return [];
}

export function bodyClassForPath(pathname: string): string {
  const classes = ['text-gray-800'];
  if (pathname === '/') classes.push('homepage-premium');
  if (
    pathname.startsWith('/collection/') ||
    pathname.startsWith('/occasion/') ||
    pathname.startsWith('/relation/') ||
    pathname.startsWith('/flowers/') ||
    pathname.startsWith('/personalized') ||
    pathname.startsWith('/celebration-calendar') ||
    pathname.startsWith('/flower-delivery-in-')
  ) {
    classes.push('cl-page-body');
  }
  if (pathname === '/flowers' || pathname === '/cakes' || pathname === '/gifts') {
    classes.push('shop-listing-body');
  }
  const root = pathname.replace(/^\/+|\/+$/g, '').split('/')[0];
  if (root && !pathname.includes('/', 1) && !RESERVED_ROOT_SEGMENTS.has(root)) {
    classes.push('cl-page-body');
  }
  if (
    pathname === '/cart' ||
    pathname.startsWith('/checkout') ||
    pathname.startsWith('/login') ||
    pathname.startsWith('/register')
  ) {
    classes.push('checkout-funnel-body');
  }
  return classes.join(' ');
}

export function bodyBackgroundForPath(pathname: string): string | undefined {
  const root = pathname.replace(/^\/+|\/+$/g, '').split('/')[0];
  if (
    pathname === '/cart' ||
    pathname.startsWith('/checkout') ||
    pathname.startsWith('/login') ||
    pathname.startsWith('/register')
  ) {
    return '#f3f5f4';
  }
  if (pathname === '/') return '#fdfcf9';
  if (
    pathname.startsWith('/collection/') ||
    pathname.startsWith('/occasion/') ||
    pathname.startsWith('/relation/') ||
    pathname.startsWith('/flowers/') ||
    pathname.startsWith('/personalized') ||
    pathname.startsWith('/celebration-calendar') ||
    pathname.startsWith('/flower-delivery-in-')
  ) {
    return '#f6f2ea';
  }
  if (pathname === '/flowers' || pathname === '/cakes' || pathname === '/gifts') {
    return '#f7f4ee';
  }
  if (root && !pathname.includes('/', 1) && !RESERVED_ROOT_SEGMENTS.has(root)) {
    return '#f6f2ea';
  }
  return '#fdfcf9';
}

/** Inline script: set body class/background before React hydrates (no server headers()). */
export function routeBodyBootScript(): string {
  const reserved = JSON.stringify([...RESERVED_ROOT_SEGMENTS]);
  return `(function(){var p=location.pathname,r=${reserved},c=['text-gray-800'],root=p.replace(/^\\/+|\\/+$/g,'').split('/')[0],b='#fdfcf9',seg=p.indexOf('/',1)>0;if(p==='/')c.push('homepage-premium');if(/^\\/(collection|occasion|relation)\\//.test(p)||p.indexOf('/flowers/')===0||p.indexOf('/personalized')===0||p.indexOf('/celebration-calendar')===0||p.indexOf('/flower-delivery-in-')===0)c.push('cl-page-body');if(p==='/flowers'||p==='/cakes'||p==='/gifts')c.push('shop-listing-body');if(root&&!seg&&r.indexOf(root)<0)c.push('cl-page-body');if(p==='/cart'||p.indexOf('/checkout')===0||p.indexOf('/login')===0||p.indexOf('/register')===0){c.push('checkout-funnel-body');b='#f3f5f4';}else if(p==='/'){b='#fdfcf9';}else if(/^\\/(collection|occasion|relation)\\//.test(p)||p.indexOf('/flowers/')===0||p.indexOf('/personalized')===0||p.indexOf('/celebration-calendar')===0||p.indexOf('/flower-delivery-in-')===0){b='#f6f2ea';}else if(p==='/flowers'||p==='/cakes'||p==='/gifts'){b='#f7f4ee';}else if(root&&!seg&&r.indexOf(root)<0){b='#f6f2ea';}document.body.className=c.join(' ');document.body.style.backgroundColor=b;})();`;
}
