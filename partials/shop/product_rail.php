<?php
/**
 * Horizontal product rail for shop sections.
 * Expects: $section (title, subtitle, items, href, key), optional $isDecorRail
 */
$isDecorRail = !empty($isDecorRail);
$items = $section['items'] ?? [];
if (count($items) === 0) {
    return;
}
$railId = 'sp-rail-' . preg_replace('/[^a-z0-9\-]+/', '-', (string) ($section['key'] ?? 'sec'));
?>
<section class="sp-rail<?= $isDecorRail ? ' sp-rail--decor' : '' ?>" aria-labelledby="<?= htmlspecialchars($railId) ?>-title">
    <div class="sp-rail__head">
        <div>
            <h2 id="<?= htmlspecialchars($railId) ?>-title"><?= htmlspecialchars($section['title'] ?? '') ?></h2>
            <?php if (!empty($section['subtitle'])): ?>
            <p><?= htmlspecialchars($section['subtitle']) ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($section['href'])): ?>
        <a href="<?= htmlspecialchars($section['href']) ?>">View all</a>
        <?php endif; ?>
    </div>
    <div class="sp-rail__track-wrap" data-sp-rail>
        <button type="button" class="sp-rail__nav sp-rail__nav--prev" data-sp-rail-prev aria-label="Previous">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </button>
        <div class="sp-rail__track" id="<?= htmlspecialchars($railId) ?>">
            <?php foreach ($items as $f):
                $card_variant = 'rail';
                include __DIR__ . '/product_card.php';
            endforeach; ?>
        </div>
        <button type="button" class="sp-rail__nav sp-rail__nav--next" data-sp-rail-next aria-label="Next">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
</section>
