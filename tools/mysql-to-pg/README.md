# MySQL → PostgreSQL conversion

The converter accepts a phpMyAdmin MySQL/MariaDB dump and produces SQL for
Supabase PostgreSQL. The current export contains 36 tables, including
`customer_addresses`.

## Outputs

| File | Purpose |
|------|---------|
| `01_schema_postgresql.sql` | DDL: enums, tables, PKs, uniques, indexes, FKs, identity |
| `02_data_postgresql.sql` | All INSERT data + sequence sync |
| `migration-output/01_schema_postgresql.sql` | DDL generated from the uploaded dump |
| `migration-output/02_data_postgresql.sql` | Converted production data (private, Git-ignored) |
| `migration-output/saiflower_postgresql_full.sql` | Schema + data for one-shot recovery (private, Git-ignored) |
| `../../packages/prisma/migrations/20260815000000_postgresql_baseline/migration.sql` | Version-controlled PostgreSQL baseline |

## Safe migration flow

```powershell
npm run db:convert -- "C:\secure\u977002836_Saiflower999.sql"
npm run db:migrate:deploy
npm run db:load
npm run db:verify
```

For a brand-new, empty Supabase project, the last three commands can be run as
`npm run db:supabase:setup`.

Set both Supabase URLs before running these commands:

- `DATABASE_URL`: transaction pooler on port 6543 for the application.
- `DIRECT_URL`: session pooler on port 5432 for migrate/load.

The loader prefers `DIRECT_URL`, avoiding transaction-pooler limitations during
the bulk import.

- All 36 table names and columns
- Integer primary keys + identity sequences at MySQL AUTO_INCREMENT values
- Unique constraints and secondary indexes
- Foreign keys, including `customer_addresses.customer_id`
- Soft Prisma relations: `flower_images`, `flower_variants`, `admin_tokens`, `wishlist`
- Row data from the dump

Never commit the source dump or files under `migration-output/`; they contain
customer details, password hashes, order history, and access tokens.
