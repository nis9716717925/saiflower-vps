# SaiFlower monorepo

Next.js storefront + Express API + Prisma for [saiflower.com](https://saiflower.com).

## Structure

```
apps/
  web/       Next.js (App Router) — storefront (+ admin reverse-proxy)
  server/    Express REST API
packages/
  shared/    Types, URL helpers, constants
  prisma/    Prisma → Supabase PostgreSQL
assets/      Shared CSS / JS / images (linked into apps/web/public/assets)
```

Admin UI is served via reverse-proxy to `ADMIN_ORIGIN` (production by default). There is no local PHP stack.

## Quick start

```bash
npm install
cp apps/server/.env.example apps/server/.env
cp apps/web/.env.example apps/web/.env.local
cp packages/prisma/.env.example packages/prisma/.env
npm run db:generate
npm run dev:server   # :4000
npm run dev:web      # :3000
```

## Scripts

| Script | Purpose |
|--------|---------|
| `npm run smoke` | Hit core routes against a running web server |
| `npm run db:load` / `db:verify` | MySQL→Postgres helpers under `tools/mysql-to-pg` |

## Notes

- Storefront URLs match the former PHP paths (rewrites / redirects in `apps/web`).
- Checkout stays WhatsApp-confirm until explicitly changed.
- Media often loads from `NEXT_PUBLIC_MEDIA_ORIGIN` (production CDN/host).
- Supabase database setup: [`docs/SUPABASE_SETUP.md`](docs/SUPABASE_SETUP.md).
