#!/usr/bin/env node
/**
 * Compare root PHP storefront pages vs Next App Router pages.
 * Prints missing / stub / present for soft-launch planning.
 */

import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const webApp = path.join(root, 'apps/web/src/app');

const SKIP_PHP = new Set([
  'config.php',
  'ajax_search.php',
  'maintenance.php',
  'page.php',
  'flower-detail.php',
  'cake-detail.php',
  'gift-detail.php',
  'event-detail.php',
]);

function listPhpPages() {
  return fs
    .readdirSync(root)
    .filter((f) => f.endsWith('.php') && !SKIP_PHP.has(f) && !f.startsWith('test'))
    .map((f) => f.replace(/\.php$/, ''))
    .sort();
}

function collectNextRoutes(dir = webApp, base = '') {
  const out = [];
  if (!fs.existsSync(dir)) return out;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name.startsWith('_') || entry.name === 'api') continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      const seg = entry.name.startsWith('(') ? '' : `/${entry.name}`;
      out.push(...collectNextRoutes(full, `${base}${seg}`));
    } else if (entry.name === 'page.tsx' || entry.name === 'page.ts' || entry.name === 'page.jsx') {
      out.push(base || '/');
    }
  }
  return out.sort();
}

function classify(phpBase, nextRoutes) {
  const candidates = [
    `/${phpBase}`,
    phpBase === 'index' ? '/' : null,
    phpBase.replace(/_/g, '-'),
  ].filter(Boolean);

  for (const c of candidates) {
    const normalized = c === 'index' ? '/' : c.startsWith('/') ? c : `/${c}`;
    if (nextRoutes.includes(normalized)) return { status: 'present', route: normalized };
    // dynamic catch
    const dyn = nextRoutes.find(
      (r) => r.includes('[') && normalized.startsWith(r.replace(/\[.*?\]/g, '').replace(/\/$/, '')),
    );
    if (dyn) return { status: 'dynamic', route: dyn };
  }
  return { status: 'missing', route: `/${phpBase}` };
}

function isStub(route) {
  const fileGuess = path.join(
    webApp,
    ...route
      .replace(/^\//, '')
      .split('/')
      .filter(Boolean)
      .map((s) => (s.includes('[') ? s : s)),
    'page.tsx',
  );
  // Also try dynamic folders
  const candidates = [
    fileGuess,
    path.join(webApp, route.replace(/^\//, ''), 'page.tsx'),
  ];
  for (const file of candidates) {
    if (fs.existsSync(file)) {
      const src = fs.readFileSync(file, 'utf8');
      if (src.includes('StubPage')) return true;
    }
  }
  // scan for StubPage under matching tree
  const rel = route.replace(/^\//, '');
  const dir = path.join(webApp, rel.split('/')[0] || '');
  if (fs.existsSync(dir)) {
    const walk = (d) => {
      for (const e of fs.readdirSync(d, { withFileTypes: true })) {
        const p = path.join(d, e.name);
        if (e.isDirectory()) {
          if (walk(p)) return true;
        } else if (e.name === 'page.tsx') {
          if (fs.readFileSync(p, 'utf8').includes('StubPage') && p.includes(rel.split('/')[0])) {
            // only mark exact stubs for known stub dirs
          }
        }
      }
      return false;
    };
  }
  const stubDirs = new Set([
    '/collection/[slug]',
    '/relation/[slug]',
    '/occasion/[slug]',
    '/flower/[slug]',
    '/personalized',
    '/celebration-calendar',
    '/flower-delivery-in-delhi',
    '/about',
    '/contact',
    '/gallery',
    '/events',
    '/blog',
  ]);
  return stubDirs.has(route) || [...stubDirs].some((s) => s.replace('[slug]', '') === route + '/');
}

const php = listPhpPages();
const nextRoutes = collectNextRoutes();
const rows = php.map((name) => {
  const info = classify(name === 'index' ? '' : name, nextRoutes.map((r) => (r === '' ? '/' : r)));
  let status = info.status;
  if (status === 'present' && isStubFile(info.route)) status = 'stub';
  return { php: `${name}.php`, next: info.route, status };
});

function isStubFile(route) {
  const parts = route.replace(/^\//, '').split('/').filter(Boolean);
  let dir = webApp;
  for (const part of parts) {
    const direct = path.join(dir, part);
    if (fs.existsSync(direct)) {
      dir = direct;
      continue;
    }
    const dyn = fs.readdirSync(dir).find((n) => n.startsWith('[') && n.endsWith(']'));
    if (dyn) {
      dir = path.join(dir, dyn);
      continue;
    }
    return false;
  }
  const page = path.join(dir, 'page.tsx');
  if (!fs.existsSync(page)) return false;
  return fs.readFileSync(page, 'utf8').includes('StubPage');
}

console.log('PHP page → Next route inventory\n');
console.log('status'.padEnd(10), 'php'.padEnd(28), 'next');
console.log('-'.repeat(72));
for (const row of rows) {
  console.log(row.status.padEnd(10), row.php.padEnd(28), row.next);
}

const counts = rows.reduce((acc, r) => {
  acc[r.status] = (acc[r.status] || 0) + 1;
  return acc;
}, {});
console.log('\nSummary:', counts);
console.log(`Next routes found: ${nextRoutes.length}`);
