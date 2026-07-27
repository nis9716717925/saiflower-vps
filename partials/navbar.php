<?php
// navbar.php - Universal
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Cart Count Logic
$cartCount = 0;
if(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'] ?? $item['qty'] ?? 0;
    }
}
?>

<header class="sticky top-0 z-50 bg-white border-b border-slate-100 shadow-sm sf-site-header">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 sm:gap-3">
            <button
                type="button"
                id="mobileMenuBtn"
                class="lg:hidden inline-flex items-center justify-center w-12 h-12 rounded-full text-gray-700 hover:text-primary hover:bg-slate-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                aria-label="Open menu"
                aria-expanded="false"
                aria-controls="mobileMenu"
            >
                <span class="material-icons-outlined text-2xl sf-mnav-icon-menu" aria-hidden="true">menu</span>
                <span class="material-icons-outlined text-2xl sf-mnav-icon-close" aria-hidden="true">close</span>
            </button>
            
            <a class="flex items-center gap-2" href="/">
                <?php 
                if(!isset($settings)) {
                    require_once __DIR__ . '/../config.php';
                    $sQ = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
                    $settings = mysqli_fetch_assoc($sQ);
                }
                $logoPath = 'uploads/logo_transparent.png';
                if(strpos($logoPath, 'uploads/') !== 0 && !empty($logoPath)) $logoPath = 'uploads/' . $logoPath;
                ?>
                
                <?php if (!empty($logoPath) && file_exists(__DIR__ . '/../' . $logoPath)): ?>
                    <img src="/<?= htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($settings['site_title'] ?? 'Sai Flower') ?> logo" width="180" height="64" class="h-10 sm:h-11 w-auto object-contain">
                <?php else: ?>
                    <span class="text-2xl font-bold text-primary flex items-center gap-2">
                        <span class="material-icons-outlined">local_florist</span>
                        <?= htmlspecialchars($settings['site_title'] ?? 'Sai Flower') ?>
                    </span>
                <?php endif; ?>
            </a>

            <div class="hidden xl:flex items-center gap-7 text-sm font-semibold text-slate-700">
                <a class="hover:text-primary transition-colors" href="/">Home</a>
                <a class="hover:text-primary transition-colors" href="/gallery">Gallery</a>
                <a class="hover:text-primary transition-colors" href="/events">Events</a>
                <a class="hover:text-primary transition-colors" href="/flowers">Flowers</a>
                <a class="hover:text-primary transition-colors" href="/cakes">Cakes</a>
                <a class="hover:text-primary transition-colors" href="/gifts">Gifts</a>
                <a class="hover:text-primary transition-colors" href="/blog">Blog</a>
                <a class="hover:text-primary transition-colors" href="/about">About</a>
                <a class="hover:text-primary transition-colors" href="/contact">Contact</a>
            </div>
        </div>
        
        <div class="hidden md:flex flex-1 justify-center">
            <div class="relative w-full max-w-lg search-wrapper">
                <form action="/search-results" method="GET" class="relative" role="search">
                    <input
                        name="q"
                        id="desktopSearchInput"
                        autocomplete="off"
                        class="w-full bg-slate-50 border border-slate-200 rounded-full pl-5 pr-12 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                        placeholder="Search flowers, occasions, gifts..."
                        type="search"
                        enterkeyhint="search"
                    />
                    <button
                        type="submit"
                        class="material-icons-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg bg-transparent border-none cursor-pointer"
                        aria-label="Search"
                    >
                        search
                    </button>
                    <div id="desktopSearchSuggestions" class="search-suggestions absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden z-[10000] max-h-96 overflow-y-auto" style="display: none;"></div>
                </form>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <button
                type="button"
                class="md:hidden inline-flex items-center justify-center w-12 h-12 rounded-full hover:text-primary hover:bg-slate-50 transition-colors text-gray-700"
                aria-label="Open search"
                aria-expanded="false"
                aria-controls="mobileSearch"
                id="mobileSearchBtn"
            >
                <span class="material-icons-outlined text-2xl">search</span>
            </button>
            
            <a href="/wishlist" class="inline-flex items-center justify-center w-11 h-11 sm:w-10 sm:h-10 rounded-full border border-slate-200 hover:bg-slate-50 hover:text-primary transition-colors text-gray-700" aria-label="Wishlist">
                <span class="material-icons-outlined text-2xl">favorite_border</span>
            </a>
            
            <a href="/cart" class="relative inline-flex items-center justify-center w-11 h-11 sm:w-10 sm:h-10 rounded-full border border-slate-200 hover:bg-slate-50 hover:text-primary transition-colors text-gray-700" aria-label="Cart">
                <span class="material-icons-outlined text-2xl">shopping_cart</span>
                <?php if($cartCount > 0): ?>
                    <span class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold">
                        <?= $cartCount ?>
                    </span>
                <?php endif; ?>
            </a>
            
            <div class="relative group">
                <a
                    class="w-10 h-10 rounded-full border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition-colors text-gray-700"
                    href="<?= isset($_SESSION['customer_id']) ? '/profile' : '/login' ?>"
                    aria-label="Account"
                >
                    <span class="material-icons-outlined md:text-xl">person_outline</span>
                </a>
                
                <div class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 hidden group-hover:block group-focus-within:block z-50">
                    <?php if(isset($_SESSION['customer_id'])): ?>
                        <div class="px-4 py-2 border-b border-slate-50">
                            <p class="text-xs text-slate-500">Signed in as</p>
                            <p class="font-bold text-sm truncate"><?= htmlspecialchars($_SESSION['customer_name']) ?></p>
                        </div>
                        <a href="/profile" class="block px-4 py-2 text-sm hover:bg-slate-50 font-semibold text-primary">My Profile</a>
                        <a href="/profile" class="block px-4 py-2 text-sm hover:bg-slate-50">My Orders</a>
                        <a href="/logout" class="block px-4 py-2 text-sm text-red-500 hover:bg-red-50">Sign Out</a>
                    <?php else: ?>
                        <a href="/login" class="block px-4 py-2 text-sm hover:bg-slate-50 font-bold">Login</a>
                        <a href="/register" class="block px-4 py-2 text-sm hover:bg-slate-50">Create Account</a>
                        <div class="border-t border-slate-50 my-1"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php include __DIR__ . '/catnav.php'; ?>
    
    <div id="mobileMenuBackdrop" class="sf-mnav-backdrop lg:hidden" hidden></div>
    <div id="mobileMenu" class="sf-mnav-drawer hidden lg:hidden" role="dialog" aria-modal="true" aria-label="Site menu" hidden>
        <div class="sf-mnav-drawer__head">
            <a class="sf-mnav-drawer__brand" href="/">Sai Flower</a>
            <button type="button" class="sf-mnav-drawer__close" id="mobileMenuClose" aria-label="Close menu">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>
        <nav class="sf-mnav-drawer__nav" aria-label="Mobile">
            <?php
            $sfCurrentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            $sfNavLinks = [
                ['/', 'Home', ['/', '/index', '/index.php']],
                ['/gallery', 'Gallery', ['/gallery', '/gallery.php']],
                ['/events', 'Events', ['/events', '/events.php']],
                ['/flowers', 'Flowers', ['/flowers', '/flowers.php']],
                ['/cakes', 'Cakes', ['/cakes', '/cakes.php']],
                ['/gifts', 'Gifts', ['/gifts', '/gifts.php']],
                ['/blog', 'Blog', ['/blog', '/blog.php']],
                ['/about', 'About', ['/about', '/about.php']],
                ['/contact', 'Contact', ['/contact', '/contact.php']],
                ['/legal', 'Help & Legal', ['/legal', '/legal.php']],
            ];
            foreach ($sfNavLinks as [$href, $label, $match]):
                $isActive = in_array($sfCurrentPath, $match, true);
            ?>
            <a class="<?= $isActive ? 'is-active' : '' ?>" href="<?= htmlspecialchars($href) ?>"<?= $isActive ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if(isset($_SESSION['customer_id'])): ?>
            <div class="sf-mnav-drawer__account">
                <a class="font-bold text-primary" href="/profile">My Profile</a>
                <a class="text-red-500" href="/logout">Logout</a>
            </div>
        <?php endif; ?>
    </div>

    <div id="mobileSearch" class="hidden md:hidden bg-slate-50 border-t border-slate-100 px-4 pb-4 shadow-sm relative z-50">
        <div class="pt-4 relative search-wrapper">
            <form action="/search-results" method="GET" class="relative" role="search">
                <input
                    name="q"
                    id="mobileSearchInput"
                    autocomplete="off"
                    enterkeyhint="search"
                    inputmode="search"
                    class="w-full bg-white border border-slate-200 rounded-2xl pl-5 pr-12 py-3 text-base focus:ring-2 focus:ring-primary/40 outline-none"
                    placeholder="Search flowers, occasions, gifts..."
                    type="search"
                />
                <button type="submit" class="material-icons-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 w-11 h-11 inline-flex items-center justify-center" aria-label="Search">
                    search
                </button>
                <div id="mobileSearchSuggestions" class="search-suggestions absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden z-[10000] max-h-[60vh] overflow-y-auto" style="display: none;"></div>
            </form>
        </div>
    </div>
