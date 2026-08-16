import { NextResponse } from 'next/server';

const API_BASE =
  process.env.NEXT_PUBLIC_API_PROXY_TARGET ??
  process.env.API_INTERNAL_URL ??
  'http://127.0.0.1:4000';

/** 301 legacy ?id= product URLs to canonical /flowers/:slug paths. */
export async function GET(
  _request: Request,
  context: { params: Promise<{ type: string; id: string }> },
) {
  const { type, id } = await context.params;
  const numericId = Number(id);
  if (!Number.isFinite(numericId) || numericId < 1) {
    return NextResponse.redirect(new URL('/flowers', process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com'), 302);
  }

  try {
    const res = await fetch(`${API_BASE}/api/v1/products/${encodeURIComponent(type)}/id/${numericId}`, {
      cache: 'no-store',
    });
    const json = (await res.json()) as { success?: boolean; data?: { slug?: string; type?: string } };
    const slug = json.data?.slug;
    const productType = json.data?.type || type;
    if (!res.ok || !slug) {
      const fallback =
        productType === 'cake' ? '/cakes' : productType === 'gift' ? '/gifts' : '/flowers';
      return NextResponse.redirect(
        new URL(fallback, process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com'),
        302,
      );
    }

    const prefix =
      productType === 'cake' ? 'cakes' : productType === 'gift' ? 'gifts' : 'flowers';
    return NextResponse.redirect(
      new URL(`/${prefix}/${slug}`, process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com'),
      301,
    );
  } catch {
    return NextResponse.redirect(new URL('/flowers', process.env.NEXT_PUBLIC_SITE_URL ?? 'https://saiflower.com'), 302);
  }
}
