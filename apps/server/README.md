# @saiflower/server

Express REST API for the SaiFlower migration.

## Database

APIs use **Prisma → Supabase PostgreSQL**.

```bash
# Put the Supabase DATABASE_URL and DIRECT_URL in both env files
npm run db:migrate:deploy
npm run db:load
npm run db:verify
npm run dev -w @saiflower/server
```

See `tools/mysql-to-pg/README.md`.

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
