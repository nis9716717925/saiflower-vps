#!/usr/bin/env node
/**
 * Upload local uploads/ folder to Supabase Storage (one file at a time).
 *
 * Required env in apps/server/.env:
 *   SUPABASE_URL=https://YOUR_PROJECT.supabase.co
 *   SUPABASE_SERVICE_ROLE_KEY=eyJ...
 *   SUPABASE_STORAGE_BUCKET=product-images
 *
 * Usage:
 *   npm run uploads:supabase
 *   npm run uploads:supabase -- --dry-run
 */
import { existsSync, readdirSync, readFileSync, statSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import dotenv from 'dotenv';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
dotenv.config({ path: path.join(root, 'apps/server/.env') });
dotenv.config({ path: path.join(root, 'packages/prisma/.env') });
dotenv.config();

const dryRun = process.argv.includes('--dry-run');
const uploadsDir = process.env.UPLOADS_DIR
  ? path.resolve(process.env.UPLOADS_DIR)
  : path.join(root, 'uploads');

const supabaseUrl = process.env.SUPABASE_URL?.trim().replace(/\/$/, '');
const serviceKey = process.env.SUPABASE_SERVICE_ROLE_KEY?.trim();
const bucket = process.env.SUPABASE_STORAGE_BUCKET?.trim() || 'product-images';

if (!supabaseUrl || !serviceKey) {
  console.error('Missing SUPABASE_URL or SUPABASE_SERVICE_ROLE_KEY.');
  console.error('Add them to apps/server/.env then run: npm run uploads:supabase');
  process.exit(1);
}

if (!existsSync(uploadsDir)) {
  console.error(`Uploads folder not found: ${uploadsDir}`);
  process.exit(1);
}

function walkFiles(dir, base = dir) {
  const out = [];
  for (const entry of readdirSync(dir)) {
    const full = path.join(dir, entry);
    if (statSync(full).isDirectory()) out.push(...walkFiles(full, base));
    else out.push(path.relative(base, full).replace(/\\/g, '/'));
  }
  return out;
}

function contentType(file) {
  const ext = path.extname(file).toLowerCase();
  const map = {
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.png': 'image/png',
    '.webp': 'image/webp',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.avif': 'image/avif',
  };
  return map[ext] ?? 'application/octet-stream';
}

async function uploadFile(key, localPath) {
  const body = readFileSync(localPath);
  const encodedKey = key.split('/').map(encodeURIComponent).join('/');
  const encodedBucket = encodeURIComponent(bucket);
  const url = `${supabaseUrl}/storage/v1/object/${encodedBucket}/${encodedKey}`;

  const res = await fetch(url, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${serviceKey}`,
      'Content-Type': contentType(key),
      'x-upsert': 'true',
    },
    body,
  });

  if (!res.ok) {
    const text = await res.text();
    throw new Error(`${res.status} ${text.slice(0, 200)}`);
  }
}

async function main() {
  const files = walkFiles(uploadsDir);
  console.log(`Bucket: ${bucket}`);
  console.log(`Source: ${uploadsDir}`);
  console.log(`Files:  ${files.length}`);
  if (dryRun) {
    console.log('Dry run — first 5 keys:', files.slice(0, 5));
    return;
  }

  let ok = 0;
  let fail = 0;
  const errors = [];

  for (let i = 0; i < files.length; i++) {
    const key = files[i];
    const localPath = path.join(uploadsDir, key);
    const size = statSync(localPath).size;

    if (size > 50 * 1024 * 1024) {
      fail += 1;
      errors.push(`${key}: exceeds Supabase free 50MB per-file limit`);
      continue;
    }

    try {
      await uploadFile(key, localPath);
      ok += 1;
    } catch (error) {
      fail += 1;
      errors.push(`${key}: ${error.message}`);
    }

    if ((i + 1) % 25 === 0 || i + 1 === files.length) {
      process.stdout.write(`\rUploaded ${i + 1}/${files.length} (ok=${ok} fail=${fail})`);
    }
  }

  console.log('\nDone.');
  const publicBase = `${supabaseUrl}/storage/v1/object/public/${encodeURIComponent(bucket)}`;
  console.log(`Public URL example: ${publicBase}/${files[0]}`);

  if (errors.length) {
    console.log('\nErrors (first 10):');
    errors.slice(0, 10).forEach((e) => console.log(' -', e));
    process.exit(1);
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
