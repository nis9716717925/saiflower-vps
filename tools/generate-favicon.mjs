/**
 * Build SaiFlower favicons from apps/web/public/favicon.png (lotus logo).
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const sharp = require(path.join(root, 'node_modules/sharp'));

const SRC_CANDIDATES = [
  path.join(root, 'apps/web/public/_favicon-build.png'),
  path.join(root, 'apps/web/public/favicon-source.webp'),
  path.join(root, 'apps/web/public/favicon.png'),
  path.join(root, 'assets/images/logo-transparent.webp'),
];
const SRC = SRC_CANDIDATES.find((p) => fs.existsSync(p));
const APP_DIR = path.join(root, 'apps/web/src/app');
const PUBLIC = path.join(root, 'apps/web/public');

function pngBuffersToIco(pngBuffers) {
  const count = pngBuffers.length;
  const headerSize = 6 + count * 16;
  let offset = headerSize;
  const entries = [];

  for (const png of pngBuffers) {
    // Read IHDR width/height
    const width = png.readUInt32BE(16);
    const height = png.readUInt32BE(20);
    entries.push({
      width: width >= 256 ? 0 : width,
      height: height >= 256 ? 0 : height,
      size: png.length,
      offset,
      png,
    });
    offset += png.length;
  }

  const buf = Buffer.alloc(offset);
  buf.writeUInt16LE(0, 0); // reserved
  buf.writeUInt16LE(1, 2); // type icon
  buf.writeUInt16LE(count, 4);

  let entryOffset = 6;
  for (const e of entries) {
    buf.writeUInt8(e.width, entryOffset);
    buf.writeUInt8(e.height, entryOffset + 1);
    buf.writeUInt8(0, entryOffset + 2); // palette
    buf.writeUInt8(0, entryOffset + 3); // reserved
    buf.writeUInt16LE(1, entryOffset + 4); // planes
    buf.writeUInt16LE(32, entryOffset + 6); // bit count
    buf.writeUInt32LE(e.size, entryOffset + 8);
    buf.writeUInt32LE(e.offset, entryOffset + 12);
    entryOffset += 16;
  }

  for (const e of entries) {
    e.png.copy(buf, e.offset);
  }
  return buf;
}

async function squarePng(size) {
  const inner = Math.round(size * 0.84);
  const resized = await sharp(SRC)
    .rotate()
    .ensureAlpha()
    .resize(inner, inner, {
      fit: 'inside',
      background: { r: 0, g: 0, b: 0, alpha: 0 },
    })
    .png()
    .toBuffer();

  return sharp({
    create: {
      width: size,
      height: size,
      channels: 4,
      background: { r: 0, g: 0, b: 0, alpha: 0 },
    },
  })
    .composite([{ input: resized, gravity: 'centre' }])
    .png()
    .toBuffer();
}

async function main() {
  if (!fs.existsSync(SRC)) throw new Error(`Missing ${SRC}`);

  const [png16, png32, png48, png180, png512] = await Promise.all([
    squarePng(16),
    squarePng(32),
    squarePng(48),
    squarePng(180),
    squarePng(512),
  ]);

  const ico = pngBuffersToIco([png16, png32, png48]);
  fs.writeFileSync(path.join(APP_DIR, 'favicon.ico'), ico);
  fs.writeFileSync(path.join(APP_DIR, 'icon.png'), png32);
  fs.writeFileSync(path.join(APP_DIR, 'apple-icon.png'), png180);
  fs.writeFileSync(path.join(PUBLIC, 'favicon.png'), png512);
  fs.writeFileSync(path.join(PUBLIC, 'favicon-32.png'), png32);

  console.log('Favicons written:', {
    ico: path.join(APP_DIR, 'favicon.ico'),
    icon: path.join(APP_DIR, 'icon.png'),
    apple: path.join(APP_DIR, 'apple-icon.png'),
    publicPng: path.join(PUBLIC, 'favicon.png'),
  });
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
