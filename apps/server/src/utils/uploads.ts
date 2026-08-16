import { existsSync, readdirSync, statSync } from 'node:fs';
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

export function countUploadFilesSync(): number | null {
  try {
    const root = resolveUploadsDirectory();
    if (!existsSync(root)) return 0;

    let count = 0;
    const walk = (dir: string) => {
      for (const entry of readdirSync(dir)) {
        const full = path.join(dir, entry);
        if (statSync(full).isDirectory()) walk(full);
        else count += 1;
      }
    };
    walk(root);
    return count;
  } catch {
    return null;
  }
}
