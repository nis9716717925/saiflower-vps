import type {
  ApiResponse,
  AuthPayload,
  CartData,
  CustomerProfile,
  PaginationMeta,
} from './types';

const API = process.env.NEXT_PUBLIC_API_URL || '/api/v1';

const GUEST_KEY = 'saiflower_guest_id';
const ACCESS_KEY = 'saiflower_access_token';
const REFRESH_KEY = 'saiflower_refresh_token';
const CUSTOMER_KEY = 'saiflower_customer';

export class ApiError extends Error {
  status: number;
  errors?: Record<string, string[]>;

  constructor(message: string, status = 400, errors?: Record<string, string[]>) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

function serverApiBase(): string {
  const proxy = process.env.NEXT_PUBLIC_API_PROXY_TARGET ?? 'http://localhost:4000';
  return `${proxy.replace(/\/$/, '')}/api/v1`;
}

export function getApiBase(): string {
  if (typeof window === 'undefined') return serverApiBase();
  return API;
}

/** Build an absolute request URL even when the API base is relative (`/api/v1`). */
export function apiUrl(path: string): string {
  const base = getApiBase().replace(/\/$/, '');
  const suffix = path.startsWith('/') ? path : `/${path}`;
  const joined = `${base}${suffix}`;
  if (joined.startsWith('http://') || joined.startsWith('https://')) return joined;
  if (typeof window !== 'undefined') {
    return new URL(joined, window.location.origin).toString();
  }
  const site = process.env.NEXT_PUBLIC_SITE_URL ?? 'http://localhost:3000';
  return new URL(joined, site).toString();
}

export function getGuestId(): string {
  if (typeof window === 'undefined') return '';
  let id = localStorage.getItem(GUEST_KEY);
  if (!id) {
    id = crypto.randomUUID();
    localStorage.setItem(GUEST_KEY, id);
  }
  return id;
}

export function setGuestId(id: string): void {
  if (typeof window === 'undefined' || !id) return;
  localStorage.setItem(GUEST_KEY, id);
}

export function getAccessToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(ACCESS_KEY);
}

export function getRefreshToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(REFRESH_KEY);
}

export function getCustomer(): CustomerProfile | null {
  if (typeof window === 'undefined') return null;
  const raw = localStorage.getItem(CUSTOMER_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as CustomerProfile;
  } catch {
    return null;
  }
}

export function setAuth(payload: AuthPayload): void {
  if (typeof window === 'undefined') return;
  localStorage.setItem(ACCESS_KEY, payload.accessToken);
  localStorage.setItem(REFRESH_KEY, payload.refreshToken);
  localStorage.setItem(CUSTOMER_KEY, JSON.stringify(payload.customer));
}

export function clearAuth(): void {
  if (typeof window === 'undefined') return;
  localStorage.removeItem(ACCESS_KEY);
  localStorage.removeItem(REFRESH_KEY);
  localStorage.removeItem(CUSTOMER_KEY);
}

function buildHeaders(): HeadersInit {
  const headers: Record<string, string> = {
    Accept: 'application/json',
  };
  if (typeof window !== 'undefined') {
    headers['X-Guest-Id'] = getGuestId();
    const token = getAccessToken();
    if (token) headers.Authorization = `Bearer ${token}`;
  }
  return headers;
}

function formatValidationErrors(errors?: Record<string, string[]>): string | null {
  if (!errors) return null;
  const parts = Object.entries(errors).flatMap(([field, msgs]) =>
    (msgs ?? []).map((m) => `${field}: ${m}`),
  );
  return parts.length ? parts.join(' · ') : null;
}

async function parseResponse<T>(res: Response): Promise<T> {
  const guestHeader = res.headers.get('X-Guest-Id');
  if (guestHeader) setGuestId(guestHeader);

  const json = (await res.json()) as ApiResponse<T>;
  if (!res.ok || json.success === false) {
    const message =
      (json.success === false
        ? formatValidationErrors(json.errors) || json.message
        : null) || `Request failed (${res.status})`;
    throw new ApiError(
      message,
      res.status,
      json.success === false ? json.errors : undefined,
    );
  }
  return json.data as T;
}

let refreshPromise: Promise<boolean> | null = null;

async function tryRefreshSession(): Promise<boolean> {
  if (typeof window === 'undefined') return false;
  const refreshToken = getRefreshToken();
  if (!refreshToken) return false;

  if (!refreshPromise) {
    refreshPromise = (async () => {
      try {
        const res = await fetch(apiUrl('/auth/refresh'), {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Guest-Id': getGuestId(),
          },
          body: JSON.stringify({ refreshToken }),
        });
        const json = (await res.json()) as ApiResponse<AuthPayload>;
        if (!res.ok || json.success === false || !json.data) {
          clearAuth();
          return false;
        }
        setAuth(json.data);
        return true;
      } catch {
        clearAuth();
        return false;
      } finally {
        refreshPromise = null;
      }
    })();
  }
  return refreshPromise;
}

async function request<T>(
  path: string,
  init: RequestInit,
  retried = false,
): Promise<T> {
  const res = await fetch(apiUrl(path), {
    ...init,
    headers: {
      ...buildHeaders(),
      ...(init.headers as Record<string, string> | undefined),
    },
  });

  if (res.status === 401 && !retried && typeof window !== 'undefined') {
    const refreshed = await tryRefreshSession();
    if (refreshed) {
      return request<T>(path, init, true);
    }
  }

  return parseResponse<T>(res);
}

export async function apiGet<T>(path: string, init?: RequestInit): Promise<T> {
  return request<T>(path, {
    ...init,
    method: 'GET',
    cache: init?.cache ?? 'no-store',
  });
}

export async function apiSend<T>(
  path: string,
  method: string,
  body?: unknown,
): Promise<T> {
  return request<T>(path, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: body != null ? JSON.stringify(body) : undefined,
  });
}

export async function fetchProducts(params: Record<string, string | number | undefined>): Promise<{
  items: import('./types').Product[];
  meta?: PaginationMeta;
}> {
  const url = new URL(apiUrl('/products'));
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') url.searchParams.set(key, String(value));
  }
  const res = await fetch(url.toString(), { cache: 'no-store' });
  const json = (await res.json()) as ApiResponse<import('./types').Product[]>;
  if (!res.ok || json.success === false) {
    throw new ApiError(
      json.success === false ? json.message : 'Failed to load products',
      res.status,
    );
  }
  return { items: json.data ?? [], meta: json.meta };
}

export async function fetchProduct(type: string, slug: string): Promise<import('./types').Product> {
  const res = await fetch(apiUrl(`/products/${type}/${encodeURIComponent(slug)}`), {
    cache: 'no-store',
  });
  const json = (await res.json()) as ApiResponse<import('./types').Product>;
  if (!res.ok || json.success === false) {
    throw new ApiError(
      json.success === false ? json.message : 'Product not found',
      res.status,
    );
  }
  if (!json.data) throw new ApiError('Product not found', 404);
  return json.data;
}

export type { CartData, AuthPayload, CustomerProfile };
