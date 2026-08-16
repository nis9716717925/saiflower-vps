#!/usr/bin/env node
/**
 * Package apps/web/public/uploads into a tarball for VPS deployment.
 *
 * Usage:
 *   npm run uploads:pack
 *   scp dist/saiflower-uploads.tar.gz user@VPS:/tmp/
 *   ssh user@VPS 'cd /var/www/saiflower-vps/apps/web/public && tar xzf /tmp/saiflower-uploads.tar.gz'
 */
import { execSync } from 'node:child_process';
import { existsSync, mkdirSync, readdirSync, statSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const uploadsDir = path.join(root, 'uploads');
const legacyPublicUploads = path.join(root, 'apps/web/public/uploads');
const sourceDir = existsSync(uploadsDir)
  ? uploadsDir
  : existsSync(legacyPublicUploads)
    ? legacyPublicUploads
    : null;
const outDir = path.join(root, 'dist');
const archive = path.join(outDir, 'saiflower-uploads.tar.gz');

function countFiles(dir) {
  let files = 0;
  let bytes = 0;
  for (const entry of readdirSync(dir, { withFileTypes: true })) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      const nested = countFiles(full);
      files += nested.files;
      bytes += nested.bytes;
    } else if (entry.isFile()) {
      files += 1;
      bytes += statSync(full).size;
    }
  }
  return { files, bytes };
}

if (!sourceDir) {
  console.error('Uploads folder not found. Expected one of:');
  console.error(`  ${uploadsDir}`);
  console.error(`  ${legacyPublicUploads}`);
  console.error('Copy uploads from Hostinger public_html/uploads first.');
  process.exit(1);
}

const { files, bytes } = countFiles(sourceDir);
mkdirSync(outDir, { recursive: true });

execSync(`tar -czf "${archive}" -C "${sourceDir}" .`, {
  stdio: 'inherit',
});

const archiveMb = (statSync(archive).size / (1024 * 1024)).toFixed(1);
console.log(`\nCreated ${archive}`);
console.log(`Source: ${files} files (~${(bytes / (1024 * 1024)).toFixed(1)} MB) → archive ${archiveMb} MB`);
console.log('\nDeploy on VPS:');
console.log('  scp dist/saiflower-uploads.tar.gz root@200.141.1.201:/tmp/');
console.log(
  '  ssh root@200.141.1.201 "mkdir -p /var/www/saiflower-vps/uploads && tar xzf /tmp/saiflower-uploads.tar.gz -C /var/www/saiflower-vps/uploads"',
);
