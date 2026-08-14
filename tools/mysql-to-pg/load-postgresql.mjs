#!/usr/bin/env node
/**
 * Load converted SaiFlower SQL into PostgreSQL.
 *
 * Usage:
 *   node tools/mysql-to-pg/load-postgresql.mjs [path-to-sql]
 *
 * Defaults to migration-output/02_data_postgresql.sql so the normal flow is:
 * Prisma migration first, converted production data second.
 */
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import pg from 'pg';

const { Client } = pg;
const __dirname = path.dirname(fileURLToPath(import.meta.url));

function useSsl(connectionString) {
  if (/^(1|true|require)$/i.test(process.env.PGSSL ?? '')) return true;
  try {
    const url = new URL(connectionString);
    return ['require', 'verify-ca', 'verify-full'].includes(url.searchParams.get('sslmode'));
  } catch {
    return false;
  }
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
  const databaseUrl = process.env.DATABASE_URL;
  if (!databaseUrl || !databaseUrl.startsWith('postgres')) {
    throw new Error('DATABASE_URL must be a PostgreSQL connection string.');
  }

  const sqlPath = process.argv[2]
    ? path.resolve(process.argv[2])
    : path.resolve(__dirname, 'migration-output', '02_data_postgresql.sql');

  if (!fs.existsSync(sqlPath)) {
    throw new Error(`SQL file not found: ${sqlPath}`);
  }

  const sql = fs.readFileSync(sqlPath, 'utf8');
  const ssl = useSsl(databaseUrl) ? { rejectUnauthorized: false } : false;
  console.log(`Loading ${path.basename(sqlPath)} (${(sql.length / 1024 / 1024).toFixed(2)} MB)...`);
  console.log('Target:', safeTarget(databaseUrl));

  const client = new Client({
    connectionString: databaseUrl,
    ssl,
    connectionTimeoutMillis: 60_000,
  });

  await client.connect();
  const started = Date.now();
  try {
    await client.query("SET statement_timeout = '0'");
    await client.query("SET lock_timeout = '0'");
    await client.query(sql);
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
