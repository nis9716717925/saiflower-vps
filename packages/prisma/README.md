# @saiflower/prisma

Prisma client for SaiFlower’s **Supabase PostgreSQL** database. Legacy table
and column names are preserved.

## Setup

1. Copy `.env.example` → `.env`
2. Copy both connection strings from Supabase Dashboard → **Connect**:

```env
DATABASE_URL="postgresql://postgres.PROJECT_REF:PASSWORD@REGION.pooler.supabase.com:6543/postgres?pgbouncer=true&connection_limit=10"
DIRECT_URL="postgresql://postgres.PROJECT_REF:PASSWORD@REGION.pooler.supabase.com:5432/postgres?sslmode=require"
```

`DATABASE_URL` is the transaction pooler used by the running API. `DIRECT_URL`
is the session pooler used by Prisma migrations and the initial data import.
The session pooler works from IPv4-only VPS networks.

3. Apply migrations and generate the client:

```bash
npm run migrate:deploy -w @saiflower/prisma
npm run generate -w @saiflower/prisma
```

## Notes

- Use committed migrations for production; do not use `prisma db push`.
- The initial production data load is handled by `tools/mysql-to-pg`.
- Never use Supabase anon/service-role API keys as database passwords.
- The Supabase RLS migration intentionally exposes no tables through the
  browser REST API; the Express API remains the only application data layer.
