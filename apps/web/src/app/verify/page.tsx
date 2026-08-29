'use client';

import Link from 'next/link';
import { OptimizedImage } from '@/components/ui/OptimizedImage';
import { useRouter, useSearchParams } from 'next/navigation';
import { Suspense, useEffect, useState } from 'react';
import { apiGet, setAuth } from '@/lib/api';
import type { AuthPayload } from '@/lib/types';

type VerifyPayload = AuthPayload & { message?: string };

function VerifyContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const token = searchParams.get('token')?.trim() ?? '';
  const [status, setStatus] = useState<'loading' | 'ok' | 'error'>('loading');
  const [message, setMessage] = useState('Verifying your email…');

  useEffect(() => {
    if (!token) {
      setStatus('error');
      setMessage('Missing verification token. Please use the link from your email.');
      return;
    }
    void (async () => {
      try {
        const data = await apiGet<VerifyPayload>(`/auth/verify?token=${encodeURIComponent(token)}`);
        if (data.accessToken && data.refreshToken && data.customer) {
          setAuth(data);
        }
        setStatus('ok');
        setMessage(data.message ?? 'Your email has been verified. You can now sign in.');
        setTimeout(() => router.replace('/login'), 1800);
      } catch (err) {
        setStatus('error');
        setMessage(err instanceof Error ? err.message : 'Verification failed.');
      }
    })();
  }, [token, router]);

  return (
    <div className="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-100 text-center">
      <OptimizedImage src="/assets/images/logo-transparent.webp" alt="Sai Flower" className="h-12 w-auto mx-auto mb-6" responsive={false} width={168} height={43} />
      <h1 className="text-2xl font-bold mb-3">Email Verification</h1>
      <p
        className={
          status === 'error'
            ? 'text-red-600 text-sm'
            : status === 'ok'
              ? 'text-green-700 text-sm'
              : 'text-slate-500 text-sm'
        }
      >
        {message}
      </p>
      {status !== 'loading' && (
        <Link href="/login" className="inline-block mt-6 text-primary font-bold hover:underline">
          Go to login
        </Link>
      )}
    </div>
  );
}

export default function VerifyPage() {
  return (
    <main className="bg-slate-50 flex items-center justify-center min-h-[70vh] px-4 py-12">
      <Suspense fallback={<div className="text-slate-500">Loading…</div>}>
        <VerifyContent />
      </Suspense>
    </main>
  );
}
