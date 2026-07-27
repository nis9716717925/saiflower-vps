import prisma from '../config/database';
import { Prisma } from '@prisma/client';
import { NotFoundError } from '../utils/errors';
import { resolveLegacyMediaUrl } from '../utils/media';

export class CategoryService {
  private slugify(value: string): string {
    return value
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  private isMissingTableError(error: unknown): boolean {
    return (
      error instanceof Prisma.PrismaClientKnownRequestError &&
      error.code === 'P2021'
    );
  }

  private mapLegacyCategory(row: Record<string, unknown>) {
    const name = String(row.name ?? '');
    const slug = this.slugify(name);
    return {
      id: String(row.id ?? slug),
      name,
      slug,
      imageUrl: resolveLegacyMediaUrl(row.image ? String(row.image) : null, 'category'),
      isActive: Number(row.status ?? 1) === 1,
      sortOrder: Number(row.sort_order ?? 0),
      children: [],
    };
  }

  private async listLegacyCategories() {
    const rows = await prisma.$queryRawUnsafe<Array<Record<string, unknown>>>(
      'SELECT id, name, image, status, sort_order FROM categories ORDER BY sort_order ASC, id ASC',
    );

    return rows
      .map((row) => this.mapLegacyCategory(row))
      .filter((row) => row.isActive);
  }

  async listAll() {
    try {
      return await prisma.category.findMany({
        where: { isActive: true, parentId: null },
        orderBy: { sortOrder: 'asc' },
        include: {
          children: {
            where: { isActive: true },
            orderBy: { sortOrder: 'asc' },
            include: {
              children: {
                where: { isActive: true },
                orderBy: { sortOrder: 'asc' },
              },
            },
          },
        },
      });
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }
      return this.listLegacyCategories();
    }
  }

  async getBySlug(slug: string) {
    try {
      const category = await prisma.category.findUnique({
        where: { slug, isActive: true },
        include: {
          parent: true,
          children: { where: { isActive: true }, orderBy: { sortOrder: 'asc' } },
        },
      });
      if (!category) throw new NotFoundError('Category not found');
      return category;
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }

      const categories = await this.listLegacyCategories();
      const category = categories.find((item) => item.slug === slug);
      if (!category) {
        throw new NotFoundError('Category not found');
      }
      return category;
    }
  }

  async listBrands() {
    try {
      return await prisma.brand.findMany({
        where: { isActive: true },
        orderBy: { name: 'asc' },
      });
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }
      return [];
    }
  }

  async getBrandBySlug(slug: string) {
    try {
      const brand = await prisma.brand.findUnique({ where: { slug, isActive: true } });
      if (!brand) throw new NotFoundError('Brand not found');
      return brand;
    } catch (error) {
      if (!this.isMissingTableError(error)) {
        throw error;
      }
      throw new NotFoundError('Brand not found');
    }
  }
}

export const categoryService = new CategoryService();
