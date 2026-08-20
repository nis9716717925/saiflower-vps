'use client';

import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useState } from 'react';
import { apiSend, setAuth } from '@/lib/api';
import { useCart } from '@/components/providers/AppProviders';
import { GoogleSignInButton } from '@/components/auth/GoogleSignInButton';
import { CheckoutProgress } from '@/components/checkout/CheckoutProgress';
import type { AuthPayload } from '@/lib/types';

interface LoginFormProps {
  googleClientId: string;
}

export function LoginForm({ googleClientId }: LoginFormProps) {
  const router = useRouter();
  const { refreshCart } = useCart();
  const searchParams = useSearchParams();
  const redirect = searchParams.get('redirect') ?? '/';
  const toCheckout = redirect.includes('/checkout');
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
    <div className="qc-auth-card">
      {toCheckout ? <CheckoutProgress current="address" /> : null}

      <Link href="/" className="qc-auth-logo-wrap">
        <img src="/assets/images/logo-transparent.webp" alt="Sai Flower" className="qc-auth-logo" />
      </Link>
      <h1 className="qc-title" style={{ textAlign: 'center', marginTop: '1rem', fontSize: '1.45rem' }}>
        Welcome back
      </h1>
      <p className="qc-subtitle" style={{ textAlign: 'center' }}>
        {toCheckout
          ? 'Sign in to continue to delivery address and WhatsApp checkout.'
          : 'Sign in to your Sai Flower account'}
      </p>

      {toCheckout && (
        <div className="qc-trust" style={{ margin: '1rem 0 0.25rem' }}>
          <div className="qc-trust__item">
            <span className="material-icons-outlined">lock</span>
            <div>
              <strong>Secure login</strong>
              <span>Cart stays saved</span>
            </div>
          </div>
          <div className="qc-trust__item">
            <span className="material-icons-outlined">location_on</span>
            <div>
              <strong>Faster delivery</strong>
              <span>Reuse addresses</span>
            </div>
          </div>
          <div className="qc-trust__item">
            <span className="material-icons-outlined">bolt</span>
            <div>
              <strong>Quick pay</strong>
              <span>WhatsApp confirm</span>
            </div>
          </div>
        </div>
      )}

      {error && (
        <div className="qc-alert qc-alert--err" style={{ marginTop: '1rem' }}>
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit} className="qc-stack" style={{ marginTop: '1rem' }}>
        <div className="qc-field">
          <label className="qc-label">Email</label>
          <input
            type="email"
            className="qc-input"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
        </div>
        <div className="qc-field">
          <label className="qc-label">Password</label>
          <input
            type="password"
            className="qc-input"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        <button type="submit" disabled={loading} className="qc-cta">
          {loading ? 'Signing in…' : toCheckout ? 'Continue to checkout' : 'Sign In'}
        </button>
      </form>

      <GoogleSignInButton
        clientId={googleClientId}
        onSuccess={() => {
          router.push(redirect);
          router.refresh();
        }}
        onError={setError}
      />

      <p className="qc-muted" style={{ textAlign: 'center', marginTop: '1.1rem' }}>
        Don&apos;t have an account?{' '}
        <Link
          href={`/register?redirect=${encodeURIComponent(redirect)}`}
          style={{ color: '#1f6a4a', fontWeight: 800 }}
        >
          Create one
        </Link>
      </p>
    </div>
  );
}
