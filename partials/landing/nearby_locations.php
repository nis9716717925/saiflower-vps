<?php
/** @var list<array{label: string, href: string}> $nearbyLocationLinks */
/** @var string $locationArea */
if (empty($nearbyLocationLinks)) {
    return;
}
?>
<section class="loc-nearby" aria-labelledby="loc-nearby-title">
    <h2 id="loc-nearby-title" class="loc-nearby__title">Flower Delivery Near <?= htmlspecialchars($locationArea) ?></h2>
    <p class="loc-nearby__sub">We also deliver fresh bouquets to neighbourhoods around <?= htmlspecialchars($locationArea) ?>.</p>
    <div class="loc-nearby__grid">
        <?php foreach ($nearbyLocationLinks as $link): ?>
        <a href="<?= htmlspecialchars($link['href']) ?>" class="loc-nearby__chip" title="Flower delivery in <?= htmlspecialchars($link['label']) ?>">
            <i class="fas fa-location-dot" aria-hidden="true"></i>
            <?= htmlspecialchars($link['label']) ?>
        </a>
        <?php endforeach; ?>
        <a href="/flowers" class="loc-nearby__chip loc-nearby__chip--all" title="Browse all flowers">
            <i class="fas fa-seedling" aria-hidden="true"></i>
            All Flowers
        </a>
    </div>
</section>
