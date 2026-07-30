# MySQL → PostgreSQL (Supabase) conversion

Source dump: `u977002836_Saiflower999 (2).sql` (35 tables).

## Outputs

| File | Purpose |
|------|---------|
| `01_schema_postgresql.sql` | DDL: enums, tables, PKs, uniques, indexes, FKs, identity |
| `02_data_postgresql.sql` | All INSERT data + sequence sync |
| `saiflower_supabase_full.sql` | Schema + data (one-shot load) |
| `../../packages/prisma/schema.prisma` | Prisma models mapped to legacy tables |
| `../../packages/prisma/migrations/20260728000000_init_legacy_schema/migration.sql` | Prisma migration |

## Apply to Supabase

This network is **IPv4-only**. Use the **Session pooler** URI (not `db.*.supabase.co`, which is IPv6-only).

```bash
# Example (region/cluster must match your project — from Dashboard → Connect):
# postgresql://postgres.PROJECT_REF:PASSWORD@aws-N-REGION.pooler.supabase.com:5432/postgres

npm run db:load
npm run db:verify
```

## Preserved

- All 35 table names and columns
- Integer primary keys + identity sequences at MySQL AUTO_INCREMENT values
- Unique constraints and secondary indexes
- Foreign keys: `cake_variants`, `gift_variants`, `homepage_section_items`
- Soft Prisma relations: `flower_images`, `flower_variants`, `admin_tokens`, `wishlist`
- Row data from the dump
