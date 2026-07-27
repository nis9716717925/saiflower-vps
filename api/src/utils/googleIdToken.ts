import { createPublicKey, createVerify } from 'crypto';
import { config } from '../config';
import { UnauthorizedError } from '../utils/errors';

type GoogleJwk = {
  kid: string;
  kty: string;
  alg?: string;
  n: string;
  e: string;
  use?: string;
};

type GoogleIdTokenPayload = {
  iss: string;
  aud: string | string[];
  azp?: string;
  sub: string;
  email: string;
  email_verified: boolean | string;
  name?: string;
  given_name?: string;
  family_name?: string;
  picture?: string;
  exp: number;
  iat: number;
  nonce?: string;
};

let cachedKeys: { fetchedAt: number; byKid: Record<string, GoogleJwk> } | null = null;
const CACHE_TTL_MS = 60 * 60 * 1000;

function base64UrlToBuffer(input: string): Buffer {
  const padded = input + '='.repeat((4 - (input.length % 4)) % 4);
  return Buffer.from(padded.replace(/-/g, '+').replace(/_/g, '/'), 'base64');
}

async function fetchGoogleJwks(force = false): Promise<Record<string, GoogleJwk>> {
  if (!force && cachedKeys && Date.now() - cachedKeys.fetchedAt < CACHE_TTL_MS) {
    return cachedKeys.byKid;
  }

  const res = await fetch('https://www.googleapis.com/oauth2/v3/certs');
  if (!res.ok) {
    throw new UnauthorizedError('Unable to fetch Google signing keys');
  }
  const data = (await res.json()) as { keys?: GoogleJwk[] };
  if (!data.keys?.length) {
    throw new UnauthorizedError('Invalid Google JWKS response');
  }

  const byKid: Record<string, GoogleJwk> = {};
  for (const key of data.keys) {
    if (key.kid) byKid[key.kid] = key;
  }
  cachedKeys = { fetchedAt: Date.now(), byKid };
  return byKid;
}

function jwkToPem(jwk: GoogleJwk): string {
  const keyObject = createPublicKey({
    key: {
      kty: 'RSA',
      n: jwk.n,
      e: jwk.e,
    },
    format: 'jwk',
  });
  return keyObject.export({ type: 'spki', format: 'pem' }).toString();
}

export async function verifyGoogleIdToken(idToken: string): Promise<GoogleIdTokenPayload> {
  const clientId = config.oauth.google.clientId;
  if (!clientId) {
    throw new UnauthorizedError('Google OAuth is not configured');
  }

  const parts = idToken.split('.');
  if (parts.length !== 3) {
    throw new UnauthorizedError('Malformed Google ID token');
  }

  const [headerB64, payloadB64, signatureB64] = parts;
  const header = JSON.parse(base64UrlToBuffer(headerB64).toString('utf8')) as {
    alg?: string;
    kid?: string;
  };
  const payload = JSON.parse(base64UrlToBuffer(payloadB64).toString('utf8')) as GoogleIdTokenPayload;

  if (header.alg !== 'RS256' || !header.kid) {
    throw new UnauthorizedError('Unsupported Google ID token');
  }

  let keys = await fetchGoogleJwks();
  if (!keys[header.kid]) {
    keys = await fetchGoogleJwks(true);
  }
  const jwk = keys[header.kid];
  if (!jwk) {
    throw new UnauthorizedError('Unknown Google signing key');
  }

  const verifier = createVerify('RSA-SHA256');
  verifier.update(`${headerB64}.${payloadB64}`);
  verifier.end();
  const valid = verifier.verify(jwkToPem(jwk), base64UrlToBuffer(signatureB64));
  if (!valid) {
    throw new UnauthorizedError('Invalid Google ID token signature');
  }

  const issuers = ['accounts.google.com', 'https://accounts.google.com'];
  if (!issuers.includes(payload.iss)) {
    throw new UnauthorizedError('Invalid Google token issuer');
  }

  const audiences = Array.isArray(payload.aud) ? payload.aud : [payload.aud];
  if (!audiences.includes(clientId)) {
    throw new UnauthorizedError('Invalid Google token audience');
  }

  if (payload.azp && payload.azp !== clientId) {
    throw new UnauthorizedError('Invalid Google authorized party');
  }

  if (!payload.exp || payload.exp < Math.floor(Date.now() / 1000)) {
    throw new UnauthorizedError('Google token has expired');
  }

  if (!payload.email || !payload.sub) {
    throw new UnauthorizedError('Google token missing required claims');
  }

  const verified =
    payload.email_verified === true || payload.email_verified === 'true';
  if (!verified) {
    throw new UnauthorizedError('Google email is not verified');
  }

  return payload;
}
