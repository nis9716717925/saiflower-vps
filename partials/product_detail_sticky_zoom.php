<?php
/** Sticky mobile/tablet CTAs + full-screen image zoom markup. Load /assets/js/product-detail-premium.js in <head> with defer. */
$pdStickyInStock = $pdStickyInStock ?? true;
?>
<div id="pdStickyBar" class="pd-sticky-cta" role="region" aria-label="Purchase">
    <div class="pd-sticky-inner">
        <button type="button" id="pdStickyAdd" class="pd-btn pd-btn--outline" <?= empty($pdStickyInStock) ? 'disabled' : '' ?> aria-label="Add to cart">
            <i class="fas fa-bag-shopping" aria-hidden="true"></i> Add to Cart
        </button>
        <button type="button" id="pdStickyBuy" class="pd-btn pd-btn--primary" <?= empty($pdStickyInStock) ? 'disabled' : '' ?> aria-label="Buy now">
            <i class="fas fa-bolt" aria-hidden="true"></i> Buy Now
        </button>
    </div>
</div>

<div id="pdImageZoom" class="pd-zoom-backdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Product image">
    <button type="button" id="pdImageZoomClose" class="pd-zoom-close" aria-label="Close enlarged image">&times;</button>
    <img id="pdImageZoomImg" src="" alt="Enlarged product image preview">
</div>
