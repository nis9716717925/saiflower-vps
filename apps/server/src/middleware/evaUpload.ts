import { mkdirSync } from 'node:fs';
import path from 'node:path';
import multer from 'multer';
import { resolveUploadsDirectory } from '../utils/uploads';

const evaDir = () => {
  const dir = path.join(resolveUploadsDirectory(), 'eva');
  mkdirSync(dir, { recursive: true });
  return dir;
};

const storage = multer.diskStorage({
  destination: (_req, _file, cb) => cb(null, evaDir()),
  filename: (_req, file, cb) => {
    const safe = file.originalname.replace(/[^a-zA-Z0-9._-]/g, '_');
    cb(null, `${Date.now()}-${Math.random().toString(36).slice(2, 8)}-${safe}`);
  },
});

export const evaUpload = multer({
  storage,
  limits: { fileSize: 12 * 1024 * 1024 },
  fileFilter: (_req, file, cb) => {
    if (!file.mimetype.startsWith('image/') && !file.mimetype.startsWith('video/')) {
      cb(new Error('Only image or video uploads are allowed'));
      return;
    }
    cb(null, true);
  },
});

export function publicEvaUploadPath(filename: string): string {
  return `/uploads/eva/${filename}`;
}
