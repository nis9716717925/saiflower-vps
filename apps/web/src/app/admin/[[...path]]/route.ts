import { NextResponse, type NextRequest } from 'next/server';

export const runtime = 'nodejs';
export const dynamic = 'force-dynamic';

const ADMIN_ORIGIN = (
  process.env.ADMIN_ORIGIN ??
  process.env.NEXT_PUBLIC_MEDIA_ORIGIN ??
  'https://saiflower.com'
).replace(/\/$/, '');

function toUpstream(pathname: string, search: string): string {
  let p = pathname.replace(/^\/admin\/?/, '');
  if (!p || p === 'index' || p === 'index.php') {
    p = 'login.php';
  }
  return `${ADMIN_ORIGIN}/admin/${p}${search}`;
}

function filterRequestHeaders(src: Headers, cookieOverride?: string): Headers {
  const out = new Headers();
  src.forEach((value, key) => {
    const k = key.toLowerCase();
    if (['host', 'connection', 'content-length', 'transfer-encoding'].includes(k)) return;
    if (k === 'cookie' && cookieOverride) return;
    out.set(key, value);
  });
  if (cookieOverride) out.set('cookie', cookieOverride);
  return out;
}

/** Normalize Set-Cookie so it works on localhost. */
function localizeSetCookie(value: string): string {
  return value
    .replace(/;\s*Domain=[^;]*/gi, '')
    .replace(/;\s*Secure/gi, '')
    .replace(/;\s*SameSite=None/gi, '; SameSite=Lax');
}

function collectSetCookies(src: Headers): string[] {
  // Node/undici: getSetCookie() when available
  const anyHeaders = src as Headers & { getSetCookie?: () => string[] };
  if (typeof anyHeaders.getSetCookie === 'function') {
    return anyHeaders.getSetCookie();
  }
  const single = src.get('set-cookie');
  return single ? [single] : [];
}

/** Extract name=value pairs for Cookie request header. */
function cookieHeaderFromSetCookies(setCookies: string[], existing = ''): string {
  const map = new Map<string, string>();
  for (const part of existing.split(';')) {
    const trimmed = part.trim();
    if (!trimmed) continue;
    const eq = trimmed.indexOf('=');
    if (eq === -1) continue;
    map.set(trimmed.slice(0, eq), trimmed.slice(eq + 1));
  }
  for (const raw of setCookies) {
    const first = raw.split(';')[0]?.trim();
    if (!first) continue;
    const eq = first.indexOf('=');
    if (eq === -1) continue;
    map.set(first.slice(0, eq), first.slice(eq + 1));
  }
  return [...map.entries()].map(([k, v]) => `${k}=${v}`).join('; ');
}

function filterResponseHeaders(src: Headers, extraSetCookies: string[] = []): Headers {
  const out = new Headers();
  src.forEach((value, key) => {
    const k = key.toLowerCase();
    if (
      ['transfer-encoding', 'content-encoding', 'content-length', 'location', 'set-cookie'].includes(
        k,
      )
    ) {
      return;
    }
    out.set(key, value);
  });
  const all = [...collectSetCookies(src), ...extraSetCookies];
  for (const c of all) out.append('set-cookie', localizeSetCookie(c));
  return out;
}

function localizeHtml(html: string, requestOrigin: string): string {
  return html
    .replaceAll('https://saiflower.com/admin', `${requestOrigin}/admin`)
    .replaceAll('http://saiflower.com/admin', `${requestOrigin}/admin`)
    .replaceAll('https://www.saiflower.com/admin', `${requestOrigin}/admin`)
    .replaceAll('action="dashboard.php"', 'action="/admin/dashboard.php"')
    .replaceAll('href="dashboard.php"', 'href="/admin/dashboard.php"')
    .replaceAll("href='dashboard.php'", "href='/admin/dashboard.php'")
    .replaceAll('href="login.php"', 'href="/admin/login.php"')
    .replaceAll('action="login.php"', 'action="/admin/login.php"');
}

/** Resolve Location against the *current* upstream URL (fixes relative dashboard.php). */
function resolveUpstreamLocation(location: string, currentUpstreamUrl: string): string | null {
  try {
    const target = new URL(location, currentUpstreamUrl);
    if (!target.pathname.includes('/admin')) {
      // Relative like "dashboard.php" already resolved under /admin/ when base is correct
      if (!currentUpstreamUrl.includes('/admin/')) return null;
    }
    if (!target.pathname.startsWith('/admin') && target.origin === new URL(ADMIN_ORIGIN).origin) {
      // e.g. wrongly resolved to https://saiflower.com/dashboard.php
      if (!location.startsWith('/') && !/^https?:/i.test(location)) {
        const baseDir = currentUpstreamUrl.replace(/[^/]+$/, '');
        return new URL(location, baseDir).toString();
      }
      return null;
    }
    return target.toString();
  } catch {
    return null;
  }
}

