# Phase 2 Migration Report — Architecture Scaffold

**Date:** 2026-07-28  
**Status:** Complete (scaffold only — no business logic / UI port yet)

## Summary

Created a clean monorepo so Next.js, Express, shared types, and Prisma can evolve without touching the live PHP site.

## Files / folders added

| Path | Purpose |
|------|---------|
| `package.json` (root workspaces) | npm workspaces orchestration |
| `.gitignore`, `.prettierrc`, `.nvmrc`, `tsconfig.base.json` | Tooling |
| `apps/web` | Next.js 15.5 + React 19 App Router |
| `apps/server` | Express API scaffold (Helmet, CORS, rate limit, XSS sanitize) |
| `packages/shared` | Types, URL helpers mirroring `.htaccess` |
| `packages/prisma` | Postgres Prisma stub (not `api_*` models) |
| `api/DEPRECATED.md` | Marks old Express/Prisma prototype as reference-only |
| `docs/MIGRATION_PHASE_2.md` | This report |
| `README.md` | Monorepo overview |

## Database changes

- None against production.
- Scaffold model only: `_schema_bootstrap` in `packages/prisma/schema.prisma`.
- Provider set to **PostgreSQL** (Supabase target). Real tables arrive in Phase 4 via introspection.

## API changes

- New mounts under `/api/v1` return **501** stubs for resources (auth, products, cart, checkout, …).
- `/health` returns OK + `checkoutMode: whatsapp_confirm`.
- Old `/api` package **not** wired into the monorepo.

## URL / SEO

- Next redirects: `/index.php`, `/occasions/:slug`, `/personalised*`, `/celebrations-calendar`, `*.php` strip.
- Middleware 301: `/flower-detail?slug=` → `/flowers/:slug` (and cake/gift/event).
- Rewrites: `/api/v1/*` → Express; `/uploads/*` → media origin (default production) so image URLs stay stable.

## Testing results

- Scaffold install / typecheck / health check (run after `npm install`).
- No pixel comparison yet (Phase 6).
- No PHP feature parity yet (Phase 3).

## Decisions applied

1. Discard `api_*` Prisma schema for migration target.
2. Checkout mode locked to WhatsApp confirm.
3. Monorepo lives in this repo alongside PHP.
4. Full MySQL dump still needed before Phase 4.

## Pending tasks (Phase 3+)

- [ ] Port PHP actions → Express services (auth, catalog, cart, shipping, orders)
- [ ] Obtain MySQL dump / remote read access for Prisma introspection
- [ ] Migrate data to Supabase Postgres
- [ ] Port pages pixel-identically (start: layout shell → home → listings → PDP)
- [ ] Admin panel parity
- [ ] Remove scaffold homepage; replace with `index.php` port
