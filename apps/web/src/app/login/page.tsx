'use client';

import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { Suspense, useState } from 'react';
import { apiSend, setAuth } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { GoogleSignInButton } from '@/components/auth/GoogleSignInButton';
import type { AuthPayload } from '@/lib/types';

function LoginForm() {
  const router = useRouter();
  const { refreshCart } = useCart();
  const searchParams = useSearchParams();
  const redirect = searchParams.get('redirect') ?? '/';
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function finishLogin(data: AuthPayload) {
    setAuth(data);
    await refreshCart();
    router.push(redirect);
    router.refresh();
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const data = await apiSend<AuthPayload>('/auth/login', 'POST', { email, password });
      await finishLogin(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login failed');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-100">
      <div className="text-center mb-8">
        <Link href="/" className="flex items-center justify-center gap-2">
          <img src="/uploads/logo_transparent.png" alt="Sai Flower" className="h-12 w-auto" />
        </Link>
        <h1 className="text-2xl font-bold mt-6 text-slate-900">Welcome Back</h1>
        <p className="text-slate-500 text-sm mt-2">Sign in to your Sai Flower account</p>
      </div>

      {error && (
        <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm border border-red-100">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
            Email
          </label>
          <input
            type="email"
            className="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div>
          <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">
            Password
          </label>
          <input
            type="password"
            className="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        <button
          type="submit"
          disabled={loading}
          className="w-full bg-primary text-white font-bold py-3 rounded-xl shadow-lg disabled:opacity-60"
        >
          {loading ? 'Signing in…' : 'Sign In'}
        </button>
      </form>

      <GoogleSignInButton
        onSuccess={() => {
          router.push(redirect);
          router.refresh();
        }}
        onError={setError}
      />

      <p className="text-center text-sm text-slate-500 mt-6">
        Don&apos;t have an account?{' '}
        <Link href="/register" className="text-primary font-bold hover:underline">
          Create one
        </Link>
      </p>
    </div>
  );
}

export default function LoginPage() {
  return (
    <main className="bg-slate-50 flex items-center justify-center min-h-[70vh] font-sans px-4 py-12">
      <Suspense fallback={<div className="text-slate-500">Loading…</div>}>
        <LoginForm />
      </Suspense>
    </main>
  );
}
