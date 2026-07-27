<?php
/**
 * Seed: Location-based flower delivery custom pages (dynamic_pages)
 * Run once on server: https://saiflower.com/tools/seed_location_flower_pages.php
 * Or: php tools/seed_location_flower_pages.php
 */
require_once __DIR__ . '/../config.php';

$locations = [
    [
        'keyword' => 'Flower Delivery in GK 1',
        'slug' => 'flower-delivery-in-gk-1',
        'area' => 'GK 1',
        'local' => 'Greater Kailash Part 1',
        'context' => 'From M Block Market to the leafy lanes near Pamposh Enclave, GK 1 is where polished gifting matters. Residents here expect bouquets that look as refined as the neighbourhood itself.',
        'nearby' => 'GK 2, Kailash Colony, and Nehru Enclave',
    ],
    [
        'keyword' => 'Flower Delivery in GK 2',
        'slug' => 'flower-delivery-in-gk-2',
        'area' => 'GK 2',
        'local' => 'Greater Kailash Part 2',
        'context' => 'GK 2 blends busy market streets with quiet residential pockets. Whether you are surprising someone near M Block or sending blooms to a home off Masjid Moth, timing and presentation count.',
        'nearby' => 'GK 1, Chittaranjan Park, and Alaknanda',
    ],
    [
        'keyword' => 'Flower Delivery in Hauz Khas',
        'slug' => 'flower-delivery-in-hauz-khas',
        'area' => 'Hauz Khas',
        'local' => 'Hauz Khas',
        'context' => 'Hauz Khas moves fast — café meetups, gallery evenings, and last-minute celebrations around the village and Deer Park. A fresh bouquet here should feel stylish, not ordinary.',
        'nearby' => 'Green Park, Safdarjung Enclave, and IIT Delhi',
    ],
    [
        'keyword' => 'Flower Delivery in Green Park',
        'slug' => 'flower-delivery-in-green-park',
        'area' => 'Green Park',
        'local' => 'Green Park',
        'context' => 'Green Park is one of South Delhi\'s most loved residential addresses. Tree-lined streets and close-knit homes make flower surprises feel especially personal.',
        'nearby' => 'Hauz Khas, Gulmohar Park, and Yusuf Sarai',
    ],
    [
        'keyword' => 'Flower Delivery in Saket',
        'slug' => 'flower-delivery-in-saket',
        'area' => 'Saket',
        'local' => 'Saket',
        'context' => 'From Select Citywalk celebrations to apartment surprises in Saket District Centre, this area sees constant gifting moments — birthdays, anniversaries, and festive home visits.',
        'nearby' => 'Malviya Nagar, Hauz Khas, and Pushp Vihar',
    ],
    [
        'keyword' => 'Flower Delivery in Malviya Nagar',
        'slug' => 'flower-delivery-in-malviya-nagar',
        'area' => 'Malviya Nagar',
        'local' => 'Malviya Nagar',
        'context' => 'Malviya Nagar mixes lively markets with family homes and student hubs. A dependable florist who understands local pin codes saves you stress on busy celebration days.',
        'nearby' => 'Saket, Hauz Khas, and Begumpur',
    ],
    [
        'keyword' => 'Flower Delivery in Vasant Kunj',
        'slug' => 'flower-delivery-in-vasant-kunj',
        'area' => 'Vasant Kunj',
        'local' => 'Vasant Kunj',
        'context' => 'Vasant Kunj\'s wide sectors and gated communities need clear delivery coordination. We route orders carefully across sectors so your bouquet reaches the right tower or villa.',
        'nearby' => 'Munirka, Mahipalpur, and Rangpuri',
    ],
    [
        'keyword' => 'Flower Delivery in Mehrauli',
        'slug' => 'flower-delivery-in-mehrauli',
        'area' => 'Mehrauli',
        'local' => 'Mehrauli',
        'context' => 'Mehrauli carries old-Delhi charm near heritage lanes and modern housing clusters. Flowers sent here often mark family gatherings, housewarmings, and festival greetings.',
        'nearby' => 'Chattarpur, Vasant Kunj, and Qutub Minar area',
    ],
    [
        'keyword' => 'Flower Delivery in Chattarpur',
        'slug' => 'flower-delivery-in-chattarpur',
        'area' => 'Chattarpur',
        'local' => 'Chattarpur',
        'context' => 'Chattarpur stretches across farmhouses, temples, and growing residential pockets. Accurate address details help our riders reach farmhouse gates and colony homes on time.',
        'nearby' => 'Mehrauli, Vasant Kunj, and Chhatarpur Extension',
    ],
    [
        'keyword' => 'Flower Delivery in CR Park',
        'slug' => 'flower-delivery-in-cr-park',
        'area' => 'CR Park',
        'local' => 'Chittaranjan Park',
        'context' => 'CR Park is famous for community warmth and festival cheer. During Durga Puja season and family milestones, fresh flowers are part of the celebration itself.',
        'nearby' => 'Kalkaji, Nehru Place, and GK 2',
    ],
    [
        'keyword' => 'Flower Delivery in Kalkaji',
        'slug' => 'flower-delivery-in-kalkaji',
        'area' => 'Kalkaji',
        'local' => 'Kalkaji',
        'context' => 'Near Kalkaji Mandir and the bustling main market, this neighbourhood sees steady demand for temple visits, anniversaries, and same-day home surprises.',
        'nearby' => 'Nehru Place, CR Park, and Okhla',
    ],
    [
        'keyword' => 'Flower Delivery in Nehru Place',
        'slug' => 'flower-delivery-in-nehru-place',
        'area' => 'Nehru Place',
        'local' => 'Nehru Place',
        'context' => 'Nehru Place is a commercial heartbeat — office birthdays, client thank-yous, and colleague celebrations happen here every week. Desk-friendly bouquets work especially well.',
        'nearby' => 'Kalkaji, CR Park, and East of Kailash',
    ],
    [
        'keyword' => 'Flower Delivery in Jor Bagh',
        'slug' => 'flower-delivery-in-jor-bagh',
        'area' => 'Jor Bagh',
        'local' => 'Jor Bagh',
        'context' => 'Jor Bagh\'s elegant homes and quiet avenues call for tasteful, premium arrangements. Understated roses and refined wrapping suit this polished South Delhi address.',
        'nearby' => 'Lodhi Road, Safdarjung, and INA Colony',
    ],
    [
        'keyword' => 'Flower Delivery in Lodhi Road',
        'slug' => 'flower-delivery-in-lodhi-road',
        'area' => 'Lodhi Road',
        'local' => 'Lodhi Road',
        'context' => 'Lodhi Road links institutions, embassies, and residential blocks. Deliveries here often go to offices, hotels, and homes near the Lodhi Garden stretch.',
        'nearby' => 'Jor Bagh, Safdarjung, and Khan Market',
    ],
    [
        'keyword' => 'Flower Delivery in Safdarjung',
        'slug' => 'flower-delivery-in-safdarjung',
        'area' => 'Safdarjung',
        'local' => 'Safdarjung',
        'context' => 'Safdarjung covers hospitals, government colonies, and busy ring-road addresses. Compassionate flower delivery for get-well wishes and family support is common here.',
        'nearby' => 'AIIMS, Green Park, and Hauz Khas',
    ],
    [
        'keyword' => 'Flower Delivery in AIIMS',
        'slug' => 'flower-delivery-in-aiims',
        'area' => 'AIIMS',
        'local' => 'AIIMS',
        'context' => 'Near AIIMS Delhi, flowers often carry heartfelt messages — recovery wishes, encouragement, and quiet support for families waiting at the hospital.',
        'nearby' => 'Safdarjung, Green Park, and Ansari Nagar',
    ],
    [
        'keyword' => 'Flower Delivery in Panchsheel',
        'slug' => 'flower-delivery-in-panchsheel',
        'area' => 'Panchsheel',
        'local' => 'Panchsheel Park',
        'context' => 'Panchsheel Park\'s calm streets and well-kept homes deserve bouquets that feel premium from the first glance. Anniversary and birthday orders are especially popular.',
        'nearby' => 'Chirag Delhi, Sheikh Sarai, and Hauz Khas',
    ],
    [
        'keyword' => 'Flower Delivery in Gulmohar Park',
        'slug' => 'flower-delivery-in-gulmohar-park',
        'area' => 'Gulmohar Park',
        'local' => 'Gulmohar Park',
        'context' => 'Gulmohar Park is a leafy, understated South Delhi gem. Residents appreciate thoughtful gifting — fresh stems, clean wrapping, and reliable arrival windows.',
        'nearby' => 'Green Park, Yusuf Sarai, and Hauz Khas',
    ],
    [
        'keyword' => 'Flower Delivery in SDA',
        'slug' => 'flower-delivery-in-sda',
        'area' => 'SDA',
        'local' => 'Safdarjung Development Area',
        'context' => 'SDA\'s wide roads and cooperative housing blocks need precise tower and block details. Once confirmed, our team delivers fresh arrangements across the colony smoothly.',
        'nearby' => 'Hauz Khas, Green Park, and IIT Delhi',
    ],
];

