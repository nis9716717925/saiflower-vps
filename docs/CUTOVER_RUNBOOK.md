# SaiFlower Cutover Runbook

**Status:** Soft-launch ready (Next + Express on staging). PHP remains production on `saiflower.com` until hard DNS cutover is explicitly approved.

## Topology (soft launch)

```
Internet
  ├─ saiflower.com          → PHP (Hostinger) — production SEO, admin, uploads
  └─ staging.example.com    → Nginx
        ├─ /api/*, /health  → Express :4000  (single process)
        ├─ /                → Next.js :3000
        ├─ /admin/*         → keep pointing at PHP (do not serve Next admin)
        └─ /uploads/*       → PHP/media origin (or Next rewrite to MEDIA_ORIGIN)
```

Hard DNS flip to Next is **out of this runbook’s default path** until SEO landings (`dynamic_pages`), sitemap, and Redis carts are ready.

## Hard constraints

1. **Single Express instance** — carts are in-memory (`Map`). Multiple processes/nodes will drop carts.
2. **`GOOGLE_MAPS_API_KEY` required** for checkout distance shipping.
3. **Staging must stay noindex** — leave `ALLOW_INDEXING` unset/`false` until Next owns the public hostname.
4. **Admin stays PHP** — `/admin/*` never routes to Next in soft launch.
5. **Checkout mode** remains `whatsapp_confirm`.

## Env matrix

### Express (`apps/server/.env`)

| Var | Soft launch |
|-----|-------------|
| `DATABASE_URL` | Supabase **pooler** URI |
| `JWT_ACCESS_SECRET` / `JWT_REFRESH_SECRET` | Strong unique secrets |
| `CORS_ORIGINS` | Staging + localhost origins |
| `PUBLIC_SITE_URL` | Staging URL (verify email links) |
| `GOOGLE_MAPS_API_KEY` | **Required** |
| `OAUTH_GOOGLE_CLIENT_ID` | Match web client id |
| `WHATSAPP_E164` | `918802004527` |
| `CHECKOUT_MODE` | `whatsapp_confirm` |

### Next (`apps/web/.env.local`)

| Var | Soft launch |
|-----|-------------|
| `NEXT_PUBLIC_SITE_URL` | Staging URL |
| `NEXT_PUBLIC_API_URL` | `/api/v1` (same-origin proxy) |
| `NEXT_PUBLIC_API_PROXY_TARGET` | `http://127.0.0.1:4000` |
| `NEXT_PUBLIC_MEDIA_ORIGIN` | **Legacy PHP host only** — do not set to the Next.js domain after cutover. See [`docs/UPLOADS_DEPLOY.md`](UPLOADS_DEPLOY.md). |
| `NEXT_PUBLIC_GOOGLE_CLIENT_ID` | Optional GIS |
| `ALLOW_INDEXING` | omit / `false` |

### Prisma (`packages/prisma/.env`)

Same `DATABASE_URL` as server (pooler for runtime).

## Preflight

```bash
npm run db:verify
npm run typecheck
npm run smoke:routes
SMOKE_WEB_BASE=https://staging.example.com SMOKE_API_BASE=https://staging.example.com npm run smoke
```

Expect: health `ok`, `database:up`, `checkoutMode:whatsapp_confirm`, legal routes 200, `robots.txt` Disallow `/` on staging.

## Soft-launch steps

1. Deploy monorepo to VPS; `npm ci` && `npm run db:generate`.
2. Start Express (PM2/systemd) on `:4000` — **one** instance.
3. Start Next `npm run build:web && npm run start -w @saiflower/web` on `:3000`.
4. Configure Nginx (sketch below).
5. Point staging DNS → VPS.
6. Manual checkout: add flower → coupon optional → shipping calc → WhatsApp → confirm `/?order_success=1&oid=…` banner → row in Supabase `orders`.
7. Confirm PHP admin still works on production Hostinger.

## Nginx sketch

```nginx
server {
  server_name staging.example.com;

  location /api/ {
    proxy_pass http://127.0.0.1:4000;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Guest-Id $http_x_guest_id;
  }

  location /health {
    proxy_pass http://127.0.0.1:4000/health;
  }

  location /admin/ {
    # Prefer proxy to PHP Hostinger or keep admin only on production domain
    return 302 https://saiflower.com/admin/;
  }

  location / {
    proxy_pass http://127.0.0.1:3000;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
  }
}
```

## Rollback

1. Point staging DNS / proxy away from Next (or stop Nginx vhost).
2. Stop Express + Next processes.
3. Production PHP on Hostinger is unaffected.
4. Expect **in-memory carts lost** on Express restart — normal.

## Hard cutover (later — do not execute until ready)

Checklist before flipping `saiflower.com`:

- [ ] `dynamic_pages` / collection SEO landings on Next
- [ ] `sitemap.xml` parity
- [ ] Redis (or sticky) carts for multi-instance
- [ ] `ALLOW_INDEXING=true` + Search Console
- [ ] Admin decision (PHP subdomain vs Next port)
- [ ] Side-by-side pixel QA sign-off
- [ ] Final MySQL→Supabase refresh if production drifted

## Contacts / ops notes

- WhatsApp confirm number: `918802004527`
- Store address / shipping rate: see `@saiflower/shared` `SHIPPING` + server env
- Order emails currently log to console (`ADMIN_ORDER_EMAIL`) — WhatsApp is the customer channel
