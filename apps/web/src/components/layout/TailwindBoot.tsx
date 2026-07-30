'use client';

import Script from 'next/script';
import { useEffect, useState } from 'react';
import { apiGet } from '@/lib/api';

const DEFAULT_PRIMARY = '#2f6f4e';

export function TailwindBoot() {
  const [ready, setReady] = useState(false);

  useEffect(() => {
    void (async () => {
      try {
        const settings = await apiGet<{ theme_primary?: string }>('/settings');
        const primary = settings?.theme_primary?.trim() || DEFAULT_PRIMARY;
        applyTailwindConfig(primary);
      } catch {
        applyTailwindConfig(DEFAULT_PRIMARY);
      } finally {
        setReady(true);
      }
    })();
  }, []);

  return (
    <>
      <Script
        src="https://cdn.tailwindcss.com?plugins=forms,container-queries"
        strategy="beforeInteractive"
        onLoad={() => {
          if (!ready) applyTailwindConfig(DEFAULT_PRIMARY);
        }}
      />
    </>
  );
}

function applyTailwindConfig(primary: string) {
  const tw = (window as Window & { tailwind?: { config?: unknown } }).tailwind;
  if (!tw) return;
  tw.config = {
    darkMode: 'class',
    theme: {
      extend: {
        colors: {
          primary,
          'background-light': '#f6f8f6',
          'background-dark': '#102216',
        },
        fontFamily: {
          display: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
          sans: ['Inter', 'Plus Jakarta Sans', 'sans-serif'],
        },
        borderRadius: {
          DEFAULT: '0.25rem',
          lg: '0.5rem',
          xl: '0.75rem',
          full: '9999px',
        },
      },
    },
  };
}
