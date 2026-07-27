<?php
/**
 * Pick Their Fav Flowers — portrait tiles from CMS section 3 (flower picker).
 * Expects: $allItemsGrouped
 */
require_once __DIR__ . '/../../includes/url_helper.php';

$lxFavFlowers = homepage_get_fav_flower_items($allItemsGrouped ?? []);
if (count($lxFavFlowers) === 0) {
    return;
}
?>
<section class="hp-section lx-tiles" aria-labelledby="lx-fav-flowers-title">
    <div class="hp-container">
        <div class="hp-section-head">
            <h2 id="lx-fav-flowers-title" class="hp-section-title">Pick Their Fav Flowers</h2>
            <p class="hp-section-sub">Shop by their favourite bloom — roses, orchids, lilies &amp; more.</p>
        </div>
        <div class="lx-tiles__track hide-scrollbar" role="list">
            <?php foreach ($lxFavFlowers as $flower): ?>
            <a href="<?= htmlspecialchars(normalize_internal_href($flower['link'])) ?>" class="lx-tile lx-tile--portrait" role="listitem">
                <span class="lx-tile__img">
                    <img src="<?= htmlspecialchars(get_image_url($flower['image'])) ?>"
                         alt="<?= htmlspecialchars($flower['title']) ?> bouquets"
                         width="260" height="347"
                         loading="lazy" decoding="async">
                </span>
                <span class="lx-tile__label"><?= htmlspecialchars($flower['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
