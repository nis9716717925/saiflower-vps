# MySQL → PostgreSQL conversion

The converter accepts a phpMyAdmin MySQL/MariaDB dump and produces SQL for the
local PostgreSQL service on the Hostinger VPS. The current export contains 36
tables, including `customer_addresses`.

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

On Linux/VPS, give the converter the uploaded dump’s Linux path. `DATABASE_URL`
must point to PostgreSQL before running migrate/load/verify.

- All 36 table names and columns
- Integer primary keys + identity sequences at MySQL AUTO_INCREMENT values
- Unique constraints and secondary indexes
- Foreign keys, including `customer_addresses.customer_id`
- Soft Prisma relations: `flower_images`, `flower_variants`, `admin_tokens`, `wishlist`
- Row data from the dump

Never commit the source dump or files under `migration-output/`; they contain
customer details, password hashes, order history, and access tokens.
