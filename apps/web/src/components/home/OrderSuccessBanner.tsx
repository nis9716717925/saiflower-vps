'use client';

import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { Suspense, useEffect, useState } from 'react';

function BannerInner() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const success = searchParams.get('order_success') === '1';
  const oid = searchParams.get('oid');
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    setVisible(success);
  }, [success]);

  if (!visible) return null;

  function dismiss() {
    setVisible(false);
    const url = new URL(window.location.href);
    url.searchParams.delete('order_success');
    url.searchParams.delete('oid');
    router.replace(url.pathname + (url.search || ''));
  }

  return (
    <div
      className="bg-green-50 border-b border-green-100 text-green-900"
      role="status"
      aria-live="polite"
    >
      <div className="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-sm">
        <p>
          <i className="fas fa-check-circle text-green-600 mr-2" aria-hidden="true" />
          Order confirmed
          {oid ? (
            <>
              {' '}
              — reference <strong>#{oid}</strong>
            </>
          ) : null}
          . We opened WhatsApp so you can confirm delivery details with Sai Flower.
        </p>
        <div className="flex items-center gap-3">
          <Link href="/flowers" className="font-bold underline">
            Keep shopping
          </Link>
          <button type="button" className="font-semibold text-green-800" onClick={dismiss}>
            Dismiss
          </button>
        </div>
      </div>
    </div>
  );
}

export function OrderSuccessBanner() {
  return (
    <Suspense fallback={null}>
      <BannerInner />
    </Suspense>
  );
}
