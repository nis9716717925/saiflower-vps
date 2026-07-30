import type { MetadataRoute } from 'next';
import { ROBOTS_DISALLOW } from '@saiflower/shared';

/**
 * Staging / pre-cutover defaults to full Disallow so the Next stack is not indexed
 * while PHP remains production. Set ALLOW_INDEXING=true only when Next owns the public hostname.
 */
export default function robots(): MetadataRoute.Robots {
  const allowIndexing = process.env.ALLOW_INDEXING === 'true';

  if (!allowIndexing) {
    return {
      rules: {
        userAgent: '*',
        disallow: '/',
      },
    };
  }

  return {
    rules: {
      userAgent: '*',
      allow: '/',
      disallow: [...ROBOTS_DISALLOW],
    },
    sitemap: `${process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com'}/sitemap.xml`,
  };
}
