'use client';

import { useEffect } from 'react';

/** PHP `/logout` — clear client session and return home. */
export default function LogoutPage() {
  useEffect(() => {
    localStorage.removeItem('saiflower_access_token');
    localStorage.removeItem('saiflower_refresh_token');
    localStorage.removeItem('saiflower_customer');
    window.location.replace('/');
  }, []);

  return (
    <main className="cat-wrap" style={{ padding: '3rem 1rem', textAlign: 'center' }}>
      <p style={{ color: '#6a6258' }}>Signing you out…</p>
    </main>
  );
}
