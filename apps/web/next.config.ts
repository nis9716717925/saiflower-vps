import type { NextConfig } from 'next';

/**
 * URL parity helpers + rewrites for the Next.js storefront.
 * Admin UI is reverse-proxied to ADMIN_ORIGIN (default: production).
 */
const nextConfig: NextConfig = {
  reactStrictMode: true,
  poweredByHeader: false,
  transpilePackages: ['@saiflower/shared'],
  images: {
    remotePatterns: [
      { protocol: 'https', hostname: 'saiflower.com' },
      { protocol: 'https', hostname: 'www.saiflower.com' },
    ],
    unoptimized: true,
  },
  async redirects() {
    return [
      { source: '/index.php', destination: '/', permanent: true },
      { source: '/occasions/:slug', destination: '/:slug', permanent: true },
      { source: '/celebrations-calendar', destination: '/celebration-calendar', permanent: true },
      { source: '/personalised', destination: '/personalized', permanent: true },
      { source: '/personalised/:slug', destination: '/personalized/:slug', permanent: true },
      // PHP typo path is canonical
      { source: '/grievance', destination: '/grievnce', permanent: true },
      { source: '/custompages', destination: '/custom-pages', permanent: true },
      // Pretty gallery id → PHP query URL
      { source: '/gallery/:id(\\d+)', destination: '/gallery-detail?id=:id', permanent: false },
      // Do NOT strip .php under /admin or /ajax
      {
        source: '/:path((?!ajax/|admin/).*)\\.php',
        destination: '/:path',
        permanent: true,
      },
    ];
  },
  async rewrites() {
    const apiBase = process.env.NEXT_PUBLIC_API_PROXY_TARGET ?? 'http://localhost:4000';
    const mediaBase = process.env.NEXT_PUBLIC_MEDIA_ORIGIN ?? 'https://saiflower.com';

    return [
      { source: '/api/v1/:path*', destination: `${apiBase}/api/v1/:path*` },
      { source: '/health', destination: `${apiBase}/health` },
      { source: '/ajax_search.php', destination: `${apiBase}/api/v1/search` },
      { source: '/ajax/homepage-occasion.php', destination: '/ajax/homepage-occasion' },
      { source: '/sitemap.xml', destination: '/api/sitemap' },
      { source: '/uploads/:path*', destination: `${mediaBase}/uploads/:path*` },
    ];
  },
  async headers() {
    return [
      {
        source: '/assets/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=604800, stale-while-revalidate=86400' }],
      },
      {
        source: '/celebrations/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=2592000, stale-while-revalidate=86400' }],
      },
      {
        source: '/favicon.png',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=2592000' }],
      },
    ];
  },
};

export default nextConfig;
