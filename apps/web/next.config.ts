import type { NextConfig } from 'next';

/**
 * URL parity with production `.htaccess`.
 * Do not change destinations without adding 301 redirects — SEO critical.
 */
const nextConfig: NextConfig = {
  reactStrictMode: true,
  poweredByHeader: false,
  transpilePackages: ['@saiflower/shared'],
  images: {
    // Phase 6+: optimize while keeping same visual dimensions / URLs where possible
    remotePatterns: [
      { protocol: 'https', hostname: 'saiflower.com' },
      { protocol: 'https', hostname: 'www.saiflower.com' },
    ],
    unoptimized: true, // keep pixel parity until explicit image-pipeline pass
  },
  async redirects() {
    return [
      { source: '/index.php', destination: '/', permanent: true },
      { source: '/occasions/:slug', destination: '/:slug', permanent: true },
      { source: '/celebrations-calendar', destination: '/celebration-calendar', permanent: true },
      { source: '/personalised', destination: '/personalized', permanent: true },
      { source: '/personalised/:slug', destination: '/personalized/:slug', permanent: true },
      // Extensionless: Next serves App Router paths; strip .php for bookmarks
      { source: '/:path*.php', destination: '/:path*', permanent: true },
    ];
  },
  async rewrites() {
    const apiBase = process.env.NEXT_PUBLIC_API_PROXY_TARGET ?? 'http://localhost:4000';
    const mediaBase = process.env.NEXT_PUBLIC_MEDIA_ORIGIN ?? 'https://saiflower.com';

    return [
      // Proxy REST API during local/dev (Phase 7 will harden)
      { source: '/api/v1/:path*', destination: `${apiBase}/api/v1/:path*` },
      { source: '/health', destination: `${apiBase}/health` },
      // Legacy search-suggest.js still calls /ajax_search.php
      { source: '/ajax_search.php', destination: `${apiBase}/api/v1/search` },
      // Keep /uploads/* working without breaking existing image URLs
      { source: '/uploads/:path*', destination: `${mediaBase}/uploads/:path*` },
    ];
  },
};

export default nextConfig;
