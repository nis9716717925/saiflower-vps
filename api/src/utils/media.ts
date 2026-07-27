import { config } from '../config';

type MediaKind = 'category' | 'product' | 'generic';

const ABSOLUTE_URL_REGEX = /^https?:\/\//i;

const sanitize = (value: string): string => value.replace(/^\/+/, '');

export const resolveLegacyMediaUrl = (
  rawValue: string | null | undefined,
  kind: MediaKind,
): string | null => {
  if (!rawValue) {
    return null;
  }

  const value = rawValue.trim();
  if (!value) {
    return null;
  }

  if (ABSOLUTE_URL_REGEX.test(value)) {
    return value;
  }

  const base = config.media.baseUrl;
  const normalized = sanitize(value);

  // Existing rows that already contain relative folders, e.g. uploads/flowers/xyz.jpg
  if (normalized.includes('/')) {
    return `${base}/${normalized}`;
  }

  if (kind === 'category') {
    return `${base}/uploads/categories/${normalized}`;
  }

  if (kind === 'product') {
    return `${base}/uploads/flowers/${normalized}`;
  }

  return `${base}/${normalized}`;
};
