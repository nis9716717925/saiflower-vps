#!/usr/bin/env node
/**
 * Verify Next.js static chunks exist after build (prevents 400 errors on live site).
 * Usage: node tools/deploy/verify-next-static.mjs
 */
import { existsSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const staticRoot = join(process.cwd(), 'apps/web/.next/static/chunks');

if (!existsSync(staticRoot)) {
  console.error('FAIL: apps/web/.next/static/chunks not found — run npm run build:web first');
  process.exit(1);
}

const appDir = join(staticRoot, 'app');
const layoutChunks = existsSync(appDir)
  ? readdirSync(appDir).filter((name) => name.startsWith('layout-') && name.endsWith('.js'))
  : [];

if (layoutChunks.length === 0) {
  console.error('FAIL: no layout-*.js chunk in apps/web/.next/static/chunks/app');
  process.exit(1);
}

const topLevel = readdirSync(staticRoot).filter((name) => name.endsWith('.js'));
if (topLevel.length < 5) {
  console.error('FAIL: too few top-level JS chunks in .next/static/chunks');
  process.exit(1);
}

let totalBytes = 0;
for (const name of topLevel) {
  totalBytes += statSync(join(staticRoot, name)).size;
}

console.log(`OK: ${layoutChunks.length} layout chunk(s), ${topLevel.length} top-level chunks (${totalBytes} bytes)`);
