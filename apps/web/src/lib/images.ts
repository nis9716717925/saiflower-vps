const FALLBACK_FLOWER =
  'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=600&q=80';

/** Prefix uploads paths and normalize image src for Next/public URLs. */
export function resolveImageSrc(src: string | null | undefined, fallback = FALLBACK_FLOWER): string {
  if (!src || !src.trim()) return fallback;
  const trimmed = src.trim();
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('/')) {
    return trimmed;
  }
  if (trimmed.startsWith('uploads/')) return `/${trimmed}`;
  return `/uploads/${trimmed}`;
}

export function productHref(type: string, slug: string): string {
  const base = type === 'cake' ? 'cakes' : type === 'gift' ? 'gifts' : 'flowers';
  return `/${base}/${slug}`;
}

export function formatInr(amount: number): string {
  return `₹${Math.round(amount).toLocaleString('en-IN')}`;
}

export function discountPercent(price: number, originalPrice?: number | null): number {
  const original = originalPrice ?? 0;
  if (original > price && original > 0) {
    return Math.round(((original - price) / original) * 100);
  }
  return 0;
}

/** Parse product gallery paths from API column (JSON array or comma-separated). */
export function parseProductGallerySources(
  galleryImages?: string[] | null,
  imagesGallery?: string | null,
): string[] {
  if (galleryImages?.length) return galleryImages;
  if (!imagesGallery?.trim()) return [];

  const trimmed = imagesGallery.trim();
  try {
    const parsed = JSON.parse(trimmed) as unknown;
    if (Array.isArray(parsed)) {
      return parsed.map((item) => String(item).trim()).filter(Boolean);
    }
  } catch {
    // fall through
  }

  return trimmed
    .split(/[,|]/)
    .map((part) => part.trim().replace(/^["']+|["']+$/g, ''))
    .filter(Boolean);
}

export function productGalleryUrls(product: {
  image: string;
  galleryImages?: string[] | null;
  imagesGallery?: string | null;
}): string[] {
  const gallery = parseProductGallerySources(product.galleryImages, product.imagesGallery);
  return [product.image, ...gallery]
    .map((src) => resolveImageSrc(src))
    .filter((src, index, images) => images.indexOf(src) === index);
}

export function reviewCountEstimate(id: number): number {
  return 80 + (id % 47) * 3;
}
