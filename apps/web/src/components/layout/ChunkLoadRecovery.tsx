'use client';

import { useEffect } from 'react';

const RELOAD_KEY = 'sf-chunk-reload';

function isChunkFailure(message: string): boolean {
  return /ChunkLoadError|Loading chunk [\w-]+ failed|Failed to fetch dynamically imported module|error loading dynamically imported module/i.test(
    message,
  );
}

/**
 * One-shot hard reload when a stale tab hits missing hashed chunks after deploy.
 * Does not loop: sessionStorage blocks a second reload in the same tab.
 */
export function ChunkLoadRecovery() {
  useEffect(() => {
    const reloadOnce = (reason: string) => {
      try {
        if (sessionStorage.getItem(RELOAD_KEY) === '1') return;
        sessionStorage.setItem(RELOAD_KEY, '1');
      } catch {
        // private mode / blocked storage — still attempt one reload via flag on window
        const w = window as Window & { __sfChunkReloaded?: boolean };
        if (w.__sfChunkReloaded) return;
        w.__sfChunkReloaded = true;
      }
      console.warn('[saiflower] chunk load failure — reloading once:', reason);
      window.location.reload();
    };

    const onError = (event: ErrorEvent) => {
      const message = event.message || event.error?.message || '';
      if (isChunkFailure(message)) reloadOnce(message);
    };

    const onRejection = (event: PromiseRejectionEvent) => {
      const reason = event.reason;
      const message =
        typeof reason === 'string'
          ? reason
          : reason?.message || reason?.toString?.() || '';
      if (isChunkFailure(String(message))) reloadOnce(String(message));
    };

    window.addEventListener('error', onError);
    window.addEventListener('unhandledrejection', onRejection);
    return () => {
      window.removeEventListener('error', onError);
      window.removeEventListener('unhandledrejection', onRejection);
    };
  }, []);

  return null;
}
