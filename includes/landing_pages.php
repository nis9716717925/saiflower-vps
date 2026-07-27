<?php
/**
 * Built-in occasion landing pages (used when no matching dynamic_pages row exists).
 * Footer sitemap and page.php both read from this registry.
 */

if (!function_exists('get_builtin_landing_pages')) {
    function get_builtin_landing_pages(): array
    {
        return [
            [
                'slug'  => 'fathers-day',
                'title' => "Father's Day",
            ],
        ];
    }
}

if (!function_exists('get_builtin_landing_page_by_slug')) {
    function get_builtin_landing_page_by_slug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === 'fathers-day') {
            return fathers_day_landing_page_data();
        }
        return null;
    }
}

if (!function_exists('fathers_day_landing_page_data')) {
    function fathers_day_landing_page_data(): array
    {
        $faqs = [
            [
                'question' => "When is Father's Day 2026 in India?",
                'answer'   => "Father's Day is celebrated on the third Sunday of June. In 2026, Father's Day falls on Sunday, 21 June. Sai Flowers offers same-day and scheduled delivery across Delhi NCR so you can surprise Dad on the day.",
            ],
            [
                'question' => 'Can I order same-day Father\'s Day flower delivery in Delhi?',
                'answer'   => 'Yes. Place your order before our daily cut-off for same-day Father\'s Day flower delivery in Delhi, Gurgaon, Noida, and nearby NCR areas. You can also choose a delivery date at checkout or message us on WhatsApp for express help.',
            ],
            [
                'question' => 'What are the best Father\'s Day gifts besides flowers?',
                'answer'   => 'Popular picks include chocolate cakes, premium gift hampers, dry-fruit combos, and flower-and-cake duos. Explore our cakes and gifts collections or ask our team to recommend a combo within your budget.',
            ],
            [
                'question' => 'Do you offer midnight delivery for Father\'s Day?',
                'answer'   => 'Midnight delivery is available in selected pin codes for special occasions. Add your preferred time in order notes or contact us on WhatsApp before 21 June to confirm midnight Father\'s Day delivery availability.',
            ],
            [
                'question' => 'How do I send Father\'s Day gifts to another city?',
                'answer'   => 'Our online checkout covers Delhi NCR delivery. For other cities, WhatsApp our team with the delivery address — we will confirm availability and suggest the best Father\'s Day bouquet or hamper option.',
            ],
        ];

        $content = <<<'HTML'
<h2>Father's Day Flowers &amp; Gifts Online in Delhi NCR</h2>
<p>Celebrate the man who has always been your strength, guide, and biggest cheerleader. This <strong>Father's Day, 21 June 2026</strong>, tell Dad how much he means to you with handpicked <a href="/flowers" title="Order flowers online Delhi">fresh flowers</a>, decadent <a href="/cakes" title="Order cakes online for Father's Day">designer cakes</a>, and thoughtful <a href="/gifts" title="Father's Day gift hampers Delhi">gift hampers</a> — delivered across Delhi NCR by <a href="/about" title="About Sai Flowers florist Delhi">Sai Flowers</a>, your trusted florist since 1998.</p>

<p>From classic rose bouquets and elegant orchids to surprise midnight cake deliveries, every arrangement is styled by expert florists using premium, daily-fresh blooms.</p>

<h2>Why Order Father's Day Gifts from Sai Flowers?</h2>
<ul>
<li><strong>Fresh, premium flowers</strong> — hand-arranged bouquets for home, office, or surprise doorstep delivery</li>
<li><strong>Same-day &amp; express delivery</strong> — Delhi, Gurgaon, Noida &amp; NCR (see our <a href="/delivery-policy" title="Flower delivery policy Delhi NCR">delivery policy</a>)</li>
<li><strong>One-stop gifting</strong> — combine flowers, cakes, and hampers in a single order</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets with reliable customer support</li>
</ul>

<h3>Top Father's Day Gift Ideas for Dad</h3>
<ul>
<li><strong>Mixed-flower or rose bouquet</strong> — timeless, elegant, and always appreciated</li>
<li><strong>Chocolate truffle or butterscotch cake</strong> — ideal for celebrations at home</li>
<li><strong>Premium gift hamper</strong> — flowers plus treats in one polished package</li>
<li><strong>Desk-friendly arrangement</strong> — compact blooms for his workplace</li>
</ul>

<h3>Order Early for 21 June Delivery</h3>
<p>Father's Day is one of our busiest weeks. Order ahead to secure your preferred date and time slot. Browse curated picks above, or shop the full <a href="/flowers">flower collection</a>, <a href="/cakes">cake menu</a>, and <a href="/gifts">gift range</a>.</p>

<p><strong>Need personalised advice?</strong> <a href="/contact" title="Contact Sai Flowers">Contact us</a> or <a href="https://wa.me/918802004527" target="_blank" rel="noopener noreferrer" title="WhatsApp Sai Flowers for Father's Day orders">WhatsApp +91 88020 04527</a> — we will help you choose the perfect Father's Day surprise within your budget.</p>
HTML;

        return [
            'id'                       => 0,
            'title'                    => "Father's Day Gifts & Flowers",
            'h1'                       => "Father's Day Flowers & Gifts Online — Delhi NCR Delivery",
            'occasion_label'           => "Father's Day",
            'short_description'        => 'Surprise Dad on 21 June 2026 with fresh flowers, cakes & premium gift hampers. Same-day Father\'s Day delivery across Delhi NCR.',
            'slug'                     => 'fathers-day',
            'content'                  => $content,
            'meta_title'               => "Father's Day Gifts & Flowers Delivery Delhi 2026 | Sai Flowers",
            'meta_description'         => "Order Father's Day flowers, cakes & gift hampers online with same-day delivery in Delhi NCR. Shop premium bouquets for Dad — 21 June 2026 | Sai Flowers.",
            'meta_keywords'            => "father's day gifts, father's day flowers, father's day flower delivery delhi, father's day cake delivery, father's day gift hampers, gifts for dad, father's day 2026, same day flower delivery delhi, father's day gifts online india, sai flowers",
            'seo_canonical'            => 'https://saiflower.com/fathers-day',
            'robots'                   => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'seo_locale'               => 'en_IN',
            'og_title'                 => "Father's Day Flowers & Gifts — Same-Day Delivery Delhi | Sai Flowers",
            'og_description'           => "Celebrate Dad on 21 June 2026. Premium Father's Day bouquets, cakes & hampers with delivery across Delhi NCR.",
            'og_type'                  => 'website',
            'og_image'                 => '/uploads/sections/img_69c3c8b961d2d_Screenshot20260325170355Google.webp',
            'og_image_alt'             => "Father's Day flower bouquet and gift delivery in Delhi by Sai Flowers",
            'enable_product_sliders'   => true,
            'slider_mode'              => 'bouquets_only',
            'slider_items_per_row'     => 8,
            'hide_product_grid'        => true,
            'products_section_heading' => "Shop Father's Day Flower Bouquets",
            'faq_section_heading'      => "Father's Day Gift Delivery — Frequently Asked Questions",
            'status'                   => 1,
            'layout_type'              => 'product_showcase',
            'page_tag'                 => 'fathers-day',
            'hero_image'               => null,
            'extra_images'             => null,
            'faqs'                     => json_encode($faqs),
            'midgrid_image'            => null,
            'midgrid_image_alt'        => null,
            'schema_event'             => [
                'name'         => "Father's Day 2026",
                'description'  => "Celebrate Father's Day on 21 June 2026 with flower and gift delivery across Delhi NCR from Sai Flowers.",
                'startDate'    => '2026-06-21',
                'endDate'      => '2026-06-21',
                'locationName' => 'Delhi NCR, India',
                'image'        => 'https://saiflower.com/uploads/sections/img_69c3c8b961d2d_Screenshot20260325170355Google.webp',
            ],
        ];
    }
}

