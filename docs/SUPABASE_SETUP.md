# Supabase cutover

SaiFlower uses Supabase only as PostgreSQL. The browser does not use the
Supabase JavaScript client; all application data continues to flow through the
Express API and Prisma.

## Required Supabase details

From Supabase Dashboard → **Connect**, copy:

1. Transaction pooler URI (port `6543`) for `DATABASE_URL`.
2. Session pooler URI (port `5432`) for `DIRECT_URL`.
3. The database password created with the project.

Use the session pooler rather than `db.PROJECT_REF.supabase.co` when the VPS has
IPv4-only networking. URL-encode special characters in the password. The anon
key, service-role key, project API URL, and Storage credentials are not needed
for the current application.

## VPS environment

Put identical database URLs in:

- `packages/prisma/.env`
- `apps/server/.env`

Remove stale database URLs from the repository-root `.env`, or make them
identical. Migration tools stop with a clear error if env files disagree.

```env
DATABASE_URL="postgresql://postgres.PROJECT_REF:PASSWORD@REGION.pooler.supabase.com:6543/postgres?pgbouncer=true&connection_limit=10"
DIRECT_URL="postgresql://postgres.PROJECT_REF:PASSWORD@REGION.pooler.supabase.com:5432/postgres?sslmode=require"
```

## First import (new empty Supabase project only)

```bash
npm install
npm run db:generate
npm run db:supabase:setup
npm run diagnose
```

The setup command applies the schema, enables RLS, imports the committed
PostgreSQL data file, and verifies row counts. Do not run the data import a
second time after it succeeds.

After successful verification:

```bash
npm run build
pm2 restart all --update-env
pm2 save
```

Expected minimum verification: `flowers` and `settings` are non-zero.
