import type { MetadataRoute } from 'next';

/**
 * Matches legacy /var/www/html/robots.txt — public indexing with AI crawlers allowed.
 */
export default function robots(): MetadataRoute.Robots {
  const allowIndexing = process.env.ALLOW_INDEXING !== 'false';
  const siteUrl = process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com';

  if (!allowIndexing) {
    return {
      rules: { userAgent: '*', disallow: '/' },
    };
  }

  const sharedDisallow = ['/admin/', '/actions/', '/includes/', '/partials/', '/orders.sql', '/config.php'];

  return {
    rules: [
      { userAgent: '*', allow: '/', disallow: sharedDisallow },
      { userAgent: 'GPTBot', allow: '/', disallow: sharedDisallow },
      { userAgent: 'ChatGPT-User', allow: '/', disallow: sharedDisallow },
      { userAgent: 'Google-Extended', allow: '/', disallow: sharedDisallow },
      { userAgent: 'ClaudeBot', allow: '/', disallow: sharedDisallow },
      { userAgent: 'anthropic-ai', allow: '/', disallow: sharedDisallow },
      { userAgent: 'PerplexityBot', allow: '/', disallow: sharedDisallow },
      { userAgent: 'Applebot-Extended', allow: '/', disallow: sharedDisallow },
      { userAgent: 'CCBot', allow: '/', disallow: sharedDisallow },
    ],
    sitemap: `${siteUrl}/sitemap.xml`,
  };
}
