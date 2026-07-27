<?php
/**
 * Central registry for flower-type, relation, occasion, and collection landings.
 * URLs: /flowers/{slug} | /relation/{slug} | /occasion/{slug} | /collection/{slug}
 */

if (!function_exists('collection_taxonomy_all')) {
    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    function collection_taxonomy_all(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $cache = [
            'flower' => collection_taxonomy_flowers(),
            'relation' => collection_taxonomy_relations(),
            'occasion' => collection_taxonomy_occasions(),
            'collection' => collection_taxonomy_collections(),
        ];

        return $cache;
    }
}

if (!function_exists('collection_url')) {
    function collection_url(string $kind, string $slug): string
    {
        $kind = collection_normalize_kind($kind);
        $slug = trim($slug, '/');
        return match ($kind) {
            'flower' => '/flowers/' . $slug,
            'relation' => '/relation/' . $slug,
            'occasion' => '/occasion/' . $slug,
            'collection' => '/collection/' . $slug,
            default => '/flowers',
        };
    }
}

if (!function_exists('collection_normalize_kind')) {
    function collection_normalize_kind(string $kind): string
    {
        $kind = strtolower(trim($kind));
        return match ($kind) {
            'flower', 'flowers', 'type', 'flower-type' => 'flower',
            'relation', 'relations', 'recipient' => 'relation',
            'occasion', 'occasions' => 'occasion',
            'collection', 'collections' => 'collection',
            default => $kind,
        };
    }
}

if (!function_exists('collection_get')) {
    function collection_get(string $kind, string $slug): ?array
    {
        $kind = collection_normalize_kind($kind);
        $slug = strtolower(trim($slug, '/'));
        $all = collection_taxonomy_all();
        if (!isset($all[$kind][$slug])) {
            return null;
        }
        $item = $all[$kind][$slug];
        $item['kind'] = $kind;
        $item['slug'] = $slug;
        $item['canonical_path'] = collection_url($kind, $slug);
        return $item;
    }
}

if (!function_exists('collection_is_flower_type_slug')) {
    function collection_is_flower_type_slug(string $slug): bool
    {
        $slug = strtolower(trim($slug, '/'));
        return isset(collection_taxonomy_flowers()[$slug]);
    }
}

if (!function_exists('collection_list')) {
    /**
     * @return list<array<string, mixed>>
     */
    function collection_list(?string $kind = null): array
    {
        $all = collection_taxonomy_all();
        $out = [];
        $kinds = $kind ? [collection_normalize_kind($kind)] : array_keys($all);
        foreach ($kinds as $k) {
            if (!isset($all[$k])) {
                continue;
            }
            foreach ($all[$k] as $slug => $item) {
                $row = $item;
                $row['kind'] = $k;
                $row['slug'] = $slug;
                $row['canonical_path'] = collection_url($k, $slug);
                $out[] = $row;
            }
        }
        return $out;
    }
}

if (!function_exists('collection_entry')) {
    /**
     * Build a normalized taxonomy entry.
     *
     * @param array<string, mixed> $filter
     * @param list<string> $related_kinds format kind:slug
     * @param list<array{q:string,a:string}> $faqs
     */
    function collection_entry(
        string $title,
        string $h1,
        string $short,
        array $filter,
        string $hero = '',
        array $related = [],
        array $faqs = [],
        string $badge = '',
        string $cta = 'Shop Now'
    ): array {
        return [
            'title' => $title,
            'h1' => $h1,
            'short_description' => $short,
            'badge' => $badge !== '' ? $badge : $title,
            'cta_label' => $cta,
            'hero_image' => $hero,
            'filter' => $filter,
            'related' => $related,
            'faqs' => $faqs,
        ];
    }
}

