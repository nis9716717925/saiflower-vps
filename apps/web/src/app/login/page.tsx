import { Suspense } from 'react';
import { getGoogleClientId } from '@/lib/google-client-id';
import { pageMetadata } from '@/lib/site-metadata';
import { LoginForm } from './LoginForm';

export const metadata = pageMetadata({
  title: 'Login | Sai Flower',
  description: 'Sign in to your Sai Flower account to track orders and checkout faster.',
  canonical: '/login',
  noIndex: true,
});

export default function LoginPage() {
  const googleClientId = getGoogleClientId();

  return (
    <main className="qc-shell qc-shell--auth">
      <Suspense fallback={<div className="qc-skeleton" />}>
        <LoginForm googleClientId={googleClientId} />
      </Suspense>
    </main>
  );
}
