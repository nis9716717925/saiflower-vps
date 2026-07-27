<?php
/**
 * Seed: Keyword custom pages — Batch 4 (dynamic_pages)
 * Layout: product_showcase | Tag: sameday
 * Run once: https://saiflower.com/tools/seed_keyword_pages_batch4.php
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

// 31 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Fresh Roses Online',
    'slug'  => 'fresh-roses-online',
    'content' => <<<'HTML'
<h2>Buy Fresh Roses Online in Delhi NCR</h2>
<p>Roses are timeless, and now you can order fresh roses online in just a few clicks. Whether you want deep red roses for romance or soft pastels for a gentle gesture, buying fresh roses online means beauty delivered straight to the doorstep. Skip the crowded market and choose fresh roses online for convenience without compromise.</p>

<p>At <strong>Sai Flower</strong>, every order of fresh roses online is hand-tied with daily-sourced, velvety blooms. We select firm, fragrant stems and arrange them close to dispatch, so your roses arrive rich in colour and beautifully presented.</p>

<h2>Why Order Fresh Roses Online</h2>
<p>Shopping from home saves time while still delivering premium quality. Our fresh roses online service brings the flower market to your screen with a few taps.</p>
<ul>
<li><strong>Daily-fresh roses</strong> — red, pink, white, yellow, and mixed</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Choice of sizes</strong> — from a dozen to 50 or 100 roses</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Roses for Every Occasion</h3>
<p>From anniversaries and proposals to birthdays and apologies, fresh roses online suit every heartfelt moment. Pick a single-colour bunch or a mixed rose design, and add a cake or chocolates so one order of fresh roses online becomes a complete surprise.</p>

<h3>Freshness Guaranteed</h3>
<p>When you buy fresh roses online, freshness is everything. Each arrangement is prepared close to dispatch and packed protectively against heat and humidity, so your roses reach the door crisp, fragrant, and long-lasting.</p>

<h2>Order Fresh Roses Online Today</h2>
<p>Browse our <a href="/flowers" title="Buy fresh roses online Delhi">rose collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out securely. With Sai Flower, ordering fresh roses online is easy, fresh, and reliable — order now and send stunning roses to someone you love.</p>
HTML,
    'meta_title' => 'Fresh Roses Online Delhi NCR | Sai Flower',
    'meta_description' => 'Buy fresh roses online in Delhi NCR with same-day & midnight delivery from Sai Flower. Velvety red & mixed roses. Order fresh roses online today.',
    'meta_keywords' => 'fresh roses online, buy roses online, red rose bouquet, rose bouquet delivery, fresh flower delivery, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'How fresh are roses ordered online?', 'answer' => 'Sai Flower uses daily-sourced, velvety roses arranged close to dispatch and packed protectively, so they arrive firm, fragrant, and long-lasting.'],
        ['question' => 'Can I order fresh roses for same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'What rose colours are available?', 'answer' => 'You can choose red, pink, white, yellow, or mixed roses in various sizes, from a dozen to grand 50 or 100-rose arrangements.'],
        ['question' => 'Can I add a cake or chocolates?', 'answer' => 'Absolutely. Pair your roses with a cake, chocolates, or a gift and we will deliver the complete combo together.'],
        ['question' => 'How do I pay when buying roses online?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Can I schedule the delivery date?', 'answer' => 'Yes. Choose your preferred delivery date at checkout and we will prepare and deliver the roses right on time.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver fresh roses across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 32 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Buy Flowers Online',
    'slug'  => 'buy-flowers-online',
    'content' => <<<'HTML'
<h2>Buy Flowers Online in Delhi NCR With Ease</h2>
<p>Gifting is simplest when you can buy flowers online from the comfort of home. From fresh roses to elegant orchids and cheerful mixed bunches, you can buy flowers online and have them delivered on time to any address. No queues, no hassle — just choose, pay, and let us handle the rest when you buy flowers online.</p>

<p>At <strong>Sai Flower</strong>, when you buy flowers online you get real florist craftsmanship, not just a photo. Every bouquet is hand-arranged with daily-fresh blooms and dispatched with care, so the flowers look just as lovely at the doorstep.</p>

<h2>Why Buy Flowers Online</h2>
<p>Online shopping gives you time, choice, and convenience. Our platform makes it easy to buy flowers online for any occasion, day or night.</p>
<ul>
<li><strong>Shop anytime</strong> — browse and order 24/7</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Secure payments</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Flowers and Gifts in One Order</h3>
<p>When you buy flowers online with us, you can add a cake, chocolates, or a soft teddy for a complete gift. Whether it is a birthday, anniversary, or thank-you, one order covers the whole celebration, making it simple to buy flowers online and delight someone instantly.</p>

<h3>Fresh and Reliable Every Time</h3>
<p>Freshness matters most. Each bouquet is prepared close to dispatch and packed protectively, then handed to trusted riders, so your flowers arrive crisp, fragrant, and right on schedule.</p>

<h2>Buy Flowers Online Today</h2>
<p>Explore our <a href="/flowers" title="Buy flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, you can buy flowers online with total confidence — order now and send a fresh, heartfelt surprise.</p>
HTML,
    'meta_title' => 'Buy Flowers Online Delhi NCR | Sai Flower',
    'meta_description' => 'Buy flowers online in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, hand-tied bouquets & gifts. Order flowers online today.',
    'meta_keywords' => 'buy flowers online, order flowers online, send flowers online, online flower delivery, flower shop online, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'How do I buy flowers online?', 'answer' => 'Browse the bouquets on this page, add your favourite to the cart, enter the recipient details and date, then pay securely via UPI, card, or wallet.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet is hand-arranged with daily-fresh blooms close to dispatch and packed protectively for freshness.'],
        ['question' => 'Can I add gifts to my flower order?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy and we will deliver the complete combo together where possible.'],
        ['question' => 'Which payment methods are accepted?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Can I schedule delivery for later?', 'answer' => 'Absolutely. Choose your preferred delivery date at checkout and we will deliver right on time.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 33 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Order Flowers Online',
    'slug'  => 'order-flowers-online',
    'content' => <<<'HTML'
<h2>Order Flowers Online in Delhi NCR</h2>
<p>Sending a heartfelt gift is easy when you order flowers online. From romantic roses to cheerful mixed bunches, you can order flowers online and have them delivered fresh to any doorstep. Whether it is a birthday, anniversary, or a spontaneous surprise, when you order flowers online the whole process takes just minutes.</p>

<p>At <strong>Sai Flower</strong>, every time you order flowers online you get expert florist craft and dependable delivery. We hand-arrange each bouquet with daily-fresh blooms and dispatch it carefully, so your gift looks exactly as promised.</p>

<h2>Why Order Flowers Online With Us</h2>
<p>Convenience meets quality on our platform. It is simple to order flowers online for any occasion, at any hour.</p>
<ul>
<li><strong>Quick and easy</strong> — order in just a few taps</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>The Complete Gifting Experience</h3>
<p>When you order flowers online with us, you can add a cake, chocolates, or a teddy in the same order. That means one simple checkout covers the whole celebration, making it effortless to order flowers online and surprise someone you love.</p>

<h3>Fresh and On Time, Always</h3>
<p>Reliability is our promise. Each bouquet is prepared close to dispatch and packed protectively, then delivered by trusted riders, so your flowers arrive crisp, fragrant, and right on schedule.</p>

<h2>Order Flowers Online Today</h2>
<p>Explore our <a href="/flowers" title="Order flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, you can order flowers online with complete confidence — order now and send a fresh, beautiful surprise.</p>
HTML,
    'meta_title' => 'Order Flowers Online Delhi NCR | Sai Flower',
    'meta_description' => 'Order flowers online in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, hand-tied bouquets & gift combos. Order flowers online now.',
    'meta_keywords' => 'order flowers online, buy flowers online, send flowers online, online flower delivery, flower shop online, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'How do I order flowers online?', 'answer' => 'Choose a bouquet on this page, add it to the cart, enter the recipient address and date, then pay securely via UPI, card, or wallet.'],
        ['question' => 'Can I order flowers for same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Are the flowers fresh when delivered?', 'answer' => 'Absolutely. Every bouquet is hand-arranged with daily-fresh blooms close to dispatch and packed protectively for freshness.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy and we will deliver the complete combo together where possible.'],
        ['question' => 'Can I include a personal message?', 'answer' => 'Yes. Add your message at checkout and we will include it with your flower order.'],
        ['question' => 'What payment methods can I use?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 34 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Send Flowers Online',
    'slug'  => 'send-flowers-online',
    'content' => <<<'HTML'
<h2>Send Flowers Online to Delhi NCR</h2>
<p>Distance should never stop you from showing you care. When you send flowers online, you can brighten someone's day no matter where you are. From fresh roses to elegant lilies, you can send flowers online and have them delivered to any doorstep in Delhi NCR. It is the easiest way to send flowers online for any occasion.</p>

<p>At <strong>Sai Flower</strong>, every time you send flowers online you get real florist quality and reliable delivery. We hand-arrange each bouquet with daily-fresh blooms and dispatch it carefully, so your surprise arrives fresh and beautiful.</p>

<h2>Why Send Flowers Online With Us</h2>
<p>Our platform makes long-distance and last-minute gifting effortless. It is simple to send flowers online for birthdays, anniversaries, or just because.</p>
<ul>
<li><strong>Effortless gifting</strong> — order from anywhere, anytime</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>One Order, Complete Surprise</h3>
<p>When you send flowers online with us, you can add a cake, chocolates, or a teddy to make the gift extra special. Whether near or far, you can send flowers online and let one order handle the entire celebration.</p>

<h3>Fresh and On Time, Every Time</h3>
<p>We treat every order like a personal gift. Each bouquet is prepared close to dispatch and packed protectively, then delivered by trusted riders, so your flowers arrive crisp, fragrant, and right on schedule.</p>

<h2>Send Flowers Online Today</h2>
<p>Explore our <a href="/flowers" title="Send flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, you can send flowers online with complete confidence — order now and make someone smile today.</p>
HTML,
    'meta_title' => 'Send Flowers Online Delhi NCR | Sai Flower',
    'meta_description' => 'Send flowers online in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, hand-tied bouquets & gifts. Send flowers online today.',
    'meta_keywords' => 'send flowers online, order flowers online, buy flowers online, online flower delivery, send bouquet online, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'How do I send flowers online?', 'answer' => 'Pick a bouquet on this page, add the recipient address and delivery date, then pay securely. Sai Flower prepares and delivers it for you.'],
        ['question' => 'Can I send flowers online for same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Can I send flowers to someone else\'s address?', 'answer' => 'Absolutely. Enter the recipient\'s address and contact details at checkout and we will deliver directly to them.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet is hand-arranged with daily-fresh blooms close to dispatch and packed protectively for freshness.'],
        ['question' => 'Can I add a gift and a message?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy, and include a personal message at checkout.'],
        ['question' => 'What payment methods are accepted?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 35 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Send Bouquet Online',
    'slug'  => 'send-bouquet-online',
    'content' => <<<'HTML'
<h2>Send Bouquet Online in Delhi NCR</h2>
<p>A hand-tied bouquet is a timeless gift, and now you can send bouquet online in just a few clicks. From romantic roses to cheerful mixed blooms, you can send bouquet online and have it delivered fresh to any doorstep. Whatever the occasion, when you send bouquet online the surprise reaches them right on time.</p>

<p>At <strong>Sai Flower</strong>, every time you send bouquet online you get expert florist craft and reliable delivery. We hand-arrange each bouquet with daily-fresh blooms and dispatch it carefully, so it looks polished from the first glance.</p>

<h2>Why Send Bouquet Online With Us</h2>
<p>Our platform makes bouquet gifting quick and stress-free for any occasion, any day.</p>
<ul>
<li><strong>Easy ordering</strong> — send a bouquet in a few taps</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Complete the Gift</h3>
<p>When you send bouquet online with us, you can add a cake, chocolates, or a soft teddy in the same order. For birthdays, anniversaries, or a heartfelt thank-you, you can send bouquet online and let one order cover the whole celebration.</p>

<h3>Fresh and On Time, Always</h3>
<p>Every bouquet is prepared close to dispatch and packed protectively, then delivered by trusted riders, so your flowers arrive crisp, fragrant, and right on schedule.</p>

<h2>Send Bouquet Online Today</h2>
<p>Explore our <a href="/flowers" title="Send bouquet online Delhi">bouquet collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, you can send bouquet online with complete confidence — order now and delight someone special.</p>
HTML,
    'meta_title' => 'Send Bouquet Online Delhi NCR | Sai Flower',
    'meta_description' => 'Send bouquet online in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, hand-tied bouquets & gift combos. Send a bouquet online now.',
    'meta_keywords' => 'send bouquet online, bouquet delivery, send flowers online, order flowers online, online flower delivery, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'How do I send a bouquet online?', 'answer' => 'Choose a bouquet on this page, enter the recipient address and delivery date, then pay securely. Sai Flower prepares and delivers it for you.'],
        ['question' => 'Is same-day bouquet delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Are bouquets hand-tied by florists?', 'answer' => 'Yes. Every bouquet is hand-tied by our expert florists using daily-fresh stems, so it looks polished and matches the photo closely.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a teddy and we will deliver the complete combo together where possible.'],
        ['question' => 'Can I include a personal message?', 'answer' => 'Yes. Add your message at checkout and we will include it with the bouquet.'],
        ['question' => 'What payment methods are accepted?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 36 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Delivery Delhi',
    'slug'  => 'flower-delivery-delhi',
    'content' => <<<'HTML'
<h2>Trusted Flower Delivery Delhi Service</h2>
<p>Looking for reliable flower delivery Delhi residents can count on? We bring fresh, hand-tied bouquets to homes and offices across the capital. Whether it is a birthday, anniversary, or heartfelt surprise, our flower delivery Delhi service makes gifting simple and dependable. For fast, fresh blooms, flower delivery Delhi has never been easier.</p>

<p>At <strong>Sai Flower</strong>, every flower delivery Delhi order is prepared with daily-fresh stems and dispatched by trusted riders. We arrange each bouquet close to dispatch, so it arrives crisp, colourful, and right on time across the city.</p>

<h2>Why Choose Our Flower Delivery Delhi</h2>
<p>A great local florist blends freshness, variety, and punctuality. That is exactly what our flower delivery Delhi service delivers, every single time.</p>
<ul>
<li><strong>Same-day and midnight slots</strong> — across Delhi and NCR</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Wide coverage</strong> — homes, offices, and hospitals</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From romantic roses to cheerful gerberas and elegant orchids, our flower delivery Delhi covers birthdays, weddings, condolences, and celebrations alike. Add a cake or gift, and one flower delivery Delhi order handles the entire surprise beautifully.</p>

<h3>Fresh and On Time Across the City</h3>
<p>Punctuality matters in a busy city. Every flower delivery Delhi order is packed protectively and routed efficiently, so your bouquet reaches the doorstep fresh, fragrant, and exactly when expected.</p>

<h2>Book Flower Delivery Delhi Online</h2>
<p>Browse our <a href="/flowers" title="Flower delivery Delhi online">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, flower delivery Delhi is fresh, reliable, and on time — order now and send a beautiful surprise across the capital.</p>
HTML,
    'meta_title' => 'Flower Delivery Delhi | Sai Flower',
    'meta_description' => 'Reliable flower delivery Delhi with same-day & midnight slots from Sai Flower. Fresh, hand-tied bouquets to homes & offices. Order flower delivery Delhi now.',
    'meta_keywords' => 'flower delivery delhi, florist in delhi, delhi flower shop, flower bouquet delhi, same day flower delivery, online flower delivery, send flowers delhi',
    'faqs' => [
        ['question' => 'Do you offer same-day flower delivery in Delhi?', 'answer' => 'Yes. Order before the daily cut-off for same-day flower delivery across Delhi and NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Which areas in Delhi do you cover?', 'answer' => 'We deliver across Delhi and nearby NCR areas including Gurgaon and Noida. Enter your pin code at checkout to confirm coverage.'],
        ['question' => 'Can you deliver flowers to a Delhi office?', 'answer' => 'Yes. Add the company name, floor, and reception details in the notes and our rider will deliver smoothly.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet is arranged with daily-fresh stems close to dispatch and packed protectively, so it arrives crisp and fragrant.'],
        ['question' => 'Do you provide midnight flower delivery in Delhi?', 'answer' => 'Yes, in selected pin codes. Add your preferred time in the notes or confirm on WhatsApp before ordering.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a gift and we will deliver the complete combo together.'],
        ['question' => 'How do I pay?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
    ],
],

// 37 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Florist in Delhi',
    'slug'  => 'florist-in-delhi',
    'content' => <<<'HTML'
<h2>Your Trusted Florist in Delhi</h2>
<p>Finding a reliable florist in Delhi makes all the difference when you want fresh, beautiful flowers. As a dedicated florist in Delhi, we combine daily-fresh blooms, expert arrangement, and prompt delivery for every occasion. Whether it is romance, celebration, or comfort, a dependable florist in Delhi turns your feelings into a stunning gift.</p>

<p>At <strong>Sai Flower</strong>, being a trusted florist in Delhi means never compromising on quality. We hand-tie every bouquet with daily-fresh stems and deliver on time, so what you order is exactly what arrives at the doorstep.</p>

<h2>Why Choose Us as Your Florist in Delhi</h2>
<p>A great florist in Delhi offers freshness, variety, and reliability in equal measure. That is our promise on every order.</p>
<ul>
<li><strong>Daily-fresh flowers</strong> — roses, lilies, orchids, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Expert florists</strong> — hand-tied, photo-perfect arrangements</li>
<li><strong>Easy online ordering</strong> — secure UPI, card, and wallet payments</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From birthdays and anniversaries to weddings and condolences, a skilled florist in Delhi has the right bloom ready. Choose romantic roses, elegant orchids, or cheerful gerberas, and add cakes or gifts so your florist in Delhi covers the whole celebration.</p>

<h3>Local Convenience, Premium Quality</h3>
<p>You want a florist who is close, quick, and trustworthy. As your florist in Delhi, we blend neighbourhood convenience with premium quality and reliable riders, so your bouquet always arrives fresh and on schedule.</p>

<h2>Order From Your Florist in Delhi</h2>
<p>Explore our <a href="/flowers" title="Florist in Delhi online">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. When you want a dependable florist in Delhi, choose Sai Flower — order now and send a beautiful surprise.</p>
HTML,
    'meta_title' => 'Florist in Delhi | Sai Flower',
    'meta_description' => 'Sai Flower is your trusted florist in Delhi with same-day & midnight delivery. Fresh, hand-tied bouquets for every occasion. Order from a Delhi florist now.',
    'meta_keywords' => 'florist in delhi, delhi florist, flower delivery delhi, delhi flower shop, best florist near me, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What makes Sai Flower a trusted florist in Delhi?', 'answer' => 'We combine daily-fresh flowers, expert hand-tied arrangements, and reliable same-day delivery across Delhi NCR, with a focus on quality and punctuality.'],
        ['question' => 'Do you offer same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available.'],
        ['question' => 'What types of flowers do you offer?', 'answer' => 'Our collection includes roses, lilies, orchids, carnations, gerberas, and mixed seasonal bouquets, plus cakes and gifts.'],
        ['question' => 'Can you deliver to offices and hospitals?', 'answer' => 'Yes. Add the venue name, floor or ward, and contact details in the notes and our rider will deliver smoothly.'],
        ['question' => 'Do you offer flowers for all budgets?', 'answer' => 'Absolutely. From affordable everyday bunches to premium and luxury arrangements, there is an option for every budget.'],
        ['question' => 'How do I contact the florist?', 'answer' => 'You can reach our team on WhatsApp for recommendations, custom orders, and delivery confirmations at any time.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 38 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Delhi Flower Shop',
    'slug'  => 'delhi-flower-shop',
    'content' => <<<'HTML'
<h2>Fresh Blooms From Your Delhi Flower Shop</h2>
<p>When you want quality flowers without the hassle, a dependable Delhi flower shop is exactly what you need. Our Delhi flower shop brings fresh roses, lilies, orchids, and mixed bouquets to your screen, ready to order and deliver. For every occasion, a trusted Delhi flower shop makes gifting simple, fast, and beautiful.</p>

<p>At <strong>Sai Flower</strong>, our Delhi flower shop blends the ease of online ordering with real florist craftsmanship. Every bouquet is hand-arranged with daily-fresh stems and delivered on time, so it looks just as lovely at the doorstep.</p>

<h2>Why Shop at Our Delhi Flower Shop</h2>
<p>A great flower shop offers variety, freshness, and dependable service. Our Delhi flower shop delivers all three for every order.</p>
<ul>
<li><strong>Wide selection</strong> — bouquets, arrangements, and combos</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, florist-made blooms</strong> — no compromise on quality</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Flowers and Gifts in One Place</h3>
<p>A complete Delhi flower shop offers more than bouquets. Add a designer cake, chocolates, or a soft teddy to your order and cover the whole celebration in one go. Our Delhi flower shop makes combo gifting simple for birthdays, anniversaries, and surprises.</p>

<h3>Reliable Delivery, Every Time</h3>
<p>Ordering from a Delhi flower shop should feel dependable. We prepare each bouquet close to dispatch, pack it protectively, and hand it to trusted riders, so your flowers arrive fresh and right on schedule.</p>

<h2>Order From Our Delhi Flower Shop</h2>
<p>Explore our <a href="/flowers" title="Delhi flower shop online">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. For a fresh, reliable Delhi flower shop, choose Sai Flower — order now and send a beautiful bouquet.</p>
HTML,
    'meta_title' => 'Delhi Flower Shop | Sai Flower',
    'meta_description' => 'Shop our Delhi flower shop for fresh, hand-tied bouquets with same-day & midnight delivery from Sai Flower. Order flowers & gifts online in Delhi now.',
    'meta_keywords' => 'delhi flower shop, flower shop in delhi, florist in delhi, flower delivery delhi, flower shop online, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What can I buy from your Delhi flower shop?', 'answer' => 'You can shop fresh bouquets, arrangements, and combos with cakes, chocolates, and teddies, all available online for delivery across Delhi NCR.'],
        ['question' => 'Does your flower shop offer same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet from our Delhi flower shop is hand-arranged with daily-fresh stems close to dispatch and packed protectively.'],
        ['question' => 'How do I pay?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Can I add a gift to my flower order?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy and we will deliver the complete combo together.'],
        ['question' => 'Can I schedule delivery for later?', 'answer' => 'Absolutely. Choose your preferred delivery date at checkout and we will deliver right on time.'],
        ['question' => 'Which areas does your flower shop serve?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 39 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Best Flower Shop Delhi',
    'slug'  => 'best-flower-shop-delhi',
    'content' => <<<'HTML'
<h2>The Best Flower Shop Delhi Trusts</h2>
<p>When only the finest will do, you want the best flower shop Delhi has to offer. Combining daily-fresh blooms, expert arrangement, and fast delivery, we have earned a name as the best flower shop Delhi families rely on. For every celebration, the best flower shop Delhi makes gifting effortless, fresh, and memorable.</p>

<p>At <strong>Sai Flower</strong>, becoming the best flower shop Delhi is about consistency. We hand-tie every bouquet with daily-fresh stems and deliver on time, so what you order online is exactly what arrives at the doorstep.</p>

<h2>What Makes Us the Best Flower Shop Delhi</h2>
<p>A top flower shop blends quality, variety, and dependability. That is why customers rank us among the best flower shop Delhi options.</p>
<ul>
<li><strong>Daily-fresh flowers</strong> — roses, lilies, orchids, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Expert florists</strong> — hand-tied, photo-perfect arrangements</li>
<li><strong>Easy online ordering</strong> — secure UPI, card, and wallet payments</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From birthdays and anniversaries to weddings and condolences, the best flower shop Delhi has the right bloom ready. Choose romantic roses, elegant orchids, or cheerful gerberas, and add cakes or gifts so the best flower shop Delhi covers the whole celebration.</p>

<h3>Premium Quality, Reliable Service</h3>
<p>Being the best means reliability too. Every order is prepared close to dispatch, packed protectively, and delivered by trusted riders, so your bouquet always arrives fresh, fragrant, and on schedule.</p>

<h2>Order From the Best Flower Shop Delhi</h2>
<p>Explore our <a href="/flowers" title="Best flower shop Delhi online">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. For the best flower shop Delhi can offer, choose Sai Flower — order now and send a beautiful surprise.</p>
HTML,
    'meta_title' => 'Best Flower Shop Delhi | Sai Flower',
    'meta_description' => 'Sai Flower is the best flower shop Delhi trusts for fresh, hand-tied bouquets with same-day & midnight delivery. Order from the best flower shop in Delhi now.',
    'meta_keywords' => 'best flower shop delhi, best florist delhi, delhi flower shop, florist in delhi, flower delivery delhi, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What makes Sai Flower the best flower shop in Delhi?', 'answer' => 'We combine daily-fresh flowers, expert hand-tied arrangements, and reliable same-day delivery, with consistent quality and punctuality that customers trust.'],
        ['question' => 'Do you offer same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'What flowers do you offer?', 'answer' => 'Our collection includes roses, lilies, orchids, carnations, gerberas, and mixed seasonal bouquets, plus cakes and gifts.'],
        ['question' => 'Do you cater to all budgets?', 'answer' => 'Absolutely. From affordable everyday bunches to premium and luxury arrangements, there is an option for every budget.'],
        ['question' => 'Can you deliver to offices and hospitals?', 'answer' => 'Yes. Add the venue name, floor or ward, and contact details in the notes and our rider will deliver smoothly.'],
        ['question' => 'How do I place an order?', 'answer' => 'Browse the bouquets, add to cart, enter delivery details, and pay securely via UPI, card, or wallet.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 40 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Bouquet Delhi',
    'slug'  => 'flower-bouquet-delhi',
    'content' => <<<'HTML'
<h2>Beautiful Flower Bouquet Delhi Delivery</h2>
<p>A hand-tied flower bouquet Delhi residents love is the perfect way to celebrate any moment. From romantic roses to cheerful mixed blooms, every flower bouquet Delhi order arrives fresh, elegantly wrapped, and ready to impress. Whatever the occasion, a beautiful flower bouquet Delhi delivery turns your feelings into a memorable gift.</p>

<p>At <strong>Sai Flower</strong>, each flower bouquet Delhi order is crafted by expert florists using daily-fresh stems. We arrange, wrap, and dispatch with care, so your flower bouquet looks polished from the very first glance.</p>

<h2>Why Choose Our Flower Bouquet Delhi</h2>
<p>A great bouquet is about freshness, design, and punctuality. Our flower bouquet Delhi service takes care of all three, every time.</p>
<ul>
<li><strong>Hand-tied by florists</strong> — photo-perfect arrangements</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Fresh, daily-sourced blooms</strong> — roses, lilies, and orchids</li>
<li><strong>Secure, easy checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Bouquets for Every Occasion</h3>
<p>From birthdays and anniversaries to congratulations and get-well wishes, a flower bouquet Delhi delivery fits every moment. Choose a classic rose bunch, a vibrant mixed design, or elegant lilies, and add a cake or gift so one flower bouquet Delhi order completes the celebration.</p>

<h3>Fresh and On Time Across the City</h3>
<p>Timing and freshness make a bouquet special. Every flower bouquet Delhi order is prepared close to dispatch, packed protectively, and routed through reliable riders, so it arrives crisp, fragrant, and right on schedule.</p>

<h2>Order Flower Bouquet Delhi Online</h2>
<p>Browse our <a href="/flowers" title="Flower bouquet Delhi online">bouquet collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, a flower bouquet Delhi delivery is fresh, elegant, and reliable — order now and send a stunning bouquet across the capital.</p>
HTML,
    'meta_title' => 'Flower Bouquet Delhi Delivery | Sai Flower',
    'meta_description' => 'Order a fresh flower bouquet Delhi delivery with same-day & midnight slots from Sai Flower. Hand-tied roses & mixed blooms. Send a flower bouquet in Delhi now.',
    'meta_keywords' => 'flower bouquet delhi, bouquet delivery delhi, flower delivery delhi, delhi flower shop, rose bouquet delivery, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'Do you offer same-day flower bouquet delivery in Delhi?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Are bouquets hand-tied by florists?', 'answer' => 'Yes. Every flower bouquet is hand-tied by our expert florists using daily-fresh stems, so it looks polished and matches the photo closely.'],
        ['question' => 'What types of bouquets can I order?', 'answer' => 'Choose from rose bouquets, mixed seasonal blooms, lilies, orchids, gerberas, and premium arrangements for every occasion and budget.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a teddy and we will deliver the complete combo together.'],
        ['question' => 'How fresh will the bouquet be?', 'answer' => 'Each bouquet is arranged close to dispatch and packed protectively, so it arrives crisp, fragrant, and long-lasting.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your flower bouquet.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver flower bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

];

echo "=== Seeding keyword pages — Batch 4 ===\n";
$created = 0;
foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    }
}
echo "=== Done. {$created} new page(s) created. ===\n";
