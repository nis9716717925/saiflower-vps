import { Suspense } from 'react';
import { getGoogleClientId } from '@/lib/google-client-id';
import { LoginForm } from './LoginForm';

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
