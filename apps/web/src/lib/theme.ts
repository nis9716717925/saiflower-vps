import type { CSSProperties } from 'react';

export const DEFAULT_THEME_PRIMARY = '#2f6f4e';

export function hexToRgbChannels(hex: string): string {
  const normalized = hex.replace('#', '').trim();
  if (!/^[0-9a-fA-F]{6}$/.test(normalized)) {
    return '47 111 78';
  }
  const r = parseInt(normalized.slice(0, 2), 16);
  const g = parseInt(normalized.slice(2, 4), 16);
  const b = parseInt(normalized.slice(4, 6), 16);
  return `${r} ${g} ${b}`;
}

export async function getThemePrimary(): Promise<string> {
  try {
    const base = process.env.NEXT_PUBLIC_API_PROXY_TARGET ?? 'http://localhost:4000';
    const res = await fetch(`${base.replace(/\/$/, '')}/api/v1/settings`, {
      next: { revalidate: 300 },
    });
    if (!res.ok) return DEFAULT_THEME_PRIMARY;
    const json = (await res.json()) as { data?: { theme_primary?: string | null } };
    const primary = json.data?.theme_primary?.trim();
    return primary || DEFAULT_THEME_PRIMARY;
  } catch {
    return DEFAULT_THEME_PRIMARY;
  }
}

export function themeCssVars(primary: string): CSSProperties {
  return {
    '--color-primary-rgb': hexToRgbChannels(primary),
    '--primary': primary,
  } as CSSProperties;
}
