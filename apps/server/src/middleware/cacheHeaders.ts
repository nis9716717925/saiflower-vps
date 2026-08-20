import type { RequestHandler } from 'express';

/** Public catalog/content responses — short CDN/browser cache + SWR. */
export function publicCatalogCache(maxAgeSeconds = 120): RequestHandler {
  return (_req, res, next) => {
    res.setHeader(
      'Cache-Control',
      `public, max-age=${maxAgeSeconds}, stale-while-revalidate=${maxAgeSeconds * 4}`,
    );
    next();
  };
}
