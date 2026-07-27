<?php
/**
 * Left hero mini-slider — synced with main #sliderTrack.
 * Uses local stock imagery from /assets/images/hero/ (not site uploads).
 */
require_once __DIR__ . '/../../includes/url_helper.php';

$lxHeroStock = require __DIR__ . '/../../includes/hero_stock_slides.php';
$lxSideSlides = $lxHeroStock['side'];
$lxSideThemes = $lxHeroStock['themes'];
?>
<div class="lx-hero-side-slider" id="sideHeroSlider">
    <div class="lx-hero-side-slider__viewport">
        <div class="lx-hero-side-slider__track" id="sideSliderTrack">
            <?php foreach ($lxSideSlides as $i => $slide):
                $bg = $lxSideThemes[$slide['theme']] ?? $lxSideThemes['lavender'];
            ?>
            <div class="lx-hero-side-slide">
                <a href="<?= htmlspecialchars(normalize_internal_href($slide['href'])) ?>"
                   class="lx-hero-side-card"
                   style="background: <?= htmlspecialchars($bg) ?>;">
                    <span class="lx-hero-side-card__copy">
                        <?php if (!empty($slide['kicker'])): ?>
                        <span class="lx-hero-side-card__kicker"><?= htmlspecialchars($slide['kicker']) ?></span>
                        <?php endif; ?>
                        <span class="lx-hero-side-card__title"><?= $slide['title'] ?></span>
                        <span class="lx-hero-side-card__cta"><?= htmlspecialchars($slide['cta']) ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </span>
                    <span class="lx-hero-side-card__img" style="background-image: url('<?= htmlspecialchars($slide['img']) ?>');">
                        <img src="<?= htmlspecialchars($slide['img']) ?>"
                             alt="<?= htmlspecialchars(strip_tags(str_replace('<br>', ' ', $slide['title']))) ?>"
                             width="400" height="400"
                             loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                             decoding="async"
                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518895949257-7621c3c786d7?auto=format&fit=crop&w=800&q=80';">
                    </span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if (count($lxSideSlides) > 1): ?>
    <div class="lx-hero-side-slider__dots" id="sideSliderTracker" aria-hidden="true"></div>
    <?php endif; ?>
</div>
