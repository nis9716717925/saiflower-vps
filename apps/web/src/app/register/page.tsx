import { Suspense } from 'react';
import { getGoogleClientId } from '@/lib/google-client-id';
import { RegisterForm } from './RegisterForm';

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
