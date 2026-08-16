import path from 'path';
import dotenv from 'dotenv';
import { PrismaClient } from '@prisma/client';

// Ensure DATABASE_URL is present even when PM2 does not inject env vars.
dotenv.config({ path: path.resolve(__dirname, '../.env') });
dotenv.config({ path: path.resolve(__dirname, '../../../apps/server/.env') });

/** Shared Prisma client for Supabase PostgreSQL (legacy table names). */
const globalForPrisma = globalThis as unknown as { prisma?: PrismaClient };

export const prisma =
  globalForPrisma.prisma ??
  new PrismaClient({
    log: process.env.NODE_ENV === 'development' ? ['error', 'warn'] : ['error'],
  });

if (process.env.NODE_ENV !== 'production') {
  globalForPrisma.prisma = prisma;
}

export default prisma;
export { PrismaClient };
export * from '@prisma/client';