</header>

<link rel="stylesheet" href="/assets/css/mobile-nav.css?v=6" />
<link rel="stylesheet" href="/assets/css/catnav.css?v=4" />
<link rel="stylesheet" href="/assets/css/search-suggest.css?v=1" />
<script defer src="/assets/js/catnav.js?v=2"></script>
<script defer src="/assets/js/search-suggest.js?v=1"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const menuBtn = document.getElementById('mobileMenuBtn');
    const menu = document.getElementById('mobileMenu');
    const backdrop = document.getElementById('mobileMenuBackdrop');
    const menuClose = document.getElementById('mobileMenuClose');
    const searchBtn = document.getElementById('mobileSearchBtn');
    const searchPanel = document.getElementById('mobileSearch');

    function setMenuOpen(open) {
        if (!menu || !menuBtn || !backdrop) return;
        menu.classList.toggle('is-open', open);
        menu.classList.toggle('hidden', !open);
        menu.hidden = !open;
        backdrop.classList.toggle('is-open', open);
        backdrop.hidden = !open;
        menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        document.body.classList.toggle('sf-mnav-locked', open);
        if (open && searchPanel) {
            searchPanel.classList.add('hidden');
            if (searchBtn) searchBtn.setAttribute('aria-expanded', 'false');
        }
    }

    if (menuBtn) menuBtn.addEventListener('click', () => setMenuOpen(!menu.classList.contains('is-open')));
    if (menuClose) menuClose.addEventListener('click', () => setMenuOpen(false));
    if (backdrop) backdrop.addEventListener('click', () => setMenuOpen(false));

    if (menu) {
        menu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setMenuOpen(false));
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setMenuOpen(false);
    });

    if (searchBtn && searchPanel) {
        searchBtn.addEventListener('click', () => {
            const willOpen = searchPanel.classList.contains('hidden');
            searchPanel.classList.toggle('hidden');
            searchBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen) {
                setMenuOpen(false);
                const input = document.getElementById('mobileSearchInput');
                if (input) requestAnimationFrame(() => input.focus());
            }
        });
    }
});
</script>
