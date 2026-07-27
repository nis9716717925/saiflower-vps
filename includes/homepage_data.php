<?php
/**
 * Static homepage content — snapshot from live saiflower.com (May 2026).
 */

function homepage_get_slides(): array {
    static $slides = [
        [
            'image' => 'uploads/slides/img_69ba47e9c99b9_file00000000f6d4720bb9d8a6de79472743.webp',
            'mobile_image' => 'uploads/slides/img_69ba47e9c99b9_file00000000f6d4720bb9d8a6de79472743.webp',
            'link' => '/flowers.php?sort=price_high',
            'title' => 'Premium Blooms, Curated for You',
            'subtitle' => 'Handpicked luxury bouquets for every special moment',
            'cta' => 'ORDER NOW',
            'theme' => 'cream',
            'status' => 1,
            'sort_order' => 0
        ],
        [
            'image' => 'uploads/slides/img_69ba47efc2ddf_file000000009bfc720babbbc96f0f07e18d.webp',
            'mobile_image' => 'uploads/slides/img_69ba47efc2ddf_file000000009bfc720babbbc96f0f07e18d.webp',
            'link' => '/tag.php?name=same%20day',
            'title' => 'Same Day Delivery in Delhi',
            'subtitle' => 'Order before 6 PM & surprise them today',
            'cta' => 'ORDER NOW',
            'theme' => 'peach',
            'status' => 1,
            'sort_order' => 1
        ],
        [
            'image' => 'uploads/slides/img_69ba47f9d9242_file00000000236c720b87fe17c7231e0f44.webp',
            'mobile_image' => 'uploads/slides/img_69ba47f9d9242_file00000000236c720b87fe17c7231e0f44.webp',
            'link' => '/flowers.php?category=3',
            'title' => 'Birthday Joy, Gift-Wrapped',
            'subtitle' => 'Curated blooms, cakes & more for thoughtful celebrations',
            'cta' => 'ORDER NOW',
            'theme' => 'blush',
            'status' => 1,
            'sort_order' => 2
        ]
    ];
    return $slides;
}

function homepage_get_circles(): array {
    static $circles = [
        [
            'name' => 'Same Day',
            'image' => 'uploads/circles/img_69c0ca5ed57d5_Untitleddesign7.webp',
            'link' => '/tag.php?name=same%20day',
            'status' => 1,
            'sort_order' => 0
        ],
        [
            'name' => 'Birthday',
            'image' => 'uploads/circles/img_69c0d1968393f_Untitleddesign10.webp',
            'link' => '/flowers.php?category=3',
            'status' => 1,
            'sort_order' => 1
        ],
        [
            'name' => 'Love & Romance',
            'image' => 'uploads/circles/img_69c0d432c0e63_Untitleddesign12.webp',
            'link' => '/tag.php?name=love',
            'status' => 1,
            'sort_order' => 2
        ],
        [
            'name' => 'Sympathy',
            'image' => 'uploads/circles/img_69c0d1f315e42_Untitleddesign11.webp',
            'link' => '/tag.php?name=sympathy',
            'status' => 1,
            'sort_order' => 3
        ],
        [
            'name' => 'Anniversary',
            'image' => 'uploads/circles/img_69c0d67ec7a7d_Weddingeventgolden202603231127.webp',
            'link' => '/flowers.php?category=4',
            'status' => 1,
            'sort_order' => 4
        ],
        [
            'name' => 'Cakes',
            'image' => 'uploads/circles/img_69c0d7a4a7e88_Cakewithflowing202603231132.webp',
            'link' => '/cakes.php',
            'status' => 1,
            'sort_order' => 5
        ],
        [
            'name' => 'Plants',
            'image' => 'uploads/circles/img_69c0d06492a19_Untitleddesign8.webp',
            'link' => '/tag.php?name=plants',
            'status' => 1,
            'sort_order' => 6
        ],
        [
            'name' => 'Gifts',
            'image' => 'uploads/circles/img_69c0d0e7c2d77_Untitleddesign9.webp',
            'link' => '/gifts.php',
            'status' => 1,
            'sort_order' => 7
        ]
    ];
    return $circles;
}

