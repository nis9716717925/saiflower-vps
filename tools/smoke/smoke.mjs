#!/usr/bin/env node
/**
 * Soft-launch smoke checks for SaiFlower Next + Express.
 *
 * Usage:
 *   node tools/smoke/smoke.mjs
 *   SMOKE_WEB_BASE=http://localhost:3000 SMOKE_API_BASE=http://localhost:4000 node tools/smoke/smoke.mjs
 */

const WEB = (process.env.SMOKE_WEB_BASE || 'http://localhost:3000').replace(/\/$/, '');
const API = (process.env.SMOKE_API_BASE || 'http://localhost:4000').replace(/\/$/, '');

const WEB_PATHS = [
  '/',
  '/flowers',
  '/cakes',
  '/gifts',
  '/cart',
  '/checkout',
  '/login',
  '/register',
  '/wishlist',
  '/profile',
  '/verify',
  '/search-results?q=rose',
  '/privacy',
  '/terms',
  '/legal',
  '/refund-policy',
  '/delivery-policy',
  '/robots.txt',
];

async function check(label, url, { expectOk = true, validate } = {}) {
  const started = Date.now();
  try {
    const res = await fetch(url, {
      redirect: 'follow',
      headers: { Accept: 'application/json,text/html,*/*' },
    });
    const ms = Date.now() - started;
    const text = await res.text();
    let extra = '';
    if (validate) {
      const msg = validate(res, text);
      if (msg) {
        console.log(`FAIL  ${label} (${res.status}, ${ms}ms) — ${msg}`);
        return false;
      }
    }
    if (expectOk && res.status >= 400) {
      console.log(`FAIL  ${label} (${res.status}, ${ms}ms)`);
      return false;
    }
    console.log(`OK    ${label} (${res.status}, ${ms}ms)${extra}`);
    return true;
  } catch (err) {
    console.log(`FAIL  ${label} — ${err instanceof Error ? err.message : err}`);
    return false;
  }
}

async function main() {
  console.log(`Smoke web=${WEB} api=${API}\n`);
  const results = [];

  results.push(
    await check('GET /health', `${API}/health`, {
      validate: (_res, body) => {
        try {
          const json = JSON.parse(body);
          if (json.status !== 'ok') return `status=${json.status}`;
          if (json.checkoutMode !== 'whatsapp_confirm') return `checkoutMode=${json.checkoutMode}`;
          if (json.database && json.database !== 'up') return `database=${json.database}`;
          return null;
        } catch {
          return 'invalid JSON';
        }
      },
    }),
  );

  results.push(
    await check('GET /api/v1/products?type=flower&limit=1', `${API}/api/v1/products?type=flower&limit=1`, {
      validate: (_res, body) => {
        try {
          const json = JSON.parse(body);
          if (!json.success) return json.message || 'success=false';
          return null;
        } catch {
          return 'invalid JSON';
        }
      },
    }),
  );

  results.push(
    await check('GET /api/v1/settings', `${API}/api/v1/settings`, {
      validate: (_res, body) => {
        try {
          const json = JSON.parse(body);
          return json.success ? null : json.message || 'success=false';
        } catch {
          return 'invalid JSON';
        }
      },
    }),
  );

  for (const path of WEB_PATHS) {
    results.push(await check(`GET ${path}`, `${WEB}${path}`));
  }

  const failed = results.filter((ok) => !ok).length;
  console.log(`\n${results.length - failed}/${results.length} checks passed`);
  process.exit(failed ? 1 : 0);
}

main();
