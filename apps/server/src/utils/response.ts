import type { PaginationMeta } from '@saiflower/shared';

export interface ApiResponseBody<T = unknown> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
  meta?: PaginationMeta;
}

export function successResponse<T>(
  message: string,
  data?: T,
  meta?: PaginationMeta,
): ApiResponseBody<T> {
  const response: ApiResponseBody<T> = { success: true, message };
  if (data !== undefined) response.data = data;
  if (meta) response.meta = meta;
  return response;
}

export function errorResponse(
  message: string,
  errors?: Record<string, string[]>,
): ApiResponseBody {
  const response: ApiResponseBody = { success: false, message };
  if (errors) response.errors = errors;
  return response;
}

export function buildPaginationMeta(page: number, limit: number, total: number): PaginationMeta {
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
