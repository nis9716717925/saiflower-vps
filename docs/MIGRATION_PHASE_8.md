# Phase 8 Migration Report — Soft-launch QA & Cutover Prep

**Date:** 2026-07-28  
**Status:** Soft-launch ready. **Hard DNS cutover to Next is not executed** — PHP remains production until SEO/admin checklist is cleared (see runbook).

## Summary

Phase 8 ships the operational layer for a **staging soft launch**: cutover runbook, automated smoke checks, staging noindex, legal pages, order-success UX, coupon UI, and safer fallbacks for non-product flower slugs. Full pixel QA + production DNS flip remain gated.

Plan source: [Phase 8 audit](6f324853-0055-4ba8-a6b9-5526ae74bbc5).

## Deliverables

| Item | Path |
|------|------|
| Cutover runbook | `docs/CUTOVER_RUNBOOK.md` |
| Smoke harness | `tools/smoke/smoke.mjs`, `npm run smoke` |
| Route inventory | `tools/smoke/route-inventory.mjs`, `npm run smoke:routes` |
| Staging robots | `apps/web/src/app/robots.ts` + `X-Robots-Tag` in middleware |
| Legal pages | `/privacy`, `/terms`, `/legal`, `/delivery-policy`, `/refund-policy` |
| Order success banner | `OrderSuccessBanner` on layout (`?order_success=1&oid=`) |
| Cart coupons | Apply/remove via `/cart/coupon` |
| Flower slug fallback | Unknown `/flowers/:slug` → search results |

## Smoke results (local)

```
npm run typecheck -w @saiflower/web  → pass
npm run smoke:routes                 → 17 present / 7 stub / 2 dynamic / 14 missing (mostly SEO tooling)
npm run smoke                        → all checks passed (health, products, settings, storefront + legal URLs)
```

## Route inventory highlights

**Present:** home, flowers/cakes/gifts, cart, checkout, auth, verify, wishlist, profile, search, legal set.  
**Stub (soft OK):** about, contact, gallery, events, blog, personalized, celebration-calendar.  
**Missing (defer):** sitemap, faq, gallery-detail, blog-detail, grievance, PHP-only helpers.

## Soft-launch blockers addressed

1. Footer legal 404s → fixed  
2. Staging indexing → `ALLOW_INDEXING=false` default + robots Disallow `/`  
3. Order success query ignored → banner  
4. Coupon API unwired → cart UI  
5. `/flowers/roses` PDP collision → search fallback + home icon link updated  

## Still required before staging checkout works

- Set `GOOGLE_MAPS_API_KEY` on the server  
- Run **one** Express process only (in-memory cart)  
- Deploy per `docs/CUTOVER_RUNBOOK.md`

## Explicitly not cut over

- Production DNS (`saiflower.com`) still PHP  
- Admin panel still PHP  
- `dynamic_pages` SEO landings, sitemap, Redis carts, reviews UI, CatNav mega, pixel QA sign-off  

## Rollback

Stop staging Nginx/Next/Express; production PHP untouched. Carts on Express restart are lost by design.

## Next (hard cutover gate)

Follow the hard-cutover checklist at the bottom of `docs/CUTOVER_RUNBOOK.md`.
