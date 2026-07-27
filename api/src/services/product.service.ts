import { Prisma } from '@prisma/client';
import prisma from '../config/database';
import { NotFoundError } from '../utils/errors';
import { parsePagination, buildPaginationMeta } from '../utils/response';
import { decimalToNumber } from '../utils/sanitize';
import { resolveLegacyMediaUrl } from '../utils/media';

export interface ProductFilters {
  categoryId?: string;
  categorySlug?: string;
  brandId?: string;
  brandSlug?: string;
  minPrice?: number;
  maxPrice?: number;
  minRating?: number;
  isFeatured?: boolean;
  inStock?: boolean;
}

interface LegacyFlowerRow {
  id: number;
  category_ids: string | null;
  name: string;
  slug: string | null;
  price: number;
  original_price: number | null;
  description: string | null;
  image: string | null;
  in_stock: number | null;
  status: number | null;
  created_at: Date | string | null;
  rating: number | null;
  tag: string | null;
}

function formatProduct(product: Record<string, unknown>) {
  return {
    ...product,
    basePrice: decimalToNumber(product.basePrice as never),
    compareAtPrice: product.compareAtPrice
      ? decimalToNumber(product.compareAtPrice as never)
      : null,
    ratingAvg: decimalToNumber(product.ratingAvg as never),
    variants: Array.isArray(product.variants)
      ? (product.variants as Record<string, unknown>[]).map((v) => ({
          ...v,
          price: decimalToNumber(v.price as never),
        }))
      : product.variants,
  };
}

export class ProductService {
  private isMissingTableError(error: unknown): boolean {
    return (
      error instanceof Prisma.PrismaClientKnownRequestError &&
      error.code === 'P2021'
    );
  }

