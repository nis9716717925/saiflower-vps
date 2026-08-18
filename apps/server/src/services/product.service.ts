import type { Prisma } from '../db/client';
import { prisma, num } from '../db/client';
import { NotFoundError } from '../utils/errors';
import {
  mediaUrl,
  normalizeProductType,
  productUrl,
} from '../utils/catalog';
import { applySurgePricing } from './pricing.service';

export type ProductListQuery = {
  type?: string;
  page?: number;
  limit?: number;
  sort?: string;
  search?: string;
  category?: string;
  price_min?: number;
  price_max?: number;
  in_stock?: number;
};

type ProductRow = {
  id: number;
  name: string;
  slug?: string | null;
  description?: string | null;
  price: unknown;
  originalPrice?: unknown | null;
  image?: string | null;
  rating?: unknown | null;
  inStock?: number | null;
  status?: number | null;
  tag?: string | null;
  categoryIds?: string | null;
  metaTitle?: string | null;
  metaDescription?: string | null;
  faqs?: string | null;
  imagesGallery?: string | null;
  icon?: string | null;
  deliverySameday?: number | null;
  deliveryNextday?: number | null;
};

/** Parse images_gallery column (JSON array or comma-separated paths). */
function parseImagesGallery(raw?: string | null): string[] {
  if (!raw?.trim()) return [];
  const trimmed = raw.trim();
  try {
    const parsed = JSON.parse(trimmed) as unknown;
    if (Array.isArray(parsed)) {
      return parsed.map((item) => String(item).trim()).filter(Boolean);
    }
  } catch {
    // fall through to delimiter split
  }
  return trimmed
    .split(/[,|]/)
    .map((part) => part.trim().replace(/^["']+|["']+$/g, ''))
    .filter(Boolean);
}

function buildGalleryImages(
  type: string,
  row: ProductRow,
  fromTable: string[],
): string[] {
  const folder = `${normalizeProductType(type)}s`;
  const fromColumn = parseImagesGallery(row.imagesGallery).map((path) =>
    mediaUrl(path, folder),
  );
  return [...new Set([...fromTable, ...fromColumn])];
}

function mapProduct(row: ProductRow, type: string) {
  const t = normalizeProductType(type);
  const basePrice = num(row.price);
  const imagePath =
    row.image != null
      ? String(row.image)
      : row.icon != null
        ? String(row.icon)
        : null;
  return {
    id: row.id,
    type: t,
    name: String(row.name ?? ''),
    slug: row.slug != null ? String(row.slug) : '',
    description: row.description != null ? String(row.description) : null,
    price: basePrice,
    originalPrice: row.originalPrice != null ? num(row.originalPrice) : null,
    image: mediaUrl(imagePath, `${t}s`),
    rating: row.rating != null ? num(row.rating) : null,
    inStock: row.inStock == null ? true : Number(row.inStock) === 1,
    status: row.status == null ? 1 : Number(row.status),
    tag: row.tag != null ? String(row.tag) : '',
    categoryIds: row.categoryIds != null ? String(row.categoryIds) : '',
    metaTitle: row.metaTitle != null ? String(row.metaTitle) : null,
    metaDescription: row.metaDescription != null ? String(row.metaDescription) : null,
    faqs: row.faqs ?? null,
    imagesGallery: row.imagesGallery ?? null,
    deliverySameday: row.deliverySameday == null ? true : Number(row.deliverySameday) === 1,
    deliveryNextday: row.deliveryNextday == null ? true : Number(row.deliveryNextday) === 1,
    url: productUrl(t, row.slug != null ? String(row.slug) : '', row.id),
  };
}

async function withLivePrice<T extends { price: number; type: string }>(item: T): Promise<T> {
  const live = await applySurgePricing(item.price, item.type);
  return { ...item, price: live };
}

function productOrderBy(sort: string | undefined, type: string) {
  const s = (sort ?? (type === 'flower' ? 'bestseller' : 'new')).toLowerCase();
  switch (s) {
    case 'price_low':
      return [{ price: 'asc' as const }];
    case 'price_high':
      return [{ price: 'desc' as const }];
    case 'name':
      return [{ name: 'asc' as const }];
    case 'rating':
      return [{ rating: 'desc' as const }, { id: 'desc' as const }];
    case 'newest':
    case 'new':
      return [{ id: 'desc' as const }];
    case 'bestseller':
    case 'trending':
    default:
      return type === 'flower'
        ? [{ rating: 'desc' as const }, { id: 'desc' as const }]
        : [{ id: 'desc' as const }];
  }
}

function addonOrderBy(sort: string | undefined): Prisma.AddonsOrderByWithRelationInput[] {
  const s = (sort ?? 'new').toLowerCase();
  switch (s) {
    case 'price_low':
      return [{ price: 'asc' }];
    case 'price_high':
      return [{ price: 'desc' }];
    case 'name':
      return [{ name: 'asc' }];
    default:
      return [{ id: 'desc' }];
  }
}

function buildPriceFilter(
  q: ProductListQuery,
): { gte?: number; lte?: number } | undefined {
  const filter: { gte?: number; lte?: number } = {};
  if (q.price_min != null && !Number.isNaN(q.price_min)) filter.gte = q.price_min;
  if (q.price_max != null && !Number.isNaN(q.price_max)) filter.lte = q.price_max;
  return Object.keys(filter).length ? filter : undefined;
}

function shuffle<T>(arr: T[]): T[] {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

/** Prefer gift-ready flower bouquets near the current PDP price for conversion. */
function pickRelatedFlowerBouquets<
  T extends {
    id: number;
    name: string;
    price: number;
    tag?: string;
    rating?: number | null;
    deliverySameday?: boolean;
  },
>(candidates: T[], current: { price: number }): T[] {
  const blocked =
    /\bcar\b|\bdecor\b|\bdecoration\b|\bjaimala\b|\bvarmala\b|\bfirst\s*night\b|\bcanopy\b|\bhaldi\b|\bmandap\b/i;
  const chocolate = /\bchocolate\b|\bferrero\b|\bkitkat\b|\bcadbury\b/i;
  const scored = candidates
    .map((p) => {
      const hay = `${p.name} ${p.tag ?? ''}`.toLowerCase();
      if (blocked.test(hay)) return null;
      const isBouquet =
        hay.includes('bouquet') || hay.includes('flower basket') || hay.includes('flower box');
      if (!isBouquet) return null;
      if (chocolate.test(hay)) return null;
      const priceGap = Math.abs(Number(p.price) - Number(current.price));
      const rating = Number(p.rating) || 4.5;
      let score = rating * 10 - priceGap / 80;
      if (Number(p.price) >= 699 && Number(p.price) <= 2499) score += 20;
      if (p.deliverySameday !== false) score += 8;
      return { p, score };
    })
    .filter((x): x is { p: T; score: number } => x != null)
    .sort((a, b) => b.score - a.score);

  const picked = scored.slice(0, 10).map((x) => x.p);
  if (picked.length >= 6) return picked;
  const extras = candidates.filter((p) => {
    const hay = `${p.name} ${p.tag ?? ''}`.toLowerCase();
    return !blocked.test(hay) && !picked.some((x) => x.id === p.id);
  });
  return [...picked, ...shuffle(extras)].slice(0, 10);
}

function mapFlowerVariant(v: {
  id: number;
  flowerId: number;
  name: string;
  price: unknown;
  originalPrice: unknown | null;
}) {
  return {
    id: v.id,
    flower_id: v.flowerId,
    name: v.name,
    price: num(v.price),
    original_price: v.originalPrice != null ? num(v.originalPrice) : null,
  };
}

function mapCakeVariant(v: {
  id: number;
  cakeId: number;
  name: string;
  price: unknown;
  originalPrice: unknown | null;
}) {
  return {
    id: v.id,
    cake_id: v.cakeId,
    name: v.name,
    price: num(v.price),
    original_price: v.originalPrice != null ? num(v.originalPrice) : null,
  };
}

function mapGiftVariant(v: {
  id: number;
  giftId: number;
  name: string;
  price: unknown;
  originalPrice: unknown | null;
}) {
  return {
    id: v.id,
    gift_id: v.giftId,
    name: v.name,
    price: num(v.price),
    original_price: v.originalPrice != null ? num(v.originalPrice) : null,
  };
}

async function listFlowerProducts(q: ProductListQuery) {
  const page = Math.max(1, q.page ?? 1);
  const limit = Math.min(200, Math.max(1, q.limit ?? 24));
  const skip = (page - 1) * limit;

  const where: Prisma.FlowersWhereInput = { status: 1 };
  const price = buildPriceFilter(q);
  if (price) where.price = price;
  if (q.in_stock != null) where.inStock = q.in_stock;
  if (q.search?.trim()) {
    const s = q.search.trim();
    where.OR = [
      { name: { contains: s, mode: 'insensitive' } },
      { slug: { contains: s, mode: 'insensitive' } },
      { tag: { contains: s, mode: 'insensitive' } },
    ];
  }
  if (q.category?.trim()) {
    where.categoryIds = { contains: `,${q.category.trim()},` };
  }

  const [total, rows] = await Promise.all([
    prisma.flowers.count({ where }),
    prisma.flowers.findMany({
      where,
      orderBy: productOrderBy(q.sort, 'flower'),
      take: limit,
      skip,
    }),
  ]);

  const items = await Promise.all(rows.map((row) => withLivePrice(mapProduct(row, 'flower'))));
  return { items, total, page, limit };
}

async function listCakeProducts(q: ProductListQuery) {
  const page = Math.max(1, q.page ?? 1);
  const limit = Math.min(200, Math.max(1, q.limit ?? 24));
  const skip = (page - 1) * limit;

  const where: Prisma.CakesWhereInput = { status: 1 };
  const price = buildPriceFilter(q);
  if (price) where.price = price;
  if (q.in_stock != null) where.inStock = q.in_stock;
  if (q.search?.trim()) {
    const s = q.search.trim();
    where.OR = [
      { name: { contains: s, mode: 'insensitive' } },
      { slug: { contains: s, mode: 'insensitive' } },
      { tag: { contains: s, mode: 'insensitive' } },
    ];
  }

  const [total, rows] = await Promise.all([
    prisma.cakes.count({ where }),
    prisma.cakes.findMany({
      where,
      orderBy: productOrderBy(q.sort, 'cake'),
      take: limit,
      skip,
    }),
  ]);

  const items = await Promise.all(rows.map((row) => withLivePrice(mapProduct(row, 'cake'))));
  return { items, total, page, limit };
}

async function listGiftProducts(q: ProductListQuery) {
  const page = Math.max(1, q.page ?? 1);
  const limit = Math.min(200, Math.max(1, q.limit ?? 24));
  const skip = (page - 1) * limit;

  const where: Prisma.GiftsWhereInput = { status: 1 };
  const price = buildPriceFilter(q);
  if (price) where.price = price;
  if (q.in_stock != null) where.inStock = q.in_stock;
  if (q.search?.trim()) {
    const s = q.search.trim();
    where.OR = [
      { name: { contains: s, mode: 'insensitive' } },
      { slug: { contains: s, mode: 'insensitive' } },
      { tag: { contains: s, mode: 'insensitive' } },
    ];
  }

  const [total, rows] = await Promise.all([
    prisma.gifts.count({ where }),
    prisma.gifts.findMany({
      where,
      orderBy: productOrderBy(q.sort, 'gift'),
      take: limit,
      skip,
    }),
  ]);

  const items = await Promise.all(rows.map((row) => withLivePrice(mapProduct(row, 'gift'))));
  return { items, total, page, limit };
}

async function listAddonProducts(q: ProductListQuery) {
  const page = Math.max(1, q.page ?? 1);
  const limit = Math.min(200, Math.max(1, q.limit ?? 24));
  const skip = (page - 1) * limit;

  const where: Prisma.AddonsWhereInput = { status: 1 };
  const price = buildPriceFilter(q);
  if (price) where.price = price;
  if (q.search?.trim()) {
    where.name = { contains: q.search.trim(), mode: 'insensitive' };
  }

  const [total, rows] = await Promise.all([
    prisma.addons.count({ where }),
    prisma.addons.findMany({
      where,
      orderBy: addonOrderBy(q.sort),
      take: limit,
      skip,
    }),
  ]);

  const items = await Promise.all(rows.map((row) => withLivePrice(mapProduct(row, 'addon'))));
  return { items, total, page, limit };
}

export async function listProducts(q: ProductListQuery) {
  const type = normalizeProductType(q.type ?? 'flower');
  switch (type) {
    case 'flower':
      return listFlowerProducts(q);
    case 'cake':
      return listCakeProducts(q);
    case 'gift':
      return listGiftProducts(q);
    case 'addon':
      return listAddonProducts(q);
    default:
      return listFlowerProducts(q);
  }
}

export async function getProductBySlug(typeRaw: string, slug: string) {
  const type = normalizeProductType(typeRaw);

  let row: ProductRow | null = null;
  switch (type) {
    case 'flower':
      row = await prisma.flowers.findFirst({ where: { slug, status: 1 } });
      break;
    case 'cake':
      row = await prisma.cakes.findFirst({ where: { slug, status: 1 } });
      break;
    case 'gift':
      row = await prisma.gifts.findFirst({ where: { slug, status: 1 } });
      break;
    case 'addon':
      throw new NotFoundError('Product not found');
  }
  if (!row) throw new NotFoundError('Product not found');

  const product = await withLivePrice(mapProduct(row, type));
  const id = product.id;

  let variants: Record<string, unknown>[] = [];
  try {
    if (type === 'flower') {
      const v = await prisma.flowerVariants.findMany({ where: { flowerId: id } });
      variants = v.map(mapFlowerVariant);
    } else if (type === 'cake') {
      const v = await prisma.cakeVariants.findMany({ where: { cakeId: id } });
      variants = v.map(mapCakeVariant);
    } else if (type === 'gift') {
      const v = await prisma.giftVariants.findMany({ where: { giftId: id } });
      variants = v.map(mapGiftVariant);
    }
  } catch {
    variants = [];
  }

  let images: string[] = [];
  try {
    if (type === 'flower') {
      const imgRows = await prisma.flowerImages.findMany({
        where: { flowerId: id },
        select: { imagePath: true },
      });
      images = imgRows.map((r) => mediaUrl(String(r.imagePath), 'flowers'));
    }
  } catch {
    images = [];
  }

  const galleryImages = buildGalleryImages(type, row, images);

  let relatedRows: ProductRow[] = [];
  switch (type) {
    case 'flower':
      relatedRows = await prisma.flowers.findMany({
        where: { status: 1, NOT: { id } },
        take: 60,
        orderBy: [{ rating: 'desc' }, { id: 'desc' }],
      });
      break;
    case 'cake':
      relatedRows = await prisma.cakes.findMany({
        where: { status: 1, NOT: { id } },
        take: 20,
      });
      break;
    case 'gift':
      relatedRows = await prisma.gifts.findMany({
        where: { status: 1, NOT: { id } },
        take: 20,
      });
      break;
  }

  const relatedMapped = await Promise.all(relatedRows.map((r) => withLivePrice(mapProduct(r, type))));
  const related =
    type === 'flower'
      ? pickRelatedFlowerBouquets(relatedMapped, product)
      : shuffle(relatedMapped).slice(0, 10);

  return {
    ...product,
    variants,
    galleryImages,
    related,
  };
}

export async function getProductById(typeRaw: string, id: number) {
  const type = normalizeProductType(typeRaw);

  let row: ProductRow | null = null;
  switch (type) {
    case 'flower':
      row = await prisma.flowers.findUnique({ where: { id } });
      break;
    case 'cake':
      row = await prisma.cakes.findUnique({ where: { id } });
      break;
    case 'gift':
      row = await prisma.gifts.findUnique({ where: { id } });
      break;
    case 'addon':
      row = await prisma.addons.findUnique({ where: { id } });
      break;
  }
  if (!row) throw new NotFoundError('Product not found');
  return withLivePrice(mapProduct(row, type));
}

export async function checkStock(typeRaw: string, id: number) {
  const product = await getProductById(typeRaw, id);
  return { id: product.id, type: product.type, inStock: product.inStock, price: product.price };
}
