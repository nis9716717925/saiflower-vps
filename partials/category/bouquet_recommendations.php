<?php
/**
 * Bouquet recommendation grid for category / personalized pages.
 * Expects: $recommendProducts (list), optional $recommendTitle, $recommendSub, $pageKey
 */
require_once __DIR__ . '/../../includes/category_recommend.php';
require_once __DIR__ . '/../../includes/collection_landing.php';

$pageKey = $pageKey ?? 'general';
$copy = category_recommend_copy($pageKey);
$recommendTitle = $recommendTitle ?? $copy['title'];
$recommendSub = $recommendSub ?? $copy['sub'];
$recommendProducts = $recommendProducts ?? [];
?>
<?php if (count($recommendProducts) > 0): ?>
<section class="cat-section" aria-labelledby="cat-rec-title">
    <div class="cat-wrap">
        <div class="cat-convert">
            <strong id="cat-rec-title"><?= htmlspecialchars($recommendTitle) ?></strong>
            <span><?= htmlspecialchars($recommendSub) ?></span>
        </div>

        <div class="cat-grid" style="margin-top:1rem" role="list">
            <?php foreach ($recommendProducts as $item):
                $name = (string) ($item['name'] ?? 'Bouquet');
                $link = function_exists('collection_product_url')
                    ? collection_product_url($item)
                    : product_url(array_merge($item, ['type' => 'flower']));
                $img = function_exists('collection_image_url')
                    ? collection_image_url($item['image'] ?? '')
                    : get_image_url($item['image'] ?? '', 'flowers');
                $price = (float) ($item['price'] ?? 0);
                $original = (float) ($item['original_price'] ?? 0);
            ?>
            <a class="cat-card" href="<?= htmlspecialchars($link) ?>" role="listitem" title="<?= htmlspecialchars($name) ?>">
                <span class="cat-card__media">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" width="320" height="320" loading="lazy" decoding="async"
                         onerror="this.src='https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&amp;fit=crop&amp;w=600&amp;q=80'">
                </span>
                <span class="cat-card__body">
                    <span class="cat-card__name"><?= htmlspecialchars($name) ?></span>
                    <span>
                        <?php if ($original > $price && $original > 0): ?>
                        <span class="cat-card__mrp">₹<?= number_format($original) ?></span>
                        <?php endif; ?>
                        <span class="cat-card__price">₹<?= number_format($price) ?></span>
                    </span>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="cat-status__actions" style="margin-top:1.1rem;justify-content:center">
            <a class="cat-btn cat-btn--primary" href="/flowers">Shop all flower bouquets</a>
            <a class="cat-btn cat-btn--ghost" href="/collection/best-sellers">Best sellers</a>
        </div>
    </div>
</section>
<?php endif; ?>