function generate_content(array $loc): string
{
    $kw = $loc['keyword'];
    $area = $loc['area'];
    $local = $loc['local'];
    $context = $loc['context'];
    $nearby = $loc['nearby'];

    return <<<HTML
<h2>Fresh {$kw} — Fast, Reliable &amp; Beautiful</h2>
<p>Need trusted <strong>{$kw}</strong> without the last-minute panic? {$context} At <strong>Sai Flower</strong>, we build every order around fresh stems, neat wrapping, and riders who know South Delhi pin codes.</p>

<p>Whether it is a birthday morning surprise or a same-day anniversary gift, our <strong>{$kw}</strong> service is designed for people who want quality without chasing multiple florists. You pick the bouquet online, we handle the rest.</p>

<h2>Why Locals Choose Our {$kw} Service</h2>
<p>South Delhi moves quickly, and your florist should keep up. A dependable <strong>{$kw}</strong> partner means clear order updates, careful handling in summer heat, and arrangements that still look crisp at the door.</p>

<p>Residents across {$local} and nearby areas like {$nearby} trust us for roses, lilies, mixed seasonal bunches, and premium hand-tied bouquets. Every <strong>{$kw}</strong> order is prepared shortly before dispatch so petals stay firm and colours stay vivid.</p>

<h3>Same-Day Flower Delivery Across {$area}</h3>
<p>Place your order before the daily cut-off and enjoy same-day <strong>{$kw}</strong> across {$local} and surrounding blocks. Add flat, tower, or landmark details at checkout — our team confirms on WhatsApp when needed.</p>

<p>Prefer a scheduled slot? Choose your date during checkout and mention a preferred time in the order notes. We align delivery with office hours, evening home visits, and hospital-friendly timings near {$area}.</p>

<h3>Popular Bouquets for {$area} Addresses</h3>
<ul>
<li><strong>Classic red rose bouquet</strong> — perfect for anniversaries and romantic surprises</li>
<li><strong>Mixed seasonal arrangement</strong> — bright, cheerful, and celebration-ready</li>
<li><strong>Premium hand-tied bouquet</strong> — larger stems with elegant wrapping for special milestones</li>
<li><strong>Compact desk bouquet</strong> — ideal for office deliveries in {$area}</li>
</ul>

<p>Browse the showcase above to match your occasion and budget. Not sure what to pick? Message our florists with the recipient's favourite colours and we will suggest the best <strong>{$kw}</strong> option.</p>

<h2>How to Order {$kw} Online</h2>
<p>Ordering takes minutes. Select a bouquet, enter the {$area} address, and pay securely via UPI, card, or wallet. You can also explore our <a href="/flowers" title="Order flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Cake delivery South Delhi">designer cake</a>, or pair blooms with a <a href="/gifts" title="Gift hampers Delhi">gift hamper</a> for a complete surprise.</p>

<p>Each <strong>{$kw}</strong> request is checked for pin-code coverage before dispatch. Protective packaging helps bouquets travel well through traffic and weather — because a beautiful gift should arrive looking as thoughtful as you intended.</p>

<p>We also handle corporate gifting, hospital visits, and festival orders across South Delhi, with handwritten note cards available on request for a warmer, more personal finishing touch.</p>

<h3>Make Your Next Celebration in {$area} Unforgettable</h3>
<p>Flowers say what messages sometimes cannot. This season, send a fresh <strong>{$kw}</strong> that feels personal, polished, and on time. Order early for preferred slots, or rely on same-day delivery when plans change at the last minute.</p>

<p>From birthdays and anniversaries to thank-you gestures and festival greetings — Sai Flower is here to help you brighten every home, office, and doorstep across {$local}. Shop now and let us deliver the smile.</p>
HTML;
}

