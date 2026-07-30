# @saiflower/prisma

Prisma package for SaiFlower — legacy MySQL catalog on **Supabase PostgreSQL**.

## Status

Schema and initial migration generated from production dump
`u977002836_Saiflower999` (35 tables). Integer IDs, table names, indexes,
uniques, and foreign keys are preserved.

## Apply

```bash
# From repo root — one-shot schema + data into Supabase
psql "$DATABASE_URL" -f tools/mysql-to-pg/saiflower_supabase_full.sql

# Or: Prisma migrate (schema) then data
cd packages/prisma
npx prisma migrate deploy
psql "$DATABASE_URL" -f ../../tools/mysql-to-pg/02_data_postgresql.sql
npx prisma generate
```

## Scripts

| Script | Purpose |
|--------|---------|
| `npm run generate -w @saiflower/prisma` | Generate Prisma Client |
| `npm run migrate:deploy -w @saiflower/prisma` | Apply migrations |
| `npm run studio -w @saiflower/prisma` | Prisma Studio |

## Do not

- Import or revive `api/prisma/schema.prisma` (`api_*` greenfield models).
- Redesign to a unified `Product` model during migration.
