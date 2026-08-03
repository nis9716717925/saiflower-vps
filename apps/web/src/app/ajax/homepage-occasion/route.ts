import { NextResponse } from 'next/server';
import { fetchLandingBouquets } from '@/lib/bouquet';
import {
  discountPercent,
  formatInr,
  productHref,
  resolveImageSrc,
} from '@/lib/images';
import type { Product } from '@/lib/types';

export const dynamic = 'force-dynamic';

const OCCASION_TABS: Record<string, { cta: string; link: string; q: string }> = {
  birthday: { cta: 'View All Birthday Gifts', link: '/occasion/birthday', q: 'birthday' },
  anniversary: {
    cta: 'View All Anniversary Gifts',
    link: '/occasion/anniversary',
    q: 'anniversary',
  },
  love: { cta: 'View All Love Gifts', link: '/occasion/love', q: 'rose' },
  wedding: { cta: 'View All Wedding Gifts', link: '/occasion/wedding', q: 'wedding' },
  congratulations: {
    cta: 'View All Congratulations Gifts',
    link: '/occasion/congratulations',
    q: 'congratulation',
  },
  sympathy: { cta: 'View All Sympathy Gifts', link: '/occasion/sympathy', q: 'lily' },
};

function escapeHtml(value: string): string {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function renderCards(products: Product[]): string {
  if (products.length === 0) {
    return '<p class="hp-occasion-empty">No products found for this occasion yet. <a href="/flowers">Browse all flowers</a>.</p>';
  }

  return products
    .map((p) => {
      const href = escapeHtml(p.url ?? productHref(p.type, p.slug));
      const img = escapeHtml(resolveImageSrc(p.image));
      const name = escapeHtml(p.name);
      const discount = discountPercent(p.price, p.originalPrice);
      const rating = (p.rating && p.rating > 0 ? p.rating : 4.8).toFixed(1);
      const orig = p.originalPrice ?? 0;
      const badge =
        discount > 0 ? `<span class="hp-occasion-card__badge">${discount}% OFF</span>` : '';
      const old =
        orig > p.price ? `<span class="hp-price-old">${escapeHtml(formatInr(orig))}</span>` : '';

      return `<article class="hp-occasion-card snap-start">
                <a href="${href}" class="hp-occasion-card__media">
                    <img src="${img}" alt="${name}" width="280" height="350" loading="lazy" decoding="async">
                    ${badge}
                    <span class="hp-occasion-card__trust"><i class="fas fa-shield-halved" aria-hidden="true"></i> Secure checkout</span>
                </a>
                <div class="hp-occasion-card__body">
                    <a href="${href}" class="hp-occasion-card__title">${name}</a>
                    <div class="hp-occasion-card__rating" aria-label="Rated ${rating} out of 5">
                        <span class="hp-stars" aria-hidden="true"><i class="fas fa-star"></i></span>
                        <span>${rating}</span>
                        <span class="hp-muted">· Verified buyers</span>
                    </div>
                    <div class="hp-occasion-card__price">
                        <span class="hp-price-current">${escapeHtml(formatInr(p.price))}</span>
                        ${old}
                    </div>
                    <a href="${href}" class="hp-occasion-card__cta">Buy Now</a>
                </div>
            </article>`;
    })
    .join('');
}

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const key = (searchParams.get('occasion') || '').toLowerCase().replace(/[^a-z0-9_-]/g, '');
  const tab = OCCASION_TABS[key];

  if (!key) {
    return NextResponse.json({ ok: false, message: 'Missing occasion' }, { status: 400 });
  }
  if (!tab) {
    return NextResponse.json({ ok: false, message: 'Occasion not found' }, { status: 404 });
  }

  try {
    const items = await fetchLandingBouquets({
      limit: 10,
      search: tab.q,
      sort: 'bestseller',
    });
    return NextResponse.json({
      ok: true,
      html: renderCards(items),
      cta: tab.cta,
      link: tab.link,
    });
  } catch {
    try {
      const fallback = await fetchLandingBouquets({ limit: 10, sort: 'bestseller' });
      return NextResponse.json({
        ok: true,
        html: renderCards(fallback),
        cta: tab.cta,
        link: tab.link,
      });
    } catch {
      return NextResponse.json({
        ok: true,
        html: renderCards([]),
        cta: tab.cta,
        link: tab.link,
      });
    }
  }
}
