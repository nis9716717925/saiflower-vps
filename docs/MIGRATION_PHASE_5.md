# Phase 5 Migration Report — Supabase PostgreSQL Cutover

**Date:** 2026-07-28  
**Status:** Application cutover complete (Prisma). **Data load awaits your Supabase `DATABASE_URL`.**

## Summary

Express APIs now read/write **Supabase PostgreSQL via Prisma** (legacy 35-table schema). The `mysql2` data path was removed. Converted dump + Node loader are ready to import production data with no redesign.

## What changed

| Area | Change |
|------|--------|
| Data layer | All services + auth middleware → `@saiflower/prisma` |
| Removed | `apps/server/src/db/mysql.ts` |
| Health | `/health` reports `database` + `tableCounts` when up |
| Loader | `tools/mysql-to-pg/load-to-supabase.mjs` (no `psql` required) |
| Verify | `tools/mysql-to-pg/verify-counts.mjs` |
| npm scripts | `npm run db:load`, `npm run db:verify` |

## Files

- Services rewritten under `apps/server/src/services/*` and `middleware/auth.ts` ([Prisma rewrite](508ee3b7-84a4-4ff2-afab-9c6f0d6ab842))
- `apps/server/src/db/client.ts` — Prisma helper + `tableCounts`
- `apps/server/src/config/index.ts` — Postgres `DATABASE_URL` only
- `tools/mysql-to-pg/load-to-supabase.mjs`
- `tools/mysql-to-pg/verify-counts.mjs`
- `docs/MIGRATION_PHASE_5.md`

## Database / data

| Step | Status |
|------|--------|
| Schema+data SQL ready (`saiflower_supabase_full.sql`) | Done (Phase 4) |
| Prisma models (35) | Done |
| App uses Prisma | Done |
| Load into **your** Supabase project | **Blocked — no Supabase credentials in env** |

Local env still points at placeholder `postgresql://postgres:postgres@localhost:5432/saiflower` (no Docker/Postgres on this machine).

## How to finish the data move (you)

1. Create a Supabase project (or use existing).
2. Copy the **Database URI** (Settings → Database). Prefer **direct** host port `5432` for the bulk load.
3. Set it in both:
   - `packages/prisma/.env`
   - `apps/server/.env`
4. Run:

```bash
npm run db:load
npm run db:verify
npm run db:generate
npm run dev:server
# GET http://localhost:4000/health  → database: up + tableCounts
```

5. For app runtime under load, switch `DATABASE_URL` to the **pooler** URI (port `6543`, `?pgbouncer=true`) if Supabase recommends it — keep direct URL for migrations/loads.

## API changes

None to route shapes. Internals now hit Postgres. Behavioral alignments from Phase 4 remain (`review_text`, date-only `delivery_date`).

## Testing results

- `npm run typecheck -w @saiflower/server` — pass  
- Prisma generate — pass  
- Live Supabase import — **pending your `DATABASE_URL`**

## Pending

- [ ] Paste Supabase `DATABASE_URL` and run `npm run db:load`
- [ ] Confirm row counts vs MySQL dump expectations (flowers ~300+, dynamic_pages ~200+, etc.)
- [ ] Optional: re-dump MySQL if production drifted since Phase 4 snapshot
- [ ] Phase 6: Next.js page ports against this API

## Rollback

PHP + Hostinger MySQL remain untouched in the repo root. Keep serving production from PHP until Phase 8 cutover.
