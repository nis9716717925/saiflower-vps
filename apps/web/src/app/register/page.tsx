'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useState } from 'react';
import { apiSend } from '@/lib/api';
import { GoogleSignInButton } from '@/components/auth/GoogleSignInButton';

export default function RegisterPage() {
  const router = useRouter();
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
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Registration failed');
    } finally {
      setLoading(false);
    }
  }

  return (
    <main className="bg-slate-50 flex items-center justify-center min-h-[70vh] font-sans px-4 py-12">
      <div className="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-100">
        <div className="text-center mb-8">
          <Link href="/" className="flex items-center justify-center gap-2">
            <img src="/uploads/logo_transparent.png" alt="Sai Flower" className="h-12 w-auto" />
          </Link>
          <h1 className="text-2xl font-bold mt-6 text-slate-900">Create Account</h1>
          <p className="text-slate-500 text-sm mt-2">Join Sai Flower for faster checkout</p>
        </div>

        {error && (
          <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-xl text-sm border border-red-100">{error}</div>
        )}
        {success && (
          <div className="mb-4 p-3 bg-green-50 text-green-700 rounded-xl text-sm border border-green-100">{success}</div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Full Name</label>
            <input
              type="text"
              className="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none focus:ring-primary"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
          </div>
          <div>
            <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Email</label>
            <input
              type="email"
              className="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none focus:ring-primary"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <div>
            <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Phone</label>
            <input
              type="tel"
              className="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none focus:ring-primary"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
            />
          </div>
          <div>
            <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Password</label>
            <input
              type="password"
              className="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none focus:ring-primary"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              minLength={6}
            />
          </div>
          <div>
            <label className="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1">Confirm Password</label>
            <input
              type="password"
              className="w-full mt-1 bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 text-sm outline-none focus:ring-primary"
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              required
              minLength={6}
            />
          </div>
          <button
            type="submit"
            disabled={loading}
            className="w-full bg-primary text-white font-bold py-3 rounded-xl shadow-lg disabled:opacity-60"
          >
            {loading ? 'Creating account…' : 'Create Account'}
          </button>
        </form>

        <GoogleSignInButton
          onSuccess={() => {
            router.push('/');
            router.refresh();
          }}
          onError={setError}
        />

        <p className="text-center text-sm text-slate-500 mt-6">
          Already have an account?{' '}
          <Link href="/login" className="text-primary font-bold hover:underline">
            Sign in
          </Link>
        </p>
      </div>
    </main>
  );
}