function homepage_get_sections(): array {
    static $sections = [
        [
            'id' => 1,
            'title' => 'Best Sellers',
            'subtitle' => '',
            'type' => 'carousel',
            'status' => 1,
            'sort_order' => 0
        ],
        [
            'id' => 2,
            'title' => 'Occasions',
            'subtitle' => '',
            'type' => 'carousel',
            'status' => 1,
            'sort_order' => 1
        ],
        [
            'id' => 3,
            'title' => 'Pick Their Fav Flower',
            'subtitle' => '',
            'type' => 'flower_picker',
            'status' => 1,
            'sort_order' => 2
        ],
        [
            'id' => 4,
            'title' => 'Make Someone Smile',
            'subtitle' => '',
            'type' => 'cta_banner',
            'status' => 1,
            'sort_order' => 3
        ],
        [
            'id' => 5,
            'title' => 'Birthday Celebration',
            'subtitle' => '',
            'type' => 'banner',
            'status' => 1,
            'sort_order' => 4
        ],
        [
            'id' => 6,
            'title' => 'Celebrations Calendar',
            'subtitle' => '',
            'type' => 'calendar',
            'status' => 1,
            'sort_order' => 5
        ],
        [
            'id' => 7,
            'title' => 'Same Day Surprises',
            'subtitle' => '',
            'type' => 'carousel',
            'status' => 1,
            'sort_order' => 6
        ],
        [
            'id' => 8,
            'title' => 'For Every Occasions',
            'subtitle' => '',
            'type' => 'carousel',
            'status' => 1,
            'sort_order' => 7
        ]
    ];
    return $sections;
}

