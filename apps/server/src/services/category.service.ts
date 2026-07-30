import { prisma } from '../db/client';
import { mediaUrl } from '../utils/catalog';

export async function listCategories() {
  const rows = await prisma.categories.findMany({
    where: { status: 1 },
    orderBy: [{ sortOrder: 'asc' }, { name: 'asc' }],
  });

  return rows.map((r) => ({
    id: r.id,
    name: String(r.name ?? ''),
    slug: null,
    image: r.image != null ? mediaUrl(String(r.image), 'categories') : null,
    sortOrder: r.sortOrder != null ? Number(r.sortOrder) : 0,
    status: Number(r.status ?? 1),
  }));
}

export async function getSettings() {
  const row = await prisma.settings.findFirst({ where: { id: 1 } });
  if (!row) return null;
  return {
    id: row.id,
    site_title: row.siteTitle,
    tagline: row.tagline,
    logo: row.logo,
    phone: row.phone,
    whatsapp: row.whatsapp,
    email: row.email,
    address: row.address,
    theme_primary: row.themePrimary,
    theme_secondary: row.themeSecondary,
    hero_title: row.heroTitle,
    hero_subtitle: row.heroSubtitle,
    hero_image: row.heroImage,
    logo_width: row.logoWidth,
    maintenance_mode: row.maintenanceMode,
    footer_about: row.footerAbout,
    newsletter_text: row.newsletterText,
  };
}
