# DEPRECATED — legacy Express scaffold

This folder (`/api`) is the **pre-monorepo** Express + Prisma prototype.

It uses a **greenfield `api_*` schema** that does **not** match production MySQL tables (`flowers`, `cakes`, `customers`, …).

## Migration target

Use:

- `apps/server` — Express API
- `packages/prisma` — Prisma (legacy tables → Supabase Postgres)
- `packages/shared` — shared types / URLs
- `apps/web` — Next.js 15 storefront

Keep this folder only as reference for middleware/JWT patterns. Do not deploy it or push its schema to Supabase.
