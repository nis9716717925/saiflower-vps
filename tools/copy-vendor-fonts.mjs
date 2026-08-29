/**
 * Copy self-hosted icon fonts into apps/web/public/assets/vendor/
 * (Font Awesome solid/brands/regular + Material Icons Outlined).
 *
 * Run after npm install:
 *   npm run vendor:fonts
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const destRoot = path.join(root, 'apps/web/public/assets/vendor');

function resolvePkgDir(pkgName) {
  const candidates = [
    path.join(root, 'apps/web/node_modules', pkgName),
    path.join(root, 'node_modules', pkgName),
  ];
  for (const dir of candidates) {
    if (fs.existsSync(dir)) return dir;
  }
  try {
    return path.dirname(require.resolve(`${pkgName}/package.json`));
  } catch {
    return null;
  }
}

function copyFile(src, dest) {
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(src, dest);
}

function copyDir(srcDir, destDir, filter = () => true) {
  if (!fs.existsSync(srcDir)) return 0;
  let count = 0;
  for (const entry of fs.readdirSync(srcDir, { withFileTypes: true })) {
    const src = path.join(srcDir, entry.name);
    const dest = path.join(destDir, entry.name);
    if (entry.isDirectory()) {
      count += copyDir(src, dest, filter);
    } else if (entry.isFile() && filter(entry.name)) {
      copyFile(src, dest);
      count += 1;
    }
  }
  return count;
}

function patchFaCssPaths(cssDir) {
  for (const file of fs.readdirSync(cssDir)) {
    if (!file.endsWith('.min.css')) continue;
    const filePath = path.join(cssDir, file);
    let css = fs.readFileSync(filePath, 'utf8');
    css = css.replaceAll('../webfonts/', '/assets/vendor/fontawesome/webfonts/');
    fs.writeFileSync(filePath, css);
  }
}

function writeMaterialIconsCss(destCss) {
  const css = `@font-face {
  font-family: 'Material Icons Outlined';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url(/assets/vendor/material-icons/material-icons-outlined.woff2) format('woff2');
}
.material-icons-outlined {
  font-family: 'Material Icons Outlined';
  font-weight: normal;
  font-style: normal;
  font-size: 24px;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  display: inline-block;
  white-space: nowrap;
  word-wrap: normal;
  direction: ltr;
  -webkit-font-smoothing: antialiased;
}
`;
  fs.mkdirSync(path.dirname(destCss), { recursive: true });
  fs.writeFileSync(destCss, css);
}

function main() {
  const faDir = resolvePkgDir('@fortawesome/fontawesome-free');
  if (!faDir) {
    console.error('Missing @fortawesome/fontawesome-free — run npm install in apps/web');
    process.exit(1);
  }

  const materialDir = resolvePkgDir('@fontsource/material-icons-outlined');
  if (!materialDir) {
    console.error('Missing @fontsource/material-icons-outlined — run npm install in apps/web');
    process.exit(1);
  }

  const faCssDest = path.join(destRoot, 'fontawesome/css');
  const faFontsDest = path.join(destRoot, 'fontawesome/webfonts');
  const materialDest = path.join(destRoot, 'material-icons');

  const faCssFiles = [
    'fontawesome.min.css',
    'solid.min.css',
    'brands.min.css',
    'regular.min.css',
  ];

  let copied = 0;
  for (const file of faCssFiles) {
    const src = path.join(faDir, 'css', file);
    if (!fs.existsSync(src)) {
      console.error(`Missing ${src}`);
      process.exit(1);
    }
    copyFile(src, path.join(faCssDest, file));
    copied += 1;
  }

  copied += copyDir(path.join(faDir, 'webfonts'), faFontsDest, (name) => name.endsWith('.woff2'));
  patchFaCssPaths(faCssDest);

  const materialWoff = path.join(
    materialDir,
    'files',
    'material-icons-outlined-latin-400-normal.woff2',
  );
  if (!fs.existsSync(materialWoff)) {
    console.error(`Missing ${materialWoff}`);
    process.exit(1);
  }
  copyFile(materialWoff, path.join(materialDest, 'material-icons-outlined.woff2'));
  writeMaterialIconsCss(path.join(destRoot, 'material-icons-outlined.css'));
  copied += 2;

  console.log(`Copied vendor fonts to apps/web/public/assets/vendor (${copied} files)`);
}

main();
