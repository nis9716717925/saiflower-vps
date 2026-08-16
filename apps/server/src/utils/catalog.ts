import type { ProductType } from '@saiflower/shared';
import { mediaUrl as buildMediaUrl } from '@saiflower/shared';

const TABLE_BY_TYPE: Record<ProductType, string> = {
  flower: 'flowers',
  cake: 'cakes',
  gift: 'gifts',
  addon: 'addons',
};

const PREFIX_BY_TYPE: Record<string, string> = {
  flower: 'flowers',
  flowers: 'flowers',
  cake: 'cakes',
  cakes: 'cakes',
  gift: 'gifts',
  gifts: 'gifts',
  event: 'events',
  events: 'events',
};

export function tableForProductType(type: string): string {
  const key = type.toLowerCase() as ProductType;
  return TABLE_BY_TYPE[key] ?? 'flowers';
}

export function normalizeProductType(type?: string | null): ProductType {
  const t = (type ?? 'flower').toLowerCase();
  if (t === 'cake' || t === 'cakes') return 'cake';
  if (t === 'gift' || t === 'gifts') return 'gift';
  if (t === 'addon' || t === 'addons') return 'addon';
  return 'flower';
}

export function productUrl(type: string, slug?: string | null, id?: number): string {
  const prefix = PREFIX_BY_TYPE[type.toLowerCase()] ?? 'flowers';
  if (slug) return `/${prefix}/${slug}`;
  if (id && id > 0) {
    const legacy: Record<string, string> = {
      flower: 'flower-detail',
      cake: 'cake-detail',
      gift: 'gift-detail',
      event: 'event-detail',
    };
    const page = legacy[normalizeProductType(type)] ?? 'flower-detail';
    return `/${page}?id=${id}`;
  }
  return `/${prefix}`;
}

export function mediaUrl(path?: string | null, defaultFolder = ''): string {
  return buildMediaUrl(path, defaultFolder) ?? '';
}

export function publicCustomer(row: {
  id: number | unknown;
  name?: string | null;
  email?: string | null;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  pincode?: string | null;
  isVerified?: number | null;
  avatarUrl?: string | null;
  authProvider?: string | null;
}) {
  return {
    id: Number(row.id),
    name: String(row.name ?? ''),
    email: String(row.email ?? ''),
    phone: row.phone != null ? String(row.phone) : null,
    address: row.address != null ? String(row.address) : null,
    city: row.city != null ? String(row.city) : null,
    pincode: row.pincode != null ? String(row.pincode) : null,
    isVerified: Number(row.isVerified ?? 0) === 1,
    avatarUrl: row.avatarUrl != null ? String(row.avatarUrl) : null,
    authProvider: row.authProvider != null ? String(row.authProvider) : 'local',
  };
}
