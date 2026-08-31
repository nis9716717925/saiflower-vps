import type { ReactNode } from 'react';

type MarqueeSpeed = 'slow' | 'normal' | 'fast';

type InfiniteMarqueeProps = {
  children: ReactNode;
  /** Optional alternate copy for the loop (e.g. links-only clone without mega menus). */
  duplicate?: ReactNode;
  className?: string;
  trackClassName?: string;
  speed?: MarqueeSpeed;
  pauseOnHover?: boolean;
  'aria-label'?: string;
};

/**
 * Seamless horizontal loop via duplicated content + CSS transform animation.
 * GPU-friendly (no JS timers); second copy is aria-hidden for screen readers.
 */
export function InfiniteMarquee({
  children,
  duplicate,
  className = '',
  trackClassName = '',
  speed = 'normal',
  pauseOnHover = true,
  'aria-label': ariaLabel,
}: InfiniteMarqueeProps) {
  const trackClasses = [
    'lx-marquee__track',
    `lx-marquee__track--${speed}`,
    pauseOnHover ? 'lx-marquee__track--pause-hover' : '',
    trackClassName,
  ]
    .filter(Boolean)
    .join(' ');

  const loopCopy = duplicate ?? children;

  return (
    <div className={`lx-marquee ${className}`.trim()} aria-label={ariaLabel}>
      <div className={trackClasses}>
        <div className="lx-marquee__group">{children}</div>
        <div className="lx-marquee__group" aria-hidden="true">
          {loopCopy}
        </div>
      </div>
    </div>
  );
}
