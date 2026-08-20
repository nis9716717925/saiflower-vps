'use client';

import { preferWebpSrc, resolveImageSrc } from '@saiflower/shared';
import type { ImgHTMLAttributes } from 'react';

type ImgProps = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt' | 'loading'>;

export interface OptimizedImageProps extends ImgProps {
  src: string | null | undefined;
  alt: string;
  /** Use eager + high fetch priority for LCP/hero images only. */
  priority?: boolean;
  /** Fallback when src is empty or fails to load. */
  fallback?: string;
  /** Prefer WebP rewrite (default true). */
  webp?: boolean;
}

/**
 * Performance-minded <img>: WebP URLs, lazy-load by default, async decode.
 * Keeps native <img> so existing CSS continues to work (Next Image stays unoptimized on VPS).
 */
export function OptimizedImage({
  src,
  alt,
  priority = false,
  fallback,
  webp = true,
  className,
  width,
  height,
  onError,
  ...rest
}: OptimizedImageProps) {
  const resolved = resolveImageSrc(src, fallback);
  const finalSrc = webp ? preferWebpSrc(resolved) : resolved;

  return (
    <img
      src={finalSrc}
      alt={alt}
      className={className}
      width={width}
      height={height}
      loading={priority ? 'eager' : 'lazy'}
      decoding={priority ? 'sync' : 'async'}
      {...(priority ? { fetchPriority: 'high' as const } : { fetchPriority: 'low' as const })}
      onError={(e) => {
        if (fallback && (e.target as HTMLImageElement).src !== fallback) {
          (e.target as HTMLImageElement).src = fallback;
        }
        onError?.(e);
      }}
      {...rest}
    />
  );
}