if (!function_exists('collection_default_faqs')) {
    /**
     * @return list<array{q:string,a:string}>
     */
    function collection_default_faqs(string $label): array
    {
        return [
            [
                'q' => "Can I get same-day delivery for {$label} in Delhi NCR?",
                'a' => "Yes. Order before our daily cut-off for same-day {$label} delivery across Delhi, Gurgaon, Noida and nearby NCR areas. WhatsApp us if you need express help.",
            ],
            [
                'q' => "Are the flowers for {$label} freshly arranged?",
                'a' => 'Every bouquet is handcrafted with daily-fresh blooms by our florists. We guarantee freshness and careful packaging for doorstep delivery.',
            ],
            [
                'q' => "What payment methods do you accept for {$label} orders?",
                'a' => 'We accept UPI, credit/debit cards, and secure online wallets at checkout. Your payment details are encrypted and never stored on our servers.',
            ],
            [
                'q' => "Can I add a personal message with {$label} gifts?",
                'a' => 'Absolutely. Add a free message card at checkout, or mention special instructions on WhatsApp and our team will include them with your order.',
            ],
            [
                'q' => "What is your return or replacement policy?",
                'a' => 'If your order arrives damaged or incorrect, contact us within the window in our refund policy. We will arrange a replacement or refund as applicable.',
            ],
        ];
    }
}

if (!function_exists('collection_taxonomy_flowers')) {
    function collection_taxonomy_flowers(): array
    {
        $hero = 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=1600&q=80';
        return [
            'roses' => collection_entry(
                'Roses',
                'Fresh Rose Bouquets Online — Delhi NCR Delivery',
                'Timeless red, pink & mixed rose bouquets for love, birthdays and celebrations. Same-day rose delivery across Delhi NCR.',
                ['tables' => ['flowers'], 'name_keywords' => ['rose', 'roses'], 'tags' => ['rose', 'roses', 'red rose'], 'match' => 'any'],
                'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?auto=format&fit=crop&w=1600&q=80',
                ['occasion:love-romance', 'relation:girlfriend', 'collection:premium-bouquets', 'flower:lilies'],
                collection_default_faqs('rose bouquets'),
                'Rose Collection'
            ),
            'lilies' => collection_entry(
                'Lilies',
                'Elegant Lily Bouquets Online — Fresh Delivery',
                'Graceful white & oriental lily arrangements for sympathy, thank-you and premium gifting.',
                ['tables' => ['flowers'], 'name_keywords' => ['lily', 'lilies'], 'tags' => ['lily', 'lilies'], 'match' => 'any'],
                $hero,
                ['occasion:sympathy', 'occasion:thank-you', 'collection:premium-bouquets', 'flower:orchids'],
                collection_default_faqs('lily bouquets')
            ),
            'sunflowers' => collection_entry(
                'Sunflowers',
                'Bright Sunflower Bouquets — Same-Day Delivery',
                'Cheerful sunflower arrangements that light up birthdays, get-well wishes and friendship days.',
                ['tables' => ['flowers'], 'name_keywords' => ['sunflower', 'sunflowers'], 'tags' => ['sunflower', 'sunflowers'], 'match' => 'any'],
                'https://images.unsplash.com/photo-1597848212624-e593c82eb025?auto=format&fit=crop&w=1600&q=80',
                ['occasion:birthday', 'relation:friends', 'collection:trending-flowers', 'flower:gerberas'],
                collection_default_faqs('sunflower bouquets')
            ),
            'orchids' => collection_entry(
                'Orchids',
                'Premium Orchid Plants & Arrangements',
                'Luxury orchid gifts for housewarming, corporate gifting and elegant celebrations.',
                ['tables' => ['flowers'], 'name_keywords' => ['orchid', 'orchids'], 'tags' => ['orchid', 'orchids'], 'match' => 'any'],
                'https://images.unsplash.com/photo-1561181286-d3fee7f4415e?auto=format&fit=crop&w=1600&q=80',
                ['collection:luxury-flowers', 'occasion:congratulations', 'flower:roses', 'collection:premium-bouquets'],
                collection_default_faqs('orchid gifts')
            ),
            'carnations' => collection_entry(
                'Carnations',
                'Carnation Bouquets Online — Fresh & Colourful',
                'Long-lasting carnation bunches for Mother\'s Day, thank-you notes and everyday affection.',
                ['tables' => ['flowers'], 'name_keywords' => ['carnation', 'carnations'], 'tags' => ['carnation', 'carnations'], 'match' => 'any'],
                $hero,
                ['relation:mother', 'occasion:thank-you', 'flower:roses', 'collection:budget-flowers'],
                collection_default_faqs('carnation bouquets')
            ),
            'gerberas' => collection_entry(
                'Gerberas',
                'Vibrant Gerbera Flower Bouquets',
                'Bold, joyful gerbera arrangements for birthdays, congratulations and brightening any room.',
                ['tables' => ['flowers'], 'name_keywords' => ['gerbera', 'gerberas'], 'tags' => ['gerbera', 'gerberas'], 'match' => 'any'],
                'https://images.unsplash.com/photo-1468327768560-75b778cbb551?auto=format&fit=crop&w=1600&q=80',
                ['occasion:birthday', 'occasion:congratulations', 'flower:sunflowers', 'collection:trending-flowers'],
                collection_default_faqs('gerbera bouquets')
            ),
            'tulips' => collection_entry(
                'Tulips',
                'Premium Tulip Bouquets — Limited Season Picks',
                'Soft pink and colourful tulip hand-bouquets for romance, birthdays and stylish gifting.',
                ['tables' => ['flowers'], 'name_keywords' => ['tulip', 'tulips'], 'tags' => ['tulip', 'tulips'], 'match' => 'any'],
                'https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=1600&q=80',
                ['occasion:love-romance', 'relation:girlfriend', 'collection:premium-bouquets', 'flower:roses'],
                collection_default_faqs('tulip bouquets')
            ),
            'mixed-flowers' => collection_entry(
                'Mixed Flowers',
                'Mixed Flower Bouquets for Every Occasion',
                'Hand-tied mixed blooms — colourful, versatile and perfect when you want a little of everything.',
                ['tables' => ['flowers'], 'name_keywords' => ['mixed', 'assorted', 'mix'], 'tags' => ['mixed', 'mixed flowers', 'assorted'], 'match' => 'any'],
                $hero,
                ['occasion:birthday', 'collection:best-sellers', 'flower:roses', 'collection:budget-flowers'],
                collection_default_faqs('mixed flower bouquets')
            ),
            'premium-flowers' => collection_entry(
                'Premium Flowers',
                'Premium Flower Collection — Designer Blooms',
                'Elevated arrangements with rare stems, richer styling and statement presentation.',
                ['tables' => ['flowers'], 'name_keywords' => ['premium', 'designer', 'luxury', 'luxe'], 'tags' => ['premium', 'luxury', 'luxe'], 'price_min' => 1999, 'match' => 'any', 'sort' => 'price_high'],
                $hero,
                ['collection:luxury-flowers', 'collection:designer-bouquets', 'flower:orchids', 'occasion:anniversary'],
                collection_default_faqs('premium flowers'),
                'Premium'
            ),
            'luxury-collection' => collection_entry(
                'Luxury Collection',
                'Luxury Flowers & Statement Bouquets',
                'Our most exclusive floral creations for milestones that deserve the extraordinary.',
                ['tables' => ['flowers'], 'name_keywords' => ['luxury', 'luxe', 'premium', 'exclusive'], 'tags' => ['luxury', 'luxe', 'premium'], 'price_min' => 2499, 'match' => 'any', 'sort' => 'price_high'],
                $hero,
                ['collection:luxury-flowers', 'collection:premium-bouquets', 'flower:orchids', 'occasion:wedding'],
                collection_default_faqs('luxury flowers'),
                'Luxe'
            ),
        ];
    }
}

