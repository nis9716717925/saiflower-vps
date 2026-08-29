'use client';

import {
  buildResponsiveSrcSet,
  IMAGE_SIZE_PRESETS,
  preferWebpSrc,
  resolveImageSrc,
} from '@saiflower/shared';
import type { ImgHTMLAttributes } from 'react';

type ImgProps = Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt' | 'loading' | 'sizes'>;

export interface OptimizedImageProps extends ImgProps {
  src: string | null | undefined;
  alt: string;
  /** Use eager + high fetch priority for LCP/hero images only. */
  priority?: boolean;
  /** Fallback when src is empty or fails to load. */
  fallback?: string;
  /** Prefer WebP rewrite (default true). */
  webp?: boolean;
  sizes?: string;
  /** Disable auto srcset (default: enabled for supported hosts). */
  responsive?: boolean;
}

/**
 * Performance-minded <img>: WebP URLs, optional srcset, lazy-load by default, async decode.
 */
export function OptimizedImage({
  src,
  alt,
  priority = false,
  fallback,
  webp = true,
  sizes,
  responsive = true,
  className,
  width,
  height,
  onError,
  srcSet: srcSetProp,
  ...rest
}: OptimizedImageProps) {
  const resolved = resolveImageSrc(src, fallback);
  const finalSrc = webp ? preferWebpSrc(resolved) : resolved;
  const srcSet = srcSetProp ?? (responsive ? buildResponsiveSrcSet(finalSrc) : undefined);

  return (
    <img
      src={finalSrc}
      alt={alt}
      className={className}
      width={width}
      height={height}
      sizes={sizes}
      srcSet={srcSet}
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

export { IMAGE_SIZE_PRESETS };
