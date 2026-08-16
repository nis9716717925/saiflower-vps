#!/usr/bin/env node
/**
 * Load converted SaiFlower SQL into Supabase PostgreSQL.
 *
 * Usage:
 *   node tools/mysql-to-pg/load-postgresql.mjs [path-to-sql]
 *
 * Defaults to tools/mysql-to-pg/02_data_postgresql.sql so the normal flow is:
 * Prisma migration first, converted production data second.
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import pg from 'pg';
import { loadDatabaseEnv } from '../load-database-env.mjs';

const { Client } = pg;
const __dirname = path.dirname(fileURLToPath(import.meta.url));

function connectionOptions(connectionString) {
  const url = new URL(connectionString);
  const host = url.hostname.toLowerCase();
  const wantsSsl =
    /^(1|true|require)$/i.test(process.env.PGSSL ?? '') ||
    ['require', 'verify-ca', 'verify-full'].includes(url.searchParams.get('sslmode') ?? '') ||
    host.includes('supabase.com') ||
    host.includes('supabase.co');

  // Newer node-pg treats sslmode=require as verify-full. Strip it and pass an
  // explicit SSL config so Supabase's pooler certificate chain is accepted.
  url.searchParams.delete('sslmode');
  url.searchParams.delete('uselibpqcompat');

  return {
    connectionString: url.toString(),
    ssl: wantsSsl ? { rejectUnauthorized: false } : false,
  };
}

function safeTarget(connectionString) {
  try {
    const url = new URL(connectionString);
    if (url.password) url.password = '****';
    return url.toString();
  } catch {
    return '<invalid DATABASE_URL>';
  }
}

async function main() {
  loadDatabaseEnv();
  // Imports must bypass the transaction pooler. On an IPv4-only VPS,
  // DIRECT_URL should be Supabase's session pooler URI on port 5432.
  const databaseUrl = process.env.DIRECT_URL || process.env.DATABASE_URL;
  if (!databaseUrl || !databaseUrl.startsWith('postgres')) {
    throw new Error('DIRECT_URL or DATABASE_URL must be a PostgreSQL connection string.');
  }

  const sqlPath = process.argv[2]
    ? path.resolve(process.argv[2])
    : path.resolve(__dirname, '02_data_postgresql.sql');

  if (!fs.existsSync(sqlPath)) {
    throw new Error(`SQL file not found: ${sqlPath}`);
  }

  const sql = fs.readFileSync(sqlPath, 'utf8');
  const { connectionString, ssl } = connectionOptions(databaseUrl);
  console.log(`Loading ${path.basename(sqlPath)} (${(sql.length / 1024 / 1024).toFixed(2)} MB)...`);
  console.log(`Target (${process.env.DIRECT_URL ? 'DIRECT_URL' : 'DATABASE_URL'}):`, safeTarget(databaseUrl));

  const client = new Client({
    connectionString,
    ssl,
    connectionTimeoutMillis: 60_000,
  });

  await client.connect();
  const started = Date.now();
  try {
    await client.query("SET statement_timeout = '0'");
    await client.query("SET lock_timeout = '0'");

    // Soft FKs were added for Prisma; the MySQL dump can contain orphans.
    // Drop them for the import, clean orphans, then restore the constraints.
    await client.query(`
      ALTER TABLE IF EXISTS flower_variants DROP CONSTRAINT IF EXISTS flower_variants_flower_id_fkey;
      ALTER TABLE IF EXISTS flower_images DROP CONSTRAINT IF EXISTS flower_images_flower_id_fkey;
    `);

    await client.query(sql);

    const cleanup = await client.query(`
      DELETE FROM flower_variants fv
      WHERE NOT EXISTS (SELECT 1 FROM flowers f WHERE f.id = fv.flower_id);
      DELETE FROM flower_images fi
      WHERE NOT EXISTS (SELECT 1 FROM flowers f WHERE f.id = fi.flower_id);
    `);
    console.log(
      `Cleaned orphan rows: flower_variants=${cleanup[0].rowCount ?? 0}, flower_images=${cleanup[1].rowCount ?? 0}`,
    );

    await client.query(`
      ALTER TABLE flower_variants
        ADD CONSTRAINT flower_variants_flower_id_fkey
        FOREIGN KEY (flower_id) REFERENCES flowers(id)
        ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
      ALTER TABLE flower_images
        ADD CONSTRAINT flower_images_flower_id_fkey
        FOREIGN KEY (flower_id) REFERENCES flowers(id)
        ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED;
    `);

    console.log(`Load completed in ${((Date.now() - started) / 1000).toFixed(1)}s`);
  } finally {
    await client.end();
  }
}

main().catch((error) => {
  console.error('Load failed:', error.message);
  if (error.position) console.error('Position:', error.position);
  if (error.detail) console.error('Detail:', error.detail);
  process.exit(1);
});
