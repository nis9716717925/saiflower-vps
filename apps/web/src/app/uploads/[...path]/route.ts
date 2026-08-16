import { NextResponse, type NextRequest } from 'next/server';
import { resolveUploadsDirectory } from '@/lib/uploads-dir';

export const runtime = 'nodejs';
export const dynamic = 'force-dynamic';

/**
 * Dev fallback when Next runs without the Express /uploads static handler.
 * Production traffic is rewritten to Express in next.config.ts.
 */
type Ctx = { params: Promise<{ path: string[] }> };

export async function GET(req: NextRequest, ctx: Ctx) {
  const apiBase = process.env.NEXT_PUBLIC_API_PROXY_TARGET ?? 'http://localhost:4000';
  const { path: segments } = await ctx.params;
  const relativePath = segments.map(decodeURIComponent).join('/');
  if (!relativePath || relativePath.includes('..')) {
    return NextResponse.json({ error: 'Invalid path' }, { status: 400 });
  }

  try {
    const upstream = await fetch(`${apiBase}/uploads/${relativePath}`, {
      headers: { Accept: 'image/*' },
      cache: 'no-store',
    });
    if (upstream.ok) {
      return new Response(await upstream.arrayBuffer(), {
        status: upstream.status,
        headers: upstream.headers,
      });
    }
  } catch {
    // fall through
  }

  return NextResponse.json(
    {
      error: 'Image not found',
      path: relativePath,
      uploadsDir: resolveUploadsDirectory(),
      hint: 'Copy legacy uploads folder to UPLOADS_DIR on the server',
    },
    { status: 404 },
  );
}

export async function HEAD(req: NextRequest, ctx: Ctx) {
  const res = await GET(req, ctx);
  return new Response(null, { status: res.status, headers: res.headers });
}
