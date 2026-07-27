<?php
$hpFinderOptions = homepage_get_gift_finder_options();
?>
<section class="hp-section hp-finder" aria-labelledby="hp-finder-title">
    <div class="hp-container">
        <div class="hp-finder-shell">
            <div class="hp-finder-head">
                <p class="hp-finder-kicker">Search Gifts Quicker <span aria-hidden="true">⚡</span></p>
                <h2 id="hp-finder-title" class="hp-section-title hp-section-title--light">Gift Finder</h2>
                <p class="hp-finder-sub">Tap a path below — we’ll take you to the perfect picks in seconds.</p>
            </div>
            <div class="hp-finder-grid">
                <?php foreach ($hpFinderOptions as $opt): ?>
                <a href="<?= htmlspecialchars($opt['link']) ?>" class="hp-finder-card">
                    <span class="hp-finder-card__img">
                        <img src="<?= htmlspecialchars($opt['image']) ?>"
                             alt="<?= htmlspecialchars($opt['label']) ?> gift finder option"
                             width="120" height="120"
                             loading="lazy"
                             decoding="async">
                    </span>
                    <span class="hp-finder-card__icon" aria-hidden="true"><i class="fas <?= htmlspecialchars($opt['icon']) ?>"></i></span>
                    <span class="hp-finder-card__label"><?= htmlspecialchars($opt['label']) ?></span>
                    <span class="hp-finder-card__sub"><?= htmlspecialchars($opt['subtitle']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
