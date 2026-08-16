/**
 * URL helpers mirroring production `.htaccess` + PHP url_helper behavior.
 * Preserve these paths for SEO parity.
 */

import type { CollectionKind, ProductType } from './types';

const SITE_ORIGIN = 'https://saiflower.com';

export function absoluteUrl(path: string): string {
  if (/^https?:\/\//i.test(path)) return path;
  const normalized = path.startsWith('/') ? path : `/${path}`;
  return `${SITE_ORIGIN}${normalized}`;
}

export function productPath(type: ProductType, slug: string, id?: number): string {
  if (type === 'addon') return '';
  const prefix =
    type === 'flower' ? 'flowers' : type === 'cake' ? 'cakes' : type === 'gift' ? 'gifts' : '';
  if (slug) return `/${prefix}/${encodeURIComponent(slug)}`;
  if (id != null) {
    // Legacy fallback — production prefers slug URLs with 301 from ?slug=
    return `/${type}-detail?id=${id}`;
  }
  return `/${prefix}`;
}

export function collectionPath(kind: CollectionKind, slug: string): string {
  if (kind === 'flower-type') return `/flowers/${encodeURIComponent(slug)}`;
  return `/${kind}/${encodeURIComponent(slug)}`;
}

export function blogPath(slug: string): string {
  return `/blog/${encodeURIComponent(slug)}`;
}

export function eventPath(slug: string): string {
  return `/events/${encodeURIComponent(slug)}`;
}

export function personalizedPath(slug?: string): string {
  return slug ? `/personalized/${encodeURIComponent(slug)}` : '/personalized';
}

export function celebrationCalendarPath(): string {
  return '/celebration-calendar';
}

/** Permanent redirects that must exist in Next.js (from .htaccess). */
export const PERMANENT_REDIRECTS: Array<{ source: string; destination: string }> = [
  { source: '/index.php', destination: '/' },
  { source: '/occasions/:slug', destination: '/occasion/:slug' },
  { source: '/celebrations-calendar', destination: '/celebration-calendar' },
  { source: '/personalised', destination: '/personalized' },
  { source: '/personalised/:slug', destination: '/personalized/:slug' },
  { source: '/flower-detail', destination: '/flowers/:slug' }, // query handled in middleware
  { source: '/cake-detail', destination: '/cakes/:slug' },
  { source: '/gift-detail', destination: '/gifts/:slug' },
  { source: '/event-detail', destination: '/events/:slug' },
];

/**
 * Next.js rewrite rules equivalent to Apache RewriteRule targets.
 * Used by apps/web/next.config.ts
 */
export const NEXT_REWRITES: Array<{ source: string; destination: string }> = [
  { source: '/blog/:slug', destination: '/blog/:slug' },
  { source: '/celebration-calendar', destination: '/celebration-calendar' },
  { source: '/personalized', destination: '/personalized' },
  { source: '/personalized/:slug', destination: '/personalized/:slug' },
  { source: '/relation/:slug', destination: '/collection?kind=relation&slug=:slug' },
  { source: '/occasion/:slug', destination: '/collection?kind=occasion&slug=:slug' },
  { source: '/collection/:slug', destination: '/collection?kind=collection&slug=:slug' },
  { source: '/flowers/:slug', destination: '/flowers/:slug' },
  { source: '/cakes/:slug', destination: '/cakes/:slug' },
  { source: '/gifts/:slug', destination: '/gifts/:slug' },
  { source: '/events/:slug', destination: '/events/:slug' },
];

/** Paths disallowed in robots.txt (must stay blocked). */
export const ROBOTS_DISALLOW = ['/admin/', '/actions/', '/includes/', '/partials/', '/config.php'] as const;
