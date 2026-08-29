/**
 * Generate width variants for WebP uploads so OptimizedImage srcset works on /uploads/.
 *
 * Naming: flowers/rose.webp → flowers/rose-w320.webp, rose-w640.webp, …
 *
 * Usage:
 *   node tools/generate-responsive-variants.mjs
 *   node tools/generate-responsive-variants.mjs --dir uploads --force
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

const WIDTHS = [320, 640, 828, 1080];
const VARIANT_RE = /-w(\d+)\.webp$/i;

function loadSharp() {
  const candidates = [
    path.join(root, 'apps/web/node_modules/sharp'),
    path.join(root, 'node_modules/sharp'),
    'sharp',
  ];
  for (const candidate of candidates) {
    try {
      return require(candidate);
    } catch {
      // try next
    }
  }
  throw new Error('sharp not found — run npm install in the monorepo root first');
}

const sharp = loadSharp();

const args = process.argv.slice(2);
const force = args.includes('--force');
const qualityIdx = args.indexOf('--quality');
const quality = qualityIdx >= 0 ? Number(args[qualityIdx + 1]) || 78 : 78;
const dirIdx = args.indexOf('--dir');
const onlyDir = dirIdx >= 0 ? args[dirIdx + 1] : 'uploads';

const targetDir = path.isAbsolute(onlyDir) ? onlyDir : path.join(root, onlyDir);

function walk(dir, files = []) {
  if (!fs.existsSync(dir)) return files;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name === '.git') continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full, files);
    else if (entry.isFile() && /\.webp$/i.test(entry.name) && !VARIANT_RE.test(entry.name)) {
      files.push(full);
    }
  }
  return files;
}

function variantPath(sourcePath, width) {
  const ext = path.extname(sourcePath);
  const base = sourcePath.slice(0, -ext.length);
  return `${base}-w${width}${ext}`;
}

async function generateVariant(sourcePath, width) {
  const outPath = variantPath(sourcePath, width);
  const sourceStat = fs.statSync(sourcePath);

  if (!force && fs.existsSync(outPath)) {
    const outStat = fs.statSync(outPath);
    if (outStat.mtimeMs >= sourceStat.mtimeMs && outStat.size > 0) {
      return { status: 'skip', outPath };
    }
  }

  const meta = await sharp(sourcePath, { failOn: 'none' }).metadata();
  const sourceWidth = meta.width || 0;
  if (sourceWidth > 0 && sourceWidth <= width) {
    fs.copyFileSync(sourcePath, outPath);
    return { status: 'copied', outPath };
  }

  await sharp(sourcePath, { failOn: 'none' })
    .rotate()
    .resize({
      width,
      fit: 'inside',
      withoutEnlargement: true,
    })
    .webp({ quality, effort: 4 })
    .toFile(outPath);

  return { status: 'created', outPath };
}

async function main() {
  if (!fs.existsSync(targetDir)) {
    console.error(`Directory not found: ${targetDir}`);
    process.exit(1);
  }

  const sources = walk(targetDir);
  console.log(`Found ${sources.length} source WebP(s) in ${path.relative(root, targetDir)}`);

  let created = 0;
  let skipped = 0;
  let failed = 0;

  for (const source of sources) {
    if (/(?:^|\/)categories\//i.test(source)) {
      skipped += WIDTHS.length;
      continue;
    }

    for (const width of WIDTHS) {
      try {
        const result = await generateVariant(source, width);
        if (result.status === 'created') {
          created += 1;
          console.log(`✓ ${path.relative(root, result.outPath)}`);
        } else if (result.status === 'copied') {
          created += 1;
          console.log(`↳ ${path.relative(root, result.outPath)} (source copy)`);
        } else {
          skipped += 1;
        }
      } catch (err) {
        failed += 1;
        console.error(
          `✗ ${path.relative(root, source)} @${width}w: ${err instanceof Error ? err.message : err}`,
        );
      }
    }
  }

  console.log(`\nDone. created=${created} skipped=${skipped} failed=${failed}`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
