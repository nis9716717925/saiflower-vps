# Phase 7 Migration Report — FE↔API Hardening

**Date:** 2026-07-28  
**Status:** Complete for P0/P1 commerce + auth wiring. SEO landings / coupons / reviews polish remain Phase 8.

## Summary

Hardened the Next.js ↔ Express contract so checkout, auth, cart, search, wishlist, and profile flows work end-to-end against Supabase-backed APIs. Fixes came from the Phase 7 audit ([FE-API audit](c19b726e-429a-4bea-83da-0e5d8b5883ac)).

## Fixes shipped

### P0 (broken flows)

| Issue | Fix |
|-------|-----|
| Checkout `shippingReady` stale after `await` | `calculateShipping()` returns fee/ready; submit gates on that result |
| Place-order address missing `India` vs shipping calc | `buildDeliveryAddress()` appends `India` to match `calculateShippingParts` |
| No `/verify` page | `apps/web/src/app/verify/page.tsx` → `GET /auth/verify?token=` |
| Buy now skipped add-to-cart | PDP buy now adds then navigates to `/checkout` |

### P1 (hardening)

| Area | Change |
|------|--------|
| API client | `apiUrl()` for relative `/api/v1`; `ApiError` + validation `errors`; 401 → `POST /auth/refresh` retry |
| CORS | `exposedHeaders: ['X-Guest-Id']` |
| Cart count | Server `count` = sum of qty (aligned with badge) |
| Login/register | `refreshCart()` after login; Google GIS button when `NEXT_PUBLIC_GOOGLE_CLIENT_ID` set |
| Search | `search-results` uses `apiUrl()`; `/ajax_search.php` rewrite already in place |
| Wishlist | List + toggle wired to `/wishlist` |
| Profile | Orders via `GET /orders/mine` |
| PDP | Related products + gallery thumbs |
| ProductCard | Wishlist heart → `/wishlist/toggle` |
| Env | `apps/web/.env.example`; Maps/Google notes on server example |

## Key files

- `apps/web/src/lib/api.ts`
- `apps/web/src/app/checkout/page.tsx`
- `apps/web/src/app/verify/page.tsx`
- `apps/web/src/app/login/page.tsx`, `register/page.tsx`
- `apps/web/src/components/auth/GoogleSignInButton.tsx`
- `apps/web/src/components/shop/ProductDetailView.tsx`, `ProductCard.tsx`
- `apps/web/src/app/wishlist/page.tsx`, `profile/page.tsx`
- `apps/server/src/app.ts`, `services/cart.service.ts`

## Env checklist

```bash
# apps/web/.env.local
NEXT_PUBLIC_API_URL=/api/v1
NEXT_PUBLIC_API_PROXY_TARGET=http://localhost:4000
NEXT_PUBLIC_GOOGLE_CLIENT_ID=   # optional

# apps/server/.env
GOOGLE_MAPS_API_KEY=            # required for checkout shipping
OAUTH_GOOGLE_CLIENT_ID=         # must match web client id
PUBLIC_SITE_URL=http://localhost:3000   # verify email links
```

## Testing

- `npm run typecheck -w @saiflower/web` — pass  
- `npm run typecheck -w @saiflower/server` — pass  

Manual smoke (with Maps key):

1. Add flower → cart → checkout → confirm shipping line → place order → WhatsApp  
2. Register → open verify link (dev token in register response) → login → cart badge persists  
3. Wishlist heart while logged in → `/wishlist`  
4. Search suggest while typing in header  

## Deferred to Phase 8

- [ ] Coupon apply UI  
- [ ] Reviews API on PDP  
- [ ] Profile `PATCH /auth/me` form  
- [ ] Collection / occasion / dynamic_pages SEO landings  
- [ ] CatNav mega menus  
- [ ] Pixel QA vs production + cutover  

## Rollback

PHP production unchanged. Next + Express remain opt-in via `npm run dev:web` / `dev:server`.