function generate_faqs(array $loc): array
{
    $kw = $loc['keyword'];
    $area = $loc['area'];
    $local = $loc['local'];
    $nearby = $loc['nearby'];

    return [
        [
            'question' => "Do you offer same-day {$kw}?",
            'answer' => "Yes. Place your order before the daily cut-off for same-day {$kw} across {$local} and nearby areas such as {$nearby}. Add your pin code at checkout or WhatsApp our team to confirm express availability.",
        ],
        [
            'question' => "Which areas near {$area} do you cover for flower delivery?",
            'answer' => "We deliver across {$local} and surrounding neighbourhoods including {$nearby}. Enter the full address with tower, flat, or landmark details for the fastest {$area} delivery.",
        ],
        [
            'question' => "What types of flowers can I order for {$area} delivery?",
            'answer' => "You can order roses, lilies, carnations, orchids, and mixed seasonal bouquets for {$area} delivery. Premium hand-tied arrangements and compact desk bunches are also available for offices and homes.",
        ],
        [
            'question' => "How do I order {$kw} online?",
            'answer' => "Visit this page, choose a bouquet from the product showcase, enter the {$area} delivery address, select your date, and complete secure checkout. You can add a personal message in the order notes.",
        ],
        [
            'question' => "Can I send flowers to an office in {$area}?",
            'answer' => "Absolutely. Many customers send desk-friendly bouquets to offices in {$area} and {$local}. Mention the building name, floor, and reception instructions in the delivery notes for smooth handover.",
        ],
        [
            'question' => "What is the price range for {$kw}?",
            'answer' => "Prices vary by bouquet size, flower type, and add-ons. Budget-friendly bunches and premium arrangements are both available. Browse the products above or contact Sai Flower on WhatsApp for a quick recommendation.",
        ],
        [
            'question' => "Can I combine flowers with a cake for {$area} delivery?",
            'answer' => "Yes. Pair your bouquet with a chocolate, butterscotch, or designer cake from our cakes section. Same-day combo delivery is available in many {$area} pin codes when ordered before the cut-off.",
        ],
    ];
}

