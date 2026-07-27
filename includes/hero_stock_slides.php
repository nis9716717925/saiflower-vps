<?php
/**
 * Curated hero imagery for split homepage sliders.
 * Local assets under /assets/images/hero/ with unique flower photos each.
 */
require_once __DIR__ . '/collection_taxonomy.php';

$heroBase = '/assets/images/hero';

return [
    'side' => [
        [
            'theme' => 'lavender',
            'kicker' => 'Sai Flower',
            'title' => 'Fresh Flowers,<br>Delivered with Love',
            'cta' => 'Shop Now',
            'href' => '/flowers.php',
            'img' => $heroBase . '/side-pink-roses.jpg',
        ],
        [
            'theme' => 'mint',
            'kicker' => 'Same Day',
            'title' => 'Surprise Them<br>Before Sunset',
            'cta' => 'Order Now',
            'href' => collection_url('collection', 'same-day-delivery'),
            'img' => $heroBase . '/side-same-day.jpg',
        ],
        [
            'theme' => 'blush',
            'kicker' => 'LUXE',
            'title' => 'Premium Bouquets<br>For Special Moments',
            'cta' => 'Explore Luxe',
            'href' => collection_url('collection', 'luxury-flowers'),
            'img' => $heroBase . '/side-luxe-bouquet.jpg',
        ],
    ],
    'main' => [
        [
            'theme' => 'peach',
            'kicker' => 'Sai Flower',
            'title' => 'Same Day Delivery<br>in Delhi',
            'subtitle' => 'Handpicked luxury bouquets for all special moments.',
            'cta' => 'Shop Now',
            'href' => collection_url('collection', 'same-day-delivery'),
            'img' => $heroBase . '/main-same-day.jpg',
        ],
        [
            'theme' => 'cream',
            'kicker' => 'Sai Flower',
            'title' => 'Premium Blooms,<br>Curated for You',
            'subtitle' => 'Handpicked luxury bouquets for every special moment.',
            'cta' => 'Order Now',
            'href' => collection_url('collection', 'premium-bouquets'),
            'img' => $heroBase . '/main-premium-blooms.jpg',
        ],
        [
            'theme' => 'blush',
            'kicker' => 'Sai Flower',
            'title' => 'Birthday Joy,<br>Gift-Wrapped',
            'subtitle' => 'Curated blooms, cakes & more for thoughtful celebrations.',
            'cta' => 'Shop Now',
            'href' => collection_url('occasion', 'birthday'),
            'img' => $heroBase . '/main-birthday-joy.jpg',
        ],
    ],
    'themes' => [
        'lavender' => 'linear-gradient(165deg, #ece4f8 0%, #ddd5ee 55%, #d4cbe8 100%)',
        'mint' => 'linear-gradient(165deg, #e4f3ec 0%, #d4ebe0 55%, #c8e4d6 100%)',
        'blush' => 'linear-gradient(165deg, #fdeef0 0%, #fce0e5 55%, #f8d4dc 100%)',
        'cream' => 'linear-gradient(135deg, #faf6ee 0%, #f3ebe0 100%)',
        'peach' => 'linear-gradient(135deg, #fdf0e8 0%, #fce4d6 100%)',
        'green' => 'linear-gradient(135deg, #f0f7f2 0%, #e4f0e8 100%)',
    ],
];
