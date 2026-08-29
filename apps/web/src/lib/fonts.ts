import {
  Cormorant_Garamond,
  Inter,
  Manrope,
  Plus_Jakarta_Sans,
} from 'next/font/google';

/** Self-hosted Google fonts — same family names as legacy CSS expects. */
export const inter = Inter({
  subsets: ['latin'],
  weight: ['400', '500', '600', '700'],
  display: 'swap',
  variable: '--font-inter',
});

export const manrope = Manrope({
  subsets: ['latin'],
  weight: ['400', '600', '700', '800'],
  display: 'swap',
  variable: '--font-manrope',
});

export const plusJakarta = Plus_Jakarta_Sans({
  subsets: ['latin'],
  weight: ['400', '500', '600', '700'],
  display: 'swap',
  variable: '--font-plus-jakarta',
});

export const cormorant = Cormorant_Garamond({
  subsets: ['latin'],
  weight: ['600', '700'],
  display: 'swap',
  variable: '--font-cormorant',
});

export const siteFontClassName = [
  inter.variable,
  manrope.variable,
  plusJakarta.variable,
  cormorant.variable,
].join(' ');