function upstreamToLocalPath(upstreamUrl: string, requestOrigin: string): string {
  const u = new URL(upstreamUrl);
  return `${requestOrigin}${u.pathname}${u.search}`;
}

async function proxy(req: NextRequest, pathSegments?: string[]) {
  const requestOrigin = req.nextUrl.origin;
  const joined = pathSegments?.length ? pathSegments.join('/') : '';
  const localPath = joined ? `/admin/${joined}` : '/admin/login.php';

  let url = toUpstream(localPath, req.nextUrl.search);
  const method = req.method;
  const body =
    method !== 'GET' && method !== 'HEAD' ? Buffer.from(await req.arrayBuffer()) : undefined;

  let cookieJar = req.headers.get('cookie') || '';
  const accumulatedSetCookies: string[] = [];
  const seen = new Set<string>();

  for (let hop = 0; hop < 8; hop += 1) {
    if (seen.has(`${method}:${url}`) && hop > 0) break;
    seen.add(`${hop === 0 ? method : 'GET'}:${url}`);

    const headers = filterRequestHeaders(req.headers, cookieJar || undefined);
    // Avoid compressed bodies we can't easily handle
    headers.set('accept-encoding', 'identity');

    const upstream = await fetch(url, {
      method: hop === 0 ? method : 'GET',
      headers,
      body: hop === 0 ? body : undefined,
      redirect: 'manual',
    });

    const newCookies = collectSetCookies(upstream.headers);
    if (newCookies.length) {
      accumulatedSetCookies.push(...newCookies);
      cookieJar = cookieHeaderFromSetCookies(newCookies, cookieJar);
    }

    if (upstream.status >= 300 && upstream.status < 400) {
      const loc = upstream.headers.get('location');
      if (!loc) break;

      const nextUpstream = resolveUpstreamLocation(loc, url);
      if (!nextUpstream || !nextUpstream.includes('/admin')) {
        // Fall back: relative sibling under /admin/
        const fallback = new URL(loc, url.endsWith('/') ? url : url.replace(/[^/]+$/, ''));
        if (fallback.pathname.startsWith('/admin')) {
          // After POST login, send the browser to the local dashboard with session cookies
          if (hop === 0 && method === 'POST') {
            const localTarget = upstreamToLocalPath(fallback.toString(), requestOrigin);
            const res = NextResponse.redirect(localTarget, 303);
            for (const c of accumulatedSetCookies) {
              res.headers.append('set-cookie', localizeSetCookie(c));
            }
            return res;
          }
          url = fallback.toString();
          continue;
        }
        break;
      }

      // After successful login POST, prefer browser redirect so cookies stick cleanly
      if (hop === 0 && method === 'POST') {
        const localTarget = upstreamToLocalPath(nextUpstream, requestOrigin);
        const res = NextResponse.redirect(localTarget, 303);
        for (const c of accumulatedSetCookies) {
          res.headers.append('set-cookie', localizeSetCookie(c));
        }
        return res;
      }

      url = nextUpstream;
      continue;
    }

    const outHeaders = filterResponseHeaders(upstream.headers, accumulatedSetCookies);
    const contentType = upstream.headers.get('content-type') || '';
    if (contentType.includes('text/html')) {
      const html = localizeHtml(await upstream.text(), requestOrigin);
      return new Response(html, { status: upstream.status, headers: outHeaders });
    }

    return new Response(await upstream.arrayBuffer(), {
      status: upstream.status,
      headers: outHeaders,
    });
  }

  return new Response('Admin proxy failed', { status: 502 });
}

type Ctx = { params: Promise<{ path?: string[] }> };

export async function GET(req: NextRequest, ctx: Ctx) {
  const { path } = await ctx.params;
  return proxy(req, path);
}

export async function POST(req: NextRequest, ctx: Ctx) {
  const { path } = await ctx.params;
  return proxy(req, path);
}

export async function PUT(req: NextRequest, ctx: Ctx) {
  const { path } = await ctx.params;
  return proxy(req, path);
}

export async function PATCH(req: NextRequest, ctx: Ctx) {
  const { path } = await ctx.params;
  return proxy(req, path);
}

export async function DELETE(req: NextRequest, ctx: Ctx) {
  const { path } = await ctx.params;
  return proxy(req, path);
}

export async function HEAD(req: NextRequest, ctx: Ctx) {
  const { path } = await ctx.params;
  return proxy(req, path);
}
