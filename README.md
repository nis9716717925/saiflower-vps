# SaiFlower monorepo

Modern stack migration of [saiflower.com](https://saiflower.com) with **pixel-identical UI** and **behavior-preserving** business logic.

## Structure

```
apps/
  web/       Next.js 15 (App Router) + React 19 — storefront
  server/    Express REST API — PHP logic port
packages/
  shared/    Types, URL helpers, constants
  prisma/    Prisma → Supabase PostgreSQL (legacy tables)
```

Legacy PHP site remains at the repo root until cutover. The old `api/` folder is deprecated (see `api/DEPRECATED.md`).

## Quick start

```bash
npm install
cp apps/server/.env.example apps/server/.env
cp apps/web/.env.example apps/web/.env.local
cp packages/prisma/.env.example packages/prisma/.env   # after creating it
npm run db:generate
npm run dev:server   # :4000
npm run dev:web      # :3000
```

## Migration phases

| Phase | Status |
|-------|--------|
| 1 Audit | Done |
| 2 Architecture | Done — see `docs/MIGRATION_PHASE_2.md` |
| 3 Express business logic | Done — see `docs/MIGRATION_PHASE_3.md` |
| 4 Prisma from legacy MySQL | Done — see `docs/MIGRATION_PHASE_4.md` |
| 5 Supabase data move | Done — see `docs/MIGRATION_PHASE_5.md` |
| 6 Next.js page ports | Core storefront done — see `docs/MIGRATION_PHASE_6.md` |
| 7 Wire FE↔API | Done — see `docs/MIGRATION_PHASE_7.md` |
| 8 QA / cutover | Soft-launch ready — see `docs/MIGRATION_PHASE_8.md` + `docs/CUTOVER_RUNBOOK.md` |

## Non-negotiables

- No UI redesign
- Same URLs (or 301 redirects)
- Checkout stays WhatsApp-confirm until explicitly approved otherwise
- Prisma models must mirror legacy tables — not the old `api_*` schema
