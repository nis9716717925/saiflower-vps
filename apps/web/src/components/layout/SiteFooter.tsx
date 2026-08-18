import Link from 'next/link';
import { MobileBottomNav } from '@/components/layout/MobileBottomNav';

const WA_HREF = 'https://wa.me/918802004527';
const TEL_HREF = 'tel:918802004527';
const PH_NUM = '8802004527';
const FOOTER_ABOUT =
  'Handcrafting beautiful moments since 1998. Premium flower delivery for weddings, events, and everyday joy.';
const YEAR = new Date().getFullYear();

export function SiteFooter() {
  return (
    <>
      <footer className="sf-site-footer hidden md:block bg-white dark:bg-slate-900 border-t border-primary/10 pt-16 pb-8 mt-20">
        <div className="container mx-auto px-4 text-center">
          <div className="flex flex-col items-center justify-center gap-8 mb-12 border-b border-slate-100 pb-10">
            <div className="flex items-center justify-center gap-4 text-slate-500">
              <i className="fas fa-credit-card text-3xl opacity-80" />
              <div className="text-left flex flex-col justify-center">
                <span className="font-bold text-slate-700 text-lg md:text-xl leading-tight">
                  100% Safe & Secure Payment
                </span>
                <span className="text-xs md:text-sm text-slate-400">Pay using secure payment methods</span>
              </div>
            </div>
            <div className="flex items-center justify-center gap-4 md:gap-6 mt-2">
              <a
                href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/"
                className="w-10 h-10 md:w-12 md:h-12 rounded-2xl md:rounded-[1.2rem] border-2 border-slate-200 flex items-center justify-center text-slate-500 hover:text-white hover:bg-[#1877F2] hover:border-[#1877F2] transition-colors duration-300"
                aria-label="Facebook"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-facebook-f text-lg md:text-xl" />
              </a>
              <a
                href="https://x.com/saiflower03"
                className="w-10 h-10 md:w-12 md:h-12 rounded-2xl md:rounded-[1.2rem] border-2 border-slate-200 flex items-center justify-center text-slate-500 hover:text-white hover:bg-black hover:border-black transition-colors duration-300"
                aria-label="X"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-x-twitter text-lg md:text-xl" />
              </a>
              <a
                href="https://www.instagram.com/saiflowerofficial/"
                className="w-10 h-10 md:w-12 md:h-12 rounded-2xl md:rounded-[1.2rem] border-2 border-slate-200 flex items-center justify-center text-slate-500 hover:text-white hover:bg-[#E4405F] hover:border-[#E4405F] transition-colors duration-300"
                aria-label="Instagram"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-instagram text-lg md:text-xl" />
              </a>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16 text-left md:text-left">
            <div className="text-center md:text-left">
              <Link className="inline-flex items-center gap-2 mb-6" href="/">
                <img
                  src="/assets/images/logo-transparent.png"
                  alt="Sai Flower Logo"
                  width={220}
                  height={64}
                  className="h-16 w-auto object-contain"
                />
              </Link>
              <p className="text-slate-500 text-sm leading-relaxed mb-6">{FOOTER_ABOUT}</p>
              <div className="flex items-center justify-center md:justify-start gap-4">
                <a
                  className="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all"
                  href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/"
                  aria-label="Facebook"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="fab fa-facebook-f" />
                </a>
                <a
                  className="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all"
                  href="https://www.instagram.com/saiflowerofficial/"
                  aria-label="Instagram"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="fab fa-instagram" />
                </a>
                <a
                  className="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all"
                  href={WA_HREF}
                  aria-label="WhatsApp"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="fab fa-whatsapp" />
                </a>
                <a
                  className="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all"
                  href="https://x.com/saiflower03"
                  aria-label="X"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  <i className="fab fa-x-twitter" />
                </a>
              </div>
            </div>

            <div>
              <h4 className="font-bold text-lg mb-6 text-center md:text-left">Explore</h4>
              <ul className="space-y-4 text-sm text-slate-500 text-center md:text-left">
                <li><Link className="hover:text-primary transition-colors" href="/flowers">Shop Flowers</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/personalized">Personalised Gifts</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/celebration-calendar">Celebrations Calendar</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/occasion/birthday">Birthday Flowers</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/flowers/roses">Roses</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/collection/same-day-delivery">Same Day Delivery</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/collection/luxury-flowers">Luxury Flowers</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/cakes">Shop Cakes</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/gifts">Shop Gifts</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/events">Events & Workshops</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/gallery">Floral Gallery</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/blog">Our Blog</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/custom-pages">Custom Pages</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/sitemap">Full Sitemap</Link></li>
              </ul>
            </div>

            <div>
              <h4 className="font-bold text-lg mb-6 text-center md:text-left">Support & Policies</h4>
              <ul className="space-y-4 text-sm text-slate-500 text-center md:text-left">
                <li><Link className="hover:text-primary transition-colors" href="/about">Our Story</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/contact">Contact Us</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/faq">Help Center</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/legal">Help & Legal</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/privacy">Privacy Policy</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/terms">Terms of Use</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/delivery-policy">Delivery Info</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/refund-policy">Refunds</Link></li>
                <li><Link className="hover:text-primary transition-colors" href="/grievnce">Grievance</Link></li>
              </ul>
            </div>

            <div>
              <h4 className="font-bold text-lg mb-6 text-center md:text-left">Get in Touch</h4>
              <div className="space-y-3 text-sm text-slate-500 text-center md:text-left">
                <p className="flex items-center justify-center md:justify-start gap-2">
                  <span className="material-icons-outlined text-sm">phone</span>
                  <a href={TEL_HREF} className="hover:text-primary">{PH_NUM}</a>
                </p>
                <p className="flex items-center justify-center md:justify-start gap-2">
                  <i className="fab fa-whatsapp text-sm" />
                  <a href={WA_HREF} className="hover:text-primary" target="_blank" rel="noopener noreferrer">
                    Chat on WhatsApp
                  </a>
                </p>
                <p className="flex items-center justify-center md:justify-start gap-2">
                  <span className="material-icons-outlined text-sm">shopping_bag</span>
                  <Link href="/flowers" className="hover:text-primary">Shop &amp; checkout to order</Link>
                </p>
              </div>
            </div>
          </div>
          <div className="pt-8 border-t border-slate-100 dark:border-slate-800 text-center">
            <p className="text-xs text-slate-400">© {YEAR} Sai Flower. All rights reserved.</p>
          </div>
        </div>
      </footer>

      <div id="mobileFooterLinks" className="md:hidden">
        <div className="sf-mfooter__inner">
          <div className="sf-mfooter__trust">
            <div className="sf-mfooter__trust-row">
              <i className="fas fa-credit-card text-2xl text-slate-400" aria-hidden="true" />
              <div className="text-left">
                <span className="font-bold text-slate-700 text-sm leading-tight block">
                  100% Safe & Secure Payment
                </span>
                <span className="text-[10px] text-slate-400">Secure payment methods</span>
              </div>
            </div>
            <div className="sf-mfooter__social">
              <a
                href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/"
                aria-label="Facebook"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-facebook-f text-sm" />
              </a>
              <a
                href="https://x.com/saiflower03"
                aria-label="X"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-x-twitter text-sm" />
              </a>
              <a
                href="https://www.instagram.com/saiflowerofficial/"
                aria-label="Instagram"
                target="_blank"
                rel="noopener noreferrer"
              >
                <i className="fab fa-instagram text-sm" />
              </a>
            </div>
          </div>

          <h4>Quick Links</h4>
          <div className="sf-mfooter__links">
            <Link href="/about">About Us</Link>
            <Link href="/contact">Contact</Link>
            <Link href="/privacy">Privacy Policy</Link>
            <Link href="/terms">Terms of Use</Link>
            <Link href="/refund-policy">Refunds</Link>
            <Link href="/delivery-policy">Delivery Info</Link>
          </div>

          <p className="sf-mfooter__copy">© {YEAR} Sai Flower</p>
        </div>
      </div>

      <MobileBottomNav />
    </>
  );
}
