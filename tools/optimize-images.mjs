/**
 * Convert raster images (jpg/jpeg/png/gif) to WebP across uploads, assets, celebrations.
 *
 * Usage:
 *   node tools/optimize-images.mjs
 *   node tools/optimize-images.mjs --delete-originals
 *   node tools/optimize-images.mjs --dir uploads --quality 78
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');

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
const deleteOriginals = args.includes('--delete-originals');
const qualityIdx = args.indexOf('--quality');
const quality = qualityIdx >= 0 ? Number(args[qualityIdx + 1]) || 78 : 78;
const dirIdx = args.indexOf('--dir');
const onlyDir = dirIdx >= 0 ? args[dirIdx + 1] : null;

const RASTER_EXT = new Set(['.jpg', '.jpeg', '.png', '.gif']);
const SKIP_NAMES = new Set(['favicon.png']); // keep classic favicon for broad browser support

const DEFAULT_DIRS = ['uploads', 'assets', 'celebrations', path.join('apps', 'web', 'public')];

function walk(dir, files = []) {
  if (!fs.existsSync(dir)) return files;
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (entry.name === 'node_modules' || entry.name === '.git') continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(full, files);
    else if (entry.isFile()) files.push(full);
  }
  return files;
}

function shouldConvert(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  if (!RASTER_EXT.has(ext)) return false;
  if (SKIP_NAMES.has(path.basename(filePath).toLowerCase())) return false;
  // Keep favicon assets as PNG/ICO for browser compatibility
  if (/^favicon/i.test(path.basename(filePath))) return false;
  return true;
}

async function convertOne(filePath) {
  const ext = path.extname(filePath);
  const outPath = filePath.slice(0, -ext.length) + '.webp';
  const inputStat = fs.statSync(filePath);

  if (fs.existsSync(outPath)) {
    const outStat = fs.statSync(outPath);
    if (outStat.mtimeMs >= inputStat.mtimeMs && outStat.size > 0) {
      if (deleteOriginals) {
        fs.unlinkSync(filePath);
        return {
          status: 'pruned',
          filePath,
          outPath,
          saved: inputStat.size,
          inSize: inputStat.size,
          outSize: outStat.size,
        };
      }
      return { status: 'skip', filePath, outPath, saved: 0 };
    }
  }

  const image = sharp(filePath, { failOn: 'none', animated: false });
  const meta = await image.metadata();
  const pipeline = image.rotate(); // honor EXIF orientation

  // Cap very large sources so product pages stay lean
  const maxEdge = 2000;
  if ((meta.width || 0) > maxEdge || (meta.height || 0) > maxEdge) {
    pipeline.resize({
      width: maxEdge,
      height: maxEdge,
      fit: 'inside',
      withoutEnlargement: true,
    });
  }

  await pipeline.webp({ quality, effort: 4 }).toFile(outPath);
  const outSize = fs.statSync(outPath).size;
  const saved = inputStat.size - outSize;

  if (deleteOriginals && outSize > 0) {
    fs.unlinkSync(filePath);
  }

  return { status: 'converted', filePath, outPath, saved, inSize: inputStat.size, outSize };
}

async function main() {
  const dirs = onlyDir
    ? [path.isAbsolute(onlyDir) ? onlyDir : path.join(root, onlyDir)]
    : DEFAULT_DIRS.map((d) => path.join(root, d));

  const seen = new Set();
  const targets = [];
  for (const dir of dirs) {
    for (const file of walk(dir)) {
      const resolved = path.resolve(file);
      if (seen.has(resolved)) continue;
      seen.add(resolved);
      if (shouldConvert(resolved)) targets.push(resolved);
    }
  }

  console.log(`Found ${targets.length} raster image(s) to process (quality=${quality})`);
  let converted = 0;
  let skipped = 0;
  let pruned = 0;
  let failed = 0;
  let bytesSaved = 0;

  // Convert sequentially to avoid sharp memory spikes on large batches
  for (const file of targets) {
    try {
      const result = await convertOne(file);
      if (result.status === 'skip') {
        skipped += 1;
        continue;
      }
      if (result.status === 'pruned') {
        pruned += 1;
        bytesSaved += result.saved;
        console.log(`⌫ ${path.relative(root, file)} (webp exists)`);
        continue;
      }
      converted += 1;
      bytesSaved += result.saved;
      const rel = path.relative(root, file);
      const pct = result.inSize ? Math.round((1 - result.outSize / result.inSize) * 100) : 0;
      console.log(`✓ ${rel} → webp (${pct}% smaller)`);
    } catch (err) {
      failed += 1;
      console.error(`✗ ${path.relative(root, file)}: ${err instanceof Error ? err.message : err}`);
    }
  }

  console.log(
    `\nDone. converted=${converted} pruned=${pruned} skipped=${skipped} failed=${failed} saved≈${(bytesSaved / 1024 / 1024).toFixed(2)} MB`,
  );
  if (!deleteOriginals) {
    console.log('Originals kept. Re-run with --delete-originals after verifying WebP URLs in the app.');
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
