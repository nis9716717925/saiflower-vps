/** Client-safe media URL helpers (no Node fs). */

export const FLOWER_PLACEHOLDER_IMAGE =
  'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=600&q=80';

export const SITE_LOGO_PATH = '/assets/images/logo-transparent.png';

function isLogoPath(value: string): boolean {
  return value === SITE_LOGO_PATH || value.endsWith('/logo-transparent.png');
}

/** Build a public `/uploads/...` URL from a DB path or filename. */
export function mediaUrl(path?: string | null, defaultFolder = ''): string | null {
  if (!path?.trim()) return null;
  const raw = path.trim();
  if (isLogoPath(raw)) return null;
  if (/^https?:\/\//i.test(raw) || raw.startsWith('/')) {
    return raw.replace(/ /g, '%20');
  }
  if (raw.startsWith('uploads/')) {
    return `/${raw}`.replace(/ /g, '%20');
  }
  const folder = defaultFolder ? `${defaultFolder.replace(/^\/|\/$/g, '')}/` : '';
  return `/uploads/${folder}${raw}`.replace(/ /g, '%20');
}

/** Normalize image src for `<img>` tags; use placeholder only when truly missing. */
export function resolveImageSrc(
  src: string | null | undefined,
  fallback = FLOWER_PLACEHOLDER_IMAGE,
): string {
  if (!src?.trim()) return fallback;
  const trimmed = src.trim();
  if (isLogoPath(trimmed)) return fallback;
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('/')) {
    return trimmed;
  }
  return mediaUrl(trimmed) ?? fallback;
}
