# Phase 6 Migration Report — Next.js Storefront Page Ports

**Date:** 2026-07-28  
**Status:** Core storefront ported (layout + home + catalog + cart/checkout/auth). SEO landings / blog / admin remain for later passes.

## Summary

Next.js App Router now renders the Sai Flower storefront using **legacy CSS/JS from `/assets`** (junctioned into `apps/web/public`), Tailwind CDN boot matching PHP, and Express `/api/v1` for catalog, cart, checkout, and settings. **No redesign** — same class names and section order as PHP where ported.

## What shipped

| Area | Implementation |
|------|----------------|
| Assets | `public/assets` → `assets/`, `public/celebrations` → `celebrations/`, favicon |
| Layout | `SiteHeader` + `CatNav` + `SiteFooter` + `TailwindBoot` (primary from `/settings`) |
| API client | `src/lib/api.ts` — guest id, JWT, SSR proxy via `NEXT_PUBLIC_API_PROXY_TARGET` |
| Home | PHP section order: hero → promo → bestsellers → fav flowers → occasions → same-day → celebrations → relationships → luxury → gift finder → cities → about → stats → testimonials → FAQ/CTA |
| Catalog | `/flowers`, `/cakes`, `/gifts` + `[slug]` PDP (`ShopListing`, `ProductCard`, `ProductDetailView`) |
| Commerce | `/cart`, `/checkout` (distance shipping + WhatsApp place-order) |
| Auth | `/login`, `/register`, `/profile` shell |
| Search | `/search-results` |
| Content stubs | about, contact, gallery, events, blog, wishlist |
| Landing stubs | `/collection/[slug]`, `/relation/[slug]`, `/occasion/[slug]`, `/flower/[slug]`, `/celebration-calendar`, `/personalized`, `/flower-delivery-in-delhi` |

## Key files

- `apps/web/src/app/layout.tsx`, `page.tsx`
- `apps/web/src/components/layout/*`
- `apps/web/src/components/home/HomeSections.tsx`
- `apps/web/src/components/shop/*`
- `apps/web/src/lib/api.ts`, `types.ts`, `images.ts`
- `apps/web/.env.local` — `NEXT_PUBLIC_API_URL=/api/v1` (browser via Next rewrite)

## Env

```
NEXT_PUBLIC_SITE_URL=http://localhost:3000
NEXT_PUBLIC_API_URL=/api/v1
NEXT_PUBLIC_API_PROXY_TARGET=http://localhost:4000
NEXT_PUBLIC_MEDIA_ORIGIN=https://saiflower.com
```

`/uploads/*` rewrites to live media origin so images work without copying ~861 files locally.

## Testing

- `npm run typecheck -w @saiflower/web` — **pass**
- Run locally:
  ```bash
  npm run dev:server   # :4000 + Supabase DATABASE_URL
  npm run dev:web      # :3000
  ```
- Spot-check: `/`, `/flowers`, product PDP, add-to-cart → `/cart` → `/checkout`

## Intentionally deferred (Phase 7 / later Phase 6)

- [ ] CMS-driven homepage product sliders / fav-flower tiles from DB (static fallbacks now)
- [ ] Full occasion tab product carousel AJAX parity
- [ ] Blog posts, gallery, events, about/contact rich content from Postgres
- [ ] `dynamic_pages` catch-all SEO routes (~200)
- [ ] Collection / relation / personalized filtered catalogs
- [ ] CatNav mega menus (currently simplified row; PHP has full `lx-catnav__mega`)
- [ ] How It Works + map embed homepage sections
- [ ] Google OAuth UI wiring
- [ ] Admin panel
- [ ] Pixel QA vs production (side-by-side)

## Follow-ups from shell audit

- Search suggest: Next rewrite `/ajax_search.php` → `/api/v1/search` so legacy `search-suggest.js` works without PHP
- Cart badge already uses API qty sum via `AppProviders` (not PHP session)

## Rollback

PHP at repo root remains the production source of truth until Phase 8 cutover.
