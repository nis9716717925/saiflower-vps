import { existsSync } from 'node:fs';
import path from 'node:path';

/** Production default: /var/www/saiflower-vps/uploads (repo root /uploads). */
export function resolveUploadsDirectory(): string {
  if (process.env.UPLOADS_DIR?.trim()) {
    return path.resolve(process.env.UPLOADS_DIR.trim());
  }

  const cwd = process.cwd();
  const candidates = [
    path.resolve(cwd, '..', '..', 'uploads'),
    path.join(cwd, 'public', 'uploads'),
    path.resolve(cwd, '..', 'web', 'public', 'uploads'),
  ];

  for (const dir of candidates) {
    if (existsSync(dir)) return dir;
  }

  return candidates[0];
}
