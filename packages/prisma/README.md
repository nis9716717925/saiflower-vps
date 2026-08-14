# @saiflower/prisma

Prisma client for SaiFlower’s local **PostgreSQL** database on the Hostinger VPS.
Legacy table and column names are preserved.

## Setup

1. Copy `.env.example` → `.env`
2. Set `DATABASE_URL` for the PostgreSQL database:

```env
DATABASE_URL="postgresql://saiflower_app:PASSWORD@127.0.0.1:5432/saiflower?schema=public"
```

Keep PostgreSQL bound to localhost when the API runs on the same VPS. Do not
expose port 5432 publicly.

3. Apply migrations and generate the client:

```bash
npm run migrate:deploy -w @saiflower/prisma
npm run generate -w @saiflower/prisma
```

## Notes

- Use committed migrations for production; do not use `prisma db push`.
- The initial production data load is handled by `tools/mysql-to-pg`.
