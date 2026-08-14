#!/usr/bin/env node
import { PrismaClient } from '@prisma/client';

async function main() {
  if (!process.env.DATABASE_URL) {
    console.error('DATABASE_URL is required');
    process.exit(1);
  }
  const prisma = new PrismaClient();
  try {
    await prisma.$queryRaw`SELECT 1`;
    const counts = {
      flowers: await prisma.flowers.count(),
      cakes: await prisma.cakes.count(),
      gifts: await prisma.gifts.count(),
      customers: await prisma.customers.count(),
      customer_addresses: await prisma.customerAddress.count(),
      orders: await prisma.orders.count(),
      dynamic_pages: await prisma.dynamicPages.count(),
      blogs: await prisma.blogs.count(),
      categories: await prisma.categories.count(),
      wishlist: await prisma.wishlist.count(),
      promo_codes: await prisma.promoCodes.count(),
      settings: await prisma.settings.count(),
    };
    console.log(JSON.stringify({ status: 'ok', counts }, null, 2));
    if (counts.flowers < 1 || counts.settings < 1) {
      console.error('WARNING: expected seed data missing — re-run load script');
      process.exit(2);
    }
  } finally {
    await prisma.$disconnect();
  }
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
