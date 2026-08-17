/**
 * Google OAuth Web client ID (GIS).
 * Public by design — used in the browser for "Continue with Google".
 */
export const GOOGLE_CLIENT_ID =
  process.env.NEXT_PUBLIC_GOOGLE_CLIENT_ID?.trim() ||
  process.env.OAUTH_GOOGLE_CLIENT_ID?.trim() ||
  '591122868014-s8k3fdmgnb8kl186vpnner41d6bisb9b.apps.googleusercontent.com';

/** Read on the server at request time (falls back to the constant above). */
export function getGoogleClientId(): string {
  return GOOGLE_CLIENT_ID;
}
