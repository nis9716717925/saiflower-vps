<?php
/**
 * Seed: Father's Day keyword custom pages (dynamic_pages)
 * Run once: https://saiflower.com/tools/seed_fathers_day_keyword_pages.php
 */
require_once __DIR__ . '/../config.php';

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

function insert_page(mysqli $conn, array $page): bool
{
    $check = $conn->prepare('SELECT id FROM dynamic_pages WHERE slug = ? LIMIT 1');
    $check->bind_param('s', $page['slug']);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "SKIP  /{$page['slug']} — already exists\n";
        return false;
    }

    $layout_type = 'product_showcase';
    $page_tag = 'sameday';
    $status = 1;
    $short_description = '';
    $faqs_json = json_encode($page['faqs'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt = $conn->prepare(
        'INSERT INTO dynamic_pages (
            title, short_description, slug, content, meta_title, meta_description,
            meta_keywords, status, layout_type, page_tag, faqs
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
        'sssssssisss',
        $page['title'],
        $short_description,
        $page['slug'],
        $page['content'],
        $page['meta_title'],
        $page['meta_description'],
        $page['meta_keywords'],
        $status,
        $layout_type,
        $page_tag,
        $faqs_json
    );

    if ($stmt->execute()) {
        $stats = count_keyword_stats($page['content'], $page['title']);
        echo "OK    /{$page['slug']} — {$stats['words']} words, {$stats['keyword_count']} keywords, {$stats['density']}% density\n";
        echo "      meta title: " . strlen($page['meta_title']) . " chars | meta desc: " . strlen($page['meta_description']) . " chars\n";
        return true;
    }

    echo "FAIL  /{$page['slug']} — {$conn->error}\n";
    return false;
}

$pages = [
    [
        'title' => 'Bouquet for fathers day',
        'slug' => 'bouquet-for-fathers-day',
        'meta_title' => 'Bouquet for Fathers Day Delivery | Sai Flower',
        'meta_description' => 'Order a fresh bouquet for fathers day with same-day delivery in Delhi NCR. Hand-tied roses & mixed blooms from Sai Flower. Shop online today.',
        'meta_keywords' => 'bouquet for fathers day, fathers day bouquet, father day flowers delhi, same day flower delivery, roses for dad, fresh flower bouquet, gift for father',
        'content' => <<<'HTML'
<h2>Honour Dad with a Beautiful Bouquet for Fathers Day</h2>
<p>Some gifts feel generic. A hand-tied <strong>bouquet for fathers day</strong> does not. It arrives at the door with colour, fragrance, and a message that says you thought about him — not just the calendar. Whether Dad loves classic roses or a cheerful mixed bunch, the right blooms turn an ordinary Sunday into a memory he will talk about all week.</p>

<p>At <strong>Sai Flower</strong>, every bouquet is built with daily-fresh stems, clean wrapping, and delivery windows that respect your schedule. We know Delhi NCR moves fast during celebration week, so our florists prepare orders shortly before dispatch to keep petals firm and presentation sharp.</p>

<h2>Why a Bouquet for Fathers Day Beats a Routine Gift</h2>
<p>Ties fade. Wallets get replaced. A thoughtful flower gift photographs beautifully, brightens his desk, and lingers in the room long after the surprise. Bold colours and structured arrangements suit dads who prefer something refined over flashy — personal without being over the top.</p>

<p>Families across South Delhi, Gurgaon, and Noida order a <strong>bouquet for fathers day</strong> for home surprises, office deliveries, and family lunch gatherings. Unlike off-the-shelf gifts, fresh flowers carry warmth that feels genuinely human.</p>

<h3>Same-Day Bouquet for Fathers Day Delivery</h3>
<p>Left it late? You are not alone. Place your <strong>bouquet for fathers day</strong> order before the daily cut-off and we deliver across Delhi NCR — presentation-ready and on time. Choose a date at checkout or WhatsApp our team to confirm express availability in your pin code.</p>

<h3>Top Bouquet Styles Dads Love</h3>
<ul>
<li><strong>Classic red rose bouquet</strong> — confident, timeless, and always appreciated</li>
<li><strong>Mixed seasonal bouquet</strong> — warm colour blends for a cheerful surprise</li>
<li><strong>Premium hand-tied arrangement</strong> — larger stems with elegant wrapping</li>
<li><strong>Compact desk bouquet</strong> — neat and professional for workplace delivery</li>
</ul>

<p>Browse the showcase above to find the right <strong>bouquet for fathers day</strong> for his taste. Share Dad's favourite colours on WhatsApp and our florists will recommend the best fit within your budget.</p>

<h2>How to Order Your Bouquet for Fathers Day Online</h2>
<p>Select your <strong>bouquet for fathers day</strong> from the product grid, enter the delivery address, and pay securely via UPI, card, or wallet. Pair blooms with a <a href="/cakes" title="Fathers day cakes Delhi">designer cake</a> or explore our full <a href="/flowers" title="Order flowers online Delhi">flower collection</a> and <a href="/gifts" title="Gift hampers for fathers day">gift hampers</a> for a complete celebration.</p>

<p>Every <strong>bouquet for fathers day</strong> is checked before dispatch. Protective packaging handles summer heat and monsoon humidity so your gift arrives looking as thoughtful as you intended.</p>

<p>We also handle corporate gifting and festival orders across Delhi NCR, with handwritten note cards available on request for a warmer finishing touch.</p>

<h3>Make This Fathers Day Truly Special</h3>
<p>Dad may insist he needs nothing, but a doorstep surprise still changes his expression. Send a fresh <strong>bouquet for fathers day</strong> that feels genuine, polished, and on time. Order early for preferred slots, or trust same-day delivery when the week gets busy.</p>

<p>From our family at Sai Flower to yours — a beautiful flower surprise is a small gesture with a big heart. Shop your <strong>bouquet for fathers day</strong> now and let us deliver the smile.</p>
HTML,
        'faqs' => [
            ['question' => 'What is the best bouquet for fathers day?', 'answer' => 'The best bouquet for fathers day depends on Dad\'s taste. Classic red or yellow roses suit traditional fathers, while mixed seasonal blooms work for a cheerful surprise. Sai Flower florists can recommend a hand-tied arrangement based on his favourite colours and your budget.'],
            ['question' => 'Can I get same-day bouquet for fathers day delivery in Delhi?', 'answer' => 'Yes. Place your order before the daily cut-off for same-day bouquet for fathers day delivery across Delhi, Gurgaon, Noida, and selected NCR areas. Add your pin code at checkout or WhatsApp our team to confirm express availability.'],
            ['question' => 'What flowers work best in a fathers day bouquet?', 'answer' => 'Roses, lilies, carnations, and mixed seasonal flowers are popular for a fathers day bouquet. Choose bold, structured arrangements with clean wrapping for a refined look that suits most dads.'],
            ['question' => 'How do I order a bouquet for fathers day online?', 'answer' => 'Visit this page, pick a bouquet from the product showcase, enter the delivery address and date, then complete secure checkout. You can add a personal message in the order notes.'],
            ['question' => 'What is the price range for fathers day bouquets?', 'answer' => 'Fathers day bouquet prices vary by size, flower type, and add-ons. Budget-friendly bunches and premium hand-tied arrangements are both available. Browse products above or contact us on WhatsApp for a quick quote.'],
            ['question' => 'Can I send a fathers day bouquet to Dad\'s office?', 'answer' => 'Absolutely. Many customers send a compact fathers day bouquet to an office in Delhi NCR. Mention the building name, floor, and reception details in the delivery notes for smooth handover.'],
            ['question' => 'Can I combine a bouquet with a cake for fathers day?', 'answer' => 'Yes. Pair your bouquet for fathers day with a chocolate, butterscotch, or designer cake from our cakes section. Same-day combo delivery is available in many NCR pin codes when ordered before the cut-off.'],
        ],
    ],
    [
        'title' => 'fathers day flowers',
        'slug' => 'fathers-day-flowers',
        'meta_title' => 'Fathers Day Flowers Delivery Delhi | Sai Flower',
        'meta_description' => 'Shop fresh fathers day flowers with same-day delivery in Delhi NCR. Roses, lilies & bouquets from Sai Flower. Order online for Dad today.',
        'meta_keywords' => 'fathers day flowers, fathers day flower delivery, flowers for dad, same day flowers delhi, rose bouquet fathers day, fresh flowers online, sai flower',
        'content' => <<<'HTML'
<h2>Fresh Fathers Day Flowers Delivered Across Delhi NCR</h2>
<p>Looking for meaningful <strong>fathers day flowers</strong> that actually feel personal? Dad may not ask for much, but a bright bouquet at the breakfast table or office desk tells him he matters. From classic roses to cheerful mixed stems, the right <strong>fathers day flowers</strong> turn a simple Sunday into something he will remember.</p>

<p><strong>Sai Flower</strong> has helped Delhi NCR families celebrate fathers for years. We source daily-fresh blooms, hand-tie every arrangement, and deliver across South Delhi, Gurgaon, Noida, and nearby areas with same-day options when you need them most.</p>

<h2>Why Fathers Day Flowers Make the Perfect Gift</h2>
<p>Not every dad wants another gadget. <strong>Fathers day flowers</strong> are warm, visual, and immediate — they brighten a room before anyone says a word. A well-chosen bunch suits quiet dads and expressive ones alike, especially when paired with a short handwritten note.</p>

<p>Customers order <strong>fathers day flowers</strong> for home surprises, office celebrations, and family gatherings. Unlike generic gifts, fresh stems carry emotion that feels real — not rushed or borrowed from a last-minute mall run.</p>

<h3>Same-Day Fathers Day Flowers in Delhi NCR</h3>
<p>Celebration week fills up fast. Order your <strong>fathers day flowers</strong> before the daily cut-off for same-day delivery across Delhi NCR. Add landmark, tower, or flat details at checkout and our riders coordinate smoothly even during peak traffic hours.</p>

<p>Prefer to schedule ahead? Pick your delivery date during checkout and mention a preferred time slot. We align with morning home visits, office hours, and evening family dinners.</p>

<h3>Popular Fathers Day Flower Picks</h3>
<ul>
<li><strong>Red and yellow roses</strong> — bold, classic, and always appreciated by traditional dads</li>
<li><strong>Mixed seasonal bouquet</strong> — cheerful colour blends for a warm family surprise</li>
<li><strong>White lilies and greens</strong> — elegant and understated for refined tastes</li>
<li><strong>Orchid arrangements</strong> — premium option for a standout milestone gift</li>
</ul>

<p>Explore the product showcase above to find <strong>fathers day flowers</strong> that match his personality. Not sure what to choose? WhatsApp our florists with Dad's favourite colours and we will guide you within your budget.</p>

<h2>How to Order Fathers Day Flowers Online</h2>
<p>Ordering takes minutes. Pick your <strong>fathers day flowers</strong> from the grid, enter the delivery address, and pay via UPI, card, or wallet. Add a <a href="/cakes" title="Fathers day cake delivery">designer cake</a> from our <a href="/cakes">cakes collection</a> or browse <a href="/gifts" title="Fathers day gifts Delhi">gift hampers</a> for a complete surprise package.</p>

<p>Each order is prepared close to dispatch so stems stay crisp. Protective wrapping shields bouquets from summer heat and monsoon humidity across the NCR.</p>

<p>We also handle hospital visits, corporate gifting, and festival orders, with note cards available on request for a more personal touch.</p>

<h3>Celebrate Dad with Flowers He Will Love</h3>
<p>He raised you, supported you, and still picks up on the first ring. This June, honour him with fresh <strong>fathers day flowers</strong> that feel sincere and beautifully presented. Book early for the best time slots or rely on same-day service when the calendar sneaks up.</p>

<p>Sai Flower is here to help you make the moment count. Shop now and let us deliver warmth straight to his doorstep.</p>
HTML,
        'faqs' => [
            ['question' => 'What are the best fathers day flowers for Dad?', 'answer' => 'Roses, lilies, carnations, and mixed seasonal bouquets are among the best fathers day flowers. Choose bold, structured arrangements for traditional dads or cheerful mixed stems for a relaxed family celebration.'],
            ['question' => 'Can I get same-day fathers day flowers delivery in Delhi?', 'answer' => 'Yes. Order before the daily cut-off for same-day fathers day flowers delivery across Delhi, Gurgaon, Noida, and selected NCR pin codes. Confirm your area on checkout or via WhatsApp.'],
            ['question' => 'How much do fathers day flowers cost?', 'answer' => 'Fathers day flowers range from affordable everyday bouquets to premium hand-tied arrangements. Prices depend on flower type, size, and add-ons. Browse the showcase above or contact Sai Flower for a quick recommendation.'],
            ['question' => 'How do I order fathers day flowers online?', 'answer' => 'Visit this page, select flowers from the product showcase, enter the delivery address and date, then complete checkout. Add a message in the order notes for a personalised touch.'],
            ['question' => 'Can I send fathers day flowers to an office?', 'answer' => 'Yes. Desk-friendly fathers day flowers are popular for office delivery in Delhi NCR. Include building name, floor, and reception instructions in the delivery notes.'],
            ['question' => 'Do you deliver fathers day flowers to Gurgaon and Noida?', 'answer' => 'Yes. We deliver fathers day flowers across Delhi NCR including Gurgaon, Noida, Faridabad, and Ghaziabad in many pin codes. Enter your address at checkout to confirm coverage.'],
            ['question' => 'Can I add a cake with fathers day flowers?', 'answer' => 'Absolutely. Pair fathers day flowers with a chocolate, butterscotch, or designer cake from our cakes section. Same-day combo delivery is available in many areas when ordered before the cut-off.'],
        ],
    ],
    [
        'title' => "father's day flowers",
        'slug' => 'fathers-day-flowers-online',
        'meta_title' => "Father's Day Flowers Online | Sai Flower",
        'meta_description' => "Send fresh father's day flowers with same-day delivery in Delhi NCR. Premium bouquets & roses from Sai Flower. Order online for Dad today.",
        'meta_keywords' => "father's day flowers, father's day flower delivery, flowers for father, online florist delhi, father's day bouquet, same day flowers, sai flower delivery",
        'content' => <<<'HTML'
<h2>Send Thoughtful Father's Day Flowers He Will Remember</h2>
<p>The best <strong>father's day flowers</strong> are not about perfection — they are about showing up with something warm, fresh, and genuinely thoughtful. Whether Dad is at home, at work, or visiting family, a well-timed bouquet says thank you in a way words sometimes cannot.</p>

<p>At <strong>Sai Flower</strong>, we craft every order with daily-fresh stems and presentation that feels polished from the first glance. Our team delivers across Delhi NCR with same-day options for families who want reliability without the stress.</p>

<h2>Why Father's Day Flowers Never Go Out of Style</h2>
<p>Flowers fit every kind of dad. Quiet fathers appreciate understated lilies and greens. Expressive dads love bold roses and vibrant mixed bunches. The right <strong>father's day flowers</strong> meet him where he is — not where a generic gift guide says he should be.</p>

<p>Across South Delhi, Gurgaon, and Noida, families choose fresh blooms for breakfast surprises, living-room celebrations, and office deliveries. Colourful stems add warmth to reunions and joy to an ordinary Sunday morning.</p>

<h3>Same-Day Father's Day Flowers Across Delhi NCR</h3>
<p>June gets busy quickly. Order your <strong>father's day flowers</strong> before the daily cut-off for same-day delivery in Delhi NCR. Share tower, flat, or landmark details at checkout — our riders use them to reach the right doorstep without delays.</p>

<p>Scheduling ahead? Select your preferred date and time window during checkout. We coordinate morning home deliveries, afternoon office drops, and evening family gatherings across the capital region.</p>

<p>Many customers also send <strong>father's day flowers</strong> when they cannot visit in person — a fresh bouquet bridges the distance and still feels intimate.</p>

<h3>Father's Day Flower Arrangements Dads Appreciate</h3>
<ul>
<li><strong>Classic rose bouquet</strong> — confident and timeless for traditional tastes</li>
<li><strong>Mixed seasonal flowers</strong> — bright, cheerful stems for family celebrations</li>
<li><strong>Premium hand-tied bouquet</strong> — elegant wrapping for milestone years</li>
<li><strong>Compact office arrangement</strong> — neat and professional for workplace surprises</li>
</ul>

<p>Use the product showcase above to shop <strong>father's day flowers</strong> by style and budget. Message our florists on WhatsApp if you want a quick recommendation based on Dad's favourite colours.</p>

<h2>Order Father's Day Flowers Online in Minutes</h2>
<p>Choose your <strong>father's day flowers</strong>, enter the delivery address, and complete secure checkout via UPI, card, or wallet. Combine blooms with a <a href="/cakes" title="Father's day cake delivery Delhi">designer cake</a> or explore our <a href="/flowers" title="Buy flowers online Delhi NCR">flowers</a> and <a href="/gifts" title="Father's day gift hampers">gifts</a> for a fuller celebration.</p>

<p>Every bunch is prepared shortly before dispatch. Protective packaging keeps stems fresh through heat and humidity — because presentation matters when you are honouring someone important.</p>

<p>Corporate orders, hospital visits, and festival gifting are also available, with handwritten cards on request for a more personal finish.</p>

<h3>Make His Father's Day Bright and Personal</h3>
<p>He has been your anchor for years. This Father's Day, return the gesture with <strong>father's day flowers</strong> that feel sincere, beautiful, and on time. Book early for the best slots or choose same-day delivery when plans shift at the last minute.</p>

<p>Trust Sai Flower to help you celebrate the man who showed up for you. Shop online today and let us deliver the warmth he deserves.</p>
HTML,
        'faqs' => [
            ['question' => "What father's day flowers should I send my dad?", 'answer' => "Roses, lilies, carnations, and mixed seasonal bouquets are popular father's day flowers. Pick bold roses for a classic look or mixed stems for a cheerful family surprise. Sai Flower florists can help you choose based on Dad's style."],
            ['question' => "Can I get same-day father's day flowers in Delhi NCR?", 'answer' => "Yes. Order before the daily cut-off for same-day father's day flowers delivery across Delhi, Gurgaon, Noida, and many NCR pin codes. Confirm availability at checkout or via WhatsApp."],
            ['question' => "How do I order father's day flowers online?", 'answer' => "Visit this page, select a bouquet from the showcase, add the delivery address and date, then complete checkout. You can include a personal message in the order notes."],
            ['question' => "What is the best time to deliver father's day flowers?", 'answer' => "Morning delivery works well for home surprises, while afternoon slots suit offices. Mention your preferred window in the order notes and our team will try to match it for your father's day flowers."],
            ['question' => "Are father's day flowers expensive?", 'answer' => "Father's day flowers are available at multiple price points — from affordable bouquets to premium arrangements. Filter products above or contact Sai Flower on WhatsApp for options within your budget."],
            ['question' => "Can father's day flowers be delivered to hospitals or offices?", 'answer' => "Yes. We deliver father's day flowers to homes, offices, and selected hospital areas in Delhi NCR. Provide clear building, floor, and reception details for smooth handover."],
            ['question' => "Can I pair father's day flowers with a gift hamper?", 'answer' => "Yes. Combine father's day flowers with cakes, chocolates, or gift hampers from our collections. Same-day combo delivery is available in many NCR areas when ordered before the cut-off."],
        ],
    ],
];

$created = 0;
$skipped = 0;

foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    } else {
        $skipped++;
    }
}

echo "\nDone. Created: {$created}, Skipped: {$skipped}\n";
