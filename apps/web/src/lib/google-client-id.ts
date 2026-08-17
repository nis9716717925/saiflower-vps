/** Google OAuth Web client ID — read on the server at request time (not build time). */
export function getGoogleClientId(): string {
  return (
    process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID?.trim() ||
    process.env.OAUTH_GOOGLE_CLIENT_ID?.trim() ||
    ''
  );
}
