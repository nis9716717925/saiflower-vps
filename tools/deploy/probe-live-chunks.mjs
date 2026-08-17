#!/usr/bin/env node
/**
 * Probe a live origin for the homepage + its critical /_next/static chunks.
 * Fails non-zero if the layout/page chunks are not HTTP 200 JS.
 *
 * Usage:
 *   node tools/deploy/probe-live-chunks.mjs
 *   node tools/deploy/probe-live-chunks.mjs --origin=https://saiflower.com
 *   node tools/deploy/probe-live-chunks.mjs --origin=http://127.0.0.1:3000
 */
const originArg = process.argv.find((a) => a.startsWith('--origin='));
const origin = (originArg ? originArg.slice('--origin='.length) : process.env.PROBE_ORIGIN || 'https://saiflower.com').replace(
  /\/$/,
  '',
);

async function headOrGet(url) {
  const res = await fetch(url, {
    method: 'GET',
    redirect: 'follow',
    headers: { Accept: '*/*', 'User-Agent': 'saiflower-chunk-probe/1.0' },
  });
  const contentType = res.headers.get('content-type') || '';
  const buf = Buffer.from(await res.arrayBuffer());
  return { status: res.status, contentType, bytes: buf.length, sample: buf.subarray(0, 80).toString('utf8') };
}

function extractChunkPaths(html) {
  const paths = new Set();
  const re = /\/_next\/static\/chunks\/[a-zA-Z0-9._/-]+\.js/g;
  for (const match of html.matchAll(re)) paths.add(match[0]);
  return [...paths];
}

function looksLikeJs(contentType, sample) {
  if (/javascript|ecmascript/i.test(contentType)) return true;
  if (sample.startsWith('<!DOCTYPE') || sample.startsWith('<html') || sample.includes('400: Bad Request')) return false;
  // Minified Next chunks usually start with (self.webpack|!function|\"use strict\")
  return sample.length > 0 && !sample.trimStart().startsWith('<');
}

async function main() {
  console.log(`Probing ${origin}`);
  const home = await fetch(`${origin}/`, {
    headers: { Accept: 'text/html', 'User-Agent': 'saiflower-chunk-probe/1.0' },
    redirect: 'follow',
  });
  const html = await home.text();
  if (!home.ok) {
    console.error(`FAIL: homepage HTTP ${home.status}`);
    process.exit(1);
  }

  const chunks = extractChunkPaths(html);
  if (chunks.length === 0) {
    console.error('FAIL: no /_next/static/chunks/*.js references found in homepage HTML');
    process.exit(1);
  }

  const layoutChunks = chunks.filter((p) => /\/layout-[^/]+\.js$/.test(p));
  const critical = [
    ...layoutChunks,
    ...chunks.filter((p) => /\/(main-app|webpack|app\/page)-[^/]*\.js$/.test(p) || /\/app\/page-[^/]+\.js$/.test(p)),
  ];
  const uniqueCritical = [...new Set(critical.length ? critical : chunks.slice(0, 8))];

  let failed = 0;
  for (const path of uniqueCritical) {
    const url = `${origin}${path}`;
    try {
      const result = await headOrGet(url);
      const ok = result.status === 200 && looksLikeJs(result.contentType, result.sample);
      console.log(
        `${ok ? 'OK ' : 'BAD'} ${result.status} ${path} (${result.bytes}b, ${result.contentType || 'no-type'})`,
      );
      if (!ok) failed += 1;
    } catch (error) {
      console.log(`BAD ERR ${path} (${error.message})`);
      failed += 1;
    }
  }

  if (failed > 0) {
    console.error(`FAIL: ${failed}/${uniqueCritical.length} critical chunk(s) unhealthy`);
    process.exit(1);
  }

  console.log(`OK: homepage + ${uniqueCritical.length} critical chunk(s) healthy`);
}

main().catch((error) => {
  console.error('FAIL:', error.message);
  process.exit(1);
});
