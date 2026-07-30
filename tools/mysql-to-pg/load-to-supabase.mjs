#!/usr/bin/env node
/**
 * Load converted MySQL → Postgres dump into Supabase (or any Postgres).
 *
 * Usage:
 *   set DATABASE_URL=postgresql://postgres:...@db....supabase.co:5432/postgres
 *   node tools/mysql-to-pg/load-to-supabase.mjs
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import pg from 'pg';

const { Client } = pg;
const __dirname = path.dirname(fileURLToPath(import.meta.url));

async function main() {
  const databaseUrl = process.env.DATABASE_URL;
  if (!databaseUrl || !databaseUrl.startsWith('postgres')) {
    console.error('Set DATABASE_URL to your Supabase Postgres connection string first.');
    process.exit(1);
  }

  const sqlPath = process.argv[2]
    ? path.resolve(process.argv[2])
    : path.resolve(__dirname, 'saiflower_supabase_full.sql');

  if (!fs.existsSync(sqlPath)) {
    console.error('SQL file not found:', sqlPath);
    process.exit(1);
  }

  const sql = fs.readFileSync(sqlPath, 'utf8');
  console.log(`Loading ${path.basename(sqlPath)} (${(sql.length / 1024 / 1024).toFixed(2)} MB)...`);
  console.log('Target host:', databaseUrl.replace(/:[^:@/]+@/, ':****@'));

  const client = new Client({
    connectionString: databaseUrl,
    ssl: { rejectUnauthorized: false },
    connectionTimeoutMillis: 60_000,
  });

  await client.connect();
  const started = Date.now();
  try {
    await client.query("SET statement_timeout = '0'");
    await client.query("SET lock_timeout = '0'");
    await client.query(sql);
    console.log(`Load completed in ${((Date.now() - started) / 1000).toFixed(1)}s`);

    const counts = await client.query(`
      SELECT 'flowers' AS t, COUNT(*)::int AS c FROM flowers
      UNION ALL SELECT 'cakes', COUNT(*)::int FROM cakes
      UNION ALL SELECT 'gifts', COUNT(*)::int FROM gifts
      UNION ALL SELECT 'customers', COUNT(*)::int FROM customers
      UNION ALL SELECT 'orders', COUNT(*)::int FROM orders
      UNION ALL SELECT 'dynamic_pages', COUNT(*)::int FROM dynamic_pages
      UNION ALL SELECT 'blogs', COUNT(*)::int FROM blogs
      UNION ALL SELECT 'promo_codes', COUNT(*)::int FROM promo_codes
      UNION ALL SELECT 'wishlist', COUNT(*)::int FROM wishlist
      ORDER BY 1
    `);
    console.log('Row counts:');
    for (const row of counts.rows) {
      console.log(`  ${row.t}: ${row.c}`);
    }
  } finally {
    await client.end();
  }
}

main().catch((err) => {
  console.error('Load failed:', err.message);
  if (err.position) console.error('Position:', err.position);
  if (err.detail) console.error('Detail:', err.detail);
  process.exit(1);
});
