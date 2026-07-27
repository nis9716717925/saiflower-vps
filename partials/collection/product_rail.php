<?php
/**
 * Shared product card + horizontal slider for collection landings.
 * Expects: $products (list), optional $slider_id, $slider_title, $slider_sub
 */
if (!function_exists('collection_render_product_card')) {
    function collection_render_product_card(array $item): string
    {
        $name = htmlspecialchars((string) ($item['name'] ?? 'Product'));
        $link = htmlspecialchars(collection_product_url($item));
        $img = htmlspecialchars(collection_image_url($item['image'] ?? ''));
        $price = (float) ($item['price'] ?? 0);
        $original = (float) ($item['original_price'] ?? 0);
        $rating = (float) ($item['rating'] ?? 0);
        $type = htmlspecialchars(ucfirst((string) ($item['type'] ?? 'flower')));
        $discount = ($original > $price && $original > 0) ? (int) round((($original - $price) / $original) * 100) : 0;

        $priceHtml = '<span class="cl-card__price">₹' . number_format($price) . '</span>';
        if ($discount > 0) {
            $priceHtml = '<span class="cl-card__mrp">₹' . number_format($original) . '</span>' . $priceHtml
                . '<span class="cl-card__off">' . $discount . '% OFF</span>';
        }

        $ratingHtml = $rating > 0
            ? '<span class="cl-card__rating" aria-label="Rated ' . htmlspecialchars((string) $rating) . ' out of 5"><i class="fas fa-star" aria-hidden="true"></i> ' . number_format($rating, 1) . '</span>'
            : '';

        return <<<HTML
<a class="cl-card" href="{$link}" title="{$name}">
  <span class="cl-card__media">
    <img src="{$img}" alt="{$name}" width="320" height="320" loading="lazy" decoding="async" onerror="this.src='https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&amp;fit=crop&amp;w=600&amp;q=80';">
    <span class="cl-card__type">{$type}</span>
  </span>
  <span class="cl-card__body">
    <span class="cl-card__name">{$name}</span>
    <span class="cl-card__meta">{$priceHtml}{$ratingHtml}</span>
  </span>
</a>
HTML;
    }
}

$products = $products ?? [];
$slider_id = $slider_id ?? ('cl-slider-' . substr(md5(($slider_title ?? '') . microtime()), 0, 8));
$slider_title = $slider_title ?? '';
$slider_sub = $slider_sub ?? '';
$as_grid = !empty($as_grid);
?>
<?php if ($slider_title !== ''): ?>
<section class="cl-section<?= $as_grid ? ' cl-section--grid' : ' cl-section--slider' ?>" aria-labelledby="<?= htmlspecialchars($slider_id) ?>-title">
  <div class="cl-container">
    <div class="cl-section__head">
      <div>
        <h2 id="<?= htmlspecialchars($slider_id) ?>-title" class="cl-section__title"><?= htmlspecialchars($slider_title) ?></h2>
        <?php if ($slider_sub !== ''): ?>
        <p class="cl-section__sub"><?= htmlspecialchars($slider_sub) ?></p>
        <?php endif; ?>
      </div>
    </div>
<?php endif; ?>

<?php if ($as_grid): ?>
    <div class="cl-grid" id="<?= htmlspecialchars($slider_id) ?>" role="list">
      <?php if (count($products) === 0): ?>
      <div class="cl-empty" role="status">
        <p>No matching products in this collection right now. Try <a href="/flowers">all flowers</a>, <a href="/cakes">cakes</a>, or <a href="/gifts">gifts</a>.</p>
      </div>
      <?php else: ?>
        <?php foreach ($products as $p): ?>
        <div class="cl-grid__item" role="listitem"><?= collection_render_product_card($p) ?></div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
<?php else: ?>
    <div class="cl-slider" data-cl-slider>
      <button type="button" class="cl-slider__nav cl-slider__nav--prev" aria-label="Previous" data-cl-prev>
        <i class="fas fa-chevron-left" aria-hidden="true"></i>
      </button>
      <div class="cl-slider__track hide-scrollbar" id="<?= htmlspecialchars($slider_id) ?>" role="list">
        <?php foreach ($products as $p): ?>
        <div class="cl-slider__item" role="listitem"><?= collection_render_product_card($p) ?></div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="cl-slider__nav cl-slider__nav--next" aria-label="Next" data-cl-next>
        <i class="fas fa-chevron-right" aria-hidden="true"></i>
      </button>
    </div>
<?php endif; ?>

<?php if ($slider_title !== ''): ?>
  </div>
</section>
<?php endif; ?>