if (!function_exists('collection_taxonomy_relations')) {
    function collection_taxonomy_relations(): array
    {
        $defs = [
            'mother' => ['Mother', ['mother', 'mom', 'mummy', 'mama'], ['mother', 'mom', 'mothers day']],
            'father' => ['Father', ['father', 'dad', 'papa'], ['father', 'dad', 'fathers day']],
            'wife' => ['Wife', ['wife'], ['wife', 'anniversary', 'love']],
            'husband' => ['Husband', ['husband'], ['husband', 'anniversary']],
            'girlfriend' => ['Girlfriend', ['girlfriend', 'gf', 'romantic'], ['girlfriend', 'love', 'romance']],
            'boyfriend' => ['Boyfriend', ['boyfriend', 'bf'], ['boyfriend', 'love']],
            'sister' => ['Sister', ['sister', 'sis'], ['sister', 'rakhi']],
            'brother' => ['Brother', ['brother', 'bhai'], ['brother', 'bhai', 'rakhi']],
            'daughter' => ['Daughter', ['daughter'], ['daughter']],
            'son' => ['Son', ['son'], ['son']],
            'grandmother' => ['Grandmother', ['grandmother', 'grandma', 'nani', 'dadi'], ['grandmother', 'grandma']],
            'grandfather' => ['Grandfather', ['grandfather', 'grandpa', 'nana', 'dada'], ['grandfather', 'grandpa']],
            'parents' => ['Parents', ['parents', 'mom & dad', 'mother father'], ['parents', 'mother', 'father']],
            'friends' => ['Friends', ['friend', 'friends', 'buddy'], ['friend', 'friendship']],
            'colleagues' => ['Colleagues', ['colleague', 'office', 'coworker', 'corporate'], ['colleague', 'office', 'corporate']],
            'him' => ['Him', ['him', 'men', 'gentleman'], ['him', 'men', 'boyfriend', 'husband']],
            'her' => ['Her', ['her', 'women', 'lady'], ['her', 'women', 'girlfriend', 'wife']],
            'kids' => ['Kids', ['kid', 'kids', 'child', 'children'], ['kids', 'children', 'child']],
        ];

        $out = [];
        foreach ($defs as $slug => [$label, $names, $tags]) {
            $out[$slug] = collection_entry(
                $label,
                "Gifts for {$label} — Flowers, Cakes & Surprises",
                "Thoughtful flowers and gifts curated for {$label}. Same-day delivery across Delhi NCR.",
                [
                    'tables' => ['flowers', 'cakes', 'gifts'],
                    'name_keywords' => $names,
                    'tags' => $tags,
                    'match' => 'any',
                ],
                'https://images.unsplash.com/photo-1487530811176-3780de880c2d?auto=format&fit=crop&w=1600&q=80',
                ['occasion:birthday', 'occasion:anniversary', 'collection:best-sellers', 'flower:roses'],
                collection_default_faqs("gifts for {$label}"),
                "For {$label}"
            );
        }
        return $out;
    }
}

