/** Client-safe media URL helpers (no Node fs). */

export const FLOWER_PLACEHOLDER_IMAGE =
  'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=600&q=80';

export const SITE_LOGO_PATH = '/assets/images/logo-transparent.webp';

const RASTER_EXT_RE = /\.(jpe?g|png|gif)(\?|#|$)/i;

function isLogoPath(value: string): boolean {
  return (
    value === SITE_LOGO_PATH ||
    value.endsWith('/logo-transparent.png') ||
    value.endsWith('/logo-transparent.webp')
  );
}

function supabasePublicBase(): string | null {
  const fromEnv =
    (typeof process !== 'undefined' &&
      (process.env.NEXT_PUBLIC_SUPABASE_STORAGE_PUBLIC_URL ||
        process.env.SUPABASE_STORAGE_PUBLIC_URL)) ||
    null;
  return fromEnv ? String(fromEnv).replace(/\/$/, '') : null;
}

function storageObjectKey(raw: string, defaultFolder: string): string {
  let key = raw.replace(/^\/+/, '');
  if (key.startsWith('uploads/')) key = key.slice('uploads/'.length);
  if (!key.includes('/') && defaultFolder) {
    key = `${defaultFolder.replace(/^\/|\/$/g, '')}/${key}`;
  }
  return key.replace(/ /g, '%20');
}

/**
 * Prefer WebP for same-origin / storage raster paths.
 * Leaves external CDNs (Unsplash, etc.) and already-webp URLs untouched.
 *
 * Category icons were uploaded as jpg/png/jpeg only (never batch-converted),
 * so rewriting those paths to .webp breaks the shop category rail.
 */
export function preferWebpSrc(src: string): string {
  if (!src || !RASTER_EXT_RE.test(src)) return src;
  if (/(?:^|\/)categories\//i.test(src)) return src;
  if (/^https?:\/\//i.test(src)) {
    try {
      const host = new URL(src).hostname;
      // Only rewrite our own hosts / storage — not Unsplash etc.
      const ours =
        host.includes('saiflower') ||
        host.includes('supabase.co') ||
        host === 'localhost' ||
        host === '127.0.0.1';
      if (!ours) return src;
    } catch {
      return src;
    }
  }
  return src.replace(RASTER_EXT_RE, '.webp$2');
}

/** Build a public image URL from a DB path, filename, or full URL. */
export function mediaUrl(path?: string | null, defaultFolder = ''): string | null {
  if (!path?.trim()) return null;
  const raw = path.trim();
  if (isLogoPath(raw)) return null;
  if (/^https?:\/\//i.test(raw)) return preferWebpSrc(raw.replace(/ /g, '%20'));
  if (raw.startsWith('/') && !raw.startsWith('/uploads/')) {
    return preferWebpSrc(raw.replace(/ /g, '%20'));
  }

  const storageBase = supabasePublicBase();
  if (storageBase) {
    return preferWebpSrc(`${storageBase}/${storageObjectKey(raw, defaultFolder)}`);
  }

  if (raw.startsWith('uploads/')) return preferWebpSrc(`/${raw}`.replace(/ /g, '%20'));
  const folder = defaultFolder ? `${defaultFolder.replace(/^\/|\/$/g, '')}/` : '';
  return preferWebpSrc(`/uploads/${folder}${raw}`.replace(/ /g, '%20'));
}

export const RESPONSIVE_WIDTHS = [320, 640, 828, 1080] as const;

const UPLOAD_VARIANT_RE = /-w\d+(?=\.[^.]+$)/;

function isUploadImagePath(src: string): boolean {
  if (/(?:^|\/)categories\//i.test(src)) return false;
  if (/^\/uploads\//i.test(src)) return true;
  try {
    const { pathname } = new URL(src);
    return /^\/uploads\//i.test(pathname);
  } catch {
    return false;
  }
}

/** e.g. /uploads/flowers/rose.webp → /uploads/flowers/rose-w640.webp */
export function uploadWidthVariantUrl(src: string, width: number): string {
  const trimmed = preferWebpSrc(src.trim());
  const hashIdx = trimmed.indexOf('#');
  const hash = hashIdx >= 0 ? trimmed.slice(hashIdx) : '';
  const withoutHash = hashIdx >= 0 ? trimmed.slice(0, hashIdx) : trimmed;
  const queryIdx = withoutHash.indexOf('?');
  const query = queryIdx >= 0 ? withoutHash.slice(queryIdx) : '';
  const pathOnly = queryIdx >= 0 ? withoutHash.slice(0, queryIdx) : withoutHash;
  const lastDot = pathOnly.lastIndexOf('.');
  if (lastDot === -1) return trimmed;
  const base = pathOnly.slice(0, lastDot).replace(UPLOAD_VARIANT_RE, '');
  const ext = pathOnly.slice(lastDot);
  return `${base}-w${width}${ext}${query}${hash}`;
}

/** Build srcset for Unsplash URLs and on-disk /uploads/ width variants. */
export function buildResponsiveSrcSet(src: string): string | undefined {
  if (!src?.trim()) return undefined;
  const trimmed = preferWebpSrc(src.trim());

  if (/images\.unsplash\.com/i.test(trimmed)) {
    try {
      const base = new URL(trimmed);
      return RESPONSIVE_WIDTHS.map((w) => {
        const url = new URL(base.toString());
        url.searchParams.set('w', String(w));
        url.searchParams.set('auto', 'format');
        url.searchParams.set('q', '80');
        return `${url.toString()} ${w}w`;
      }).join(', ');
    } catch {
      return undefined;
    }
  }

  if (isUploadImagePath(trimmed)) {
    return RESPONSIVE_WIDTHS.map((w) => `${uploadWidthVariantUrl(trimmed, w)} ${w}w`).join(', ');
  }

  return undefined;
}

/** Common `sizes` hints for product/media layouts. */
export const IMAGE_SIZE_PRESETS = {
  productCard: '(max-width: 640px) 46vw, (max-width: 1024px) 33vw, 280px',
  productGrid: '(max-width: 640px) 50vw, (max-width: 1024px) 33vw, 320px',
  productDetail: '(max-width: 768px) 100vw, 50vw',
  hero: '100vw',
  gallery: '(max-width: 640px) 100vw, 50vw',
} as const;

/** Normalize image src for `<img>` tags; use placeholder only when truly missing. */
export function resolveImageSrc(
  src: string | null | undefined,
  fallback = FLOWER_PLACEHOLDER_IMAGE,
): string {
  if (!src?.trim()) return fallback;
  const trimmed = src.trim();
  if (isLogoPath(trimmed)) return fallback;
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
    return preferWebpSrc(trimmed);
  }
  if (trimmed.startsWith('/') && !trimmed.startsWith('/uploads/')) {
    return preferWebpSrc(trimmed);
  }
  return preferWebpSrc(mediaUrl(trimmed) ?? fallback);
}