function generate_meta_title(array $loc): string
{
    return 'Flower Delivery in ' . $loc['area'] . ' | Same Day | Sai Flower';
}

function generate_meta_description(array $loc): string
{
    $area = $loc['area'];
    $local = $loc['local'];
    return "Order fresh flower delivery in {$area} with same-day service across {$local}. Roses, bouquets & gifts from Sai Flower. Shop online today.";
}

function generate_h1(array $loc): string
{
    return 'Flower Delivery in ' . $loc['area'];
}

function generate_short_description(array $loc): string
{
    $area = $loc['area'];
    return "Order fresh flowers in {$area} with same-day delivery across Delhi NCR. Premium bouquets, roses & gifts from Sai Flower — trusted since 1998.";
}

function generate_meta_keywords(array $loc): string
{
    $slugArea = strtolower($loc['area']);
    return implode(', ', [
        strtolower($loc['keyword']),
        "same day flowers {$slugArea}",
        "online florist {$slugArea} delhi",
        'rose bouquet delivery south delhi',
        "birthday flowers {$slugArea}",
        'fresh flower delivery delhi ncr',
        'sai flower delivery',
    ]);
}

function count_keyword_stats(string $html, string $keyword): array
{
    $text = html_entity_decode(strip_tags($html));
    $text = preg_replace('/\s+/', ' ', trim($text));
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $count = preg_match_all('/' . preg_quote($keyword, '/') . '/i', $text);
    $total = count($words);
    return [
        'words' => $total,
        'keyword_count' => $count,
        'density' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
    ];
}

$layout_type = 'product_showcase';
$page_tag = 'sameday';
$status = 1;

$created = 0;
$skipped = 0;
$errors = [];

$check = $conn->prepare('SELECT id FROM dynamic_pages WHERE slug = ? LIMIT 1');
$insert = $conn->prepare(
    'INSERT INTO dynamic_pages (
        title, short_description, slug, content, meta_title, meta_description,
        meta_keywords, status, layout_type, page_tag, faqs
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);

foreach ($locations as $loc) {
    $slug = $loc['slug'];
    $title = $loc['keyword'];
    $short_description = generate_short_description($loc);
    $content = generate_content($loc);
    $meta_title = generate_meta_title($loc);
    $meta_description = generate_meta_description($loc);
    $meta_keywords = generate_meta_keywords($loc);
    $faqs_json = json_encode(generate_faqs($loc), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stats = count_keyword_stats($content, $title);

    $check->bind_param('s', $slug);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "SKIP  /{$slug} — already exists\n";
        $skipped++;
        continue;
    }

    $insert->bind_param(
        'sssssssisss',
        $title,
        $short_description,
        $slug,
        $content,
        $meta_title,
        $meta_description,
        $meta_keywords,
        $status,
        $layout_type,
        $page_tag,
        $faqs_json
    );

    if ($insert->execute()) {
        echo "OK    /{$slug} — {$stats['words']} words, {$stats['keyword_count']} keywords, {$stats['density']}% density\n";
        echo "      meta title: " . strlen($meta_title) . " chars | meta desc: " . strlen($meta_description) . " chars\n";
        $created++;
    } else {
        $msg = $conn->error;
        echo "FAIL  /{$slug} — {$msg}\n";
        $errors[] = "{$slug}: {$msg}";
    }
}

echo "\nDone. Created: {$created}, Skipped: {$skipped}, Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    exit(1);
}
