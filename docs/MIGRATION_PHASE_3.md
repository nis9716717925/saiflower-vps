# Phase 3 Migration Report — Express Business Logic Port

**Date:** 2026-07-28  
**Status:** Complete (API logic ported against legacy MySQL; Prisma still Phase 4)

## Summary

PHP storefront business logic is now exposed as REST endpoints under `apps/server` (`/api/v1`). Behavior mirrors production: multi-table catalog, session-equivalent cart (JWT + `X-Guest-Id`), surge pricing, promo codes, Google Distance Matrix shipping, WhatsApp-confirm checkout, wishlist/reviews/auth.

## Database

- **Runtime:** `mysql2` pool → legacy MySQL (same tables as PHP).
- **Not used yet:** `@saiflower/prisma` / Postgres (Phase 4 introspection + Phase 5 Supabase).
- Configure via `MYSQL_URL` or `MYSQL_HOST` / `MYSQL_USER` / `MYSQL_PASSWORD` / `MYSQL_DATABASE`.

## API map (PHP → Express)

| PHP | Express |
|-----|---------|
| `login.php` / `register.php` / `verify.php` | `POST /auth/login`, `/register`, `GET\|POST /auth/verify` |
| `actions/google_auth.php` | `POST /auth/google` |
| `profile.php` | `GET\|PATCH /auth/me` |
| `flowers.php` / `cakes.php` / `gifts.php` | `GET /products?type=` |
| `*-detail.php` | `GET /products/:type/:slug` |
| `ajax_search.php` (products) | `GET /search?q=` |
| `cart.php` | `GET\|POST\|PATCH\|DELETE /cart` |
| promo apply | `POST /cart/coupon`, `POST /coupons/validate` |
| `actions/calculate_shipping.php` | `POST /shipping/calculate` |
| `actions/submit_order.php` | `POST /checkout/place-order` |
| `actions/toggle_wishlist.php` | `POST /wishlist/toggle` |
| `actions/submit_review.php` | `POST /reviews` |
| categories / settings | `GET /categories`, `GET /settings` |

## Files changed / added

- `apps/server/src/db/mysql.ts`
- `apps/server/src/services/*` (auth, product, cart, coupon, shipping, order, wishlist, review, category, search, pricing)
- `apps/server/src/routes/*` (auth, product, cart, checkout, misc)
- `apps/server/src/middleware/auth.ts`
- `apps/server/src/utils/{jwt,catalog}.ts`
- `apps/server/src/config/index.ts` (MySQL + checkout/shipping)
- `apps/server/.env.example`
- `docs/MIGRATION_PHASE_3.md`

## Behavioral notes (intentional parity)

- Checkout remains **WhatsApp confirm** (`whatsappUrl` + `redirect` returned).
- Cart is in-memory per process (PHP used PHP session). Sticky across deploys needs Redis later — same API shape.
- Search returns product hits; collection/celebration taxonomy shortcuts still live in PHP includes (port with Phase 6 static data).
- Verification email: link logged when SMTP not configured (PHP `@mail` often fails similarly).
- Admin CMS APIs not in Phase 3 (scheduled with admin UI port).

## Testing

1. Copy `.env.example` → `.env` and set MySQL credentials (Hostinger remote MySQL or local dump).
2. `npm run typecheck -w @saiflower/server`
3. `npm run dev:server`
4. `GET /health` → `database: up` when MySQL reachable
5. Smoke: `GET /api/v1/products?type=flower`, `POST /api/v1/auth/login`, cart + shipping + place-order

## Pending

- [ ] Phase 4: introspect MySQL → Prisma Postgres models (drop mysql2 later)
- [ ] Phase 5: Supabase data migration
- [ ] Phase 6: Next.js pages calling these APIs (pixel-identical)
- [ ] Admin REST parity
- [ ] Persist carts (Redis) if multi-instance deploy
- [ ] Port collection/celebration search shortcuts
