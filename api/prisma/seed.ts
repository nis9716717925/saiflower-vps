import { PrismaClient, DiscountType } from '@prisma/client';
import bcrypt from 'bcryptjs';

const prisma = new PrismaClient();

async function main() {
  console.log('Seeding database...');

  const passwordHash = await bcrypt.hash('Password123!', 12);

  const user = await prisma.user.upsert({
    where: { email: 'demo@saiflower.com' },
    update: {},
    create: {
      email: 'demo@saiflower.com',
      passwordHash,
      firstName: 'Demo',
      lastName: 'User',
      phone: '+919876543210',
      isVerified: true,
    },
  });

  const brand = await prisma.brand.upsert({
    where: { slug: 'saiflower' },
    update: {},
    create: {
      name: 'Saiflower',
      slug: 'saiflower',
      description: 'Premium flowers and gifts',
    },
  });

  const flowers = await prisma.category.upsert({
    where: { slug: 'flowers' },
    update: {},
    create: { name: 'Flowers', slug: 'flowers', sortOrder: 1 },
  });

  const roses = await prisma.category.upsert({
    where: { slug: 'roses' },
    update: {},
    create: { name: 'Roses', slug: 'roses', parentId: flowers.id, sortOrder: 1 },
  });

  const product = await prisma.product.upsert({
    where: { slug: 'red-roses-bouquet' },
    update: {},
    create: {
      name: 'Red Roses Bouquet',
      slug: 'red-roses-bouquet',
      description: 'A stunning bouquet of 12 fresh red roses, perfect for any romantic occasion.',
      shortDescription: '12 fresh red roses in premium wrapping',
      sku: 'RRB-001',
      basePrice: 1299,
      compareAtPrice: 1599,
      categoryId: roses.id,
      brandId: brand.id,
      isFeatured: true,
      images: {
        create: [
          { url: '/assets/images/red-roses.jpg', altText: 'Red Roses Bouquet', isPrimary: true, sortOrder: 0 },
        ],
      },
      variants: {
        create: [
          { name: '12 Roses', sku: 'RRB-001-12', price: 1299, stock: 50 },
          { name: '24 Roses', sku: 'RRB-001-24', price: 2299, stock: 30 },
        ],
      },
    },
  });

  await prisma.coupon.upsert({
    where: { code: 'WELCOME10' },
    update: {},
    create: {
      code: 'WELCOME10',
      description: '10% off your first order',
      discountType: DiscountType.PERCENTAGE,
      discountValue: 10,
      minOrderAmount: 500,
      maxDiscount: 500,
      perUserLimit: 1,
    },
  });

  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(0, 0, 0, 0);

  await prisma.deliverySlot.createMany({
    data: [
      { date: tomorrow, startTime: '09:00', endTime: '12:00', maxOrders: 20 },
      { date: tomorrow, startTime: '14:00', endTime: '18:00', maxOrders: 20 },
      { date: tomorrow, startTime: '18:00', endTime: '21:00', maxOrders: 15 },
    ],
    skipDuplicates: true,
  });

  await prisma.appSetting.createMany({
    data: [
      { key: 'appName', value: 'Saiflower', group: 'branding' },
      { key: 'supportEmail', value: 'support@saiflower.com', group: 'branding' },
      { key: 'primaryColor', value: '#e91e63', group: 'branding' },
    ],
    skipDuplicates: true,
  });

  console.log('Seed complete!');
  console.log(`Demo user: demo@saiflower.com / Password123!`);
  console.log(`Sample product: ${product.slug}`);
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());
