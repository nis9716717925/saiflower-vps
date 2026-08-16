import { readFile } from 'node:fs/promises';
import path from 'node:path';
import { NextResponse, type NextRequest } from 'next/server';

export const runtime = 'nodejs';
export const dynamic = 'force-dynamic';

const MEDIA_ORIGIN = (
  process.env.NEXT_PUBLIC_MEDIA_ORIGIN ??
  process.env.MEDIA_BASE_URL ??
  'https://saiflower.com'
).replace(/\/$/, '');

const BROWSER_UA =
  'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

const FALLBACK_LOGO = path.join(
  process.cwd(),
  'public',
  'assets',
  'images',
  'logo-transparent.png',
);

const CONTENT_TYPES: Record<string, string> = {
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.png': 'image/png',
  '.webp': 'image/webp',
  '.gif': 'image/gif',
  '.svg': 'image/svg+xml',
  '.avif': 'image/avif',
};

function contentTypeFor(filePath: string): string {
  const ext = path.extname(filePath).toLowerCase();
  return CONTENT_TYPES[ext] ?? 'application/octet-stream';
}

function cacheControl(): string {
  if (process.env.NODE_ENV === 'production') {
    return 'public, max-age=604800, stale-while-revalidate=86400';
  }
  return 'no-store, no-cache, must-revalidate';
}

async function localUpload(relativePath: string): Promise<Response | null> {
  const localPath = path.join(process.cwd(), 'public', 'uploads', relativePath);
  const resolved = path.resolve(localPath);
  const uploadsRoot = path.resolve(path.join(process.cwd(), 'public', 'uploads'));
  if (!resolved.startsWith(uploadsRoot)) return null;

  try {
    const data = await readFile(resolved);
    return new Response(data, {
      status: 200,
      headers: {
        'Content-Type': contentTypeFor(relativePath),
        'Cache-Control': cacheControl(),
      },
    });
  } catch {
    return null;
  }
}

async function fallbackLogo(): Promise<Response> {
  const data = await readFile(FALLBACK_LOGO);
  return new Response(data, {
    status: 200,
    headers: {
      'Content-Type': 'image/png',
      'Cache-Control': 'no-store',
      'X-Media-Fallback': 'logo',
    },
  });
}

async function proxyUpload(relativePath: string): Promise<Response> {
  const local = await localUpload(relativePath);
  if (local) return local;

  const upstream = new URL(`/uploads/${relativePath}`, `${MEDIA_ORIGIN}/`).toString();

  try {
    const res = await fetch(upstream, {
      headers: {
        'User-Agent': BROWSER_UA,
        Accept: 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
        Referer: `${MEDIA_ORIGIN}/`,
        'Accept-Language': 'en-US,en;q=0.9',
      },
      redirect: 'follow',
      cache: 'no-store',
    });

    if (res.ok) {
      const headers = new Headers();
      const type = res.headers.get('content-type') ?? contentTypeFor(relativePath);
      headers.set('Content-Type', type);
      headers.set('Cache-Control', cacheControl());
      return new Response(await res.arrayBuffer(), { status: res.status, headers });
    }
  } catch {
    // fall through to logo placeholder
  }

  return fallbackLogo();
}

type Ctx = { params: Promise<{ path: string[] }> };

async function handle(req: NextRequest, ctx: Ctx): Promise<Response> {
  const { path: segments } = await ctx.params;
  const relativePath = segments.map(decodeURIComponent).join('/');
  if (!relativePath || relativePath.includes('..')) {
    return NextResponse.json({ error: 'Invalid path' }, { status: 400 });
  }

  const res = await proxyUpload(relativePath);
  if (req.method === 'HEAD') {
    return new Response(null, { status: res.status, headers: res.headers });
  }
  return res;
}

export async function GET(req: NextRequest, ctx: Ctx) {
  return handle(req, ctx);
}

export async function HEAD(req: NextRequest, ctx: Ctx) {
  return handle(req, ctx);
}
