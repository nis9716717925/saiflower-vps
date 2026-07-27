<?php
/**
 * Premium shop product card.
 * Expects: $f (flower row), optional $card_variant ('grid'|'rail')
 */
require_once __DIR__ . '/../../includes/shop_merchandising.php';

$card_variant = $card_variant ?? 'grid';
$p_link = product_url(['type' => 'flower', 'slug' => $f['slug'] ?? '', 'id' => $f['id']]);
$finalImagePath = get_image_url($f['image'], 'flowers');
$price = function_exists('apply_surge_pricing') ? (float) apply_surge_pricing($f['price'], 'flower') : (float) $f['price'];
$original = (float) ($f['original_price'] ?? 0);
$discount = shop_discount_percent($f);
$rating = (float) ($f['rating'] ?? 4.8);
$reviews = shop_review_count_estimate($f);
$inStock = !isset($f['in_stock']) || (int) $f['in_stock'] === 1;
$isDecor = shop_is_decoration_product($f);
$sameDay = !isset($f['delivery_sameday']) || (int) $f['delivery_sameday'] === 1;
$csrf = function_exists('generate_csrf_token') ? generate_csrf_token() : '';
$name = htmlspecialchars((string) $f['name']);
$imgAlt = htmlspecialchars((string) ($f['image_alt'] ?? $f['name']));
?>
<article class="sp-card<?= $isDecor ? ' sp-card--decor' : '' ?> sp-card--<?= htmlspecialchars($card_variant) ?>">
    <div class="sp-card__media">
        <?php if ($discount > 0): ?>
        <span class="sp-card__badge sp-card__badge--sale"><?= $discount ?>% OFF</span>
        <?php elseif ($isDecor): ?>
        <span class="sp-card__badge sp-card__badge--decor">Decor</span>
        <?php elseif ($price >= 2499): ?>
        <span class="sp-card__badge sp-card__badge--luxe">Luxe</span>
        <?php endif; ?>

        <button type="button" class="sp-card__wish" onclick="toggleWishlist(this, <?= (int) $f['id'] ?>, 'flower')" aria-label="Add to wishlist">
            <i class="far fa-heart" aria-hidden="true"></i>
        </button>

        <button type="button" class="sp-card__qv" data-sp-quickview
                data-id="<?= (int) $f['id'] ?>"
                data-name="<?= $name ?>"
                data-price="<?= number_format($price) ?>"
                data-img="<?= htmlspecialchars($finalImagePath) ?>"
                data-link="<?= htmlspecialchars($p_link) ?>"
                data-rating="<?= number_format($rating, 1) ?>"
                aria-label="Quick view <?= $name ?>">
            Quick view
        </button>

        <a class="sp-card__img-link" href="<?= htmlspecialchars($p_link) ?>" title="<?= $name ?>">
            <img src="<?= htmlspecialchars($finalImagePath) ?>"
                 alt="<?= $imgAlt ?>"
                 width="420" height="520"
                 loading="lazy" decoding="async"
                 onerror="this.src='https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&amp;fit=crop&amp;w=600&amp;q=80'">
        </a>

        <?php if (!$inStock): ?>
        <span class="sp-card__oos">Out of stock</span>
        <?php endif; ?>
    </div>

    <div class="sp-card__body">
        <div class="sp-card__rating" aria-label="Rated <?= number_format($rating, 1) ?> from <?= $reviews ?> reviews">
            <i class="fas fa-star" aria-hidden="true"></i>
            <span><?= number_format($rating, 1) ?></span>
            <span class="sp-card__reviews">(<?= number_format($reviews) ?>)</span>
        </div>

        <h3 class="sp-card__title">
            <a href="<?= htmlspecialchars($p_link) ?>"><?= $name ?></a>
        </h3>

        <p class="sp-card__delivery">
            <?php if ($sameDay && $inStock): ?>
            <i class="fas fa-bolt" aria-hidden="true"></i> Same day delivery
            <?php else: ?>
            <i class="fas fa-truck" aria-hidden="true"></i> Scheduled delivery
            <?php endif; ?>
        </p>

        <div class="sp-card__price-row">
            <span class="sp-card__price">₹<?= number_format($price) ?></span>
            <?php if ($discount > 0): ?>
            <span class="sp-card__mrp">₹<?= number_format($original) ?></span>
            <span class="sp-card__save">Save <?= $discount ?>%</span>
            <?php endif; ?>
        </div>

        <p class="sp-card__trust"><i class="fas fa-shield-halved" aria-hidden="true"></i> Freshness guaranteed</p>

        <div class="sp-card__actions">
            <?php if ($inStock): ?>
            <a class="sp-card__cta sp-card__cta--ghost" href="<?= htmlspecialchars($p_link) ?>">View</a>
            <form action="/cart.php" method="POST" class="sp-card__cart-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="product_id" value="<?= (int) $f['id'] ?>">
                <input type="hidden" name="name" value="<?= $name ?>">
                <input type="hidden" name="price" value="<?= $price ?>">
                <input type="hidden" name="image" value="<?= htmlspecialchars((string) $f['image']) ?>">
                <input type="hidden" name="category" value="flower">
                <input type="hidden" name="add_to_cart" value="1">
                <button type="submit" class="sp-card__cta sp-card__cta--primary">Add to cart</button>
            </form>
            <?php else: ?>
            <button type="button" class="sp-card__cta sp-card__cta--disabled" disabled>Sold out</button>
            <?php endif; ?>
        </div>
    </div>
</article>
