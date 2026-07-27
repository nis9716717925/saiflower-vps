export interface ApiResponse<T = unknown> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
  meta?: PaginationMeta;
}

export interface PaginationMeta {
  page: number;
  limit: number;
  total: number;
  totalPages: number;
  hasNextPage: boolean;
  hasPrevPage: boolean;
}

export interface PaginationQuery {
  page?: number;
  limit?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
  search?: string;
}

export function successResponse<T>(
  message: string,
  data?: T,
  meta?: PaginationMeta,
): ApiResponse<T> {
  const response: ApiResponse<T> = { success: true, message };
  if (data !== undefined) response.data = data;
  if (meta) response.meta = meta;
  return response;
}

export function errorResponse(
  message: string,
  errors?: Record<string, string[]>,
): ApiResponse {
  const response: ApiResponse = { success: false, message };
  if (errors) response.errors = errors;
  return response;
}

export function parsePagination(query: Record<string, unknown>): Required<PaginationQuery> {
  const page = Math.max(1, parseInt(String(query.page ?? '1'), 10) || 1);
  const limit = Math.min(100, Math.max(1, parseInt(String(query.limit ?? '20'), 10) || 20));
  const sortBy = String(query.sortBy ?? 'createdAt');
  const sortOrder = query.sortOrder === 'asc' ? 'asc' : 'desc';
  const search = query.search ? String(query.search).trim() : '';

  return { page, limit, sortBy, sortOrder, search };
}

export function buildPaginationMeta(
  page: number,
  limit: number,
  total: number,
): PaginationMeta {
  const totalPages = Math.ceil(total / limit) || 1;
  return {
    page,
    limit,
    total,
    totalPages,
    hasNextPage: page < totalPages,
    hasPrevPage: page > 1,
  };
}
