# Phase 4 Migration Report — MySQL → Prisma / PostgreSQL Schema

**Date:** 2026-07-28  
**Status:** Complete (schema + converted dump ready; Supabase load = Phase 5)

## Summary

Production MySQL dump `u977002836_Saiflower999` was converted to PostgreSQL DDL + data and a full Prisma schema that **preserves all 35 legacy tables**, integer IDs, indexes, uniques, and foreign keys. No redesign to unified `Product` / `api_*` models.

## Source → artifacts

| Artifact | Path |
|----------|------|
| PG schema DDL | `tools/mysql-to-pg/01_schema_postgresql.sql` |
| PG data INSERTs (~3MB) | `tools/mysql-to-pg/02_data_postgresql.sql` |
| One-shot load | `tools/mysql-to-pg/saiflower_supabase_full.sql` |
| Converter | `tools/mysql-to-pg/convert_dump.py` |
| Prisma schema | `packages/prisma/schema.prisma` (35 models) |
| Prisma migration | `packages/prisma/migrations/20260728000000_init_legacy_schema/` |

## Tables migrated (35)

`addons`, `admin_tokens`, `admin_users`, `blogs`, `cakes`, `cake_variants`, `categories`, `comments`, `customers`, `dynamic_pages`, `events`, `faqs`, `flowers`, `flower_images`, `flower_variants`, `gallery`, `gifts`, `gift_variants`, `global_pricing`, `homepage_circles`, `homepage_sections`, `homepage_section_items`, `homepage_slides`, `leads`, `occasions`, `orders`, `pricing_log`, `products`, `product_occasions`, `promo_codes`, `reviews`, `seo_meta`, `settings`, `tags`, `wishlist`

## Preserved

- Integer primary keys + identity sequences starting at MySQL `AUTO_INCREMENT` values  
- Unique keys (slugs, emails, promo codes, wishlist tuple, etc.)  
- FKs: cake/gift variants, homepage section items, flower images/variants, admin tokens, wishlist→customers  
- Enums mapped: order status, promo discount types, comment status, product_occasions type  

## Database changes (target Postgres)

- MySQL types → Postgres (`tinyint`→`smallint`, `datetime`→`timestamptz`, enums as PG ENUMs)
- No intentional column renames; Prisma uses `@map` for snake_case columns

## API alignment fixes (this phase)

Phase 3 services adjusted to match dump columns (so they work on Postgres after cutover):

- `reviews.comment` → `reviews.review_text` (alias `comment` in API responses)
- `orders.delivery_date` stores date only (PG `date` type; time stays in WhatsApp message)
- Google signup uses empty string password (column `NOT NULL` in dump)

## Runtime note

- **Phase 3 Express** still queries **legacy MySQL** via `mysql2` (live Hostinger).
- **Phase 4** delivers the Prisma/Postgres schema + import scripts.
- **Phase 5** applies `saiflower_supabase_full.sql` (or migrate + data) to Supabase and switches `DATABASE_URL`; then services can move from mysql2 → Prisma.

## Apply to Supabase (Phase 5)

```bash
# Set DATABASE_URL to Supabase connection string (use pooler as needed)
psql "$DATABASE_URL" -f tools/mysql-to-pg/saiflower_supabase_full.sql
npm run db:generate
```

Or:

```bash
npm run migrate:deploy -w @saiflower/prisma
psql "$DATABASE_URL" -f tools/mysql-to-pg/02_data_postgresql.sql
```

## Testing results

- `npm run generate -w @saiflower/prisma` — success  
- Schema models count = SQL tables count = **35**  
- Live Supabase apply not run in this phase (needs your project credentials)

## Pending (Phase 5+)

- [ ] Create Supabase project + set `DATABASE_URL` / pooler URL  
- [ ] Load full SQL dump; verify row counts vs MySQL  
- [ ] Switch Express data layer from mysql2 → Prisma  
- [ ] Connection pooling (Supabase pooler / PgBouncer)  
- [ ] Re-export fresh dump if production drifted since `u977002836_Saiflower999` snapshot  
