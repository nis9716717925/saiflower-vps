'use client';

import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useState } from 'react';
import { apiSend } from '@/lib/api';
import { GoogleSignInButton } from '@/components/auth/GoogleSignInButton';
import { OptimizedImage } from '@/components/ui/OptimizedImage';
import { CheckoutProgress } from '@/components/checkout/CheckoutProgress';

interface RegisterFormProps {
  googleClientId: string;
}

export function RegisterForm({ googleClientId }: RegisterFormProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const redirect = searchParams.get('redirect') ?? '/';
  const toCheckout = redirect.includes('/checkout');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);
    try {
      const data = await apiSend<{
        message: string;
        verificationToken?: string;
      }>('/auth/register', 'POST', {
        name,
        email,
        phone,
        password,
        confirmPassword,
      });
      let msg =
        data.message ??
        'Registration successful! Please check your email to verify your account.';
      if (data.verificationToken) {
        msg += ` Dev verify: /verify?token=${data.verificationToken}`;
      }
      setSuccess(msg);
      setTimeout(() => {
        router.push(`/login?redirect=${encodeURIComponent(redirect)}`);
      }, 1200);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Registration failed');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="qc-auth-card">
      {toCheckout ? <CheckoutProgress current="address" /> : null}

      <Link href="/">
        <OptimizedImage src="/assets/images/logo-transparent.webp" alt="Sai Flower" className="qc-auth-logo" responsive={false} width={168} height={43} />
      </Link>
      <h1 className="qc-title" style={{ textAlign: 'center', marginTop: '1rem', fontSize: '1.45rem' }}>
        Create account
      </h1>
      <p className="qc-subtitle" style={{ textAlign: 'center' }}>
        {toCheckout
          ? 'Join Sai Flower for saved addresses and faster WhatsApp checkout.'
          : 'Join Sai Flower for faster checkout'}
      </p>

      {error && (
        <div className="qc-alert qc-alert--err" style={{ marginTop: '1rem' }}>
          {error}
        </div>
      )}
      {success && (
        <div className="qc-alert qc-alert--ok" style={{ marginTop: '1rem' }}>
          {success}
        </div>
      )}

      <form onSubmit={handleSubmit} className="qc-stack" style={{ marginTop: '1rem' }}>
        <div className="qc-field">
          <label className="qc-label">Full name</label>
          <input
            type="text"
            className="qc-input"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
        </div>
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
          <label className="qc-label">Phone</label>
          <input
            type="tel"
            className="qc-input"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
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
            minLength={6}
          />
        </div>
        <div className="qc-field">
          <label className="qc-label">Confirm password</label>
          <input
            type="password"
            className="qc-input"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            required
            minLength={6}
          />
        </div>
        <button type="submit" disabled={loading} className="qc-cta">
          {loading ? 'Creating account…' : 'Create account'}
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
        Already have an account?{' '}
        <Link
          href={`/login?redirect=${encodeURIComponent(redirect)}`}
          style={{ color: '#1f6a4a', fontWeight: 800 }}
        >
          Sign in
        </Link>
      </p>
    </div>
  );
}
