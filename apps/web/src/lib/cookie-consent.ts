export const COOKIE_CONSENT_KEY = 'saiflower_cookie_consent';

export type CookieConsentChoice = 'all' | 'essential';

export function getCookieConsent(): CookieConsentChoice | null {
  if (typeof window === 'undefined') return null;
  const value = localStorage.getItem(COOKIE_CONSENT_KEY);
  if (value === 'all' || value === 'essential') return value;
  return null;
}

export function setCookieConsent(choice: CookieConsentChoice): void {
  localStorage.setItem(COOKIE_CONSENT_KEY, choice);
  document.documentElement.classList.remove('sf-cookie-pending');
}

export function hasCookieConsent(): boolean {
  return getCookieConsent() !== null;
}
