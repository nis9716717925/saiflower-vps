<?php
/**
 * Trust stats band — quick social proof numbers.
 */
$lxStats = [
    ['value' => '10K+', 'label' => 'Happy Customers'],
    ['value' => '4.8★', 'label' => 'Average Rating'],
    ['value' => '25+', 'label' => 'Years of Craft'],
    ['value' => 'Same Day', 'label' => 'Delhi NCR Delivery'],
];
?>
<section class="lx-stats" aria-label="Sai Flower at a glance">
    <div class="lx-stats__inner">
        <?php foreach ($lxStats as $stat): ?>
        <div class="lx-stats__item">
            <span class="lx-stats__value"><?= htmlspecialchars($stat['value']) ?></span>
            <span class="lx-stats__label"><?= htmlspecialchars($stat['label']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
