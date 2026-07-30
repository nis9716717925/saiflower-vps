# @saiflower/server

Express REST API for the SaiFlower migration.

## Phase 5

APIs use **Prisma → Supabase PostgreSQL**.

```bash
# Put Supabase URI in packages/prisma/.env and apps/server/.env
npm run db:load
npm run db:verify
npm run dev -w @saiflower/server
```

See `docs/MIGRATION_PHASE_5.md`.

## Run

```bash
cp apps/server/.env.example apps/server/.env
npm install
npm run build -w @saiflower/shared
npm run generate -w @saiflower/prisma
npm run dev -w @saiflower/server
```

## Rules

- Never change business logic vs PHP without explicit approval.
- Checkout stays `whatsapp_confirm` (production parity).
- Do not use `/api` greenfield `api_*` schema — use `@saiflower/prisma` after Phase 4 introspection.
