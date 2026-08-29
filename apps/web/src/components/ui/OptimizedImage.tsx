'use client';

import {
  buildResponsiveSrcSet,
  IMAGE_SIZE_PRESETS,
  preferWebpSrc,
  resolveImageSrc,
} from '@saiflower/shared';
import { useCallback, useEffect, useState, type ImgHTMLAttributes, type SyntheticEvent } from 'react';

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

function rasterFallbackUrl(url: string): string | null {
  if (!/\.webp(\?|#|$)/i.test(url)) return null;
  return url.replace(/\.webp(\?|#|$)/i, '.jpeg$1');
}

/**
 * Performance-minded <img>: lazy-load by default, optional srcset for verified hosts.
 * On error: drops srcset, retries alternate raster extension, then optional fallback.
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
  const initialSrcSet =
    srcSetProp ?? (responsive ? buildResponsiveSrcSet(finalSrc) : undefined);

  const [imgSrc, setImgSrc] = useState(finalSrc);
  const [imgSrcSet, setImgSrcSet] = useState<string | undefined>(initialSrcSet);
  const [altExtTried, setAltExtTried] = useState(false);

  useEffect(() => {
    setImgSrc(finalSrc);
    setImgSrcSet(initialSrcSet);
    setAltExtTried(false);
  }, [finalSrc, initialSrcSet]);

  const handleError = useCallback(
    (e: SyntheticEvent<HTMLImageElement>) => {
      const el = e.target as HTMLImageElement;

      if (imgSrcSet) {
        setImgSrcSet(undefined);
        setImgSrc(finalSrc);
        el.removeAttribute('srcset');
        el.src = finalSrc;
        return;
      }

      if (!altExtTried && /\.webp(?:\?|#|$)/i.test(imgSrc)) {
        const altSrc = rasterFallbackUrl(imgSrc);
        if (altSrc && altSrc !== imgSrc) {
          setAltExtTried(true);
          setImgSrc(altSrc);
          el.src = altSrc;
          return;
        }
      }

      if (fallback && el.src !== fallback) {
        setImgSrc(fallback);
        el.src = fallback;
        return;
      }

      onError?.(e);
    },
    [altExtTried, fallback, finalSrc, imgSrc, imgSrcSet, onError],
  );

  return (
    <img
      src={imgSrc}
      alt={alt}
      className={className}
      width={width}
      height={height}
      sizes={sizes}
      srcSet={imgSrcSet}
      loading={priority ? 'eager' : 'lazy'}
      decoding={priority ? 'sync' : 'async'}
      {...(priority ? { fetchPriority: 'high' as const } : { fetchPriority: 'low' as const })}
      onError={handleError}
      {...rest}
    />
  );
}

export { IMAGE_SIZE_PRESETS };
