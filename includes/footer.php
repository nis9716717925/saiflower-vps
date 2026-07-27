<?php
global $conn;
if (!isset($settings) || empty($settings)) {
    if (isset($conn)) {
        $q_set = @mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
        if($q_set) { $settings = mysqli_fetch_assoc($q_set); } else { $settings = []; }
    } else { $settings = []; }
}
$wa_num = '8802004527';
$ph_num = '8802004527';
$email_display = !empty($settings['email']) ? str_replace('📧', '', $settings['email']) : 'info@saiflowers.com';
$wa_href = 'https://wa.me/918802004527';
$tel_href = 'tel:918802004527';
?>

<footer class="hidden md:block bg-white dark:bg-slate-900 border-t border-primary/10 pt-16 pb-8 mt-20">
    <div class="container mx-auto px-4 text-center">
        
        <div class="flex flex-col items-center justify-center gap-8 mb-12 border-b border-slate-100 pb-10">
            <div class="flex items-center justify-center gap-4 text-slate-500">
                <i class="fas fa-credit-card text-3xl opacity-80"></i>
                <div class="text-left flex flex-col justify-center">
                    <span class="font-bold text-slate-700 text-lg md:text-xl leading-tight">100% Safe & Secure Payment</span>
                    <span class="text-xs md:text-sm text-slate-400">Pay using secure payment methods</span>
                </div>
            </div>
            <div class="flex items-center justify-center gap-4 md:gap-6 mt-2">
                <a href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/" class="w-10 h-10 md:w-12 md:h-12 rounded-2xl md:rounded-[1.2rem] border-2 border-slate-200 flex items-center justify-center text-slate-500 hover:text-white hover:bg-[#1877F2] hover:border-[#1877F2] transition-colors duration-300" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-facebook-f text-lg md:text-xl"></i>
                </a>
                <a href="https://x.com/saiflower03" class="w-10 h-10 md:w-12 md:h-12 rounded-2xl md:rounded-[1.2rem] border-2 border-slate-200 flex items-center justify-center text-slate-500 hover:text-white hover:bg-black hover:border-black transition-colors duration-300" aria-label="X" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-x-twitter text-lg md:text-xl"></i>
                </a>
                <a href="https://www.instagram.com/saiflowerofficial/" class="w-10 h-10 md:w-12 md:h-12 rounded-2xl md:rounded-[1.2rem] border-2 border-slate-200 flex items-center justify-center text-slate-500 hover:text-white hover:bg-[#E4405F] hover:border-[#E4405F] transition-colors duration-300" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-instagram text-lg md:text-xl"></i>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16 text-left md:text-left">
            <div class="text-center md:text-left">
                <a class="inline-flex items-center gap-2 mb-6" href="/">
                    <img src="/uploads/logo_transparent.png" alt="Sai Flower Logo" width="220" height="64" class="h-16 w-auto object-contain">
                </a>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    <?= !empty($settings['footer_about']) ? htmlspecialchars($settings['footer_about']) : 'Handcrafting beautiful moments since 1998. Premium flower delivery for weddings, events, and everyday joy.' ?>
                </p>
                <div class="flex items-center justify-center md:justify-start gap-4">
                    <a class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all" href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                    <a class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all" href="https://www.instagram.com/saiflowerofficial/" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                    <a class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all" href="<?= htmlspecialchars($wa_href) ?>" aria-label="WhatsApp" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i></a>
                    <a class="w-9 h-9 rounded-full bg-primary/10 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all" href="https://x.com/saiflower03" aria-label="X" target="_blank" rel="noopener noreferrer"><i class="fab fa-x-twitter"></i></a>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-lg mb-6 text-center md:text-left">Explore</h4>
                <ul class="space-y-4 text-sm text-slate-500 text-center md:text-left">
                    <li><a class="hover:text-primary transition-colors" href="/flowers">Shop Flowers</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/personalized">Personalised Gifts</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/celebration-calendar">Celebrations Calendar</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/occasion/birthday">Birthday Flowers</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/flowers/roses">Roses</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/collection/same-day-delivery">Same Day Delivery</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/collection/luxury-flowers">Luxury Flowers</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/cakes">Shop Cakes</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/gifts">Shop Gifts</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/events">Events & Workshops</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/gallery">Floral Gallery</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/blog">Our Blog</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/custom-pages">Custom Pages</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/sitemap">Full Sitemap</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-lg mb-6 text-center md:text-left">Support & Policies</h4>
                <ul class="space-y-4 text-sm text-slate-500 text-center md:text-left">
                    <li><a class="hover:text-primary transition-colors" href="/about">Our Story</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/contact">Contact Us</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/legal">Help & Legal</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/privacy">Privacy Policy</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/terms">Terms of Use</a></li>
                    <li><a class="hover:text-primary transition-colors" href="/delivery-policy">Delivery Info</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-lg mb-6 text-center md:text-left">Get in Touch</h4>
                <div class="space-y-3 text-sm text-slate-500 text-center md:text-left">
                    <p class="flex items-center justify-center md:justify-start gap-2"><span class="material-icons-outlined text-sm">phone</span> <a href="<?= htmlspecialchars($tel_href) ?>" class="hover:text-primary"><?= htmlspecialchars($ph_num) ?></a></p>
                    <p class="flex items-center justify-center md:justify-start gap-2"><i class="fab fa-whatsapp text-sm"></i> <a href="<?= htmlspecialchars($wa_href) ?>" class="hover:text-primary" target="_blank" rel="noopener noreferrer">Chat on WhatsApp</a></p>
                    <p class="flex items-center justify-center md:justify-start gap-2"><span class="material-icons-outlined text-sm">shopping_bag</span> <a href="/flowers" class="hover:text-primary">Shop &amp; checkout to order</a></p>
                </div>
            </div>
        </div>
        <div class="pt-8 border-t border-slate-100 dark:border-slate-800 text-center">
            <p class="text-xs text-slate-400">© <?= date('Y') ?> Sai Flower. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Mobile Footer Links -->
<div id="mobileFooterLinks" class="md:hidden pt-6 pb-2 px-4 mt-4 mb-2 bg-transparent border-t border-slate-100 dark:border-slate-800">
    <div class="container mx-auto opacity-95 text-center">
        <div class="flex flex-col items-center justify-center gap-4 mb-6 py-4 border-b border-slate-100/50">
            <div class="flex items-center justify-center gap-3">
                <i class="fas fa-credit-card text-2xl text-slate-400"></i>
                <div class="text-left flex flex-col justify-center">
                    <span class="font-bold text-slate-700 text-sm leading-tight">100% Safe & Secure Payment</span>
                    <span class="text-[10px] text-slate-400">Secure payment methods</span>
                </div>
            </div>
            <div class="flex items-center justify-center gap-4 mt-1">
                <a href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/" class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-white hover:bg-[#1877F2] transition-colors duration-300" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-facebook-f text-sm"></i>
                </a>
                <a href="https://x.com/saiflower03" class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-white hover:bg-black transition-colors duration-300" aria-label="X" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-x-twitter text-sm"></i>
                </a>
                <a href="https://www.instagram.com/saiflowerofficial/" class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-400 hover:text-white hover:bg-[#E4405F] transition-colors duration-300" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-instagram text-sm"></i>
                </a>
            </div>
        </div>

        <h4 class="font-bold text-sm mb-4 text-center text-primary uppercase tracking-[0.15em]">Quick Links</h4>
        <div class="grid grid-cols-2 gap-y-3 gap-x-3 text-[13px] text-slate-500 font-medium px-2 mb-6">
            <a class="hover:text-primary transition-colors py-1" href="/about">About Us</a>
            <a class="hover:text-primary transition-colors py-1" href="/contact">Contact</a>
            <a class="hover:text-primary transition-colors py-1" href="/privacy">Privacy Policy</a>
            <a class="hover:text-primary transition-colors py-1" href="/terms">Terms of Use</a>
            <a class="hover:text-primary transition-colors py-1" href="/refund-policy">Refunds</a>
            <a class="hover:text-primary transition-colors py-1" href="/delivery-policy">Delivery Info</a>
        </div>
        
        <div class="text-center mb-4 border-t border-slate-100 pt-6 flex flex-col gap-2">
            <a href="/custom-pages" class="text-[13px] text-slate-500 font-medium hover:text-primary transition-colors">Custom Pages</a>
            <a href="/sitemap" class="text-[13px] text-slate-500 font-medium hover:text-primary transition-colors">View Full Sitemap</a>
        </div>

        <div class="text-center mt-4 text-[10px] text-slate-400 font-medium tracking-[0.05em] uppercase pb-2">
            © <?= date('Y') ?> Sai Flower
        </div>
    </div>
</div>

<div id="mobileBottomNav" class="md:hidden fixed bottom-0 left-0 w-full bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 z-[100] px-2 py-3">
    <div class="flex items-center justify-around">
        <a href="/" class="flex flex-col items-center gap-1 text-slate-500 hover:text-primary transition-all">
            <span class="material-icons-outlined text-xl">home</span>
            <span class="text-[10px] font-bold uppercase tracking-tighter">Home</span>
        </a>
        <a href="/flowers" class="flex flex-col items-center gap-1 text-slate-500 hover:text-primary transition-all">
            <span class="material-icons-outlined text-xl">shopping_cart</span>
            <span class="text-[10px] font-bold uppercase tracking-tighter">Shop</span>
        </a>
        <a href="<?= htmlspecialchars($wa_href) ?>" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center -mt-8" aria-label="WhatsApp Sai Flowers">
            <div class="w-14 h-14 bg-[#25D366] text-white rounded-full flex items-center justify-center shadow-xl border-4 border-white dark:border-slate-900 scale-110">
                <i class="fab fa-whatsapp text-2xl"></i>
            </div>
            <span class="text-[10px] font-black text-[#25D366] uppercase mt-1">WhatsApp</span>
        </a>
        <a href="<?= htmlspecialchars($tel_href) ?>" class="flex flex-col items-center gap-1 text-slate-500 hover:text-primary transition-all">
            <span class="material-icons-outlined text-xl">call</span>
            <span class="text-[10px] font-bold uppercase tracking-tighter">Call</span>
        </a>
        <a href="/contact" class="flex flex-col items-center gap-1 text-slate-500 hover:text-primary transition-all">
            <span class="material-icons-outlined text-xl">location_on</span>
            <span class="text-[10px] font-bold uppercase tracking-tighter">Visit</span>
        </a>
    </div>
</div>

<div id="mobileBottomNavSpacer" class="md:hidden h-20"></div>

<style>
    .md\:hidden a { -webkit-tap-highlight-color: transparent; }
    
    @keyframes pulse-green {
        0% { transform: scale(1.1); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.4); }
        70% { transform: scale(1.15); box-shadow: 0 0 0 10px rgba(37, 211, 102, 0); }
        100% { transform: scale(1.1); box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
    }
    #mobileBottomNav a div { animation: pulse-green 2.5s infinite; }

    @media (prefers-reduced-motion: reduce) {
        #mobileBottomNav a div { animation: none !important; }
    }

    #mobileBottomNav {
        padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));
    }
</style>
