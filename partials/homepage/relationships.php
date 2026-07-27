<?php
/**
 * For Every Relationship — recipient shortcut tiles.
 * Links go to dedicated /relation/{slug} landings.
 */
require_once __DIR__ . '/../../includes/url_helper.php';
require_once __DIR__ . '/../../includes/collection_taxonomy.php';

$lxRelationships = [
    ['label' => 'Him', 'slug' => 'him', 'img' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Her', 'slug' => 'her', 'img' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Kids', 'slug' => 'kids', 'img' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Friend', 'slug' => 'friends', 'img' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Girlfriend', 'slug' => 'girlfriend', 'img' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Boyfriend', 'slug' => 'boyfriend', 'img' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Wife', 'slug' => 'wife', 'img' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=400&h=400&q=80'],
    ['label' => 'Husband', 'slug' => 'husband', 'img' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&h=400&q=80'],
];
?>
<section class="hp-section lx-tiles lx-tiles--band" aria-labelledby="lx-relationships-title">
    <div class="hp-container">
        <div class="hp-section-head">
            <h2 id="lx-relationships-title" class="hp-section-title">For Every Relationship</h2>
            <p class="hp-section-sub">Thoughtful gifts for everyone you love — find theirs in a tap.</p>
        </div>
        <div class="lx-tiles__track hide-scrollbar" role="list">
            <?php foreach ($lxRelationships as $rel): ?>
            <a href="<?= htmlspecialchars(collection_url('relation', $rel['slug'])) ?>" class="lx-tile lx-tile--square" role="listitem">
                <span class="lx-tile__img">
                    <img src="<?= htmlspecialchars($rel['img']) ?>"
                         alt="Gifts for <?= htmlspecialchars(strtolower($rel['label'])) ?>"
                         width="220" height="220"
                         loading="lazy" decoding="async">
                </span>
                <span class="lx-tile__label"><?= htmlspecialchars($rel['label']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
