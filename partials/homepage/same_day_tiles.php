<?php
/**
 * Same Day Delights — quick category tiles for last-minute gifting.
 */
require_once __DIR__ . '/../../includes/url_helper.php';
require_once __DIR__ . '/../../includes/collection_taxonomy.php';

$lxSameDayTiles = [
    ['label' => 'Flowers', 'href' => collection_url('collection', 'same-day-delivery'), 'img' => get_image_url('uploads/sections/img_69b00d7d6b073_img69a6aa4b1d253WhatsAppImage20260303at23112PM.webp')],
    ['label' => 'Cakes', 'href' => '/cakes', 'img' => get_image_url('uploads/circles/img_69c0d7a4a7e88_Cakewithflowing202603231132.webp')],
    ['label' => 'Plants', 'href' => collection_url('collection', 'plants'), 'img' => get_image_url('uploads/circles/img_69c0d06492a19_Untitleddesign8.webp')],
    ['label' => 'Chocolates', 'href' => '/search-results?q=chocolate', 'img' => 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Personalised', 'href' => '/personalized', 'img' => 'https://images.unsplash.com/photo-1513201099705-a9746e1e201f?auto=format&fit=crop&w=400&h=400&q=80'],
];
?>
<section class="hp-section lx-tiles" aria-labelledby="lx-sameday-title">
    <div class="hp-container">
        <div class="hp-section-head">
            <h2 id="lx-sameday-title" class="hp-section-title">Same Day Delights</h2>
            <p class="hp-section-sub">Last-minute? We've got you — order by 6 PM for delivery today.</p>
        </div>
        <div class="lx-tiles__track hide-scrollbar" role="list">
            <?php foreach ($lxSameDayTiles as $tile): ?>
            <a href="<?= htmlspecialchars(normalize_internal_href($tile['href'])) ?>" class="lx-tile lx-tile--square" role="listitem">
                <span class="lx-tile__img">
                    <img src="<?= htmlspecialchars($tile['img']) ?>"
                         alt="Same day <?= htmlspecialchars(strtolower($tile['label'])) ?> delivery"
                         width="240" height="240"
                         loading="lazy" decoding="async">
                </span>
                <span class="lx-tile__label"><?= htmlspecialchars($tile['label']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