  private slugify(value: string): string {
    return value
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  private async getLegacyCategoriesMap() {
    const categories = await prisma.$queryRawUnsafe<Array<{ id: number; name: string }>>(
      'SELECT id, name FROM categories',
    );
    const map = new Map<number, { id: string; name: string; slug: string }>();
    for (const category of categories) {
      map.set(category.id, {
        id: String(category.id),
        name: category.name,
        slug: this.slugify(category.name),
      });
    }
    return map;
  }

  private parseCategoryIds(value: string | null): number[] {
    if (!value) {
      return [];
    }
    return value
      .split(',')
      .map((item) => parseInt(item.trim(), 10))
      .filter((item) => Number.isFinite(item));
  }

  private mapLegacyProduct(row: LegacyFlowerRow, categoriesMap: Map<number, { id: string; name: string; slug: string }>) {
    const categoryIds = this.parseCategoryIds(row.category_ids);
    const firstCategory = categoryIds.length > 0 ? categoriesMap.get(categoryIds[0]) : undefined;

    return {
      id: String(row.id),
      name: row.name,
      slug: row.slug || this.slugify(row.name),
      description: row.description,
      basePrice: Number(row.price ?? 0),
      compareAtPrice: row.original_price != null ? Number(row.original_price) : null,
      ratingAvg: Number(row.rating ?? 0),
      isFeatured: (row.tag ?? '').toLowerCase() === 'featured',
      inStock: Number(row.in_stock ?? 1) === 1,
      category: firstCategory ?? null,
      brand: null,
      images: row.image
        ? [
            {
              id: `legacy-image-${row.id}`,
              imageUrl: resolveLegacyMediaUrl(row.image, 'product'),
              altText: row.name,
              sortOrder: 0,
            },
          ]
        : [],
      variants: [],
      createdAt: row.created_at ? new Date(row.created_at).toISOString() : null,
    };
  }

  private async listLegacy(query: Record<string, unknown>, filters: ProductFilters = {}) {
    const { page, limit, search } = parsePagination(query);
    const categoriesMap = await this.getLegacyCategoriesMap();
    const rows = await prisma.$queryRawUnsafe<LegacyFlowerRow[]>(
      'SELECT id, category_ids, name, slug, price, original_price, description, image, in_stock, status, created_at, rating, tag FROM flowers WHERE status = 1 ORDER BY created_at DESC',
    );

    let items = rows.map((row) => this.mapLegacyProduct(row, categoriesMap));

    if (search) {
      const normalized = search.toLowerCase();
      items = items.filter(
        (item) =>
          item.name.toLowerCase().includes(normalized) ||
          (item.description ?? '').toLowerCase().includes(normalized),
      );
    }

    if (filters.categorySlug) {
      items = items.filter((item) => item.category?.slug === filters.categorySlug);
    }

    if (filters.minPrice !== undefined) {
      items = items.filter((item) => item.basePrice >= filters.minPrice!);
    }

    if (filters.maxPrice !== undefined) {
      items = items.filter((item) => item.basePrice <= filters.maxPrice!);
    }

    if (filters.minRating !== undefined) {
      items = items.filter((item) => item.ratingAvg >= filters.minRating!);
    }

    if (filters.isFeatured !== undefined) {
      items = items.filter((item) => item.isFeatured === filters.isFeatured);
    }

    if (filters.inStock) {
      items = items.filter((item) => item.inStock);
    }

    const total = items.length;
    const start = (page - 1) * limit;
    const paged = items.slice(start, start + limit);

    return {
      items: paged,
      meta: buildPaginationMeta(page, limit, total),
    };
  }

  private async getLegacyBySlug(slug: string) {
    const categoriesMap = await this.getLegacyCategoriesMap();
    const rows = await prisma.$queryRawUnsafe<LegacyFlowerRow[]>(
      'SELECT id, category_ids, name, slug, price, original_price, description, image, in_stock, status, created_at, rating, tag FROM flowers WHERE status = 1',
    );
    const row = rows.find(
      (item) => (item.slug || this.slugify(item.name)).toLowerCase() === slug.toLowerCase(),
    );
    if (!row) {
      throw new NotFoundError('Product not found');
    }
    return this.mapLegacyProduct(row, categoriesMap);
  }

  private async getLegacyRelated(slug: string, limit = 8) {
    const categoriesMap = await this.getLegacyCategoriesMap();
    const rows = await prisma.$queryRawUnsafe<LegacyFlowerRow[]>(
      'SELECT id, category_ids, name, slug, price, original_price, description, image, in_stock, status, created_at, rating, tag FROM flowers WHERE status = 1 ORDER BY created_at DESC',
    );
    const current = rows.find(
      (item) => (item.slug || this.slugify(item.name)).toLowerCase() === slug.toLowerCase(),
    );
    if (!current) {
      throw new NotFoundError('Product not found');
    }
    const currentCategories = new Set(this.parseCategoryIds(current.category_ids));
    return rows
      .filter((item) => item.id !== current.id)
      .filter((item) => {
        const ids = this.parseCategoryIds(item.category_ids);
        return ids.some((id) => currentCategories.has(id));
      })
      .slice(0, limit)
      .map((item) => this.mapLegacyProduct(item, categoriesMap));
  }

  async list(query: Record<string, unknown>, filters: ProductFilters = {}) {
    try {
      const { page, limit, sortBy, sortOrder, search } = parsePagination(query);
      const skip = (page - 1) * limit;

      const where: Prisma.ProductWhereInput = { isActive: true };

      if (search) {
        where.OR = [
          { name: { contains: search } },
          { description: { contains: search } },
          { sku: { contains: search } },
        ];
      }

      if (filters.categoryId) where.categoryId = filters.categoryId;
      if (filters.categorySlug) {
        const cat = await prisma.category.findUnique({ where: { slug: filters.categorySlug } });
        if (cat) where.categoryId = cat.id;
      }
      if (filters.brandId) where.brandId = filters.brandId;
      if (filters.brandSlug) {
        const brand = await prisma.brand.findUnique({ where: { slug: filters.brandSlug } });
        if (brand) where.brandId = brand.id;
      }
      if (filters.minPrice !== undefined || filters.maxPrice !== undefined) {
        where.basePrice = {};
        if (filters.minPrice !== undefined) where.basePrice.gte = filters.minPrice;
        if (filters.maxPrice !== undefined) where.basePrice.lte = filters.maxPrice;
      }
      if (filters.minRating !== undefined) where.ratingAvg = { gte: filters.minRating };
      if (filters.isFeatured !== undefined) where.isFeatured = filters.isFeatured;
      if (filters.inStock) {
        where.variants = { some: { stock: { gt: 0 }, isActive: true } };
      }

      const allowedSort = ['createdAt', 'basePrice', 'ratingAvg', 'name'];
      const orderField = allowedSort.includes(sortBy) ? sortBy : 'createdAt';

      const [products, total] = await Promise.all([
        prisma.product.findMany({
          where,
          skip,
          take: limit,
          orderBy: { [orderField]: sortOrder },
          include: {
            images: { orderBy: { sortOrder: 'asc' }, take: 1 },
            category: { select: { id: true, name: true, slug: true } },
            brand: { select: { id: true, name: true, slug: true } },
            variants: { where: { isActive: true }, select: { id: true, stock: true, price: true } },
          },
        }),
        prisma.product.count({ where }),
      ]);

      return {
        items: products.map((p) => formatProduct(p as unknown as Record<string, unknown>)),
        meta: buildPaginationMeta(page, limit, total),
      };
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }
      return this.listLegacy(query, filters);
    }
  }

