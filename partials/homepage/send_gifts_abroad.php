<?php
$hpAnywhereCities = homepage_get_anywhere_cities();
?>
<section class="hp-section hp-abroad" id="hp-send-gifts-abroad" aria-labelledby="hp-abroad-title">
    <div class="hp-container">
        <div class="hp-section-head">
            <h2 id="hp-abroad-title" class="hp-section-title">Send Gifts Anywhere</h2>
            <p class="hp-section-sub">Surprise loved ones across India with premium flowers &amp; curated gifts from Sai Flowers.</p>
        </div>
        <div class="hp-abroad-scroll" role="list">
            <?php foreach ($hpAnywhereCities as $city): ?>
            <a href="<?= htmlspecialchars($city['link']) ?>" class="hp-abroad-card" role="listitem">
                <span class="hp-abroad-card__img-wrap">
                    <img src="<?= htmlspecialchars($city['image']) ?>"
                         alt="<?= htmlspecialchars($city['name']) ?> gifting"
                         width="150" height="150"
                         loading="lazy"
                         decoding="async">
                </span>
                <span class="hp-abroad-card__label"><?= htmlspecialchars($city['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>