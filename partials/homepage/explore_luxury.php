<?php
/**
 * Explore Luxury — premium collection shortcut tiles.
 */
require_once __DIR__ . '/../../includes/url_helper.php';
require_once __DIR__ . '/../../includes/collection_taxonomy.php';

$lxLuxuryTiles = [
    ['label' => 'Luxe Vibe', 'sub' => 'Statement arrangements', 'href' => collection_url('collection', 'luxury-flowers'), 'img' => get_image_url('uploads/sections/img_69c127d60bae3_img69a6d18fd207dChatGPTImageMar32026054446PM.webp')],
    ['label' => 'Flowers', 'sub' => 'Fresh designer bouquets', 'href' => '/flowers.php', 'img' => get_image_url('uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp')],
    ['label' => 'Cakes', 'sub' => 'Baked for celebrations', 'href' => '/cakes.php', 'img' => get_image_url('uploads/circles/img_69c0d7a4a7e88_Cakewithflowing202603231132.webp')],
    ['label' => 'Hampers', 'sub' => 'Curated gift boxes', 'href' => collection_url('collection', 'hampers'), 'img' => get_image_url('uploads/circles/img_69c0d0e7c2d77_Untitleddesign9.webp')],
    ['label' => 'Plants', 'sub' => 'Green & evergreen gifts', 'href' => collection_url('collection', 'plants'), 'img' => get_image_url('uploads/circles/img_69c0d06492a19_Untitleddesign8.webp')],
];
?>
<section class="hp-section lx-tiles lx-tiles--band" aria-labelledby="lx-luxury-title">
    <div class="hp-container">
        <div class="hp-section-head">
            <h2 id="lx-luxury-title" class="hp-section-title">Explore Luxury</h2>
            <p class="hp-section-sub">Our most premium picks — for moments that deserve something extraordinary.</p>
        </div>
        <div class="lx-tiles__track hide-scrollbar" role="list">
            <?php foreach ($lxLuxuryTiles as $tile): ?>
            <a href="<?= htmlspecialchars(normalize_internal_href($tile['href'])) ?>" class="lx-tile lx-tile--square" role="listitem">
                <span class="lx-tile__img">
                    <img src="<?= htmlspecialchars($tile['img']) ?>"
                         alt="<?= htmlspecialchars($tile['label']) ?> — luxury collection"
                         width="240" height="240"
                         loading="lazy" decoding="async">
                </span>
                <span class="lx-tile__label"><?= htmlspecialchars($tile['label']) ?><span class="lx-tile__sub"><?= htmlspecialchars($tile['sub']) ?></span></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