if (!function_exists('get_sitemap_landing_pages')) {
    /**
     * Merges active dynamic_pages with built-in landing pages (DB wins on slug conflict).
     *
     * @return array<int, array{title: string, slug: string}>
     */
    function get_sitemap_landing_pages($conn): array
    {
        $by_slug = [];

        if ($conn instanceof mysqli) {
            $res = @mysqli_query($conn, "SELECT title, slug FROM dynamic_pages WHERE status = 1 ORDER BY title ASC");
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    if (!empty($row['slug'])) {
                        $by_slug[$row['slug']] = [
                            'title' => $row['title'],
                            'slug'  => $row['slug'],
                        ];
                    }
                }
            }
        }

        foreach (get_builtin_landing_pages() as $page) {
            if (!empty($page['slug']) && !isset($by_slug[$page['slug']])) {
                $by_slug[$page['slug']] = [
                    'title' => $page['title'],
                    'slug'  => $page['slug'],
                ];
            }
        }

        if (file_exists(__DIR__ . '/collection_taxonomy.php')) {
            require_once __DIR__ . '/collection_taxonomy.php';
            foreach (collection_list() as $page) {
                $path = ltrim((string) ($page['canonical_path'] ?? ''), '/');
                if ($path === '' || isset($by_slug[$path])) {
                    continue;
                }
                $by_slug[$path] = [
                    'title' => $page['title'] ?? $path,
                    'slug'  => $path,
                ];
            }
        }

        $pages = array_values($by_slug);
        usort($pages, static function ($a, $b) {
            return strcasecmp($a['title'], $b['title']);
        });

        return $pages;
    }
}
