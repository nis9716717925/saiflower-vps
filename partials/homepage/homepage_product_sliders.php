<?php
global $conn;
if (!isset($conn) || !($conn instanceof mysqli)) {
    return;
}
$hpProductSliders = homepage_get_product_sliders($conn, $allItemsGrouped ?? []);
$hpProductSliderKeys = $hpProductSliderKeys ?? null;
?>
<div class="hp-product-sliders">
    <?php foreach ($hpProductSliders as $slider):
        if ($hpProductSliderKeys !== null && !in_array($slider['key'], $hpProductSliderKeys, true)) {
            continue;
        }
        if (trim($slider['html'] ?? '') === '') {
            continue;
        }
        $sliderKey = htmlspecialchars($slider['key']);
    ?>
    <section class="hp-section hp-product-slider-section" aria-labelledby="hp-slider-<?= $sliderKey ?>">
        <div class="hp-container">
            <div class="hp-section-head">
                <h2 id="hp-slider-<?= $sliderKey ?>" class="hp-section-title"><?= htmlspecialchars($slider['title']) ?></h2>
                <?php if (!empty($slider['subtitle'])): ?>
                <p class="hp-section-sub"><?= htmlspecialchars($slider['subtitle']) ?></p>
                <?php endif; ?>
            </div>

            <div class="hp-occasion-carousel-wrap hp-product-carousel-wrap" data-hp-slider="<?= $sliderKey ?>">
                <button type="button" class="hp-occasion-nav hp-occasion-nav--prev" aria-label="Previous in <?= htmlspecialchars($slider['title']) ?>">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <div class="hp-occasion-track-wrap">
                    <div class="hp-occasion-track hide-scrollbar" role="list">
                        <?= $slider['html'] ?>
                    </div>
                </div>
                <button type="button" class="hp-occasion-nav hp-occasion-nav--next" aria-label="Next in <?= htmlspecialchars($slider['title']) ?>">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>

            <?php if (!empty($slider['view_all'])): ?>
            <div class="hp-product-slider-footer">
                <a href="<?= htmlspecialchars($slider['view_all']) ?>" class="hp-occasion-viewall">
                    View all <?= htmlspecialchars($slider['title']) ?>
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endforeach; ?>
</div>
