<?php
$hpOccasionTabs = homepage_get_occasion_tabs($conn);
$hpActiveTab = $hpOccasionTabs[0] ?? null;
$hpInitialProducts = $hpActiveTab ? homepage_fetch_occasion_products($conn, $hpActiveTab, 10) : [];

$hpOccasionTabIcons = [
    'birthday' => 'fa-cake-candles',
    'anniversary' => 'fa-ring',
    'love' => 'fa-heart',
    'wedding' => 'fa-champagne-glasses',
    'congratulations' => 'fa-party-horn',
    'sympathy' => 'fa-dove',
    'thankyou' => 'fa-hands-holding-heart',
];
?>
<section class="hp-section hp-occasions" aria-labelledby="hp-occasions-title" id="hpOccasionsSection">
    <div class="hp-container">
        <div class="hp-section-head">
            <h2 id="hp-occasions-title" class="hp-section-title">Tailored For Your Occasions</h2>
            <p class="hp-section-sub">Handpicked bouquets for every celebration — switch tabs to explore.</p>
        </div>

        <div class="hp-occasion-tabs hide-scrollbar" role="tablist" aria-label="Occasions">
            <?php foreach ($hpOccasionTabs as $i => $tab): ?>
            <button type="button"
                    role="tab"
                    class="hp-occasion-tab<?= $i === 0 ? ' is-active' : '' ?>"
                    data-occasion="<?= htmlspecialchars($tab['key']) ?>"
                    data-cta="<?= htmlspecialchars($tab['cta']) ?>"
                    data-link="<?= htmlspecialchars($tab['list_link']) ?>"
                    aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                    id="hp-tab-<?= htmlspecialchars($tab['key']) ?>">
                <i class="fas <?= htmlspecialchars($hpOccasionTabIcons[$tab['key']] ?? 'fa-gift') ?>" aria-hidden="true"></i>
                <span><?= htmlspecialchars($tab['label']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="hp-occasion-carousel-wrap">
            <button type="button" class="hp-occasion-nav hp-occasion-nav--prev" id="hpOccasionPrev" aria-label="Previous products">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <div class="hp-occasion-track-wrap">
                <div class="hp-occasion-track hide-scrollbar" id="hpOccasionTrack" role="tabpanel" aria-live="polite">
                    <?= homepage_render_occasion_cards($hpInitialProducts) ?>
                </div>
                <div class="hp-occasion-skeleton" id="hpOccasionSkeleton" hidden aria-hidden="true">
                    <?php for ($s = 0; $s < 4; $s++): ?>
                    <div class="hp-skeleton-card"></div>
                    <?php endfor; ?>
                </div>
            </div>
            <button type="button" class="hp-occasion-nav hp-occasion-nav--next" id="hpOccasionNext" aria-label="Next products">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        <div class="hp-occasion-footer">
            <a href="<?= htmlspecialchars($hpActiveTab['list_link'] ?? '/flowers.php') ?>" class="hp-occasion-viewall" id="hpOccasionViewAll">
                <?= htmlspecialchars($hpActiveTab['cta'] ?? 'View All Gifts') ?>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <p class="hp-trust-strip">
            <span class="hp-trust-strip__stars" aria-hidden="true">⭐</span>
            Rated <strong>4.8</strong> / 5 &nbsp;|&nbsp; Trusted by <strong>4,62,543</strong> Happy Customers
        </p>
    </div>
</section>
