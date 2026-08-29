import { Suspense } from 'react';
import { getGoogleClientId } from '@/lib/google-client-id';
import { pageMetadata } from '@/lib/site-metadata';
import { RegisterForm } from './RegisterForm';

export const metadata = pageMetadata({
  title: 'Create Account | Sai Flower',
  description: 'Register for a Sai Flower account to save addresses, track orders, and checkout quickly.',
  canonical: '/register',
  noIndex: true,
});

export default function RegisterPage() {
  const googleClientId = getGoogleClientId();

  return (
    <main className="qc-shell qc-shell--auth">
      <Suspense fallback={<div className="qc-skeleton" />}>
        <RegisterForm googleClientId={googleClientId} />
      </Suspense>
    </main>
  );
}
