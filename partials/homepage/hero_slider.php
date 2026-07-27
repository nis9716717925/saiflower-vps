<?php
/**
 * Homepage hero slider — split layout uses local stock full-cover imagery.
 * Keeps #heroSlider, #sliderTrack, #sliderTracker, moveSlide() bindings.
 */
require_once __DIR__ . '/../../includes/url_helper.php';

$lxHeroStock = $lxHeroStock ?? require __DIR__ . '/../../includes/hero_stock_slides.php';
$lxHeroUseStock = $lxHeroUseStock ?? true;
$hpSlideThemes = $lxHeroStock['themes'];

$heroSlides = ($lxHeroUseStock && !empty($lxHeroStock['main']))
    ? $lxHeroStock['main']
    : ($slides ?? []);
?>
<section class="hp-hero-carousel relative bg-white overflow-hidden">
    <div class="hp-hero-carousel__outer w-full">
        <div class="relative w-full group/slider hp-hero-carousel__wrap" id="heroSlider">
            <div class="hp-hero-carousel__viewport relative w-full overflow-hidden">
                <div class="flex flex-nowrap h-full transition-transform duration-500 ease-in-out hp-hero-carousel__track" id="sliderTrack">
                    <?php
                    if (count($heroSlides) > 0):
                        foreach ($heroSlides as $index => $s):
                            $link = normalize_internal_href($s['href'] ?? $s['link'] ?? '/flowers.php');
                            $title = $s['title'] ?? 'Fresh Flowers Delivered';
                            $subtitle = $s['subtitle'] ?? 'Premium bouquets with same-day delivery across Delhi NCR';
                            $cta = $s['cta'] ?? 'ORDER NOW';
                            $kicker = $s['kicker'] ?? 'Sai Flower';
                            $theme = $s['theme'] ?? 'green';
                            $bg = $hpSlideThemes[$theme] ?? $hpSlideThemes['green'];
                            $img = $s['img'] ?? '';
                            if ($lxHeroUseStock && empty($s['img'])) {
                                continue;
                            }
                            if (!$lxHeroUseStock) {
                                $img = get_image_url($s['image'] ?? '');
                            }
                            $mobileImg = (!$lxHeroUseStock && !empty($s['mobile_image']))
                                ? get_image_url($s['mobile_image'])
                                : $img;
                            $priority = ($index === 0) ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"';
                            $titlePlain = strip_tags(str_replace('<br>', ' ', $title));
                            $imgFallback = 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=1200&q=80';
                    ?>
                    <div class="hp-hero-slide flex-shrink-0" data-theme="<?= htmlspecialchars($theme) ?>">
                        <a href="<?= htmlspecialchars($link) ?>" class="hp-hero-slide__mobile block w-full h-full md:hidden">
                            <picture class="w-full h-full block">
                                <img src="<?= htmlspecialchars($mobileImg) ?>"
                                     class="w-full h-full block object-cover"
                                     alt="<?= htmlspecialchars($titlePlain) ?>"
                                     width="1920" height="685"
                                     decoding="sync"
                                     onerror="this.onerror=null;this.src='<?= htmlspecialchars($imgFallback) ?>';"
                                     <?= $priority ?>>
                            </picture>
                        </a>

                        <?php if ($lxHeroUseStock): ?>
                        <div class="hp-hero-slide__card hp-hero-slide__card--cover hidden md:flex"
                             style="background-image: url('<?= htmlspecialchars($img) ?>');">
                            <div class="hp-hero-slide__copy hp-hero-slide__copy--overlay">
                                <span class="hp-hero-slide__kicker"><?= htmlspecialchars($kicker) ?></span>
                                <h2 class="hp-hero-slide__title"><?= $title ?></h2>
                                <p class="hp-hero-slide__sub"><?= htmlspecialchars($subtitle) ?></p>
                                <span class="hp-hero-slide__cta"><?= htmlspecialchars($cta) ?></span>
                            </div>
                            <a href="<?= htmlspecialchars($link) ?>" class="hp-hero-slide__card-link" aria-label="<?= htmlspecialchars($titlePlain) ?>"></a>
                        </div>
                        <?php else: ?>
                        <div class="hp-hero-slide__card hidden md:flex" style="background: <?= htmlspecialchars($bg) ?>;">
                            <div class="hp-hero-slide__copy">
                                <h2 class="hp-hero-slide__title"><?= htmlspecialchars($title) ?></h2>
                                <p class="hp-hero-slide__sub"><?= htmlspecialchars($subtitle) ?></p>
                                <span class="hp-hero-slide__cta"><?= htmlspecialchars($cta) ?></span>
                            </div>
                            <a href="<?= htmlspecialchars($link) ?>" class="hp-hero-slide__visual" tabindex="-1" aria-hidden="true">
                                <img src="<?= htmlspecialchars($img) ?>"
                                     alt=""
                                     width="480"
                                     height="400"
                                     loading="<?= $index === 0 ? 'eager' : 'lazy' ?>"
                                     decoding="async">
                            </a>
                            <a href="<?= htmlspecialchars($link) ?>" class="hp-hero-slide__card-link" aria-label="<?= htmlspecialchars($titlePlain) ?>"></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="hp-hero-slide flex-shrink-0 min-w-full flex items-center justify-center text-gray-400">
                        <span>No Slides Configured</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <button type="button" onclick="moveSlide(-1)" class="hp-hero-carousel__nav hp-hero-carousel__nav--prev hidden md:flex" aria-label="Previous slide">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <button type="button" onclick="moveSlide(1)" class="hp-hero-carousel__nav hp-hero-carousel__nav--next hidden md:flex" aria-label="Next slide">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>

            <?php if (count($heroSlides) > 1): ?>
            <div class="hp-hero-carousel__dots lx-hero-split__dots absolute bottom-2 left-0 right-0 flex justify-center items-center gap-2 z-20 pb-2" id="sliderTracker"></div>
            <style>
                .slider-dot { width: 8px; height: 8px; background-color: rgba(255,255,255,0.5); border-radius: 50%; opacity: 1; transition: background-color 0.3s, opacity 0.3s; cursor: pointer; position: relative; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.4); }
                .slider-dot:hover { background-color: rgba(255,255,255,0.9); }
                .slider-dot.active-pill { width: 32px; background-color: rgba(255,255,255,0.3); border-radius: 12px; }
                .slider-dot.active-pill::after {
                    content: ''; position: absolute; top: 0; left: 0; height: 100%; width: 0%;
                    background-color: #ffffff; border-radius: 12px;
                    animation: progressDot 3s linear forwards;
                }
                @keyframes progressDot { from { width: 0%; } to { width: 100%; } }
            </style>
            <?php endif; ?>
        </div>
    </div>
</section>