if (!function_exists('collection_taxonomy_occasions')) {
    function collection_taxonomy_occasions(): array
    {
        $defs = [
            'birthday' => [
                'Birthday',
                'Birthday Flowers & Gifts Online — Same-Day Delivery',
                'Celebrate another year with cakes, blooms and surprise combos.',
                ['name_keywords' => ['birthday'], 'tags' => ['birthday'], 'category_names' => ['Birthday'], 'fallback_category_id' => 3],
                ['relation:her', 'flower:roses', 'collection:best-sellers', 'occasion:congratulations'],
            ],
            'anniversary' => [
                'Anniversary',
                'Anniversary Flowers & Romantic Gifts',
                'Mark your love story with roses, premium bouquets and candlelit combos.',
                ['name_keywords' => ['anniversary'], 'tags' => ['anniversary', 'love'], 'category_names' => ['Anniversary'], 'fallback_category_id' => 4],
                ['relation:wife', 'relation:husband', 'flower:roses', 'occasion:love-romance'],
            ],
            'wedding' => [
                'Wedding',
                'Wedding Flowers & Congratulatory Bouquets',
                'Elegant stems and décor-ready bouquets for newlyweds and wedding hosts.',
                ['name_keywords' => ['wedding', 'bridal'], 'tags' => ['wedding', 'bridal']],
                ['flower:orchids', 'collection:luxury-flowers', 'occasion:congratulations', 'flower:lilies'],
            ],
            'congratulations' => [
                'Congratulations',
                'Congratulations Flowers & Celebration Gifts',
                'Cheer on promotions, launches and big wins with bright, festive arrangements.',
                ['name_keywords' => ['congratulat', 'success'], 'tags' => ['congratulations', 'congrats']],
                ['flower:gerberas', 'flower:sunflowers', 'relation:colleagues', 'collection:trending-flowers'],
            ],
            'love-romance' => [
                'Love & Romance',
                'Romantic Flowers & Love Gifts Online',
                'Say it with red roses, tulips and intimate premium bouquets.',
                ['name_keywords' => ['love', 'romance', 'romantic', 'valentine'], 'tags' => ['love', 'romance', 'valentine']],
                ['flower:roses', 'flower:tulips', 'relation:girlfriend', 'occasion:anniversary'],
            ],
            'get-well-soon' => [
                'Get Well Soon',
                'Get Well Soon Flowers & Cheerful Bouquets',
                'Soft, uplifting blooms to send comfort and recovery wishes.',
                ['name_keywords' => ['get well', 'well soon', 'recovery'], 'tags' => ['get well', 'getwell', 'recovery']],
                ['flower:lilies', 'flower:sunflowers', 'relation:friends', 'collection:budget-flowers'],
            ],
            'sympathy' => [
                'Sympathy',
                'Sympathy & Condolence Flowers',
                'Graceful white lilies and respectful arrangements for difficult moments.',
                ['name_keywords' => ['sympathy', 'condolence', 'funeral'], 'tags' => ['sympathy', 'condolence']],
                ['flower:lilies', 'flower:orchids', 'collection:premium-bouquets', 'occasion:thank-you'],
            ],
            'thank-you' => [
                'Thank You',
                'Thank You Flowers & Gratitude Gifts',
                'Simple, heartfelt bouquets to say thank you beautifully.',
                ['name_keywords' => ['thank you', 'thanks', 'gratitude'], 'tags' => ['thank', 'thank you', 'thanks']],
                ['flower:carnations', 'flower:mixed-flowers', 'relation:colleagues', 'collection:budget-flowers'],
            ],
            'new-baby' => [
                'New Baby',
                'New Baby Flowers & Welcome Gifts',
                'Soft pastel blooms and thoughtful gifts to welcome a newborn.',
                ['name_keywords' => ['baby', 'newborn', 'welcome baby'], 'tags' => ['baby', 'newborn', 'new baby']],
                ['relation:daughter', 'relation:son', 'flower:carnations', 'collection:flower-combos'],
            ],
            'housewarming' => [
                'Housewarming',
                'Housewarming Flowers & Plant Gifts',
                'Stylish arrangements and plants to bless a new home.',
                ['name_keywords' => ['housewarming', 'new home', 'plant'], 'tags' => ['housewarming', 'plants', 'plant']],
                ['flower:orchids', 'collection:plants', 'relation:friends', 'occasion:congratulations'],
            ],
            'graduation' => [
                'Graduation',
                'Graduation Flowers & Congrats Gifts',
                'Celebrate academic milestones with cheerful bouquets and combos.',
                ['name_keywords' => ['graduation', 'graduate', 'convocation'], 'tags' => ['graduation', 'graduate']],
                ['occasion:congratulations', 'flower:sunflowers', 'relation:friends', 'collection:trending-flowers'],
            ],
            'festivals' => [
                'Festivals',
                'Festival Flowers & Gift Hampers',
                'Diwali, Christmas, Holi and festive gifting curated for the season.',
                ['name_keywords' => ['diwali', 'christmas', 'festival', 'holi', 'rakhi'], 'tags' => ['diwali', 'christmas', 'festival', 'rakhi']],
                ['collection:flower-combos', 'relation:brother', 'relation:sister', 'collection:best-sellers'],
            ],
            'mothers-day' => [
                "Mother's Day",
                "Mother's Day Flowers & Gifts Online",
                'Celebrate Mum with carnations, roses and premium gift combos.',
                ['name_keywords' => ['mother', 'mom', "mother's day", 'mothers day'], 'tags' => ['mother', 'mothers-day', 'mom']],
                ['relation:mother', 'flower:carnations', 'flower:roses', 'collection:best-sellers'],
            ],
            'fathers-day' => [
                "Father's Day",
                "Father's Day Flowers & Gifts Online",
                'Surprise Dad with fresh blooms, cakes and thoughtful hampers.',
                ['name_keywords' => ['father', 'dad', "father's day", 'fathers day'], 'tags' => ['father', 'fathers-day', 'dad']],
                ['relation:father', 'collection:flower-combos', 'flower:mixed-flowers', 'collection:best-sellers'],
            ],
            'valentines-day' => [
                "Valentine's Day",
                "Valentine's Day Roses & Romantic Gifts",
                'Red roses, premium LUXE bouquets and midnight-ready romance.',
                ['name_keywords' => ['valentine', 'valentines'], 'tags' => ['valentine', 'valentines', 'love']],
                ['flower:roses', 'occasion:love-romance', 'relation:girlfriend', 'collection:luxury-flowers'],
            ],
        ];

        $out = [];
        foreach ($defs as $slug => [$label, $h1, $short, $filter, $related]) {
            $filter['tables'] = $filter['tables'] ?? ['flowers', 'cakes', 'gifts'];
            $filter['match'] = $filter['match'] ?? 'any';
            $out[$slug] = collection_entry(
                $label,
                $h1,
                $short,
                $filter,
                'https://images.unsplash.com/photo-1525310072745-f49212b5ac6d?auto=format&fit=crop&w=1600&q=80',
                $related,
                collection_default_faqs($label),
                $label
            );
        }
        return $out;
    }
}

