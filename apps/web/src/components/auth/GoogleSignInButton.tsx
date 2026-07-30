'use client';

import { useEffect, useRef } from 'react';
import { apiSend, setAuth } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import type { AuthPayload } from '@/lib/types';

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
  onSuccess?: () => void;
  onError?: (message: string) => void;
}

export function GoogleSignInButton({ onSuccess, onError }: GoogleSignInButtonProps) {
  const { refreshCart } = useCart();
  const containerRef = useRef<HTMLDivElement>(null);
  const clientId = process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID?.trim() ?? '';

  useEffect(() => {
    if (!clientId || !containerRef.current) return;

    let cancelled = false;

    async function handleCredential(credential: string) {
      try {
        const data = await apiSend<AuthPayload>('/auth/google', 'POST', { credential });
        setAuth(data);
        await refreshCart();
        onSuccess?.();
      } catch (err) {
        onError?.(err instanceof Error ? err.message : 'Google sign-in failed');
      }
    }

    function render() {
      if (cancelled || !containerRef.current || !window.google?.accounts?.id) return;
      containerRef.current.innerHTML = '';
      window.google.accounts.id.initialize({
        client_id: clientId,
        callback: (response) => {
          void handleCredential(response.credential);
        },
      });
      window.google.accounts.id.renderButton(containerRef.current, {
        theme: 'outline',
        size: 'large',
        width: 320,
        text: 'continue_with',
      });
    }

    const existing = document.querySelector<HTMLScriptElement>('script[data-sf-gis]');
    if (existing) {
      render();
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.dataset.sfGis = '1';
    script.onload = () => render();
    document.head.appendChild(script);

    return () => {
      cancelled = true;
    };
  }, [clientId, onError, onSuccess, refreshCart]);

  if (!clientId) return null;

  return (
    <div className="mt-4">
      <div className="relative my-4 text-center text-xs uppercase tracking-widest text-slate-400">
        <span className="bg-white px-3 relative z-10">or</span>
        <span className="absolute left-0 right-0 top-1/2 border-t border-slate-100" />
      </div>
      <div ref={containerRef} className="flex justify-center" />
    </div>
  );
}
