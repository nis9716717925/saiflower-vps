import { PrismaClient } from '@prisma/client';

/** Shared Prisma client for PostgreSQL on the Hostinger VPS (legacy table names). */
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