if (!function_exists('collection_taxonomy_collections')) {
    function collection_taxonomy_collections(): array
    {
        return [
            'best-sellers' => collection_entry(
                'Best Sellers',
                'Best Selling Flowers — Customer Favourites',
                'Our most-loved bouquets, rated highly and ordered again and again.',
                ['tables' => ['flowers'], 'sort' => 'rating', 'match' => 'all'],
                'https://images.unsplash.com/photo-1487530811176-3780de880c2d?auto=format&fit=crop&w=1600&q=80',
                ['collection:trending-flowers', 'collection:premium-bouquets', 'flower:roses', 'occasion:birthday'],
                collection_default_faqs('best sellers'),
                'Bestsellers'
            ),
            'premium-bouquets' => collection_entry(
                'Premium Bouquets',
                'Premium Bouquets — Designer Flower Arrangements',
                'Elevated styling, richer stems and gift-ready presentation.',
                ['tables' => ['flowers'], 'price_min' => 1999, 'tags' => ['premium', 'designer'], 'name_keywords' => ['premium', 'designer'], 'match' => 'any', 'sort' => 'price_high'],
                '',
                ['collection:luxury-flowers', 'collection:designer-bouquets', 'flower:premium-flowers', 'occasion:anniversary'],
                collection_default_faqs('premium bouquets')
            ),
            'luxury-flowers' => collection_entry(
                'Luxury Flowers',
                'Luxury Flower Delivery — LUXE Collection',
                'Statement arrangements for VIP moments and unforgettable surprises.',
                ['tables' => ['flowers'], 'price_min' => 2499, 'tags' => ['luxury', 'luxe'], 'name_keywords' => ['luxury', 'luxe'], 'match' => 'any', 'sort' => 'price_high'],
                '',
                ['collection:premium-bouquets', 'flower:luxury-collection', 'flower:orchids', 'occasion:wedding'],
                collection_default_faqs('luxury flowers'),
                'LUXE'
            ),
            'same-day-delivery' => collection_entry(
                'Same Day Delivery',
                'Same Day Flower Delivery in Delhi NCR',
                'Order by 6 PM — fresh flowers, cakes and gifts delivered today.',
                ['tables' => ['flowers', 'cakes', 'gifts'], 'tags' => ['same day', 'sameday', 'express', 'on-demand'], 'name_keywords' => ['same day', 'express'], 'match' => 'any'],
                '',
                ['collection:midnight-delivery', 'collection:best-sellers', 'occasion:birthday', 'flower:roses'],
                collection_default_faqs('same-day delivery'),
                'Same Day'
            ),
            'midnight-delivery' => collection_entry(
                'Midnight Delivery',
                'Midnight Flower Delivery — Surprise After Hours',
                'Selected pin codes for midnight surprises on birthdays, anniversaries and Valentine\'s.',
                ['tables' => ['flowers', 'cakes', 'gifts'], 'tags' => ['midnight', 'night'], 'name_keywords' => ['midnight'], 'match' => 'any'],
                '',
                ['collection:same-day-delivery', 'occasion:birthday', 'occasion:anniversary', 'occasion:love-romance'],
                collection_default_faqs('midnight delivery')
            ),
            'flower-combos' => collection_entry(
                'Flower Combos',
                'Flower Combos — Blooms with Cake & Gifts',
                'Ready-to-wow combos pairing flowers with cakes, chocolates and hampers.',
                ['tables' => ['gifts', 'flowers'], 'tags' => ['combo', 'hamper'], 'name_keywords' => ['combo', 'hamper', 'with cake'], 'match' => 'any'],
                '',
                ['collection:best-sellers', 'occasion:birthday', 'occasion:anniversary', 'collection:budget-flowers'],
                collection_default_faqs('flower combos')
            ),
            'new-arrivals' => collection_entry(
                'New Arrivals',
                'Newly Added Flowers — Fresh From Our Studio',
                'Just launched arrangements and seasonal specials.',
                ['tables' => ['flowers'], 'sort' => 'newest', 'match' => 'all'],
                '',
                ['collection:trending-flowers', 'collection:best-sellers', 'flower:tulips', 'flower:mixed-flowers'],
                collection_default_faqs('new arrivals')
            ),
            'trending-flowers' => collection_entry(
                'Trending Flowers',
                'Trending Flower Bouquets Right Now',
                'What Delhi NCR is ordering this week — bright, giftable and on-trend.',
                ['tables' => ['flowers'], 'sort' => 'rating', 'match' => 'all'],
                '',
                ['collection:best-sellers', 'collection:new-arrivals', 'flower:gerberas', 'flower:sunflowers'],
                collection_default_faqs('trending flowers')
            ),
            'budget-flowers' => collection_entry(
                'Budget Flowers',
                'Budget Flower Bouquets Under ₹999',
                'Beautiful, affordable blooms for everyday affection without the stretch.',
                ['tables' => ['flowers'], 'price_max' => 999, 'sort' => 'price_low', 'match' => 'all'],
                '',
                ['collection:best-sellers', 'flower:carnations', 'flower:mixed-flowers', 'occasion:thank-you'],
                collection_default_faqs('budget flowers')
            ),
            'designer-bouquets' => collection_entry(
                'Designer Bouquets',
                'Designer Flower Bouquets — Artisan Arrangements',
                'Studio-styled designer pieces with distinctive colour stories and silhouettes.',
                ['tables' => ['flowers'], 'name_keywords' => ['designer', 'signature', 'studio'], 'tags' => ['designer', 'signature'], 'price_min' => 1799, 'match' => 'any', 'sort' => 'price_high'],
                '',
                ['collection:premium-bouquets', 'collection:luxury-flowers', 'flower:orchids', 'flower:tulips'],
                collection_default_faqs('designer bouquets')
            ),
            'plants' => collection_entry(
                'Plants',
                'Indoor Plants & Green Gifts Online',
                'Long-lasting plant gifts for desks, homes and housewarmings.',
                ['tables' => ['flowers', 'gifts'], 'name_keywords' => ['plant', 'succulent', 'money plant', 'jade'], 'tags' => ['plants', 'plant'], 'match' => 'any'],
                '',
                ['occasion:housewarming', 'relation:colleagues', 'collection:budget-flowers', 'flower:orchids'],
                collection_default_faqs('plants')
            ),
            'hampers' => collection_entry(
                'Gift Hampers',
                'Curated Gift Hampers with Flowers & Treats',
                'Beautifully boxed hampers for corporate and personal celebrations.',
                ['tables' => ['gifts'], 'name_keywords' => ['hamper', 'box', 'basket'], 'tags' => ['hamper', 'gift box'], 'match' => 'any'],
                '',
                ['collection:flower-combos', 'occasion:festivals', 'relation:colleagues', 'collection:luxury-flowers'],
                collection_default_faqs('gift hampers')
            ),
        ];
    }
}

if (!function_exists('collection_label_to_flower_slug')) {
    /** Map homepage/CMS flower labels to taxonomy slugs. */
    function collection_label_to_flower_slug(string $label): ?string
    {
        $key = strtolower(trim($label));
        $map = [
            'roses' => 'roses',
            'rose' => 'roses',
            'red roses' => 'roses',
            'lilies' => 'lilies',
            'lily' => 'lilies',
            'sunflowers' => 'sunflowers',
            'sunflower' => 'sunflowers',
            'orchids' => 'orchids',
            'orchid' => 'orchids',
            'carnations' => 'carnations',
            'carnation' => 'carnations',
            'gerberas' => 'gerberas',
            'gerbera' => 'gerberas',
            'tulips' => 'tulips',
            'tulip' => 'tulips',
            'mixed flowers' => 'mixed-flowers',
            'premium flowers' => 'premium-flowers',
            'luxury collection' => 'luxury-collection',
            'luxe' => 'luxury-collection',
        ];
        return $map[$key] ?? null;
    }
}
