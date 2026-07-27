<?php
/**
 * Promo trio — three pastel gift-box promo cards under the hero.
 * Pure presentation; links go to existing catalogue routes.
 */
require_once __DIR__ . '/../../includes/url_helper.php';
require_once __DIR__ . '/../../includes/collection_taxonomy.php';

$lxPromoCards = [
    [
        'badge' => 'Gift Box',
        'title' => 'Awesome Gift Box Collections',
        'cta' => 'Shop Now',
        'href' => collection_url('collection', 'hampers'),
        'theme' => 'sky',
        'img' => get_image_url('uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp'),
        'alt' => 'Gift box bouquet collection',
    ],
    [
        'badge' => 'Occasion Gift',
        'title' => 'Best Occasion Gift Collections',
        'cta' => 'Discover Now',
        'href' => collection_url('occasion', 'birthday'),
        'theme' => 'mint',
        'img' => get_image_url('uploads/sections/img_69c123f37096e_Screenshot20260313001120SamsungNotes.webp'),
        'alt' => 'Colourful occasion bouquet',
    ],
    [
        'badge' => 'Hot Sale',
        'title' => 'Combo Sets Up To 50% Off',
        'cta' => 'Discover Now',
        'href' => collection_url('collection', 'flower-combos'),
        'theme' => 'blush',
        'img' => get_image_url('uploads/sections/img_69c12ca4d6812_img69b5447deae89WhatsAppImage20260314at31518PM.webp'),
        'alt' => 'Pink tulip combo set',
    ],
];
?>
<section class="lx-promo-trio" aria-label="Featured offers">
    <div class="lx-promo-trio__inner">
        <?php foreach ($lxPromoCards as $card): ?>
        <a href="<?= htmlspecialchars(normalize_internal_href($card['href'])) ?>" class="lx-promo-card lx-promo-card--<?= htmlspecialchars($card['theme']) ?>">
            <span class="lx-promo-card__copy">
                <span class="lx-promo-card__badge"><?= htmlspecialchars($card['badge']) ?></span>
                <span class="lx-promo-card__title"><?= htmlspecialchars($card['title']) ?></span>
                <span class="lx-promo-card__cta"><?= htmlspecialchars($card['cta']) ?></span>
            </span>
            <span class="lx-promo-card__img">
                <img src="<?= htmlspecialchars($card['img']) ?>" alt="<?= htmlspecialchars($card['alt']) ?>" width="280" height="280" loading="lazy" decoding="async">
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</section>
