<?php
/**
 * Universal category mega-nav data + markup (FNP-style).
 * Included from navbar.php site-wide. Preserves mega menus, links, features.
 */
require_once __DIR__ . '/../includes/url_helper.php';
require_once __DIR__ . '/../includes/collection_taxonomy.php';
require_once __DIR__ . '/../includes/personalized_taxonomy.php';

if (!isset($all_circles) || !is_array($all_circles) || $all_circles === []) {
    $all_circles = [];
    $dataHelper = __DIR__ . '/../includes/homepage_data.php';
    if (is_file($dataHelper)) {
        require_once $dataHelper;
        if (function_exists('homepage_get_circles')) {
            $all_circles = homepage_get_circles();
        }
    }
}

$hpCircleImg = static function (array $circles, string ...$needles): string {
    foreach ($circles as $c) {
        foreach ($needles as $needle) {
            if (stripos($c['name'] ?? '', $needle) !== false) {
                return get_image_url($c['image'] ?? '');
            }
        }
    }
    if (!empty($circles[0]['image'])) {
        return get_image_url($circles[0]['image']);
    }
    return '/uploads/logo_transparent.png';
};

$hpSecondaryNav = [
    [
        'label' => 'Birthday',
        'href' => collection_url('occasion', 'birthday'),
        'mega' => [
            'columns' => [
                [
                    'title' => 'Shop Birthday',
                    'links' => [
                        ['label' => 'Birthday Bouquets', 'href' => collection_url('occasion', 'birthday')],
                        ['label' => 'Birthday Combos', 'href' => collection_url('collection', 'flower-combos')],
                        ['label' => 'Birthday Cakes', 'href' => '/cakes.php'],
                    ],
                ],
                [
                    'title' => 'Popular Picks',
                    'links' => [
                        ['label' => 'Same Day Birthday Gifts', 'href' => collection_url('collection', 'same-day-delivery')],
                        ['label' => 'Rose Bouquets', 'href' => collection_url('flower', 'roses')],
                        ['label' => 'Gift Hampers', 'href' => collection_url('collection', 'hampers')],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Birthday Joy, Gift-Wrapped',
                'sub' => 'Curated blooms & cakes',
                'href' => collection_url('occasion', 'birthday'),
                'img' => $hpCircleImg($all_circles, 'Birthday'),
            ],
        ],
    ],
    [
        'label' => 'Anniversary',
        'href' => collection_url('occasion', 'anniversary'),
        'mega' => [
            'columns' => [
                [
                    'title' => 'Shop Anniversary',
                    'links' => [
                        ['label' => 'Anniversary Flowers', 'href' => collection_url('occasion', 'anniversary')],
                        ['label' => 'Romantic Roses', 'href' => collection_url('flower', 'roses')],
                        ['label' => 'Flower & Cake Combos', 'href' => '/cakes.php'],
                    ],
                ],
                [
                    'title' => 'Make It Special',
                    'links' => [
                        ['label' => 'Premium Collection', 'href' => collection_url('collection', 'premium-bouquets')],
                        ['label' => 'Same Day Delivery', 'href' => collection_url('collection', 'same-day-delivery')],
                        ['label' => 'Gift Hampers', 'href' => collection_url('collection', 'hampers')],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Celebrate Your Love Story',
                'sub' => 'Handcrafted romantic bouquets',
                'href' => collection_url('occasion', 'anniversary'),
                'img' => $hpCircleImg($all_circles, 'Anniversary'),
            ],
        ],
    ],
    [
        'label' => 'Occasions',
        'href' => collection_url('occasion', 'birthday'),
        'mega' => [
            'columns' => [
                [
                    'title' => 'By Occasion',
                    'links' => [
                        ['label' => 'Love & Romance', 'href' => collection_url('occasion', 'love-romance')],
                        ['label' => 'Wedding', 'href' => collection_url('occasion', 'wedding')],
                        ['label' => 'Congratulations', 'href' => collection_url('occasion', 'congratulations')],
                        ['label' => 'Sympathy', 'href' => collection_url('occasion', 'sympathy')],
                    ],
                ],
                [
                    'title' => 'More Moments',
                    'links' => [
                        ['label' => 'Birthday', 'href' => collection_url('occasion', 'birthday')],
                        ['label' => 'Anniversary', 'href' => collection_url('occasion', 'anniversary')],
                        ['label' => 'Thank You', 'href' => collection_url('occasion', 'thank-you')],
                        ['label' => 'Celebrations Calendar', 'href' => '/celebration-calendar'],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Celebrations Calendar',
                'sub' => 'Every gifting day, mapped',
                'href' => '/celebration-calendar',
                'img' => $hpCircleImg($all_circles, 'Love', 'Romance'),
            ],
        ],
    ],
    [
        'label' => 'Flowers',
        'href' => '/flowers.php',
        'mega' => [
            'columns' => [
                [
                    'title' => 'Collections',
                    'links' => [
                        ['label' => 'All Bouquets', 'href' => '/flowers.php'],
                        ['label' => 'Rose Bouquets', 'href' => collection_url('flower', 'roses')],
                        ['label' => 'Premium Collection', 'href' => collection_url('collection', 'premium-bouquets')],
                        ['label' => 'Newly Added', 'href' => collection_url('collection', 'new-arrivals')],
                    ],
                ],
                [
                    'title' => 'By Flower Type',
                    'links' => [
                        ['label' => 'Orchids', 'href' => collection_url('flower', 'orchids')],
                        ['label' => 'Lilies', 'href' => collection_url('flower', 'lilies')],
                        ['label' => 'Carnations', 'href' => collection_url('flower', 'carnations')],
                        ['label' => 'Tulips', 'href' => collection_url('flower', 'tulips')],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Fresh From Our Studio',
                'sub' => 'Cut & arranged the same day',
                'href' => collection_url('collection', 'same-day-delivery'),
                'img' => $hpCircleImg($all_circles, 'Same Day'),
            ],
        ],
    ],
    [
        'label' => 'LUXE',
        'href' => collection_url('collection', 'luxury-flowers'),
        'mega' => [
            'columns' => [
                [
                    'title' => 'Shop LUXE',
                    'links' => [
                        ['label' => 'Premium Roses', 'href' => collection_url('flower', 'roses')],
                        ['label' => 'Designer Bouquets', 'href' => collection_url('collection', 'designer-bouquets')],
                        ['label' => 'Luxury Combos', 'href' => collection_url('collection', 'flower-combos')],
                        ['label' => 'All Luxe Collection', 'href' => collection_url('collection', 'luxury-flowers')],
                    ],
                ],
                [
                    'title' => 'Curated For',
                    'links' => [
                        ['label' => 'Anniversary', 'href' => collection_url('occasion', 'anniversary')],
                        ['label' => 'Wedding', 'href' => collection_url('occasion', 'wedding')],
                        ['label' => 'Corporate Gifting', 'href' => collection_url('relation', 'colleagues')],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Statement Luxury Blooms',
                'sub' => 'Our most premium arrangements',
                'href' => collection_url('collection', 'luxury-flowers'),
                'img' => $hpCircleImg($all_circles, 'Love', 'Romance'),
            ],
        ],
    ],
    [
        'label' => 'Personalised',
        'href' => personalized_url(),
        'mega' => [
            'simple' => true,
            'columns' => [
                [
                    'title' => 'Personalised Gifts',
                    'links' => [
                        ['label' => 'Photo Frames & Keepsakes', 'href' => personalized_url('photo-frames')],
                        ['label' => 'Custom Message Cards', 'href' => personalized_url('custom-message-cards')],
                        ['label' => 'Engraved Gifts', 'href' => personalized_url('engraved-gifts')],
                        ['label' => 'All Personalised', 'href' => personalized_url()],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Make It Truly Theirs',
                'sub' => 'Personalised gifts launching soon',
                'href' => personalized_url(),
                'img' => $hpCircleImg($all_circles, 'Gifts'),
            ],
        ],
    ],
    [
        'label' => 'Lifestyle',
        'href' => '/gifts',
        'mega' => [
            'simple' => true,
            'columns' => [
                [
                    'title' => 'Home & Living',
                    'links' => [
                        ['label' => 'Home Décor', 'href' => '/gifts'],
                        ['label' => 'Wellness & Candles', 'href' => '/gifts'],
                        ['label' => 'Planters & Pots', 'href' => collection_url('collection', 'plants')],
                        ['label' => 'Personalised Gifts', 'href' => personalized_url()],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Elevate Their Space',
                'sub' => 'Thoughtful home & lifestyle picks',
                'href' => '/gifts',
                'img' => $hpCircleImg($all_circles, 'Plants'),
            ],
        ],
    ],
    [
        'label' => 'Hampers',
        'href' => collection_url('collection', 'hampers'),
        'mega' => [
            'columns' => [
                [
                    'title' => 'Gift Hampers',
                    'links' => [
                        ['label' => 'Flower Hampers', 'href' => collection_url('collection', 'hampers')],
                        ['label' => 'Chocolate Hampers', 'href' => '/search-results.php?q=chocolate'],
                        ['label' => 'Gourmet Hampers', 'href' => collection_url('collection', 'hampers')],
                        ['label' => 'All Hampers', 'href' => collection_url('collection', 'hampers')],
                    ],
                ],
                [
                    'title' => 'Popular Combos',
                    'links' => [
                        ['label' => 'Flower & Cake', 'href' => '/cakes.php'],
                        ['label' => 'Flower & Chocolates', 'href' => collection_url('collection', 'flower-combos')],
                        ['label' => 'Same Day Hampers', 'href' => collection_url('collection', 'same-day-delivery')],
                    ],
                ],
            ],
            'feature' => [
                'label' => 'Curated Gift Hampers',
                'sub' => 'Flowers, treats & more in one box',
                'href' => collection_url('collection', 'hampers'),
                'img' => $hpCircleImg($all_circles, 'Gifts', 'Cakes'),
            ],
        ],
    ],
    ['label' => 'Same Day Delivery', 'href' => collection_url('collection', 'same-day-delivery')],
    ['label' => 'Plants', 'href' => collection_url('collection', 'plants')],
    [
        'label' => 'Combos',
        'href' => collection_url('collection', 'flower-combos'),
        'mega' => [
            'simple' => true,
            'columns' => [
                [
                    'title' => 'Gift Combos',
                    'links' => [
                        ['label' => 'Flower & Cake', 'href' => '/cakes.php'],
                        ['label' => 'Gift Hampers', 'href' => collection_url('collection', 'hampers')],
                        ['label' => 'All Gifts', 'href' => '/gifts'],
                    ],
                ],
            ],
        ],
    ],
    [
        'label' => 'International',
        'href' => '/#hp-send-gifts-abroad',
        'mega' => [
            'simple' => true,
            'columns' => [
                [
                    'title' => 'Delivery Locations',
                    'links' => [
                        ['label' => 'Delhi NCR', 'href' => '/flower-delivery-in-delhi'],
                        ['label' => 'Send Gifts Abroad', 'href' => '/#hp-send-gifts-abroad'],
                    ],
                ],
            ],
        ],
    ],
];
?>
<nav class="lx-catnav" aria-label="Shop categories">
    <ul class="lx-catnav__list hide-scrollbar">
        <?php foreach ($hpSecondaryNav as $item):
            $hasMega = !empty($item['mega']['columns']);
            $isSimple = !empty($item['mega']['simple']);
            $colCount = $hasMega ? count($item['mega']['columns']) : 0;
        ?>
        <li class="lx-catnav__item">
            <a class="lx-catnav__link" href="<?= htmlspecialchars(normalize_internal_href($item['href'])) ?>"<?= $hasMega ? ' aria-haspopup="true"' : '' ?>>
                <?= htmlspecialchars($item['label']) ?>
                <?php if ($hasMega): ?><span class="lx-caret" aria-hidden="true"></span><?php endif; ?>
            </a>
            <?php if ($hasMega): ?>
            <div class="lx-catnav__mega<?= $isSimple ? ' lx-catnav__mega--simple' : '' ?>" style="--lx-mega-cols: <?= (int) $colCount ?>;<?= $isSimple ? ' width: min(420px, calc(100vw - 3rem));' : '' ?>">
                <div class="lx-catnav__mega-inner">
                    <?php foreach ($item['mega']['columns'] as $col): ?>
                    <div class="lx-catnav__col">
                        <p class="lx-catnav__col-title"><?= htmlspecialchars($col['title']) ?></p>
                        <?php foreach ($col['links'] as $link): ?>
                        <a href="<?= htmlspecialchars(normalize_internal_href($link['href'])) ?>"><?= htmlspecialchars($link['label']) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!empty($item['mega']['feature'])): $feat = $item['mega']['feature']; ?>
                    <a class="lx-catnav__feature" href="<?= htmlspecialchars(normalize_internal_href($feat['href'])) ?>">
                        <img src="<?= htmlspecialchars($feat['img']) ?>" alt="" width="240" height="240" loading="lazy" decoding="async">
                        <span class="lx-catnav__feature-label">
                            <?= htmlspecialchars($feat['label']) ?>
                            <span><?= htmlspecialchars($feat['sub']) ?></span>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
