#!/usr/bin/env node
/**
 * Verify Next.js static chunks exist after build (prevents 400 errors on live site).
 *
 * Usage:
 *   node tools/deploy/verify-next-static.mjs
 *   node tools/deploy/verify-next-static.mjs --dir=apps/web/.next-build
 *   NEXT_DIST_DIR=.next-build node tools/deploy/verify-next-static.mjs
 */
import { existsSync, readFileSync, readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

function argValue(prefix) {
  const hit = process.argv.find((a) => a.startsWith(prefix));
  return hit ? hit.slice(prefix.length) : null;
}

const distArg = argValue('--dir=') || process.env.NEXT_DIST_DIR;
const distRel = distArg
  ? distArg.includes('/') || distArg.includes('\\')
    ? distArg
    : join('apps/web', distArg)
  : 'apps/web/.next';

const distRoot = join(process.cwd(), distRel);
const staticRoot = join(distRoot, 'static/chunks');
const buildIdPath = join(distRoot, 'BUILD_ID');

if (!existsSync(staticRoot)) {
  console.error(`FAIL: ${distRel}/static/chunks not found — run a web build first`);
  process.exit(1);
}

if (!existsSync(buildIdPath)) {
  console.error(`FAIL: ${distRel}/BUILD_ID missing`);
  process.exit(1);
}

const buildId = readFileSync(buildIdPath, 'utf8').trim();
if (!buildId) {
  console.error('FAIL: BUILD_ID is empty');
  process.exit(1);
}

const appDir = join(staticRoot, 'app');
const layoutChunks = existsSync(appDir)
  ? readdirSync(appDir).filter((name) => name.startsWith('layout-') && name.endsWith('.js'))
  : [];

if (layoutChunks.length === 0) {
  console.error(`FAIL: no layout-*.js chunk in ${distRel}/static/chunks/app`);
  process.exit(1);
}

const topLevel = readdirSync(staticRoot).filter((name) => name.endsWith('.js'));
if (topLevel.length < 5) {
  console.error('FAIL: too few top-level JS chunks in static/chunks');
  process.exit(1);
}

/** Collect hashed chunk paths referenced by Next manifests (best-effort). */
const requiredFiles = new Set();
for (const name of layoutChunks) {
  requiredFiles.add(join(appDir, name));
}

const manifestCandidates = [
  join(distRoot, 'app-build-manifest.json'),
  join(distRoot, 'build-manifest.json'),
  join(distRoot, 'static', buildId, '_buildManifest.js'),
];

for (const manifestPath of manifestCandidates) {
  if (!existsSync(manifestPath)) continue;
  const text = readFileSync(manifestPath, 'utf8');
  const matches = text.matchAll(/static\/chunks\/[a-zA-Z0-9._/-]+\.js/g);
  for (const match of matches) {
    requiredFiles.add(join(distRoot, match[0]));
  }
}

const missing = [];
for (const filePath of requiredFiles) {
  if (!existsSync(filePath) || statSync(filePath).size < 20) {
    missing.push(filePath.replace(process.cwd() + '\\', '').replace(process.cwd() + '/', ''));
  }
}

if (missing.length > 0) {
  console.error('FAIL: missing or empty chunk files:');
  for (const file of missing.slice(0, 20)) console.error(`  - ${file}`);
  if (missing.length > 20) console.error(`  … and ${missing.length - 20} more`);
  process.exit(1);
}

let totalBytes = 0;
for (const name of topLevel) {
  totalBytes += statSync(join(staticRoot, name)).size;
}

console.log(
  `OK: buildId=${buildId} layout=${layoutChunks[0]} topLevel=${topLevel.length} checked=${requiredFiles.size} bytes=${totalBytes} dir=${distRel}`,
);
