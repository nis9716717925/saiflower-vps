<?php
/**
 * Seed: Keyword custom pages — Batch 6 (dynamic_pages)
 * Layout: product_showcase | Tag: sameday
 * Run once: https://saiflower.com/tools/seed_keyword_pages_batch6.php
 * Safe to re-run: existing slugs are skipped automatically.
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
        echo "OK    /{$page['slug']} — {$stats['words']} words, {$stats['keyword_count']} keywords, {$stats['density']}% density | title " . strlen($page['meta_title']) . "c | desc " . strlen($page['meta_description']) . "c\n";
        return true;
    }

    echo "FAIL  /{$page['slug']} — {$conn->error}\n";
    return false;
}

$pages = [

// 51 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Luxury Rose Bouquet',
    'slug'  => 'luxury-rose-bouquet',
    'content' => <<<'HTML'
<h2>Impress Them With a Luxury Rose Bouquet</h2>
<p>For a gesture that speaks of grandeur and passion, nothing rivals a luxury rose bouquet. Overflowing with premium roses and finished with designer wrapping, a luxury rose bouquet is the ultimate romantic statement. Whether for an anniversary, proposal, or milestone, a luxury rose bouquet turns emotion into pure elegance. Gifting a luxury rose bouquet leaves an impression that lasts.</p>

<p>At <strong>Sai Flower</strong>, every luxury rose bouquet is designed with the finest daily-fresh roses. We craft opulent, balanced arrangements with refined detailing, so your gift feels as exclusive as the moment it celebrates.</p>

<h2>What Makes a Rose Bouquet Luxurious</h2>
<p>A luxury rose bouquet stands apart through quality, scale, and presentation. Every detail is chosen to dazzle.</p>
<ul>
<li><strong>Finest premium roses</strong> — velvety, fragrant, and daily-fresh</li>
<li><strong>Grand sizes</strong> — 50 and 100-rose arrangements available</li>
<li><strong>Signature wrapping</strong> — luxury boxes, silk ribbons, and fine paper</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
</ul>

<h3>For Life's Grandest Moments</h3>
<p>Milestone anniversaries, dream proposals, and Valentine's celebrations call for a luxury rose bouquet. Choose a lavish red rose arrangement or a designer mix, and add a premium cake or hamper so one luxury rose bouquet crowns the occasion in style.</p>

<h3>Exclusive Quality, Reliable Service</h3>
<p>Luxury should feel effortless. Each luxury rose bouquet is prepared close to dispatch, packed with meticulous care, and delivered by trusted riders, arriving fresh, flawless, and stunning.</p>

<h2>Order a Luxury Rose Bouquet Online</h2>
<p>Explore our <a href="/flowers" title="Luxury rose bouquet Delhi">rose collection</a>, add a <a href="/cakes" title="Order premium cakes online">premium cake</a> or <a href="/gifts" title="Luxury gifts Delhi">gift</a>, and check out securely. With Sai Flower, a luxury rose bouquet means unmatched romance, freshness, and on-time delivery — order now and celebrate in grand style.</p>
HTML,
    'meta_title' => 'Luxury Rose Bouquet Delivery | Sai Flower',
    'meta_description' => 'Order a luxury rose bouquet in Delhi NCR with premium roses & signature wrapping from Sai Flower. Same-day & midnight delivery. Shop luxury roses now.',
    'meta_keywords' => 'luxury rose bouquet, premium rose bouquet, red rose bouquet, luxury flower bouquet, 100 roses bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What makes a rose bouquet luxurious?', 'answer' => 'A luxury rose bouquet uses the finest daily-fresh roses in grand, opulent arrangements with signature wrapping like boxes and silk ribbons for an exclusive look.'],
        ['question' => 'Can I order a 100-rose bouquet?', 'answer' => 'Yes. We offer grand 50 and 100-rose arrangements perfect for proposals and milestone celebrations. Contact us for custom sizes.'],
        ['question' => 'Is same-day luxury delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available.'],
        ['question' => 'Which rose colours can I choose?', 'answer' => 'Choose red, pink, white, or mixed premium roses. Red is the classic choice for romance and proposals.'],
        ['question' => 'Can I add premium gifts?', 'answer' => 'Absolutely. Pair it with a premium cake, chocolates, or a luxury hamper and we will deliver the complete combo together.'],
        ['question' => 'Is a luxury rose bouquet good for a proposal?', 'answer' => 'Definitely. A lavish red rose arrangement makes any proposal unforgettable.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver luxury rose bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 52 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Premium Rose Box',
    'slug'  => 'premium-rose-box',
    'content' => <<<'HTML'
<h2>Elegant Gifting With a Premium Rose Box</h2>
<p>Sleek, stylish, and utterly romantic, a premium rose box is a modern way to say "I care." Fresh roses arranged neatly in a designer box create a stunning, Instagram-worthy gift. Whether for an anniversary, proposal, or Valentine's Day, a premium rose box makes a lasting impression. Gifting a premium rose box blends elegance with pure emotion.</p>

<p>At <strong>Sai Flower</strong>, every premium rose box is arranged with the finest daily-fresh roses. We design each box with balance and refinement, so your gift arrives fresh, structured, and beautifully presented.</p>

<h2>Why Choose a Premium Rose Box</h2>
<p>A rose box offers style and staying power in one chic package. Our premium rose box is a sophisticated choice for modern gifting.</p>
<ul>
<li><strong>Finest fresh roses</strong> — neatly arranged in a designer box</li>
<li><strong>Elegant presentation</strong> — luxury boxes in classic colours</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Perfect for romance</strong> — anniversaries and proposals</li>
</ul>

<h3>A Modern Romantic Statement</h3>
<p>A premium rose box suits those who love style and sophistication. Choose a classic red box for passion, or a mixed-tone box for a softer look. For extra impact, pair a premium rose box with chocolates, creating a complete, elegant surprise.</p>

<h3>Fresh, Chic, and On Time</h3>
<p>Presentation deserves freshness. Each premium rose box is prepared close to dispatch and packed carefully, so the roses arrive firm, richly coloured, and long-lasting.</p>

<h2>Order a Premium Rose Box Online</h2>
<p>Browse our <a href="/flowers" title="Premium rose box Delhi">rose collection</a>, add <a href="/gifts" title="Chocolates and gifts Delhi">chocolates or a gift</a>, or include a <a href="/cakes" title="Order cakes online">cake</a>, and check out securely. With Sai Flower, a premium rose box brings elegance, freshness, and reliable delivery — order now and gift romance in style.</p>
HTML,
    'meta_title' => 'Premium Rose Box Delivery Delhi | Sai Flower',
    'meta_description' => 'Order a premium rose box in Delhi NCR with fresh roses in a designer box from Sai Flower. Same-day & midnight delivery. Shop the premium rose box now.',
    'meta_keywords' => 'premium rose box, rose box delivery, luxury rose box, red rose box, premium flower bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What is a premium rose box?', 'answer' => 'A premium rose box features fresh roses neatly arranged in a stylish designer box, creating an elegant, modern gift that looks stunning and lasts well.'],
        ['question' => 'Which colours are available?', 'answer' => 'Choose a classic red box, mixed tones, or pastel shades. Our florists can customise the look for your occasion.'],
        ['question' => 'Is same-day rose box delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available.'],
        ['question' => 'Is a rose box good for a proposal?', 'answer' => 'Absolutely. A premium rose box makes a chic, romantic proposal or anniversary gift.'],
        ['question' => 'Can I add chocolates?', 'answer' => 'Yes. Pair the rose box with chocolates or a cake and we will deliver the complete combo together.'],
        ['question' => 'Will the roses stay fresh in the box?', 'answer' => 'Yes. Roses are arranged with a fresh base close to dispatch and packed carefully, so they stay firm and beautiful.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver rose boxes across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 53 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Floral Gift Delivery',
    'slug'  => 'floral-gift-delivery',
    'content' => <<<'HTML'
<h2>Thoughtful Floral Gift Delivery in Delhi NCR</h2>
<p>Flowers are the perfect gift, and floral gift delivery makes sending them effortless. From elegant bouquets to flower combos with cakes and chocolates, floral gift delivery covers every occasion beautifully. Whether it is a birthday, anniversary, or heartfelt gesture, floral gift delivery brings joy to the doorstep. One order of floral gift delivery makes gifting simple and memorable.</p>

<p>At <strong>Sai Flower</strong>, every floral gift delivery is prepared with daily-fresh blooms and thoughtful add-ons. We arrange and dispatch with care, so your gift arrives fresh, elegant, and ready to delight.</p>

<h2>Why Choose Our Floral Gift Delivery</h2>
<p>A great floral gift blends beauty, freshness, and convenience. Our floral gift delivery delivers all three, every time.</p>
<ul>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Combos available</strong> — flowers with cakes, chocolates, and teddies</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Floral Gifts for Every Occasion</h3>
<p>From celebrations to comfort, floral gift delivery suits every moment. Send a romantic rose bouquet, a cheerful mixed bunch, or an elegant orchid arrangement. Add a cake or chocolates, and one floral gift delivery becomes a complete, heartfelt present.</p>

<h3>Fresh and On Time, Always</h3>
<p>Every floral gift deserves to arrive at its best. Each floral gift delivery is prepared close to dispatch and packed protectively, so your blooms and add-ons arrive fresh and beautiful.</p>

<h2>Order Floral Gift Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Floral gift delivery Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, floral gift delivery is fresh, thoughtful, and reliable — order now and send the perfect floral surprise.</p>
HTML,
    'meta_title' => 'Floral Gift Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Order floral gift delivery in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh bouquets & gift combos for every occasion. Send floral gifts now.',
    'meta_keywords' => 'floral gift delivery, flower gift delivery, floral gifts online, flower gift combo, same day flower delivery, online flower delivery, gift combo delivery',
    'faqs' => [
        ['question' => 'What is included in floral gift delivery?', 'answer' => 'Floral gift delivery includes fresh bouquets and optional add-ons like cakes, chocolates, and teddies, delivered together for a complete gift.'],
        ['question' => 'Is same-day floral gift delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Can I customise my floral gift?', 'answer' => 'Absolutely. Choose your bouquet and add any combination of cake, chocolates, or a teddy to build the perfect gift.'],
        ['question' => 'What occasions suit floral gifts?', 'answer' => 'Floral gifts suit birthdays, anniversaries, congratulations, get-well wishes, and just-because gestures.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every floral gift is arranged with daily-fresh blooms close to dispatch and packed protectively for freshness.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your floral gift.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 54 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Hand Tied Bouquet',
    'slug'  => 'hand-tied-bouquet',
    'content' => <<<'HTML'
<h2>Artisan Charm of a Hand Tied Bouquet</h2>
<p>There is a special beauty in a hand tied bouquet, where every stem is arranged by skilled florists for a natural, elegant look. A hand tied bouquet feels personal, crafted, and thoughtful, making it a wonderful gift for any occasion. Whether for romance or celebration, a hand tied bouquet showcases true floral artistry. Gifting a hand tied bouquet shows you chose something made with care.</p>

<p>At <strong>Sai Flower</strong>, every hand tied bouquet is created by expert florists using daily-fresh blooms. We shape each arrangement by hand close to dispatch, so it arrives fresh, balanced, and beautifully styled.</p>

<h2>Why Choose a Hand Tied Bouquet</h2>
<p>A hand tied bouquet blends craftsmanship with fresh beauty. Every one is unique, natural, and elegant.</p>
<ul>
<li><strong>Florist-crafted</strong> — arranged stem by stem by hand</li>
<li><strong>Natural, elegant style</strong> — relaxed yet refined</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, daily-sourced blooms</strong> — roses, lilies, and mixes</li>
</ul>

<h3>Perfect for Every Occasion</h3>
<p>From birthdays and anniversaries to thank-yous and congratulations, a hand tied bouquet suits every moment. Choose romantic roses, cheerful mixes, or elegant lilies, and add a cake or gift so one hand tied bouquet completes the celebration.</p>

<h3>Fresh and Beautifully Crafted</h3>
<p>Artistry deserves freshness. Each hand tied bouquet is prepared close to dispatch and packed protectively, so every stem arrives crisp, fragrant, and gorgeously arranged.</p>

<h2>Order a Hand Tied Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Hand tied bouquet Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, a hand tied bouquet brings artistry, freshness, and reliable delivery — order now and gift something crafted with care.</p>
HTML,
    'meta_title' => 'Hand Tied Bouquet Delivery Delhi | Sai Flower',
    'meta_description' => 'Order a hand tied bouquet in Delhi NCR with florist-crafted, fresh blooms from Sai Flower. Same-day & midnight delivery. Send a hand tied bouquet now.',
    'meta_keywords' => 'hand tied bouquet, florist bouquet, artisan flower bouquet, fresh flower bouquet, bouquet delivery, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What is a hand tied bouquet?', 'answer' => 'A hand tied bouquet is arranged stem by stem by skilled florists for a natural, elegant look, making each one unique and thoughtfully crafted.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Can I choose the flowers?', 'answer' => 'Yes. Tell us your preferred blooms or colours on WhatsApp and our florists will craft a custom hand tied bouquet, subject to availability.'],
        ['question' => 'What occasions suit a hand tied bouquet?', 'answer' => 'Hand tied bouquets suit birthdays, anniversaries, thank-yous, congratulations, and romantic gestures.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a teddy and we will deliver the complete combo together.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every hand tied bouquet is arranged with daily-fresh blooms close to dispatch and packed protectively for freshness.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 55 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Personalized Flower Bouquet',
    'slug'  => 'personalized-flower-bouquet',
    'content' => <<<'HTML'
<h2>Make It Special With a Personalized Flower Bouquet</h2>
<p>Nothing shows thought like a personalized flower bouquet designed just for the recipient. Choose their favourite blooms, colours, and style, and a personalized flower bouquet becomes a truly one-of-a-kind gift. Whether for a birthday, anniversary, or heartfelt surprise, a personalized flower bouquet speaks straight to the heart. Gifting a personalized flower bouquet turns flowers into something deeply meaningful.</p>

<p>At <strong>Sai Flower</strong>, every personalized flower bouquet is crafted to your wishes using daily-fresh blooms. Tell us your preferences, and our florists design an arrangement that feels personal, fresh, and beautifully unique.</p>

<h2>Why Choose a Personalized Flower Bouquet</h2>
<p>Personal touches make gifts unforgettable. A custom personalized flower bouquet shows genuine care and attention.</p>
<ul>
<li><strong>Your choice of blooms</strong> — favourite flowers and colours</li>
<li><strong>Custom style</strong> — from classic to modern designs</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Add-ons available</strong> — cakes, chocolates, and teddies</li>
</ul>

<h3>Designed Around Them</h3>
<p>Whether they love roses, lilies, or vibrant mixes, a personalized flower bouquet reflects their taste. Add a meaningful message and a favourite treat, and one personalized flower bouquet becomes a heartfelt, tailor-made surprise.</p>

<h3>Fresh and Thoughtfully Made</h3>
<p>Custom gifts deserve freshness. Each personalized flower bouquet is prepared close to dispatch and packed protectively, so your unique arrangement arrives crisp, fragrant, and beautiful.</p>

<h2>Order a Personalized Flower Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Personalized flower bouquet Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and message us your preferences. With Sai Flower, a personalized flower bouquet brings meaning, freshness, and reliable delivery — order now and gift something truly personal.</p>
HTML,
    'meta_title' => 'Personalized Flower Bouquet Delivery | Sai Flower',
    'meta_description' => 'Order a personalized flower bouquet in Delhi NCR with custom blooms & colours from Sai Flower. Same-day & midnight delivery. Design a custom bouquet now.',
    'meta_keywords' => 'personalized flower bouquet, custom flower bouquet, customised bouquet, personalised flowers, hand tied bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'How do I personalise my flower bouquet?', 'answer' => 'Tell us your preferred blooms, colours, and style on WhatsApp or in the order notes, and our florists will craft a custom bouquet, subject to availability.'],
        ['question' => 'Is same-day delivery available for custom bouquets?', 'answer' => 'Yes, in many cases. Because custom designs need coordination, we recommend WhatsApp-ing us to confirm same-day availability.'],
        ['question' => 'Can I choose specific flowers and colours?', 'answer' => 'Absolutely. Choose the recipient\'s favourite flowers and colour theme and we will design the bouquet accordingly.'],
        ['question' => 'Can I add a personal message?', 'answer' => 'Yes. Add a meaningful message at checkout and we will include it with your personalized bouquet.'],
        ['question' => 'Can I add gifts to a personalised bouquet?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy and we will deliver the complete custom combo together.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every personalized bouquet is arranged with daily-fresh blooms close to dispatch and packed protectively for freshness.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 56 ────────────────────────────────────────────────────────────────────
[
    'title' => "Valentine's Day Flowers",
    'slug'  => 'valentines-day-flowers',
    'content' => <<<'HTML'
<h2>Celebrate Love With Valentine's Day Flowers</h2>
<p>The most romantic day of the year deserves the most beautiful blooms, and Valentine's Day flowers say it perfectly. From passionate red roses to dreamy mixed arrangements, Valentine's Day flowers turn 14 February into an unforgettable celebration of love. Whether for your partner or crush, Valentine's Day flowers make hearts flutter. Sending Valentine's Day flowers is the timeless way to say "I love you."</p>

<p>At <strong>Sai Flower</strong>, every arrangement of Valentine's Day flowers is hand-tied with daily-fresh blooms and delivered on time. We design romantic, elegant bouquets so your love arrives fresh, fragrant, and stunning.</p>

<h2>Why Choose Our Valentine's Day Flowers</h2>
<p>Valentine's week is our most romantic season. Our Valentine's Day flowers combine passion, beauty, and reliable delivery.</p>
<ul>
<li><strong>Romantic red roses</strong> — the classic Valentine's choice</li>
<li><strong>Same-day and midnight delivery</strong> — surprise them on time</li>
<li><strong>Combos available</strong> — flowers with cakes, chocolates, and teddies</li>
<li><strong>Book early</strong> — secure your preferred slot in the busy season</li>
</ul>

<h3>The Perfect Romantic Gift</h3>
<p>Red roses declare passion, while pink and mixed blooms convey sweetness. For extra romance, pair Valentine's Day flowers with chocolates and a plush teddy. However you choose, Valentine's Day flowers create a moment your special someone will always remember.</p>

<h3>Fresh, Romantic, and On Time</h3>
<p>Love should arrive at its best. Each order of Valentine's Day flowers is prepared close to dispatch and packed protectively, so your bouquet arrives crisp, fragrant, and beautifully arranged.</p>

<h2>Order Valentine's Day Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Valentine's Day flowers Delhi">romantic flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Valentine gifts Delhi">gift</a>, and book early. With Sai Flower, Valentine's Day flowers bring passion, freshness, and on-time delivery — order now and make 14 February magical.</p>
HTML,
    'meta_title' => "Valentine's Day Flowers Delivery | Sai Flower",
    'meta_description' => "Send Valentine's Day flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Romantic roses & combos. Order Valentine's flowers online now.",
    'meta_keywords' => "valentine's day flowers, valentine flowers, romantic flower bouquet, red rose bouquet, valentine gifts, same day flower delivery, online flower delivery",
    'faqs' => [
        ['question' => "What are the best Valentine's Day flowers?", 'answer' => "Red roses are the classic symbol of love for Valentine's Day, while pink and mixed blooms add sweetness. Sai Flower can craft a custom romantic arrangement."],
        ['question' => 'Can I get midnight delivery on Valentine\'s Day?', 'answer' => 'Yes. Midnight delivery is available in selected pin codes, so you can surprise your partner right at 12 a.m. on 14 February.'],
        ['question' => 'Should I order Valentine\'s flowers in advance?', 'answer' => "Yes. Valentine's week is very busy, so we recommend booking early to secure your preferred bouquet and delivery slot."],
        ['question' => 'Can I add chocolates or a teddy?', 'answer' => 'Absolutely. Pair the flowers with chocolates, a cake, or a plush teddy and we will deliver the complete romantic combo together.'],
        ['question' => 'Do you offer same-day Valentine\'s delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Can I include a love note?', 'answer' => 'Yes. Add your personal message at checkout and we will include it with your Valentine\'s Day flowers.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 57 ────────────────────────────────────────────────────────────────────
[
    'title' => "Mother's Day Flowers",
    'slug'  => 'mothers-day-flowers',
    'content' => <<<'HTML'
<h2>Honour Mom With Mother's Day Flowers</h2>
<p>Mothers give endlessly, and Mother's Day flowers are a beautiful way to say thank you. From graceful lilies to cheerful mixed bouquets, Mother's Day flowers show love, gratitude, and appreciation. Whether she loves roses or carnations, Mother's Day flowers make her feel truly cherished. Sending Mother's Day flowers is the perfect gesture to celebrate the most special woman in your life.</p>

<p>At <strong>Sai Flower</strong>, every arrangement of Mother's Day flowers is hand-tied with daily-fresh blooms and delivered on time. We design warm, elegant bouquets so your love reaches Mom fresh, fragrant, and heartfelt.</p>

<h2>Why Choose Our Mother's Day Flowers</h2>
<p>Mother's Day deserves something special. Our Mother's Day flowers combine beauty, sentiment, and reliable delivery.</p>
<ul>
<li><strong>Elegant blooms</strong> — roses, lilies, carnations, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — surprise her on time</li>
<li><strong>Combos available</strong> — flowers with cakes and gifts</li>
<li><strong>Book early</strong> — secure your slot in the busy season</li>
</ul>

<h3>The Perfect Gift for Mom</h3>
<p>Carnations symbolise a mother's love, while roses and lilies convey admiration and grace. For extra warmth, pair Mother's Day flowers with a cake or a thoughtful gift. However you choose, Mother's Day flowers create a moment she will treasure.</p>

<h3>Fresh, Warm, and On Time</h3>
<p>Mom deserves flowers at their best. Each order of Mother's Day flowers is prepared close to dispatch and packed protectively, so the bouquet arrives crisp, fragrant, and beautiful.</p>

<h2>Order Mother's Day Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Mother's Day flowers Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Mother's Day gifts Delhi">gift</a>, and book early. With Sai Flower, Mother's Day flowers bring warmth, freshness, and on-time delivery — order now and make Mom's day unforgettable.</p>
HTML,
    'meta_title' => "Mother's Day Flowers Delivery | Sai Flower",
    'meta_description' => "Send Mother's Day flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh roses, lilies & combos. Order Mother's Day flowers now.",
    'meta_keywords' => "mother's day flowers, mothers day bouquet, flowers for mom, carnation bouquet, mother's day gifts, same day flower delivery, online flower delivery",
    'faqs' => [
        ['question' => "What are the best Mother's Day flowers?", 'answer' => "Carnations symbolise a mother's love, while roses and lilies convey admiration and grace. Sai Flower can tailor a warm arrangement to Mom's taste."],
        ['question' => 'Do you offer same-day Mother\'s Day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Should I book Mother\'s Day flowers early?', 'answer' => "Yes. Mother's Day is a busy season, so we recommend booking early to secure your preferred bouquet and slot."],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Pair the flowers with a cake, chocolates, or a gift and we will deliver the complete combo together.'],
        ['question' => 'Can I send flowers to Mom in another area?', 'answer' => 'Yes. Enter her address at checkout. We deliver across Delhi NCR, and you can WhatsApp us for other locations.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add a heartfelt message at checkout and we will include it with your Mother\'s Day flowers.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 58 ────────────────────────────────────────────────────────────────────
[
    'title' => "Women's Day Flowers",
    'slug'  => 'womens-day-flowers',
    'content' => <<<'HTML'
<h2>Celebrate Her With Women's Day Flowers</h2>
<p>International Women's Day is the perfect time to honour the amazing women in your life, and Women's Day flowers do it beautifully. From vibrant mixed bouquets to elegant roses, Women's Day flowers express admiration, respect, and appreciation. Whether for family, friends, or colleagues, Women's Day flowers make 8 March truly special. Sending Women's Day flowers is a thoughtful way to say "you are appreciated."</p>

<p>At <strong>Sai Flower</strong>, every arrangement of Women's Day flowers is hand-tied with daily-fresh blooms and delivered on time. We design bright, elegant bouquets so your appreciation arrives fresh, cheerful, and heartfelt.</p>

<h2>Why Choose Our Women's Day Flowers</h2>
<p>Women's Day calls for a meaningful gesture. Our Women's Day flowers combine beauty, sentiment, and reliable delivery.</p>
<ul>
<li><strong>Bright, elegant blooms</strong> — roses, gerberas, lilies, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Perfect for work or home</strong> — colleagues, family, and friends</li>
<li><strong>Combos available</strong> — flowers with cakes and gifts</li>
</ul>

<h3>A Gesture of Appreciation</h3>
<p>Bright, cheerful blooms celebrate strength and grace. For colleagues, a neat bouquet works beautifully; for loved ones, choose roses or lilies. Pair Women's Day flowers with a cake or gift, and one order of Women's Day flowers becomes a complete tribute.</p>

<h3>Fresh, Cheerful, and On Time</h3>
<p>Appreciation deserves flowers at their best. Each order of Women's Day flowers is prepared close to dispatch and packed protectively, so the bouquet arrives crisp, colourful, and beautiful.</p>

<h2>Order Women's Day Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Women's Day flowers Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Women's Day gifts Delhi">gift</a>, and check out in minutes. With Sai Flower, Women's Day flowers bring cheer, freshness, and on-time delivery — order now and celebrate the women who inspire you.</p>
HTML,
    'meta_title' => "Women's Day Flowers Delivery | Sai Flower",
    'meta_description' => "Send Women's Day flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Bright bouquets & combos for 8 March. Order Women's Day flowers now.",
    'meta_keywords' => "women's day flowers, womens day bouquet, 8 march flowers, flowers for her, appreciation flowers, same day flower delivery, online flower delivery",
    'faqs' => [
        ['question' => "What are the best Women's Day flowers?", 'answer' => "Bright, cheerful blooms like mixed roses, gerberas, and lilies are ideal for Women's Day, symbolising strength, grace, and appreciation."],
        ['question' => 'Can I send Women\'s Day flowers to an office?', 'answer' => 'Yes. Add the company name, floor, and reception details in the notes and our rider will deliver smoothly to colleagues.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Can I order multiple bouquets for a team?', 'answer' => 'Yes. We handle bulk orders for teams and offices. Contact us on WhatsApp to arrange multiple deliveries.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the flowers with a cake, chocolates, or a gift and we will deliver the complete combo together.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add an appreciative message at checkout and we will include it with your Women\'s Day flowers.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 59 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Raksha Bandhan Flowers',
    'slug'  => 'raksha-bandhan-flowers',
    'content' => <<<'HTML'
<h2>Celebrate the Bond With Raksha Bandhan Flowers</h2>
<p>Raksha Bandhan celebrates the beautiful bond between siblings, and Raksha Bandhan flowers make the day even more special. From cheerful mixed bouquets to elegant roses, Raksha Bandhan flowers express love, protection, and togetherness. Whether paired with rakhi and sweets or sent on their own, Raksha Bandhan flowers spread festive joy. Sending Raksha Bandhan flowers is a heartfelt way to honour your brother or sister.</p>

<p>At <strong>Sai Flower</strong>, every arrangement of Raksha Bandhan flowers is hand-tied with daily-fresh blooms and delivered on time. We design bright, festive bouquets so your love reaches your sibling fresh, cheerful, and heartfelt.</p>

<h2>Why Choose Our Raksha Bandhan Flowers</h2>
<p>The festival deserves a joyful gesture. Our Raksha Bandhan flowers combine festive beauty and reliable delivery.</p>
<ul>
<li><strong>Bright, festive blooms</strong> — roses, gerberas, lilies, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Combos available</strong> — flowers with sweets, cakes, and gifts</li>
<li><strong>Book early</strong> — secure your slot for the festival rush</li>
</ul>

<h3>The Perfect Festive Gift</h3>
<p>Cheerful mixed blooms suit the joyful spirit of Rakhi, while roses add elegance. For a complete celebration, pair Raksha Bandhan flowers with sweets or a cake. However you choose, Raksha Bandhan flowers make your sibling feel loved and remembered.</p>

<h3>Fresh, Festive, and On Time</h3>
<p>The occasion deserves flowers at their best. Each order of Raksha Bandhan flowers is prepared close to dispatch and packed protectively, so the bouquet arrives crisp, colourful, and beautiful.</p>

<h2>Order Raksha Bandhan Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Raksha Bandhan flowers Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Rakhi gifts Delhi">gift</a>, and book early. With Sai Flower, Raksha Bandhan flowers bring festive cheer, freshness, and on-time delivery — order now and celebrate the sibling bond.</p>
HTML,
    'meta_title' => 'Raksha Bandhan Flowers Delivery | Sai Flower',
    'meta_description' => 'Send Raksha Bandhan flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Festive bouquets, rakhi & cake combos. Order Rakhi flowers now.',
    'meta_keywords' => 'raksha bandhan flowers, rakhi flowers, rakhi bouquet, flowers for brother, festival flower delivery, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What flowers are best for Raksha Bandhan?', 'answer' => 'Bright, festive blooms like mixed roses, gerberas, and lilies suit the joyful spirit of Rakhi. Sai Flower can tailor a cheerful arrangement for your sibling.'],
        ['question' => 'Can I add rakhi and sweets to the flowers?', 'answer' => 'Yes. Pair the flowers with sweets, a cake, or a gift and we will deliver the complete festive combo together.'],
        ['question' => 'Is same-day delivery available for Rakhi?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Should I book Rakhi flowers in advance?', 'answer' => 'Yes. The festival is a busy period, so we recommend booking early to secure your preferred bouquet and slot.'],
        ['question' => 'Can I send flowers to my sibling in another area?', 'answer' => 'Yes. Enter their address at checkout. We deliver across Delhi NCR, and you can WhatsApp us for other locations.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add a heartfelt message at checkout and we will include it with your Raksha Bandhan flowers.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 60 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Festival Flower Delivery',
    'slug'  => 'festival-flower-delivery',
    'content' => <<<'HTML'
<h2>Brighten Celebrations With Festival Flower Delivery</h2>
<p>Festivals are all about joy, togetherness, and beauty, and festival flower delivery adds the perfect finishing touch. From Diwali and Holi to Raksha Bandhan and New Year, festival flower delivery brings fresh, vibrant blooms to your celebrations. Whether for home décor or gifting loved ones, festival flower delivery spreads happiness. One order of festival flower delivery makes every occasion more colourful.</p>

<p>At <strong>Sai Flower</strong>, every festival flower delivery is prepared with daily-fresh blooms and delivered on time. We design bright, festive arrangements so your celebrations feel fresh, cheerful, and complete.</p>

<h2>Why Choose Our Festival Flower Delivery</h2>
<p>Festivals deserve reliable, beautiful flowers. Our festival flower delivery combines vibrant blooms with dependable service.</p>
<ul>
<li><strong>Bright, festive blooms</strong> — roses, marigolds, gerberas, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Décor and gifting options</strong> — bouquets and combos</li>
<li><strong>Book early</strong> — secure your slot during festival rush</li>
</ul>

<h3>Flowers for Every Festival</h3>
<p>From decorating your home to gifting family and friends, festival flower delivery suits every celebration. Choose cheerful mixed blooms, elegant roses, or traditional arrangements. Add sweets or a cake, and one festival flower delivery completes the festive spirit beautifully.</p>

<h3>Fresh, Festive, and On Time</h3>
<p>Celebrations deserve flowers at their best. Each festival flower delivery is prepared close to dispatch and packed protectively, so your blooms arrive crisp, colourful, and full of life.</p>

<h2>Order Festival Flower Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Festival flower delivery Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Festival gifts Delhi">gift</a>, and book early. With Sai Flower, festival flower delivery brings joy, freshness, and on-time service — order now and make every festival more beautiful.</p>
HTML,
    'meta_title' => 'Festival Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Order festival flower delivery in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh, festive bouquets & combos. Send festival flowers now.',
    'meta_keywords' => 'festival flower delivery, festive flowers, diwali flowers, occasion flower delivery, same day flower delivery, online flower delivery, flower delivery delhi',
    'faqs' => [
        ['question' => 'Which festivals do you deliver flowers for?', 'answer' => 'We deliver flowers for Diwali, Holi, Raksha Bandhan, New Year, and other festivals, offering both décor and gifting arrangements.'],
        ['question' => 'Is same-day festival flower delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Should I book festival flowers in advance?', 'answer' => 'Yes. Festivals are busy periods, so we recommend booking early to secure your preferred blooms and delivery slot.'],
        ['question' => 'Can I order flowers for home décor?', 'answer' => 'Absolutely. We offer bouquets and decorative arrangements for festive home décor. Contact us for bulk or custom orders.'],
        ['question' => 'Can I add sweets or a cake?', 'answer' => 'Yes. Pair the flowers with sweets, a cake, or a gift and we will deliver the complete festive combo together.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add a festive message at checkout and we will include it with your festival flowers.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

];

echo "=== Seeding keyword pages — Batch 6 ===\n";
$created = 0;
foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    }
}
echo "=== Done. {$created} new page(s) created. ===\n";
