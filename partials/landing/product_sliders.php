<?php
/** @var list<array{key: string, title: string, subtitle: string, view_all: string, html: string}> $landingSliders */
if (empty($landingSliders)) {
    return;
}
?>
<link rel="stylesheet" href="/assets/css/homepage-premium.css?v=3" />
<div class="hp-product-sliders landing-page-sliders" style="margin-bottom: 1.5rem;">
    <?php foreach ($landingSliders as $slider):
        if (trim($slider['html'] ?? '') === '') {
            continue;
        }
        $sliderKey = htmlspecialchars($slider['key']);
    ?>
    <section class="hp-section hp-product-slider-section" aria-labelledby="landing-slider-<?= $sliderKey ?>">
        <div class="hp-container" style="max-width: 1200px;">
            <div class="hp-section-head">
                <h2 id="landing-slider-<?= $sliderKey ?>" class="hp-section-title" style="font-size: clamp(1.25rem, 3vw, 1.65rem); color: var(--primary, #2f6f4e);">
                    <?= htmlspecialchars($slider['title']) ?>
                </h2>
                <?php if (!empty($slider['subtitle'])): ?>
                <p class="hp-section-sub"><?= htmlspecialchars($slider['subtitle']) ?></p>
                <?php endif; ?>
            </div>

            <div class="hp-occasion-carousel-wrap hp-product-carousel-wrap" data-hp-slider="<?= $sliderKey ?>">
                <button type="button" class="hp-occasion-nav hp-occasion-nav--prev" aria-label="Previous products in <?= htmlspecialchars($slider['title']) ?>">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <div class="hp-occasion-track-wrap">
                    <div class="hp-occasion-track hide-scrollbar" role="list">
                        <?= $slider['html'] ?>
                    </div>
                </div>
                <button type="button" class="hp-occasion-nav hp-occasion-nav--next" aria-label="Next products in <?= htmlspecialchars($slider['title']) ?>">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>

            <?php if (!empty($slider['view_all'])): ?>
            <div class="hp-product-slider-footer">
                <a href="<?= htmlspecialchars($slider['view_all']) ?>" class="hp-occasion-viewall" title="View all <?= htmlspecialchars($slider['title']) ?>">
                    View all
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endforeach; ?>
</div>
<script defer src="/assets/js/homepage-premium.js?v=3"></script>
