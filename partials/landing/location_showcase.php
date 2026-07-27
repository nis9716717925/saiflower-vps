<?php
/**
 * Location landing — product-first layout (FNP / IGP style).
 */
require_once __DIR__ . '/../../includes/location_landing.php';

$locProductCount = count($tag_results);
?>
<div class="loc-showcase">
    <div class="loc-showcase__top">
        <nav class="loc-crumb" aria-label="Breadcrumb">
            <a href="/">Home</a>
            <span aria-hidden="true">/</span>
            <a href="/flowers">Flowers</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page"><?= htmlspecialchars($locationArea) ?></span>
        </nav>
        <h1 class="loc-showcase__title"><?= htmlspecialchars($page_h1) ?></h1>
        <p class="loc-showcase__count" id="locProductCount" data-total="<?= (int) $locProductCount ?>" data-label="<?= (int) $locProductCount ?> bouquets · Same-day delivery in <?= htmlspecialchars($locationArea) ?>">
            <?= (int) $locProductCount ?> bouquets · Same-day delivery in <?= htmlspecialchars($locationArea) ?>
        </p>
    </div>

    <div class="loc-toolbar" role="toolbar" aria-label="Filter and sort products">
        <div class="loc-filters hide-scrollbar" id="locFilters">
            <button type="button" class="loc-filter is-active" data-filter="all">All</button>
            <button type="button" class="loc-filter" data-filter="rose">Roses</button>
            <button type="button" class="loc-filter" data-filter="under-999">Under ₹999</button>
            <button type="button" class="loc-filter" data-filter="same-day">Same Day</button>
            <button type="button" class="loc-filter" data-filter="premium">Premium</button>
            <button type="button" class="loc-filter" data-filter="orchid">Orchids</button>
        </div>
        <label class="loc-sort">
            <span class="sr-only">Sort products</span>
            <select id="locSort" aria-label="Sort products">
                <option value="recommended">Sort: Recommended</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
            </select>
        </label>
    </div>

    <div class="loc-results-wrap">
        <div class="loc-results-grid" id="<?= htmlspecialchars($products_section_id) ?>" role="list" aria-label="<?= htmlspecialchars($products_section_heading) ?>">
            <?php if ($locProductCount > 0): ?>
                <?php foreach ($tag_results as $item):
                    $filterTags = location_product_filter_meta($item);
                    $link = function_exists('occasion_product_url') ? occasion_product_url($item) : '/flowers';
                    $dbImage = $item['image'] ?? '';
                    $finalPath = (strpos($dbImage, 'uploads/') === 0) ? '/' . $dbImage : '/uploads/' . $dbImage;
                    $price = (float) ($item['price'] ?? 0);
                    $originalPrice = (float) ($item['original_price'] ?? 0);
                    $discount = ($originalPrice > $price && $originalPrice > 0)
                        ? (int) round((($originalPrice - $price) / $originalPrice) * 100) : 0;
                    $imgAlt = function_exists('occasion_product_image_alt')
                        ? occasion_product_image_alt($item, $occasion_label)
                        : htmlspecialchars($item['name'] ?? '');
                ?>
                <a href="<?= htmlspecialchars($link) ?>"
                   class="loc-card"
                   role="listitem"
                   data-filters="<?= htmlspecialchars(implode(' ', $filterTags)) ?>"
                   data-price="<?= (int) $price ?>"
                   data-rating="<?= (float) ($item['rating'] ?? 0) ?>"
                   title="<?= htmlspecialchars($item['name'] ?? '') ?>">
                    <div class="loc-card__img">
                        <img src="<?= htmlspecialchars($finalPath) ?>"
                             alt="<?= htmlspecialchars($imgAlt) ?>"
                             width="320" height="320"
                             loading="lazy" decoding="async">
                        <?php if ($discount > 0): ?>
                        <span class="loc-card__badge"><?= $discount ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    <div class="loc-card__body">
                        <h3 class="loc-card__name"><?= htmlspecialchars($item['name'] ?? '') ?></h3>
                        <div class="loc-card__price">
                            <?php if ($originalPrice > $price): ?>
                            <span class="loc-card__old">₹<?= number_format($originalPrice) ?></span>
                            <?php endif; ?>
                            <span class="loc-card__now">₹<?= number_format($price) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="loc-empty">
                    <p>Browse our <a href="/flowers">flowers</a> for delivery in <?= htmlspecialchars($locationArea) ?>.</p>
                </div>
            <?php endif; ?>
        </div>
        <p class="loc-no-match" id="locNoMatch" hidden>No bouquets match this filter. <button type="button" class="loc-reset" data-filter="all">Show all</button></p>
    </div>

    <div class="loc-showcase__footer">
        <a href="/flowers" class="loc-viewall">View all flowers in <?= htmlspecialchars($locationArea) ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
    </div>

    <?php include __DIR__ . '/location_trust_bar.php'; ?>
</div>
<script defer src="/assets/js/location-landing.js?v=1"></script>