function homepage_get_section_items(): array {
    static $items = [
        [
            'image' => 'uploads/sections/img_69aff80390f51_WhatsAppImage20251204at021647d47ec67e.webp',
            'mobile_image' => '',
            'title' => 'Luxury Black Wrapped Red Rose Bouquet – Premium Romantic Flower Gift for Anniversary & Valentine',
            'subtitle' => '',
            'price' => '599',
            'link' => '/luxury-black-wrapped-red-rose-bouquet-premium-romantic-flower-gift-for-anniversary-valentine',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '799',
            'discount_label' => '25%',
            'id' => 1,
            'section_id' => 1,
            'sort_order' => 0
        ],
        [
            'image' => 'uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp',
            'mobile_image' => '',
            'title' => 'Elegant White Rose Bouquet – Premium Fresh White Roses Luxury Flower Arrangement',
            'subtitle' => '',
            'price' => '699',
            'link' => '/elegant-white-rose-bouquet-premium-fresh-white-roses-luxury-flower-arrangement',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '999',
            'discount_label' => '30%',
            'id' => 2,
            'section_id' => 1,
            'sort_order' => 1
        ],
        [
            'image' => 'uploads/sections/img_69affd636df2d_img69a2e27150cf8WhatsAppImage20260228at60459PM.webp',
            'mobile_image' => '',
            'title' => 'Elegant 6 Red Rose Petite Bouquet - Gold Designer Wrap',
            'subtitle' => '',
            'price' => '699',
            'link' => '/elegant-6-red-rose-petite-bouquet-gold-wrap',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '999',
            'discount_label' => '30%',
            'id' => 3,
            'section_id' => 1,
            'sort_order' => 2
        ],
        [
            'image' => 'uploads/sections/img_69b00138c4d62_img69a2dc61ad80aWhatsAppImage20260228at53727PM.webp',
            'mobile_image' => '',
            'title' => 'Premium Red Rose Bouquet with White Fillers – Elegant Romantic Flower Gift for Valentine & Anniversary',
            'subtitle' => '',
            'price' => '799',
            'link' => '/premium-red-rose-bouquet-with-white-fillers-elegant-romantic-flower-gift-for-valentine-anniversary',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '999',
            'discount_label' => '20%',
            'id' => 4,
            'section_id' => 1,
            'sort_order' => 3
        ],
        [
            'image' => 'uploads/sections/img_69b00371393da_img69a2e6bd0f223WhatsAppImage20260228at62503PM1.webp',
            'mobile_image' => '',
            'title' => 'Chic 8 Red Rose Bouquet in Contrast Crimson & White Wrap',
            'subtitle' => '',
            'price' => '999',
            'link' => '/chic-8-red-rose-bouquet-in-contrast-crimson-white-wrap',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '1299',
            'discount_label' => '23%',
            'id' => 5,
            'section_id' => 1,
            'sort_order' => 4
        ],
        [
            'image' => 'uploads/sections/img_69b003efc61ae_img69a6b1c6b3b9aWhatsAppImage20260303at30238PM.webp',
            'mobile_image' => '',
            'title' => 'Premium 10 Red Roses Bouquet – Luxury Romantic Flower Arrangement in Black & Red Wrap',
            'subtitle' => '',
            'price' => '999',
            'link' => '/premium-10-red-roses-bouquet-luxury-romantic-flower-arrangement-in-black-red-wrap',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '1599',
            'discount_label' => '38%',
            'id' => 6,
            'section_id' => 1,
            'sort_order' => 5
        ],
        [
            'image' => 'uploads/sections/img_69b004c6d5fb1_img69a2ddd6ccb7eWhatsAppImage20260228at54457PM.webp',
            'mobile_image' => '',
            'title' => 'Premium 12 Red Roses Bouquet in Black Wrapping Paper – Luxury Romantic Flower Gift',
            'subtitle' => '',
            'price' => '1199',
            'link' => '/premium-12-red-roses-bouquet-in-black-wrapping-paper-luxury-romantic-flower-gift',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '1599',
            'discount_label' => '25%',
            'id' => 7,
            'section_id' => 1,
            'sort_order' => 6
        ],
        [
            'image' => 'uploads/sections/img_69b0083c48b2a_img69a6abb280a5fWhatsAppImage20260303at23509PM.webp',
            'mobile_image' => '',
            'title' => 'Premium Purple Orchid Bouquet – Luxury Fresh Orchid Flower Arrangement by Sai Flower',
            'subtitle' => '',
            'price' => '1899',
            'link' => '/premium-purple-orchid-bouquet-luxury-fresh-flower-arrangement',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '2899',
            'discount_label' => '34%',
            'id' => 8,
            'section_id' => 1,
            'sort_order' => 7
        ],
        [
            'image' => 'uploads/sections/img_69b00b48d29e1_img69a2e81c69b97WhatsAppImage20260228at62855PM.webp',
            'mobile_image' => '',
            'title' => 'Grand Statement 50 Red Rose Bouquet – Luxury Midnight Black & White Wrap',
            'subtitle' => '',
            'price' => '2900',
            'link' => '/grand-statement-50-red-rose-bouquet-midnight-black-white',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '3600',
            'discount_label' => '19%',
            'id' => 9,
            'section_id' => 1,
            'sort_order' => 8
        ],
        [
            'image' => 'uploads/sections/img_69b00d7d6b073_img69a6aa4b1d253WhatsAppImage20260303at23112PM.webp',
            'mobile_image' => '',
            'title' => 'Premium Red Rose Bouquet – Luxury Romantic Fresh Flower Arrangement by Sai Flower',
            'subtitle' => '',
            'price' => '999',
            'link' => '/premium-red-rose-bouquet-luxury-romantic-fresh-flower-arrangement',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '1599',
            'discount_label' => '38%',
            'id' => 10,
            'section_id' => 1,
            'sort_order' => 9
        ],
        [
            'image' => 'uploads/sections/img_69c123f37096e_Screenshot20260313001120SamsungNotes.webp',
            'mobile_image' => '',
            'title' => 'Sage Sunflower Bloom Luxe Bouquet',
            'subtitle' => '',
            'price' => '2199',
            'link' => '/sage-sunflower-bloom-luxe-bouquet',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '2999',
            'discount_label' => '27%',
            'id' => 11,
            'section_id' => 2,
            'sort_order' => 0
        ],
        [
            'image' => 'uploads/sections/img_69c1251e2fe60_Screenshot20260313000251SamsungNotes.webp',
            'mobile_image' => '',
            'title' => 'Ocean Blue Orchid Grand Bouquet',
            'subtitle' => '',
            'price' => '5199',
            'link' => '/ocean-blue-orchid-grand-bouquet',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '9899',
            'discount_label' => '24%',
            'id' => 12,
            'section_id' => 2,
            'sort_order' => 1
        ],
        [
            'image' => 'uploads/sections/img_69c12599c780c_Screenshot20260316171527SamsungNotes.webp',
            'mobile_image' => '',
            'title' => 'Sweet Pink Tulip Bloom Bouquet',
            'subtitle' => '',
            'price' => '3999',
            'link' => '/sweet-pink-tulip-bloom-bouquet',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '8999',
            'discount_label' => '56%',
            'id' => 13,
            'section_id' => 2,
            'sort_order' => 2
        ],
        [
            'image' => 'uploads/sections/img_69c127d60bae3_img69a6d18fd207dChatGPTImageMar32026054446PM.webp',
            'mobile_image' => '',
            'title' => 'Luxury 100+ Red Roses Bouquet – Premium Grand Romantic Flower Arrangement',
            'subtitle' => '',
            'price' => '9999',
            'link' => '/luxury-100-red-roses-bouquet-premium-grand-romantic-flower-arrangement',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '12999',
            'discount_label' => '23%',
            'id' => 14,
            'section_id' => 2,
            'sort_order' => 3
        ],
        [
            'image' => 'uploads/sections/img_69c12821aa9cf_img69a6cfcf3c0a1ChatGPTImageMar32026053404PM.webp',
            'mobile_image' => '',
            'title' => 'Premium Blue Orchid & White Rose Luxury Bouquet – Elegant Designer Flower Arrangement',
            'subtitle' => '',
            'price' => '4599',
            'link' => '/premium-blue-orchid-white-rose-luxury-bouquet-elegant-designer-flower-arrangement',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '5599',
            'discount_label' => '18%',
            'id' => 15,
            'section_id' => 2,
            'sort_order' => 4
        ],
        [
            'image' => 'uploads/sections/img_69c12a490d272_img69b555470c87eWhatsAppImage20260314at34432PM.webp',
            'mobile_image' => '',
            'title' => 'Midnight White Lily Grace Bouquet',
            'subtitle' => '',
            'price' => '2399',
            'link' => '/midnight-white-lily-grace-bouquet',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '3599',
            'discount_label' => '33%',
            'id' => 16,
            'section_id' => 2,
            'sort_order' => 5
        ],
        [
            'image' => 'uploads/sections/img_69c12c0007f57_img69b55761a78f2WhatsAppImage20260314at34904PM.webp',
            'mobile_image' => '',
            'title' => 'Mint White Lily Elegance Bouquet',
            'subtitle' => '',
            'price' => '2199',
            'link' => '/mint-white-lily-elegance-bouquet',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '3299',
            'discount_label' => '33%',
            'id' => 17,
            'section_id' => 2,
            'sort_order' => 6
        ],
        [
            'image' => 'uploads/sections/img_69c12c6960068_img69a6d49308275ChatGPTImageMar32026055603PM.webp',
            'mobile_image' => '',
            'title' => 'White Daisy Bouquet with Red Rose – Elegant Romantic Flower Arrangement',
            'subtitle' => '',
            'price' => '2799',
            'link' => '/white-daisy-bouquet-with-red-rose-elegant-romantic-flower-arrangement',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '4500',
            'discount_label' => '38%',
            'id' => 18,
            'section_id' => 2,
            'sort_order' => 7
        ],
        [
            'image' => 'uploads/sections/img_69c12ca4d6812_img69b5447deae89WhatsAppImage20260314at31518PM.webp',
            'mobile_image' => '',
            'title' => 'Pink Blush Baby Rose Bouquet',
            'subtitle' => '',
            'price' => '2199',
            'link' => '/pink-blush-baby-rose-bouquet',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '2999',
            'discount_label' => '27%',
            'id' => 19,
            'section_id' => 2,
            'sort_order' => 8
        ],
        [
            'image' => 'uploads/sections/img_69c12d233da58_img69a58d0563f07GeminiGeneratedImage4e2iwk4e2iwk4e2i.webp',
            'mobile_image' => '',
            'title' => 'Luxury 100 Red Rose Bouquet with Gypsophila & Pink Wrap – Sai Flower',
            'subtitle' => '',
            'price' => '9999',
            'link' => '/luxury-100-red-rose-bouquet-with-gypsophila-pink-wrap-sai-flower',
            'top_badge_text' => '',
            'badge_text' => '',
            'rating' => '',
            'delivery_info' => 'Same day',
            'original_price' => '15000',
            'discount_label' => '38%',
            'id' => 20,
            'section_id' => 2,
            'sort_order' => 9
        ],
        [
            'image' => 'uploads/sections/img_6998729febff3_IMG3579scaled.webp',
            'title' => 'Carnations',
            'link' => '/tag?name=carnation',
            'subtitle' => '',
            'price' => '0',
            'mobile_image' => '',
            'id' => 21,
            'section_id' => 3,
            'sort_order' => 0
        ],
        [
            'image' => 'uploads/sections/img_699872f585703_ob7q8ewBZK1758599912308.webp',
            'title' => 'Orchids',
            'link' => '/tag?name=orchid',
            'subtitle' => '',
            'price' => '0',
            'mobile_image' => '',
            'id' => 22,
            'section_id' => 3,
            'sort_order' => 1
        ],
        [
            'image' => 'uploads/sections/img_699dccd658a6d_Screenshot20250911031911SamsungNotes.webp',
            'title' => 'Red Roses',
            'link' => '/tag?name=rose',
            'subtitle' => '',
            'price' => '0',
            'mobile_image' => '',
            'id' => 23,
            'section_id' => 3,
            'sort_order' => 2
        ],
        [
            'image' => 'uploads/sections/img_699dcd3bc3e78_Screenshot20250906175129SamsungNotes.webp',
            'title' => 'Lilies',
            'link' => '/tag?name=lily',
            'subtitle' => '',
            'price' => '0',
            'mobile_image' => '',
            'id' => 24,
            'section_id' => 3,
            'sort_order' => 3
        ],
        [
            'image' => 'uploads/sections/img_69b3fc46e7fd4_WhatsAppImage20260313at52923PM.webp',
            'title' => 'Sunflower',
            'link' => '/golden-sunflower-garden-bouquet',
            'subtitle' => '',
            'price' => '0',
            'mobile_image' => '',
            'id' => 25,
            'section_id' => 3,
            'sort_order' => 4
        ],
        [
            'image' => 'uploads/sections/img_69bbcddb5096e_img69b909c6a9eb8WhatsAppImage20260316at54826PM.webp',
            'title' => 'Tulip',
            'link' => '/blush-pink-tulip-hand-bouquet',
            'subtitle' => '',
            'price' => '0',
            'mobile_image' => '',
            'id' => 26,
            'section_id' => 3,
            'sort_order' => 5
        ],
        [
            'image' => '',
            'title' => '',
            'link' => '/gifts.php?promo=GIFTING-A-SMILE',
            'subtitle' => '',
            'price' => '0',
            'mobile_image' => '',
            'id' => 27,
            'section_id' => 4,
            'sort_order' => 0
        ],
        [
            'image' => 'uploads/sections/img_699996ed02085_happybirthdaycelebrationbannerbackgroundwithballoonhappybirthdaysocialmediabannervector.webp',
            'mobile_image' => 'uploads/sections/img_699996ed02085_happybirthdaycelebrationbannerbackgroundwithballoonhappybirthdaysocialmediabannervector.webp',
            'link' => '/make-every-birthday-special-with-sai-flower',
            'title' => 'Birthday Celebration',
            'subtitle' => '',
            'price' => '0',
            'id' => 28,
            'section_id' => 5,
            'sort_order' => 0
        ],
        [
            'image' => '/assets/images/homepage/calendar/new-year.jpg',
            'title' => 'New Year',
            'link' => '/flowers.php',
            'subtitle' => '1 Jan',
            'price' => '0',
            'mobile_image' => '',
            'id' => 29,
            'section_id' => 6,
            'sort_order' => 0
        ],
        [
            'image' => '/assets/images/homepage/calendar/rose-day.jpg',
            'title' => 'Rose Day',
            'link' => '/flowers.php',
            'subtitle' => '7 Feb',
            'price' => '0',
            'mobile_image' => '',
            'id' => 30,
            'section_id' => 6,
            'sort_order' => 1
        ],
        [
            'image' => '/assets/images/homepage/calendar/valentines.jpg',
            'title' => 'Valentine\'s Day',
            'link' => '/flowers.php',
            'subtitle' => '14 Feb',
            'price' => '0',
            'mobile_image' => '',
            'id' => 31,
            'section_id' => 6,
            'sort_order' => 2
        ],
        [
            'image' => '/assets/images/homepage/calendar/womens-day.jpg',
            'title' => 'Women\'s Day',
            'link' => '/flowers.php',
            'subtitle' => '8 Mar',
            'price' => '0',
            'mobile_image' => '',
            'id' => 34,
            'section_id' => 6,
            'sort_order' => 3
        ],
        [
            'image' => '/assets/images/homepage/calendar/mothers-day.jpg',
            'title' => 'Mother\'s Day',
            'link' => '/flowers.php',
            'subtitle' => '11 May',
            'price' => '0',
            'mobile_image' => '',
            'id' => 35,
            'section_id' => 6,
            'sort_order' => 4
        ],
        [
            'image' => 'uploads/sections/img_69c3c8b961d2d_Screenshot20260325170355Google.webp',
            'title' => 'Father\'s Day',
            'link' => '/fathers-day',
            'subtitle' => '21 Jun',
            'price' => '0',
            'mobile_image' => '',
            'id' => 36,
            'section_id' => 6,
            'sort_order' => 5
        ],
        [
            'image' => '/assets/images/homepage/calendar/doctors-day.jpg',
            'title' => 'Doctor\'s Day',
            'link' => '/flowers.php',
            'subtitle' => '1 Jul',
            'price' => '0',
            'mobile_image' => '',
            'id' => 37,
            'section_id' => 6,
            'sort_order' => 6
        ],
        [
            'image' => 'uploads/sections/img_69c3cc5bcd3e3_Screenshot20260325171935Google1.webp',
            'title' => 'Friendship Day',
            'link' => '/flowers.php',
            'subtitle' => '2 Aug',
            'price' => '0',
            'mobile_image' => '',
            'id' => 38,
            'section_id' => 6,
            'sort_order' => 7
        ],
        [
            'image' => '/assets/images/homepage/calendar/raksha-bandhan.jpg',
            'title' => 'Raksha Bandhan',
            'link' => '/flowers.php',
            'subtitle' => '9 Aug',
            'price' => '0',
            'mobile_image' => '',
            'id' => 39,
            'section_id' => 6,
            'sort_order' => 8
        ],
        [
            'image' => '/assets/images/homepage/calendar/teachers-day.jpg',
            'title' => 'Teacher\'s Day',
            'link' => '/flowers.php',
            'subtitle' => '5 Sep',
            'price' => '0',
            'mobile_image' => '',
            'id' => 40,
            'section_id' => 6,
            'sort_order' => 9
        ],
        [
            'image' => '/assets/images/homepage/calendar/daughters-day.jpg',
            'title' => 'Daughter\'s Day',
            'link' => '/flowers.php',
            'subtitle' => '28 Sep',
            'price' => '0',
            'mobile_image' => '',
            'id' => 41,
            'section_id' => 6,
            'sort_order' => 10
        ],
        [
            'image' => 'uploads/sections/img_69c3d68f3616b_Screenshot20260325172225Google1.webp',
            'title' => 'Diwali',
            'link' => '/flowers.php',
            'subtitle' => '8 Nov',
            'price' => '0',
            'mobile_image' => '',
            'id' => 42,
            'section_id' => 6,
            'sort_order' => 11
        ],
        [
            'image' => '/assets/images/homepage/calendar/mens-day.jpg',
            'title' => 'International Men\'s Day',
            'link' => '/flowers.php',
            'subtitle' => '19 Nov',
            'price' => '0',
            'mobile_image' => '',
            'id' => 43,
            'section_id' => 6,
            'sort_order' => 12
        ],
        [
            'image' => '/assets/images/homepage/calendar/christmas.jpg',
            'title' => 'Christmas',
            'link' => '/flowers.php',
            'subtitle' => '25 Dec',
            'price' => '0',
            'mobile_image' => '',
            'id' => 44,
            'section_id' => 6,
            'sort_order' => 13
        ]
    ];
    return $items;
}

function homepage_get_section_items_grouped(): array {
    static $grouped = null;
    if ($grouped !== null) {
        return $grouped;
    }
    $grouped = [];
    foreach (homepage_get_section_items() as $item) {
        $sid = (int) $item['section_id'];
        if (!isset($grouped[$sid])) {
            $grouped[$sid] = [];
        }
        $grouped[$sid][] = $item;
    }
    return $grouped;
}

function homepage_get_reviews(): array {
    return [];
}

function homepage_get_promos(): array {
    static $promos = [
        [
            'code' => 'GIFTING A SMILE',
            'discount_text' => '10% off on order above 499',
            'min_order_amount' => 499,
            'status' => 1,
            'is_featured' => 1
        ]
    ];
    return $promos;
}

function homepage_display_price($price): string {
    return number_format((float) $price);
}
