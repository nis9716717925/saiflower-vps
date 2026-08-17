'use client';

import { useEffect, useRef, useState } from 'react';
import { apiSend, setAuth } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { GOOGLE_CLIENT_ID } from '@/lib/google-client-id';
import type { AuthPayload } from '@/lib/types';

const GIS_SRC = 'https://accounts.google.com/gsi/client';

declare global {
  interface Window {
    google?: {
      accounts: {
        id: {
          initialize: (config: {
            client_id: string;
            callback: (response: { credential: string }) => void;
          }) => void;
          renderButton: (
            parent: HTMLElement,
            options: { theme?: string; size?: string; width?: number; text?: string },
          ) => void;
        };
      };
    };
  }
}

interface GoogleSignInButtonProps {
  clientId?: string;
  onSuccess?: () => void;
  onError?: (message: string) => void;
}

function waitForGoogle(maxMs = 10000): Promise<boolean> {
  return new Promise((resolve) => {
    if (window.google?.accounts?.id) {
      resolve(true);
      return;
    }
    const started = Date.now();
    const tick = () => {
      if (window.google?.accounts?.id) {
        resolve(true);
        return;
      }
      if (Date.now() - started >= maxMs) {
        resolve(false);
        return;
      }
      window.setTimeout(tick, 50);
    };
    tick();
  });
}

function loadGisScript(): Promise<void> {
  if (window.google?.accounts?.id) return Promise.resolve();

  const existing = document.querySelector<HTMLScriptElement>('script[data-sf-gis]');
  if (existing) {
    return existing.dataset.sfGisLoaded === '1'
      ? Promise.resolve()
      : new Promise((resolve, reject) => {
          existing.addEventListener('load', () => resolve(), { once: true });
          existing.addEventListener('error', () => reject(new Error('GIS script failed')), {
            once: true,
          });
        });
  }

  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = GIS_SRC;
    script.async = true;
    script.defer = true;
    script.dataset.sfGis = '1';
    script.onload = () => {
      script.dataset.sfGisLoaded = '1';
      resolve();
    };
    script.onerror = () => reject(new Error('GIS script failed'));
    document.head.appendChild(script);
  });
}

export function GoogleSignInButton({
  clientId: clientIdProp,
  onSuccess,
  onError,
}: GoogleSignInButtonProps) {
  const { refreshCart } = useCart();
  const containerRef = useRef<HTMLDivElement>(null);
  const onSuccessRef = useRef(onSuccess);
  const onErrorRef = useRef(onError);
  const [renderState, setRenderState] = useState<'loading' | 'ready' | 'error'>('loading');

  const clientId = clientIdProp?.trim() || GOOGLE_CLIENT_ID;

  onSuccessRef.current = onSuccess;
  onErrorRef.current = onError;

  useEffect(() => {
    if (!clientId || !containerRef.current) return;

    let cancelled = false;

    async function handleCredential(credential: string) {
      try {
        const data = await apiSend<AuthPayload>('/auth/google', 'POST', { credential });
        setAuth(data);
        await refreshCart();
        onSuccessRef.current?.();
      } catch (err) {
        onErrorRef.current?.(err instanceof Error ? err.message : 'Google sign-in failed');
      }
    }

    async function renderButton() {
      if (cancelled || !containerRef.current) return;
      setRenderState('loading');

      try {
        await loadGisScript();
        const ready = await waitForGoogle();
        if (cancelled || !ready || !window.google?.accounts?.id || !containerRef.current) {
          if (!cancelled) setRenderState('error');
          return;
        }

        const node = containerRef.current;
        node.innerHTML = '';
        window.google.accounts.id.initialize({
          client_id: clientId,
          callback: (response) => {
            void handleCredential(response.credential);
          },
        });
        window.google.accounts.id.renderButton(node, {
          theme: 'outline',
          size: 'large',
          width: Math.min(320, node.parentElement?.clientWidth || 320),
          text: 'continue_with',
        });
        if (!cancelled) setRenderState('ready');
      } catch {
        if (!cancelled) setRenderState('error');
      }
    }

    void renderButton();

    return () => {
      cancelled = true;
    };
  }, [clientId, refreshCart]);

  if (!clientId) return null;

  return (
    <div className="sf-google-auth">
      <div className="sf-google-auth__divider" aria-hidden="true">
        <span>or</span>
      </div>
      <div ref={containerRef} className="sf-google-auth__button" />
      {renderState === 'loading' && (
        <p className="sf-google-auth__hint">Loading Google sign-in…</p>
      )}
      {renderState === 'error' && (
        <p className="sf-google-auth__hint sf-google-auth__hint--warn">
          Google sign-in could not load. Refresh the page or use email login below.
        </p>
      )}
    </div>
  );
}