  async getBySlug(slug: string) {
    try {
      const product = await prisma.product.findUnique({
        where: { slug, isActive: true },
        include: {
          images: { orderBy: { sortOrder: 'asc' } },
          variants: { where: { isActive: true } },
          category: true,
          brand: true,
          reviews: {
            where: { isApproved: true },
            take: 10,
            orderBy: { createdAt: 'desc' },
            include: {
              user: { select: { firstName: true, lastName: true } },
            },
          },
        },
      });
      if (!product) throw new NotFoundError('Product not found');
      return formatProduct(product as unknown as Record<string, unknown>);
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }
      return this.getLegacyBySlug(slug);
    }
  }

  async getRelated(slug: string, limit = 8) {
    try {
      const product = await prisma.product.findUnique({ where: { slug } });
      if (!product) throw new NotFoundError('Product not found');

      const related = await prisma.product.findMany({
        where: {
          isActive: true,
          id: { not: product.id },
          OR: [
            { categoryId: product.categoryId },
            ...(product.brandId ? [{ brandId: product.brandId }] : []),
          ],
        },
        take: limit,
        include: {
          images: { orderBy: { sortOrder: 'asc' }, take: 1 },
          category: { select: { name: true, slug: true } },
        },
      });

      return related.map((p) => formatProduct(p as unknown as Record<string, unknown>));
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }
      return this.getLegacyRelated(slug, limit);
    }
  }

  async checkStock(productId: string, variantId?: string) {
    try {
      if (variantId) {
        const variant = await prisma.productVariant.findUnique({ where: { id: variantId } });
        if (!variant) throw new NotFoundError('Variant not found');
        return { available: variant.stock, inStock: variant.stock > 0 };
      }
      const variants = await prisma.productVariant.findMany({
        where: { productId, isActive: true },
      });
      const totalStock = variants.reduce((sum, v) => sum + v.stock, 0);
      return { available: totalStock, inStock: totalStock > 0 };
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }

      if (variantId) {
        const variants = await prisma.$queryRawUnsafe<Array<{ id: number }>>(
          'SELECT id FROM flower_variants WHERE id = ? LIMIT 1',
          Number(variantId),
        );
        if (variants.length === 0) {
          throw new NotFoundError('Variant not found');
        }
        return { available: 1, inStock: true };
      }

      const flowers = await prisma.$queryRawUnsafe<Array<{ in_stock: number | null }>>(
        'SELECT in_stock FROM flowers WHERE id = ? LIMIT 1',
        Number(productId),
      );
      if (flowers.length === 0) {
        throw new NotFoundError('Product not found');
      }
      const inStock = Number(flowers[0].in_stock ?? 0) === 1;
      return { available: inStock ? 1 : 0, inStock };
    }
  }
}

export const productService = new ProductService();
