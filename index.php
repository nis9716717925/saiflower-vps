<?php
// index.php - IGP Style Homepage (static content)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/homepage_data.php';
require_once __DIR__ . '/includes/homepage_premium.php';
require_once __DIR__ . '/includes/seo_helper.php';

$slides = homepage_get_slides();
$all_circles = homepage_get_circles();
$allSections = homepage_get_sections();
$allItemsGrouped = homepage_get_section_items_grouped();
$reviewsData = homepage_get_reviews();
$promosData = homepage_get_promos();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php include __DIR__.'/partials/tailwind_config.php'; ?>
    <title>Sai Flower | Online Flower &amp; Bouquet Delivery Delhi</title>
    <meta name="description" content="Order fresh flowers and bouquets online from Sai Flower. Same-day flower delivery for birthdays, anniversaries, weddings, and special occasions in Delhi NCR.">
    <meta name="keywords" content="flower delivery Delhi, online bouquets, same day delivery, wedding flowers, Sai Flower">
    <?php
    set_page_canonical_url(seo_site_base_url() . '/');
    echo render_canonical_link();
    echo render_social_meta_tags(
        'Sai Flower | Online Flower & Bouquet Delivery Delhi',
        'Order fresh flowers and bouquets online from Sai Flower. Same-day flower delivery for birthdays, anniversaries, weddings, and special occasions in Delhi NCR.'
    );
    ?>
    <meta name="author" content=" Sai Flower ">
    <meta name="publisher" content=" Sai Flower ">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_organization_json_ld();
    echo generate_website_json_ld();
    ?>
    <?php
    $favVer = time();
    $favUrl = '/favicon.png';
    if(file_exists(__DIR__ . '/favicon.png')) $favUrl = '/favicon.png';
    ?>
    <link rel="icon" type="image/png" href="<?= $favUrl ?>?v=<?= $favVer ?>">
    <link rel="apple-touch-icon" href="<?= $favUrl ?>?v=<?= $favVer ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
    <link rel="stylesheet" href="/assets/css/homepage-premium.css?v=6" />
    <link rel="stylesheet" href="/assets/css/homepage-firstview.css?v=3" />
    <link rel="stylesheet" href="/assets/css/homepage-luxe.css?v=20" />
    <link rel="stylesheet" href="/assets/css/homepage-mobile.css?v=3" />
    <script defer src="/assets/js/homepage-premium.js"></script>
    <script defer src="/assets/js/homepage-luxe.js?v=9"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fdfcf9; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .circle-nav-item img { transition: transform 0.3s ease; }
        .circle-nav-item:hover img { transform: scale(1.1); box-shadow: 0 10px 20px -5px rgba(0,0,0,0.2); }
        
        .product-card { transition: all 0.3s ease; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); }

        .igp-border-container {
            border: 1.5px solid #d4af37;
            border-radius: 2rem;
            overflow: hidden;
            background: white;
        }

        .grid-item-custom {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .v-separator::after {
            content: "";
            position: absolute;
            right: 0;
            top: 15%;
            bottom: 15%;
            width: 1px;
            background-color: #d4af37;
            opacity: 0.8;
        }

        .h-separator::before {
            content: "";
            position: absolute;
            bottom: 0;
            left: 15%;
            right: 15%;
            height: 1px;
            background-color: #d4af37;
            opacity: 0.8;
        }

        /* Master Grid Styles */
        .master-section-container {
            width: 100%;
            margin: 0 auto;
            background-color: transparent;
            padding: 0;
            position: relative;
            overflow: hidden;
        }
        .master-product-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            width: 92%;
            max-width: 440px; 
            margin: 0 auto;
            z-index: 2;
            box-sizing: border-box;
        }
        .master-line { position: absolute; background: #d4af37; z-index: 1; opacity: 0.8; }
        .master-v-line { width: 2px; height: 100%; left: 50%; top: 0; transform: translateX(-50%); }
        .master-h-line { height: 2px; width: 100%; top: 50%; left: 0; transform: translateY(-50%); }

        .master-card {
            background: white;
            border: none;
            overflow: visible;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: auto !important;
        }
        .master-card-img-wrapper {
            width: 100%;
            height: auto;
            overflow: hidden;
            position: relative;
            border-radius: inherit;
            display: block;
        }
        .master-card img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .m-square .master-card-img-wrapper { aspect-ratio: 1/1; }
        .m-rect   .master-card-img-wrapper { aspect-ratio: 3/4; }
        .m-circle .master-card-img-wrapper { aspect-ratio: 1/1; border-radius: 50%; }
        .m-heart  .master-card-img-wrapper {
            aspect-ratio: 1/1;
            clip-path: url(#heartPath);
            -webkit-clip-path: url(#heartPath);
        }
        
        .master-label-ext {
            margin-top: 8px;
            text-align: center;
            font-size: 11px;
            color: #111;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            width: 100%;
            line-height: 1.2;
            word-wrap: break-word;
            min-height: 22px;
            padding: 0 4px;
        }

        .heart-carousel-container {
            width: 100%;
            padding: 10px 0;
            overflow-x: auto;
            display: flex;
            gap: 16px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            justify-content: center;
        }
        .heart-carousel-item { 
            flex: 0 0 calc(25% - 12px);
            scroll-snap-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .heart-carousel-item .master-card { width: 100%; max-width: 160px; }

        /* Circle Carousel */
        .circle-carousel-container {
            width: 100%;
            padding: 10px 0;
            overflow-x: auto;
            display: flex;
            gap: 20px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            justify-content: center;
        }
        .circle-carousel-item {
            flex: 0 0 calc(20% - 16px);
            scroll-snap-align: center;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .circle-carousel-item .master-card { width: 100%; max-width: 140px; }

        /* ===== SEARCH DROPDOWN OVERLAP & ANIMATION FIX ===== */
        .search-wrapper,
        .search-container,
        .search-box,
        #searchBox {
            position: relative;
            z-index: 9999 !important;
        }

        #searchSuggestions,
        .search-suggestions,
        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background: #fff;
            z-index: 10000 !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            /* Step 1 Kill Animations */
            animation: none !important;
            transition: none !important;
            transform: none !important;
            opacity: 1 !important;
        }

        #searchSuggestions *,
        .search-suggestions *,
        .search-item,
        .search-result-item {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }

        #heroSlider,
        #sliderTrack,
        .group\/slider,
        .dyn-slide,
        .slider-dot,
        .active-pill {
            position: relative;
            z-index: 1 !important;
        }

        @media (max-width: 640px) {
            .master-section-container { 
                padding: 0;
                display: flex;
                justify-content: center;
            }

            .master-product-grid { 
                gap: 12px;
                width: 94%;
                max-width: 340px;
                margin: 0 auto;
            }

            .heart-carousel-container { 
                gap: 12px;
                padding: 15px 12px;
                justify-content: flex-start;
                overflow-x: auto;
                width: 100%;
            }

            .heart-carousel-item { 
                flex: 0 0 calc(50% - 6px);
                scroll-snap-align: start;
            }

            .circle-carousel-container {
                gap: 10px;
                padding: 12px 10px;
                justify-content: flex-start;
                width: 100%;
            }

            .circle-carousel-item {
                flex: 0 0 calc(33% - 7px);
                scroll-snap-align: start;
            }
        }

    </style>
    <meta name="google-site-verification" content="eB9VORqGBu2riVGwdtWi5Ycg4aQyGLOlVnl1Elc7_sI" />
    <meta name="google-site-verification" content="_3OJRaDzm_rnfg5OqKQWnN6jxKp0bhQh6KkPVcU8Cio" />
</head>
<body class="bg-white text-gray-800 homepage-premium">



<?php include __DIR__.'/partials/navbar.php'; ?>

<?php include __DIR__ . '/partials/homepage/hero_firstview.php'; ?>

<?php
$dynamicSecIndex = 0;
?>

<?php include __DIR__ . '/partials/homepage/promo_trio.php'; ?>

<?php
$hpProductSliderKeys = ['best-sellers', 'same-day-surprises', 'occasions', 'for-every-occasions'];
include __DIR__ . '/partials/homepage/homepage_product_sliders.php';
?>

<?php include __DIR__ . '/partials/homepage/fav_flowers.php'; ?>

<?php include __DIR__ . '/partials/homepage/tailored_occasions.php'; ?>

<?php
$hpProductSliderKeys = ['on-demand'];
include __DIR__ . '/partials/homepage/homepage_product_sliders.php';
?>

<?php include __DIR__ . '/partials/homepage/same_day_tiles.php'; ?>

<?php include __DIR__ . '/partials/homepage/celebrations_calendar.php'; ?>

<?php include __DIR__ . '/partials/homepage/relationships.php'; ?>

<?php include __DIR__ . '/partials/homepage/explore_luxury.php'; ?>

<?php
$hpProductSliderKeys = ['newly-added'];
include __DIR__ . '/partials/homepage/homepage_product_sliders.php';
include __DIR__ . '/partials/homepage/gift_finder.php';
include __DIR__ . '/partials/homepage/send_gifts_abroad.php';
?>

<?php include __DIR__ . '/partials/homepage/about_us.php'; ?>

<?php include __DIR__ . '/partials/homepage/stats_band.php'; ?>

<?php include __DIR__ . '/partials/homepage/testimonials.php'; ?>

<!-- How it works -->
<section class="lx-steps" aria-labelledby="lx-steps-title">
    <div class="lx-steps__inner">
        <div class="lx-section-head">
            <span class="lx-kicker">Effortless Gifting</span>
            <h2 id="lx-steps-title">How It Works</h2>
            <p>From browsing to their doorstep in three simple steps — most Delhi NCR orders arrive the same day.</p>
        </div>
        <div class="lx-steps__grid">
            <div class="lx-step">
                <span class="lx-step__num" aria-hidden="true">1</span>
                <h3 class="lx-step__title">Pick the Perfect Bloom</h3>
                <p class="lx-step__text">Browse curated bouquets, cakes and hampers — or use the Gift Finder to shop by occasion, recipient or budget.</p>
            </div>
            <div class="lx-step">
                <span class="lx-step__num" aria-hidden="true">2</span>
                <h3 class="lx-step__title">Checkout Securely</h3>
                <p class="lx-step__text">Add a personal message, choose your delivery slot and pay safely through trusted payment gateways.</p>
            </div>
            <div class="lx-step">
                <span class="lx-step__num" aria-hidden="true">3</span>
                <h3 class="lx-step__title">We Handcraft &amp; Deliver</h3>
                <p class="lx-step__text">Our florists arrange your order with freshly cut flowers and hand-deliver it — same-day, express or midnight.</p>
            </div>
        </div>
    </div>
</section>

<!-- FAQs -->
<section class="lx-faq" aria-labelledby="lx-faq-title">
    <div class="lx-faq__inner">
        <div class="lx-section-head">
            <span class="lx-kicker">Good to Know</span>
            <h2 id="lx-faq-title">Frequently Asked Questions</h2>
        </div>
        <div class="lx-faq__list">
            <details class="lx-faq__item">
                <summary class="lx-faq__q">Do you offer same-day flower delivery in Delhi NCR?</summary>
                <div class="lx-faq__a">Yes — place your order before 6 PM and we deliver the same day across Delhi NCR. Express and midnight delivery slots are also available on select products.</div>
            </details>
            <details class="lx-faq__item">
                <summary class="lx-faq__q">How do you keep the flowers fresh during delivery?</summary>
                <div class="lx-faq__a">Every bouquet is made to order with freshly cut blooms, hydrated right up to dispatch and packaged carefully so it arrives looking its best. Freshness is guaranteed on every order.</div>
            </details>
            <details class="lx-faq__item">
                <summary class="lx-faq__q">Can I add a cake, chocolates or a personal note to my order?</summary>
                <div class="lx-faq__a">Absolutely. You can pair your flowers with <a href="/cakes">cakes</a> and <a href="/gifts">gift hampers</a> at checkout, and include a free personalised message card with every order.</div>
            </details>
            <details class="lx-faq__item">
                <summary class="lx-faq__q">Do you handle weddings and event décor?</summary>
                <div class="lx-faq__a">Yes — Sai Flower specialises in wedding flowers, corporate events and large-scale décor: stage backdrops, centrepieces, bridal bouquets and full venue styling. <a href="/contact">Contact us</a> for a custom quote.</div>
            </details>
            <details class="lx-faq__item">
                <summary class="lx-faq__q">Is online payment safe on your website?</summary>
                <div class="lx-faq__a">Completely. All payments are processed through secure, trusted payment gateways — we never store your card details.</div>
            </details>
        </div>
        <p class="lx-faq__footer">Still have questions? <a href="/faq.php">Read all FAQs</a> or <a href="/contact.php">talk to our team</a>.</p>
    </div>
</section>

<?php include __DIR__ . '/partials/commutes_map_embed.php'; ?>

<!-- Final CTA -->
<section class="lx-final-cta" aria-labelledby="lx-final-cta-title">
    <div class="lx-final-cta__shell">
        <p class="lx-final-cta__kicker">Handcrafted Since 1998</p>
        <h2 id="lx-final-cta-title" class="lx-final-cta__title">Make Someone's Day Bloom Today</h2>
        <p class="lx-final-cta__sub">Order before 6 PM for same-day delivery in Delhi NCR. Fresh, handcrafted bouquets — delivered with love and a personal note.</p>
        <div class="lx-final-cta__actions">
            <a href="/flowers.php" class="lx-btn-primary">Shop Fresh Flowers <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            <a href="/contact.php" class="lx-btn-secondary">Plan a Wedding or Event</a>
        </div>
        <p class="lx-final-cta__note"><i class="fas fa-shield-halved" aria-hidden="true"></i> 100% secure payments &nbsp;·&nbsp; Freshness guaranteed &nbsp;·&nbsp; Rated 4.8/5 by 10,000+ customers</p>
    </div>
</section>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
    // Hero slider — desktop multi-card step includes gap
    const track = document.getElementById('sliderTrack');
    const slides = track ? Array.from(track.children) : [];
    let currentSlide = 0;
    let autoSlideInterval;

    function getHeroSlideStep() {
        if (!track || slides.length === 0) return 0;
        const gap = parseFloat(getComputedStyle(track).gap) || 0;
        return slides[0].offsetWidth + gap;
    }

    function applyHeroSlideTransform() {
        if (!track || slides.length === 0) return;
        const step = getHeroSlideStep();
        track.style.transform = `translateX(-${currentSlide * step}px)`;
    }
    
    function moveSlide(dir) {
        if(slides.length === 0) return;
        currentSlide = (currentSlide + dir + slides.length) % slides.length;
        applyHeroSlideTransform();
        updateTracker();
        syncSideToMain();
    }

    if(slides.length > 0) {
        window.addEventListener('resize', () => {
            if (currentSlide >= slides.length) currentSlide = 0;
            applyHeroSlideTransform();
            if (typeof applySideSlideTransform === 'function') applySideSlideTransform();
        });
    }

    // Left side hero mini-slider — advances in sync with main hero
    const sideTrack = document.getElementById('sideSliderTrack');
    const sideSlides = sideTrack ? Array.from(sideTrack.children) : [];
    let currentSideSlide = 0;

    function applySideSlideTransform() {
        if (!sideTrack || sideSlides.length === 0) return;
        sideTrack.style.transform = 'translateX(-' + (currentSideSlide * 100) + '%)';
    }

    function syncSideToMain() {
        if (sideSlides.length === 0) return;
        currentSideSlide = currentSlide % sideSlides.length;
        applySideSlideTransform();
        updateSideTracker();
    }

    function updateSideTracker() {
        const sideTracker = document.getElementById('sideSliderTracker');
        if (!sideTracker || sideSlides.length <= 1) return;
        sideTracker.innerHTML = '';
        for (let i = 0; i < sideSlides.length; i++) {
            const dot = document.createElement('div');
            dot.className = i === currentSideSlide ? 'slider-dot active-pill' : 'slider-dot';
            sideTracker.appendChild(dot);
        }
    }

    if (sideSlides.length > 0) {
        syncSideToMain();
    }
    
    function updateTracker() {
        const tracker = document.getElementById('sliderTracker');
        if(!tracker || slides.length <= 1) return;
        tracker.innerHTML = '';
        for(let i=0; i<slides.length; i++) {
            const dot = document.createElement('div');
            if(i === currentSlide) {
                dot.className = 'slider-dot active-pill';
            } else {
                dot.className = 'slider-dot';
                dot.onclick = () => {
                    const diff = i - currentSlide;
                    moveSlide(diff);
                    startAutoSlide();
                };
            }
            tracker.appendChild(dot);
        }
        
        const activeDot = tracker.querySelector('.active-pill');
        if (activeDot) {
            activeDot.style.animation = 'none';
            activeDot.offsetHeight;
            activeDot.style.animation = null;
        }
    }
    
    function startAutoSlide() {
        stopAutoSlide();
        autoSlideInterval = setInterval(() => moveSlide(1), 3000);
    }
    
    function stopAutoSlide() {
        if (autoSlideInterval) clearInterval(autoSlideInterval);
    }
    
    if(track && slides.length > 0) {
        let touchStartX = 0;
        let touchEndX = 0;
        track.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoSlide();
        }, {passive: true});
        track.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoSlide();
        }, {passive: true});
        function handleSwipe() {
            if (touchEndX < touchStartX - 50) moveSlide(1);
            if (touchEndX > touchStartX + 50) moveSlide(-1);
        }
        startAutoSlide();
        updateTracker();
        const heroSlider = document.getElementById('heroSlider');
        if (heroSlider) {
            heroSlider.addEventListener('mouseenter', stopAutoSlide);
            heroSlider.addEventListener('mouseleave', startAutoSlide);
        }
    }

    window.dynSliders = {};

    window.moveDynSlide = function(secId, dir) {
        if(window.dynSliders[secId]) {
            window.dynSliders[secId].moveSlide(dir);
            window.dynSliders[secId].startAutoSlide();
        }
    };

    window.initDynSlider = function(secId) {
        const track = document.getElementById('dynTrack_' + secId);
        if(!track) return;
        const slideEls = Array.from(track.querySelectorAll('.dyn-slide'));
        if(slideEls.length <= 1) return;

        const sd = {
            current: 0,
            total: slideEls.length,
            interval: null,

            moveSlide(dir) {
                this.current = (this.current + dir + this.total) % this.total;
                track.style.transform = `translateX(-${this.current * (100 / this.total)}%)`;
                this.updateTracker();
            },

            updateTracker() {
                const tracker = document.getElementById('dynTracker_' + secId);
                if(!tracker) return;
                tracker.innerHTML = '';
                for(let i = 0; i < this.total; i++) {
                    const dot = document.createElement('div');
                    if(i === this.current) {
                        dot.className = 'slider-dot active-pill';
                    } else {
                        dot.className = 'slider-dot';
                        dot.onclick = () => { this.moveSlide(i - this.current); this.startAutoSlide(); };
                    }
                    tracker.appendChild(dot);
                }
                const activeDot = tracker.querySelector('.active-pill');
                if (activeDot) { activeDot.style.animation = 'none'; activeDot.offsetHeight; activeDot.style.animation = null; }
            },

            startAutoSlide() { this.stopAutoSlide(); this.interval = setInterval(() => this.moveSlide(1), 3000); },
            stopAutoSlide() { if(this.interval) clearInterval(this.interval); }
        };

        window.dynSliders[secId] = sd;

        let touchStartX = 0;
        track.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; sd.stopAutoSlide(); }, {passive: true});
        track.addEventListener('touchend', e => {
            const diff = e.changedTouches[0].screenX - touchStartX;
            if (diff < -50) sd.moveSlide(1);
            if (diff > 50)  sd.moveSlide(-1);
            sd.startAutoSlide();
        }, {passive: true});

        const wrapper = document.getElementById('dynSlider_' + secId);
        if(wrapper) {
            wrapper.addEventListener('mouseenter', () => sd.stopAutoSlide());
            wrapper.addEventListener('mouseleave', () => sd.startAutoSlide());
        }

        sd.startAutoSlide();
        sd.updateTracker();
    };
</script>
    <svg style="position: absolute; width: 0; height: 0;" aria-hidden="true" focusable="false">
        <defs>
            <filter id="wiggle">
                <feTurbulence type="fractalNoise" baseFrequency="0.05" numOctaves="3" result="noise" />
                <feDisplacementMap in="SourceGraphic" in2="noise" scale="3" />
            </filter>
            <clipPath id="heartPath" clipPathUnits="objectBoundingBox">
                <path d="M0.5,0.9 L0.44,0.84 C0.22,0.64 0.08,0.51 0.08,0.36 C0.08,0.23 0.18,0.13 0.31,0.13 C0.38,0.13 0.45,0.16 0.5,0.21 C0.55,0.16 0.62,0.13 0.69,0.13 C0.82,0.13 0.92,0.23 0.92,0.36 C0.92,0.51 0.78,0.64 0.56,0.84 L0.5,0.9 Z" />
            </clipPath>
        </defs>
    </svg>
</body>
</html><?php // Last Build: Fri Mar 13 14:49:27 IST 2026 ?>
