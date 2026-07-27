<?php
/**
 * Homepage first view — luxe light theme.
 * Order: category icons → hero split → trust bar.
 * Category mega-nav lives in the universal header (partials/catnav.php).
 * Expects: $slides, $all_circles
 */
require_once __DIR__ . '/../../includes/url_helper.php';

// Category icons — line-art in gold rings (FNP-style)
require_once __DIR__ . '/../../includes/collection_taxonomy.php';

$hpCategoryIcons = [
    ['label' => 'Birthday', 'href' => collection_url('occasion', 'birthday'), 'icon' => 'fa-cake-candles'],
    ['label' => 'Anniversary', 'href' => collection_url('occasion', 'anniversary'), 'icon' => 'fa-heart'],
    ['label' => 'Same Day', 'href' => collection_url('collection', 'same-day-delivery'), 'icon' => 'fa-bolt'],
    ['label' => 'Roses', 'href' => collection_url('flower', 'roses'), 'icon' => 'fa-spa'],
    ['label' => 'Wedding', 'href' => collection_url('occasion', 'wedding'), 'icon' => 'fa-ring'],
    ['label' => 'Plants', 'href' => collection_url('collection', 'plants'), 'icon' => 'fa-leaf'],
    ['label' => 'Personalised', 'href' => '/gifts.php', 'icon' => 'fa-pen-nib'],
    ['label' => 'LUXE', 'href' => collection_url('collection', 'luxury-flowers'), 'icon' => 'fa-gem'],
    ['label' => 'Hampers', 'href' => collection_url('collection', 'hampers'), 'icon' => 'fa-gift'],
    ['label' => 'Occasions', 'href' => '/celebration-calendar', 'icon' => 'fa-calendar-days'],
];

// Trust bar — social proof & guarantees under the hero
$hpTrustItems = [
    ['icon' => 'fa-truck-fast', 'title' => 'Same-Day Delivery', 'sub' => 'Order by 6 PM, delivered today in Delhi NCR'],
    ['icon' => 'fa-star', 'title' => 'Rated 4.8 / 5', 'sub' => 'Loved by 10,000+ happy customers'],
    ['icon' => 'fa-leaf', 'title' => 'Freshness Guaranteed', 'sub' => 'Handcrafted with fresh blooms since 1998'],
    ['icon' => 'fa-shield-halved', 'title' => '100% Secure Payments', 'sub' => 'Safe checkout with trusted gateways'],
];
?>
<div class="hp-fnp-firstview">
    <h1 class="sr-only">Sai Flower — Online flower delivery in Delhi NCR</h1>

    <!-- Category icon row -->
    <section class="hp-fnp-icons" aria-label="Popular categories">
        <div class="hp-fnp-icons__scroll hide-scrollbar">
            <?php foreach ($hpCategoryIcons as $icon): ?>
            <a href="<?= htmlspecialchars(normalize_internal_href($icon['href'])) ?>" class="hp-fnp-icons__item">
                <span class="hp-fnp-icons__img hp-fnp-icons__img--icon">
                    <i class="fas <?= htmlspecialchars($icon['icon']) ?>" aria-hidden="true"></i>
                </span>
                <span class="hp-fnp-icons__label"><?= htmlspecialchars($icon['label']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="lx-hero-split">
        <aside class="lx-hero-split__side" aria-label="Featured promotions">
            <?php include __DIR__ . '/hero_side_slider.php'; ?>
        </aside>
        <div class="lx-hero-split__main">
            <?php
            $lxHeroUseStock = true;
            $lxHeroStock = require __DIR__ . '/../../includes/hero_stock_slides.php';
            include __DIR__ . '/hero_slider.php';
            ?>
        </div>
    </div>

    <!-- Trust bar: social proof & guarantees -->
    <section class="lx-trustbar" aria-label="Why shop with Sai Flower">
        <div class="lx-trustbar__inner">
            <?php foreach ($hpTrustItems as $trust): ?>
            <div class="lx-trustbar__item">
                <span class="lx-trustbar__icon" aria-hidden="true"><i class="fas <?= htmlspecialchars($trust['icon']) ?>"></i></span>
                <div>
                    <p class="lx-trustbar__title"><?= htmlspecialchars($trust['title']) ?></p>
                    <p class="lx-trustbar__sub"><?= htmlspecialchars($trust['sub']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
