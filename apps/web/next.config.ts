import type { NextConfig } from 'next';
import bundleAnalyzer from '@next/bundle-analyzer';
import path from 'node:path';

const withBundleAnalyzer = bundleAnalyzer({
  enabled: process.env.ANALYZE === 'true',
});

/**
 * URL parity helpers + rewrites for the Next.js storefront.
 * Admin UI is reverse-proxied to ADMIN_ORIGIN (default: production).
 */
const nextConfig: NextConfig = {
  reactStrictMode: true,
  poweredByHeader: false,
  // Allow atomic VPS deploys: build into .next-build while live .next keeps serving.
  distDir: process.env.NEXT_DIST_DIR || '.next',
  outputFileTracingRoot: path.join(__dirname, '../..'),
  transpilePackages: ['@saiflower/shared'],
  images: {
    formats: ['image/avif', 'image/webp'],
    deviceSizes: [640, 750, 828, 1080, 1200, 1920],
    imageSizes: [16, 32, 48, 64, 96, 128, 256, 384],
    minimumCacheTTL: 60 * 60 * 24 * 30,
    remotePatterns: [
      { protocol: 'https', hostname: 'saiflower.com' },
      { protocol: 'https', hostname: 'www.saiflower.com' },
      { protocol: 'https', hostname: 'images.unsplash.com' },
      { protocol: 'https', hostname: 'wcoeimnhzzjmftnqpzwo.supabase.co' },
    ],
    // Pre-converted WebP on disk + nginx/express cache; avoid sharp resize CPU on VPS.
    unoptimized: true,
  },
  compress: true,
  experimental: {
    optimizePackageImports: ['@saiflower/shared'],
  },
  async redirects() {
    return [
      { source: '/index.php', destination: '/', permanent: true },
      { source: '/occasions/:slug', destination: '/occasion/:slug', permanent: true },
      // Legacy singular flower-type paths from PHP nav
      { source: '/flower/:slug', destination: '/flowers/:slug', permanent: true },
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

    return [
      { source: '/api/v1/:path*', destination: `${apiBase}/api/v1/:path*` },
      { source: '/health', destination: `${apiBase}/health` },
      { source: '/uploads/:path*', destination: `${apiBase}/uploads/:path*` },
      { source: '/ajax_search.php', destination: `${apiBase}/api/v1/search` },
      { source: '/ajax/homepage-occasion.php', destination: '/ajax/homepage-occasion' },
      { source: '/sitemap.xml', destination: '/api/sitemap' },
      // /uploads/* is proxied to Express static (UPLOADS_DIR on disk)
    ];
  },
  async headers() {
    const longCache = 'public, max-age=2592000, stale-while-revalidate=86400';
    const weekCache = 'public, max-age=604800, stale-while-revalidate=86400';
    return [
      {
        source: '/assets/vendor/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=2592000, immutable' }],
      },
      {
        source: '/assets/:path*',
        headers: [{ key: 'Cache-Control', value: weekCache }],
      },
      {
        source: '/assets/images/:path*',
        headers: [{ key: 'Cache-Control', value: longCache }],
      },
      {
        source: '/celebrations/:path*',
        headers: [{ key: 'Cache-Control', value: longCache }],
      },
      {
        source: '/uploads/:path*',
        headers: [{ key: 'Cache-Control', value: longCache }],
      },
      {
        source: '/sw.js',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=0, must-revalidate' }],
      },
      {
        source: '/favicon.png',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=2592000' }],
      },
      {
        source: '/_next/static/:path*',
        headers: [{ key: 'Cache-Control', value: 'public, max-age=31536000, immutable' }],
      },
    ];
  },
};

export default withBundleAnalyzer(nextConfig);
