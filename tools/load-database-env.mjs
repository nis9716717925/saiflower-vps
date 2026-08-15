import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import dotenv from 'dotenv';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const ENV_FILES = [
  path.join(ROOT, 'packages', 'prisma', '.env'),
  path.join(ROOT, 'apps', 'server', '.env'),
  path.join(ROOT, '.env'),
];

/**
 * Load database URLs consistently for migration tools and reject the conflicting
 * env-file state that previously caused imports to target the wrong database.
 */
export function loadDatabaseEnv() {
  const parsedFiles = [];

  for (const file of ENV_FILES) {
    if (!fs.existsSync(file)) continue;
    const values = dotenv.parse(fs.readFileSync(file));
    parsedFiles.push({ file, values });
    dotenv.config({ path: file, override: false, quiet: true });
  }

  for (const key of ['DATABASE_URL', 'DIRECT_URL']) {
    const definitions = parsedFiles
      .filter(({ values }) => values[key])
      .map(({ file, values }) => ({ file, value: values[key] }));
    const unique = new Set(definitions.map(({ value }) => value));
    if (unique.size > 1) {
      const files = definitions.map(({ file }) => path.relative(ROOT, file)).join(', ');
      throw new Error(`${key} conflicts across env files: ${files}. Make every copy identical.`);
    }
  }

  return parsedFiles.map(({ file }) => path.relative(ROOT, file));
}
