#!/usr/bin/env node
/**
 * One-shot VPS diagnostic: reports which layer is returning no products.
 *
 * Usage (from repo root):
 *   node tools/diagnose/vps-check.mjs
 */
import { PrismaClient } from '@prisma/client';

const API_PORT = process.env.PORT ?? '4000';
const API_PREFIX = process.env.API_PREFIX ?? '/api/v1';
const WEB_ORIGIN = process.env.DIAG_WEB_ORIGIN ?? 'http://127.0.0.1:3000';
const API_ORIGIN = process.env.DIAG_API_ORIGIN ?? `http://127.0.0.1:${API_PORT}`;

function maskUrl(value) {
  if (!value) return '(not set)';
  try {
    const url = new URL(value);
    if (url.password) url.password = '****';
    return url.toString();
  } catch {
    return '(unparseable)';
  }
}

async function probe(label, url) {
  try {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    const text = await res.text();
    let itemCount = null;
    try {
      const json = JSON.parse(text);
      if (Array.isArray(json?.data)) itemCount = json.data.length;
    } catch {
      // non-JSON response (likely HTML)
    }
    console.log(
      `${label}: HTTP ${res.status}` +
        (itemCount !== null ? ` items=${itemCount}` : '') +
        ` body="${text.slice(0, 160).replace(/\s+/g, ' ')}"`,
    );
  } catch (error) {
    console.log(`${label}: REQUEST FAILED (${error.message})`);
  }
}

async function main() {
  console.log('=== env ===');
  console.log('DATABASE_URL     :', maskUrl(process.env.DATABASE_URL));
  console.log('PORT             :', API_PORT);
  console.log('API_PREFIX       :', API_PREFIX);
  console.log('NEXT_PUBLIC_API_URL          :', process.env.NEXT_PUBLIC_API_URL ?? '(not set -> /api/v1)');
  console.log(
    'NEXT_PUBLIC_API_PROXY_TARGET :',
    process.env.NEXT_PUBLIC_API_PROXY_TARGET ?? '(not set -> http://localhost:4000)',
  );

  console.log('\n=== database ===');
  const prisma = new PrismaClient();
  try {
    const [flowers, activeFlowers, categories, settings, orders] = await Promise.all([
      prisma.flowers.count(),
      prisma.flowers.count({ where: { status: 1 } }),
      prisma.categories.count(),
      prisma.settings.count(),
      prisma.orders.count(),
    ]);
    console.log({ flowers, activeFlowers, categories, settings, orders });
    if (flowers === 0) console.log('VERDICT: database is empty -> run npm run db:load');
    else if (activeFlowers === 0) console.log('VERDICT: rows exist but no flower has status=1 -> API filters them out');
  } catch (error) {
    console.log('DB QUERY FAILED:', error.message);
    console.log('VERDICT: fix DATABASE_URL / Postgres credentials');
  } finally {
    await prisma.$disconnect();
  }

  console.log('\n=== express api (direct) ===');
  await probe('health        ', `${API_ORIGIN}/health`);
  await probe('products      ', `${API_ORIGIN}${API_PREFIX}/products?type=flower&limit=3`);
  await probe('categories    ', `${API_ORIGIN}${API_PREFIX}/categories`);

  console.log('\n=== next.js (what the browser hits) ===');
  await probe('web api proxy ', `${WEB_ORIGIN}${API_PREFIX}/products?type=flower&limit=3`);
  await probe('web homepage  ', WEB_ORIGIN);

  console.log('\nRead the first failing layer above: database -> express -> next.js.');
}

main().catch((error) => {
  console.error('Diagnostic crashed:', error);
  process.exit(1);
});
