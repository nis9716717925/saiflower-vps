<?php
/**
 * Seed: Keyword custom pages — Batch 7 (dynamic_pages)
 * Greater Kailash / GK location cluster
 * Layout: product_showcase | Tag: sameday
 * Run once: https://saiflower.com/tools/seed_keyword_pages_batch7.php
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

// 61 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Delivery Greater Kailash',
    'slug'  => 'flower-delivery-greater-kailash',
    'content' => <<<'HTML'
<h2>Fresh Flower Delivery Greater Kailash Residents Trust</h2>
<p>Looking for dependable flower delivery Greater Kailash locals can rely on? We bring fresh, hand-tied bouquets straight to homes and offices across GK-1, GK-2, and the M-Block market area. Whether it is a birthday, anniversary, or a sudden surprise, our flower delivery Greater Kailash service makes gifting simple, fast, and beautiful right here in South Delhi.</p>

<p>At <strong>Sai Flower</strong>, every flower delivery Greater Kailash order is prepared with daily-fresh stems and dispatched by local riders who know the neighbourhood. We arrange each bouquet close to dispatch, so it arrives crisp, colourful, and right on time.</p>

<h2>Why Choose Our Flower Delivery Greater Kailash</h2>
<p>Being local means being fast and reliable. Our flower delivery Greater Kailash service blends neighbourhood convenience with premium florist quality.</p>
<ul>
<li><strong>Same-day and midnight slots</strong> — across GK and South Delhi</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Quick local riders</strong> — familiar with GK-1 and GK-2 lanes</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From romantic roses to cheerful gerberas and elegant orchids, our flower delivery Greater Kailash covers birthdays, weddings, and condolences alike. Add a cake or gift, and one flower delivery Greater Kailash order handles the whole surprise beautifully.</p>

<h3>Fresh and On Time in Your Neighbourhood</h3>
<p>Local delivery should be quick. Every bouquet is packed protectively and routed efficiently through GK, so your flowers reach the doorstep fresh, fragrant, and exactly when expected.</p>

<h2>Order Flower Delivery Greater Kailash Online</h2>
<p>Browse our <a href="/flowers" title="Flower delivery Greater Kailash">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, flower delivery Greater Kailash is fresh, local, and reliable — order now and send a beautiful surprise across GK.</p>
HTML,
    'meta_title' => 'Flower Delivery Greater Kailash | Sai Flower',
    'meta_description' => 'Reliable flower delivery Greater Kailash with same-day & midnight slots from Sai Flower. Fresh bouquets to GK-1, GK-2 & South Delhi. Order flowers in GK now.',
    'meta_keywords' => 'flower delivery greater kailash, flower delivery gk, florist in greater kailash, flower shop greater kailash, same day flower delivery, online flower delivery, gk flower delivery',
    'faqs' => [
        ['question' => 'Do you offer same-day flower delivery in Greater Kailash?', 'answer' => 'Yes. Order before the daily cut-off for same-day flower delivery across GK-1, GK-2, and nearby South Delhi, with midnight slots also available.'],
        ['question' => 'Which parts of Greater Kailash do you cover?', 'answer' => 'We deliver across GK-1, GK-2, the M-Block and N-Block markets, and surrounding South Delhi areas. Confirm your pin code at checkout.'],
        ['question' => 'Can you deliver to a GK office?', 'answer' => 'Yes. Add the building name, floor, and reception details in the notes and our local rider will deliver smoothly.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet is arranged with daily-fresh stems close to dispatch and packed protectively, so it arrives crisp and fragrant.'],
        ['question' => 'Do you provide midnight delivery in GK?', 'answer' => 'Yes, in selected GK pin codes. Add your preferred time in the notes or confirm on WhatsApp before ordering.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a gift and we will deliver the complete combo together.'],
        ['question' => 'How do I pay?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
    ],
],

// 62 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Delivery GK',
    'slug'  => 'flower-delivery-gk',
    'content' => <<<'HTML'
<h2>Quick and Fresh Flower Delivery GK</h2>
<p>Need reliable flower delivery GK residents can count on? We bring fresh, hand-tied bouquets to homes and offices across Greater Kailash in minutes of planning. Whether it is a birthday, anniversary, or spontaneous surprise, our flower delivery GK service makes gifting effortless. For fast, fresh blooms in South Delhi, flower delivery GK has never been simpler.</p>

<p>At <strong>Sai Flower</strong>, every flower delivery GK order is prepared with daily-fresh stems and handled by local riders who know GK-1 and GK-2 well. We arrange each bouquet close to dispatch, so it arrives crisp, colourful, and right on time.</p>

<h2>Why Choose Our Flower Delivery GK</h2>
<p>Local service means speed and reliability. Our flower delivery GK blends neighbourhood convenience with premium florist quality.</p>
<ul>
<li><strong>Same-day and midnight slots</strong> — across GK and South Delhi</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Fast local riders</strong> — familiar with GK lanes and markets</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From romantic roses to cheerful gerberas and elegant orchids, our flower delivery GK covers birthdays, weddings, and condolences alike. Add a cake or gift, and one flower delivery GK order handles the entire surprise beautifully.</p>

<h3>Fresh and On Time Locally</h3>
<p>Local delivery should be quick and dependable. Every bouquet is packed protectively and routed efficiently through GK, so your flowers arrive fresh, fragrant, and exactly when expected.</p>

<h2>Order Flower Delivery GK Online</h2>
<p>Browse our <a href="/flowers" title="Flower delivery GK">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, flower delivery GK is fresh, local, and reliable — order now and send a beautiful surprise across Greater Kailash.</p>
HTML,
    'meta_title' => 'Flower Delivery GK Greater Kailash | Sai Flower',
    'meta_description' => 'Fast flower delivery GK with same-day & midnight slots from Sai Flower. Fresh bouquets to GK-1, GK-2 & South Delhi. Order flower delivery GK online now.',
    'meta_keywords' => 'flower delivery gk, flower delivery greater kailash, florist in gk, flower shop greater kailash, same day flower delivery, online flower delivery, gk florist',
    'faqs' => [
        ['question' => 'Do you offer same-day flower delivery in GK?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across GK-1, GK-2, and nearby South Delhi, with midnight slots also available.'],
        ['question' => 'Which areas of GK do you cover?', 'answer' => 'We deliver across GK-1, GK-2, the M-Block and N-Block markets, and surrounding South Delhi. Confirm your pin code at checkout.'],
        ['question' => 'Can you deliver to a GK office or apartment?', 'answer' => 'Yes. Add the building name, floor, and contact details in the notes and our local rider will deliver smoothly.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet is arranged with daily-fresh stems close to dispatch and packed protectively for freshness.'],
        ['question' => 'Is midnight delivery available in GK?', 'answer' => 'Yes, in selected GK pin codes. Add your preferred time in the notes or confirm on WhatsApp.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a gift and we will deliver the complete combo together.'],
        ['question' => 'How do I pay?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
    ],
],

// 63 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Florist in Greater Kailash',
    'slug'  => 'florist-in-greater-kailash',
    'content' => <<<'HTML'
<h2>Your Trusted Florist in Greater Kailash</h2>
<p>Finding a reliable florist in Greater Kailash makes gifting fresh flowers effortless. As a dedicated florist in Greater Kailash, we combine daily-fresh blooms, expert arrangement, and quick local delivery across GK-1, GK-2, and South Delhi. Whether for romance, celebration, or comfort, a trusted florist in Greater Kailash turns your feelings into a stunning gift.</p>

<p>At <strong>Sai Flower</strong>, being a florist in Greater Kailash means never compromising on quality. We hand-tie every bouquet with daily-fresh stems and deliver on time, so what you order is exactly what arrives at the doorstep.</p>

<h2>Why Choose Us as Your Florist in Greater Kailash</h2>
<p>A great local florist offers freshness, variety, and reliability. That is our promise on every GK order.</p>
<ul>
<li><strong>Daily-fresh flowers</strong> — roses, lilies, orchids, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across GK and South Delhi</li>
<li><strong>Expert florists</strong> — hand-tied, photo-perfect arrangements</li>
<li><strong>Local riders</strong> — quick delivery within Greater Kailash</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From birthdays and anniversaries to weddings and condolences, a skilled florist in Greater Kailash has the right bloom ready. Choose romantic roses, elegant orchids, or cheerful gerberas, and add cakes or gifts so your florist in Greater Kailash covers the whole celebration.</p>

<h3>Local Convenience, Premium Quality</h3>
<p>You want a florist who is close, quick, and trustworthy. As your florist in Greater Kailash, we blend neighbourhood convenience with premium quality, so your bouquet always arrives fresh and on schedule.</p>

<h2>Order From Your Florist in Greater Kailash</h2>
<p>Explore our <a href="/flowers" title="Florist in Greater Kailash">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. When you want a dependable florist in Greater Kailash, choose Sai Flower — order now and send a beautiful surprise.</p>
HTML,
    'meta_title' => 'Florist in Greater Kailash | Sai Flower',
    'meta_description' => 'Sai Flower is your trusted florist in Greater Kailash with same-day & midnight delivery. Fresh bouquets to GK-1, GK-2 & South Delhi. Order from a GK florist now.',
    'meta_keywords' => 'florist in greater kailash, florist in gk, flower delivery greater kailash, flower shop greater kailash, best florist in greater kailash, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What makes Sai Flower a trusted florist in Greater Kailash?', 'answer' => 'We combine daily-fresh flowers, expert hand-tied arrangements, and quick local delivery across GK-1, GK-2, and South Delhi, with a focus on quality and punctuality.'],
        ['question' => 'Do you offer same-day delivery in GK?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available across Greater Kailash.'],
        ['question' => 'What types of flowers do you offer?', 'answer' => 'Our collection includes roses, lilies, orchids, carnations, gerberas, and mixed seasonal bouquets, plus cakes and gifts.'],
        ['question' => 'Can you deliver to GK offices and apartments?', 'answer' => 'Yes. Add the building name, floor, and contact details in the notes and our local rider will deliver smoothly.'],
        ['question' => 'Do you offer flowers for all budgets?', 'answer' => 'Absolutely. From affordable everyday bunches to premium and luxury arrangements, there is an option for every budget.'],
        ['question' => 'How do I contact the florist?', 'answer' => 'You can reach our team on WhatsApp for recommendations, custom orders, and delivery confirmations at any time.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Greater Kailash, South Delhi, and the wider Delhi NCR. Confirm coverage at checkout.'],
    ],
],

// 64 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Florist in GK',
    'slug'  => 'florist-in-gk',
    'content' => <<<'HTML'
<h2>Your Reliable Florist in GK</h2>
<p>When you need fresh flowers fast, a dependable florist in GK makes all the difference. As a dedicated florist in GK, we bring daily-fresh blooms, expert arrangement, and quick local delivery to GK-1, GK-2, and South Delhi. Whether for romance, celebration, or comfort, a trusted florist in GK turns your feelings into a beautiful gift.</p>

<p>At <strong>Sai Flower</strong>, being a florist in GK means quality you can count on. We hand-tie every bouquet with daily-fresh stems and deliver on time, so what you order online is exactly what arrives at the doorstep.</p>

<h2>Why Choose Us as Your Florist in GK</h2>
<p>A great local florist blends freshness, variety, and reliability. That is our promise on every order.</p>
<ul>
<li><strong>Daily-fresh flowers</strong> — roses, lilies, orchids, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across GK and South Delhi</li>
<li><strong>Expert florists</strong> — hand-tied, photo-perfect arrangements</li>
<li><strong>Local riders</strong> — fast delivery within Greater Kailash</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From birthdays and anniversaries to weddings and condolences, a skilled florist in GK has the right bloom ready. Choose romantic roses, elegant orchids, or cheerful gerberas, and add cakes or gifts so your florist in GK covers the whole celebration.</p>

<h3>Local Convenience, Premium Quality</h3>
<p>You want a florist who is close, quick, and trustworthy. As your florist in GK, we combine neighbourhood convenience with premium quality, so your bouquet always arrives fresh and on schedule.</p>

<h2>Order From Your Florist in GK</h2>
<p>Explore our <a href="/flowers" title="Florist in GK">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. When you want a dependable florist in GK, choose Sai Flower — order now and send a beautiful surprise across Greater Kailash.</p>
HTML,
    'meta_title' => 'Florist in GK Greater Kailash | Sai Flower',
    'meta_description' => 'Sai Flower is your reliable florist in GK with same-day & midnight delivery. Fresh bouquets to GK-1, GK-2 & South Delhi. Order from a GK florist online now.',
    'meta_keywords' => 'florist in gk, florist in greater kailash, flower delivery gk, flower shop greater kailash, best florist in greater kailash, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What makes Sai Flower a trusted florist in GK?', 'answer' => 'We combine daily-fresh flowers, expert hand-tied arrangements, and quick local delivery across GK-1, GK-2, and South Delhi, focusing on quality and punctuality.'],
        ['question' => 'Do you offer same-day delivery in GK?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available across Greater Kailash.'],
        ['question' => 'What flowers do you offer?', 'answer' => 'Our collection includes roses, lilies, orchids, carnations, gerberas, and mixed seasonal bouquets, plus cakes and gifts.'],
        ['question' => 'Can you deliver to GK offices and homes?', 'answer' => 'Yes. Add the building name, floor, and contact details in the notes and our local rider will deliver smoothly.'],
        ['question' => 'Do you cater to all budgets?', 'answer' => 'Absolutely. From affordable everyday bunches to premium and luxury arrangements, there is an option for every budget.'],
        ['question' => 'How do I contact the florist?', 'answer' => 'You can reach our team on WhatsApp for recommendations, custom orders, and delivery confirmations at any time.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across GK, South Delhi, and the wider Delhi NCR. Confirm coverage at checkout.'],
    ],
],

// 65 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Online Flower Delivery Greater Kailash',
    'slug'  => 'online-flower-delivery-greater-kailash',
    'content' => <<<'HTML'
<h2>Easy Online Flower Delivery Greater Kailash</h2>
<p>Gifting flowers is simple with online flower delivery Greater Kailash residents love. Browse fresh bouquets from home and have them delivered to any address across GK-1, GK-2, and South Delhi. Whether it is a birthday, anniversary, or surprise, online flower delivery Greater Kailash makes the whole process quick and convenient, with local riders ensuring on-time arrival.</p>

<p>At <strong>Sai Flower</strong>, every online flower delivery Greater Kailash order combines a smooth website with real florist craft. We hand-arrange each bouquet with daily-fresh stems and dispatch it locally, so it looks just as lovely at the doorstep.</p>

<h2>Why Choose Online Flower Delivery Greater Kailash</h2>
<p>Ordering online saves time while keeping quality high. Our online flower delivery Greater Kailash is built for convenience.</p>
<ul>
<li><strong>Shop anytime</strong> — order day or night from anywhere</li>
<li><strong>Same-day and midnight slots</strong> — across GK and South Delhi</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Secure payments</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Perfect for Every Occasion</h3>
<p>From birthdays and anniversaries to get-well wishes, online flower delivery Greater Kailash fits every moment. Add a cake or teddy for a complete gift, and one order handles the whole celebration. Our online flower delivery Greater Kailash also covers corporate and bulk gifting.</p>

<h3>Fresh and On Time Locally</h3>
<p>Freshness is our priority. Each online flower delivery Greater Kailash order is prepared close to dispatch and routed through local riders, so your bouquet arrives crisp, fragrant, and on schedule.</p>

<h2>Order Online Flower Delivery Greater Kailash</h2>
<p>Explore our <a href="/flowers" title="Online flower delivery Greater Kailash">flower range</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out securely. With Sai Flower, online flower delivery Greater Kailash is effortless, fresh, and reliable — order now and send a heartfelt surprise across GK.</p>
HTML,
    'meta_title' => 'Online Flower Delivery Greater Kailash | Sai Flower',
    'meta_description' => 'Order online flower delivery Greater Kailash with same-day & midnight slots from Sai Flower. Fresh bouquets to GK-1, GK-2 & South Delhi. Shop flowers in GK now.',
    'meta_keywords' => 'online flower delivery greater kailash, flower delivery greater kailash, online flower delivery gk, florist in greater kailash, same day flower delivery, online flower delivery, gk flower shop',
    'faqs' => [
        ['question' => 'How does online flower delivery in Greater Kailash work?', 'answer' => 'Choose a bouquet on this page, enter the GK delivery address and date, then pay securely. Sai Flower prepares and delivers it locally for you.'],
        ['question' => 'Is same-day online delivery available in GK?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across GK-1, GK-2, and nearby South Delhi, with midnight slots also available.'],
        ['question' => 'Can I schedule delivery for a future date?', 'answer' => 'Definitely. Select your preferred delivery date at checkout and we will prepare and deliver the bouquet right on schedule.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet is arranged with daily-fresh stems close to dispatch and packed protectively for freshness.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy and we will deliver the complete combo together across GK.'],
        ['question' => 'Which payment methods are accepted?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Greater Kailash, South Delhi, and the wider Delhi NCR. Confirm coverage at checkout.'],
    ],
],

// 66 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Same Day Flower Delivery Greater Kailash',
    'slug'  => 'same-day-flower-delivery-greater-kailash',
    'content' => <<<'HTML'
<h2>Fast Same Day Flower Delivery Greater Kailash</h2>
<p>When you need blooms today, our same day flower delivery Greater Kailash service is here to help. Order in the morning or afternoon and we hand-arrange and dispatch your bouquet within hours across GK-1, GK-2, and South Delhi. Perfect for last-minute birthdays and surprises, same day flower delivery Greater Kailash ensures your gift arrives fresh and on time.</p>

<p>At <strong>Sai Flower</strong>, speed never lowers quality. Every same day flower delivery Greater Kailash order uses daily-fresh stems and local riders who know the neighbourhood, so your bouquet arrives crisp and beautiful.</p>

<h2>Why Choose Our Same-Day GK Service</h2>
<p>Last-minute gifting should feel easy. Our same day flower delivery Greater Kailash keeps popular blooms ready so your urgent order never feels rushed.</p>
<ul>
<li><strong>Order before the cut-off</strong> — and we deliver today</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Fast local riders</strong> — quick across GK and South Delhi</li>
<li><strong>Live help</strong> — WhatsApp us for express slots</li>
</ul>

<h3>Perfect for Last-Minute Occasions</h3>
<p>Forgot a special date? Same day flower delivery Greater Kailash saves the moment. Send a romantic rose bunch, a cheerful birthday arrangement, or elegant lilies within hours. Add a cake or gift, and one same day flower delivery Greater Kailash order covers the whole surprise.</p>

<h3>Freshness Even at Speed</h3>
<p>Quick does not mean careless. Each bouquet is assembled close to dispatch and packed protectively, so petals stay firm and fragrant on arrival in GK.</p>

<h2>Book Same Day Flower Delivery Greater Kailash</h2>
<p>Pick a bouquet from our <a href="/flowers" title="Same day flower delivery Greater Kailash">collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and choose the same-day slot. With Sai Flower, same day flower delivery Greater Kailash is fast, fresh, and stress-free — order now and surprise someone today.</p>
HTML,
    'meta_title' => 'Same Day Flower Delivery Greater Kailash | Sai Flower',
    'meta_description' => 'Need flowers today in GK? Get same day flower delivery Greater Kailash with fresh bouquets from Sai Flower. Order before cut-off & surprise them now.',
    'meta_keywords' => 'same day flower delivery greater kailash, same day flower delivery gk, urgent flower delivery greater kailash, flower delivery greater kailash, midnight flower delivery, online flower delivery, gk florist',
    'faqs' => [
        ['question' => 'What is the cut-off for same-day delivery in Greater Kailash?', 'answer' => 'Order before our daily cut-off to guarantee same-day dispatch across GK-1, GK-2, and South Delhi. For later orders, midnight or next-day slots apply.'],
        ['question' => 'Which parts of GK are covered for same-day delivery?', 'answer' => 'We deliver same day across GK-1, GK-2, the M-Block and N-Block markets, and nearby South Delhi. Confirm your pin code at checkout.'],
        ['question' => 'Are same-day flowers fresh?', 'answer' => 'Yes. Every same-day bouquet is hand-tied with daily-fresh stems close to dispatch, so freshness stays high.'],
        ['question' => 'Can I get same-day delivery for a birthday?', 'answer' => 'Absolutely. Same day flower delivery in Greater Kailash is ideal for last-minute birthdays. Add a cake or gift for a complete surprise.'],
        ['question' => 'Is there an extra charge for same-day delivery?', 'answer' => 'Any applicable delivery fee is shown transparently at checkout before you pay.'],
        ['question' => 'Can I choose a delivery time?', 'answer' => 'You can request a preferred time window at checkout or on WhatsApp, and we will do our best to accommodate it.'],
        ['question' => 'How do I confirm my order?', 'answer' => 'You will receive an order confirmation, and our team is available on WhatsApp for live updates.'],
    ],
],

// 67 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Midnight Flower Delivery Greater Kailash',
    'slug'  => 'midnight-flower-delivery-greater-kailash',
    'content' => <<<'HTML'
<h2>Surprise Them With Midnight Flower Delivery Greater Kailash</h2>
<p>Be the first to celebrate with midnight flower delivery Greater Kailash. A fresh bouquet arriving right at 12 a.m. turns any birthday, anniversary, or surprise into an unforgettable memory across GK-1, GK-2, and South Delhi. With reliable midnight flower delivery Greater Kailash, you can make the moment magical the second the clock strikes twelve.</p>

<p>At <strong>Sai Flower</strong>, every midnight flower delivery Greater Kailash order is hand-tied with daily-fresh blooms and dispatched by local riders, so it arrives beautiful, fragrant, and right on time at midnight.</p>

<h2>Why Choose Midnight Flower Delivery Greater Kailash</h2>
<p>A midnight surprise shows extra thought and beats the morning rush of wishes. Our midnight flower delivery Greater Kailash is trusted for punctuality and presentation.</p>
<ul>
<li><strong>On-time 12 a.m. arrival</strong> — be first to celebrate in GK</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, mixed blooms, and more</li>
<li><strong>Add-ons available</strong> — cakes, chocolates, and teddies</li>
<li><strong>Local riders</strong> — quick across GK and South Delhi</li>
</ul>

<h3>Perfect for Special Surprises</h3>
<p>Birthdays are the most popular reason for midnight flower delivery Greater Kailash, but anniversaries and proposals work beautifully too. Pair a romantic rose bouquet with a midnight cake, and one midnight flower delivery Greater Kailash becomes an unforgettable celebration.</p>

<h3>Book Early to Reserve Your Slot</h3>
<p>Midnight slots are limited and fill quickly on weekends and festivals. Ordering in advance secures your bouquet and time. Our team confirms every midnight flower delivery Greater Kailash so there are no last-minute worries.</p>

<h2>Order Midnight Flower Delivery Greater Kailash</h2>
<p>Choose a bouquet from our <a href="/flowers" title="Midnight flower delivery Greater Kailash">collection</a>, add a <a href="/cakes" title="Midnight cakes Delhi">midnight cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, then select the midnight slot. With Sai Flower, midnight flower delivery Greater Kailash is smooth and delightful — order now and make their celebration unforgettable.</p>
HTML,
    'meta_title' => 'Midnight Flower Delivery Greater Kailash | Sai Flower',
    'meta_description' => 'Surprise loved ones with midnight flower delivery Greater Kailash. Fresh bouquets & cakes at 12 a.m. from Sai Flower to GK & South Delhi. Book midnight slot now.',
    'meta_keywords' => 'midnight flower delivery greater kailash, midnight flower delivery gk, 12 am flower delivery greater kailash, flower delivery greater kailash, same day flower delivery, online flower delivery, gk florist',
    'faqs' => [
        ['question' => 'What time is midnight delivery made in Greater Kailash?', 'answer' => 'Midnight orders are delivered around 12 a.m., typically between 11:30 p.m. and 12:30 a.m., across GK and nearby South Delhi.'],
        ['question' => 'Which GK areas support midnight delivery?', 'answer' => 'Midnight delivery is available in selected pin codes across GK-1, GK-2, and nearby South Delhi. Confirm at checkout or on WhatsApp.'],
        ['question' => 'Should I book midnight delivery in advance?', 'answer' => 'Yes. Midnight slots are limited and fill fast on weekends and festivals, so we recommend ordering at least a day ahead.'],
        ['question' => 'Can I add a cake to midnight delivery?', 'answer' => 'Absolutely. Pair your bouquet with a midnight cake or chocolates and we will deliver the complete surprise together at 12 a.m.'],
        ['question' => 'Is there an extra fee for midnight delivery?', 'answer' => 'Any midnight delivery charge is shown clearly at checkout before payment.'],
        ['question' => 'What occasions suit midnight delivery?', 'answer' => 'Birthdays, anniversaries, and surprise celebrations are perfect for midnight delivery in Greater Kailash.'],
        ['question' => 'How do I confirm my order?', 'answer' => 'You will get an order confirmation, and our team stays reachable on WhatsApp to confirm timing.'],
    ],
],

// 68 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Shop Greater Kailash',
    'slug'  => 'flower-shop-greater-kailash',
    'content' => <<<'HTML'
<h2>Fresh Blooms From Your Flower Shop Greater Kailash</h2>
<p>When you want quality flowers without the hassle, a dependable flower shop Greater Kailash is exactly what you need. Our flower shop Greater Kailash brings fresh roses, lilies, orchids, and mixed bouquets to your screen, ready to order and deliver across GK-1, GK-2, and South Delhi. For every occasion, a trusted flower shop Greater Kailash makes gifting simple and beautiful.</p>

<p>At <strong>Sai Flower</strong>, our flower shop Greater Kailash blends easy online ordering with real florist craft. Every bouquet is hand-arranged with daily-fresh stems and delivered locally on time, so it looks just as lovely at the doorstep.</p>

<h2>Why Shop at Our Flower Shop Greater Kailash</h2>
<p>A great flower shop offers variety, freshness, and dependable service. Our flower shop Greater Kailash delivers all three.</p>
<ul>
<li><strong>Wide selection</strong> — bouquets, arrangements, and combos</li>
<li><strong>Same-day and midnight delivery</strong> — across GK and South Delhi</li>
<li><strong>Fresh, florist-made blooms</strong> — no compromise on quality</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Flowers and Gifts in One Place</h3>
<p>A complete flower shop Greater Kailash offers more than bouquets. Add a designer cake, chocolates, or a soft teddy and cover the whole celebration in one go. Our flower shop Greater Kailash makes combo gifting simple for birthdays, anniversaries, and surprises.</p>

<h3>Reliable Local Delivery, Every Time</h3>
<p>Ordering from a flower shop Greater Kailash should feel dependable. We prepare each bouquet close to dispatch and hand it to local riders, so your flowers arrive fresh and right on schedule.</p>

<h2>Order From Our Flower Shop Greater Kailash</h2>
<p>Explore our <a href="/flowers" title="Flower shop Greater Kailash">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. For a fresh, reliable flower shop Greater Kailash, choose Sai Flower — order now and send a beautiful bouquet across GK.</p>
HTML,
    'meta_title' => 'Flower Shop Greater Kailash | Sai Flower',
    'meta_description' => 'Shop our flower shop Greater Kailash for fresh bouquets with same-day & midnight delivery from Sai Flower. Order flowers & gifts to GK-1, GK-2 & South Delhi now.',
    'meta_keywords' => 'flower shop greater kailash, flower shop gk, florist in greater kailash, flower delivery greater kailash, best florist in greater kailash, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What can I buy from your flower shop in Greater Kailash?', 'answer' => 'You can shop fresh bouquets, arrangements, and combos with cakes, chocolates, and teddies, all available online for delivery across GK and South Delhi.'],
        ['question' => 'Does your flower shop offer same-day delivery in GK?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available across Greater Kailash.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet is hand-arranged with daily-fresh stems close to dispatch and packed protectively for freshness.'],
        ['question' => 'How do I pay?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Can I add a gift to my order?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy and we will deliver the complete combo together across GK.'],
        ['question' => 'Can I schedule delivery for later?', 'answer' => 'Absolutely. Choose your preferred delivery date at checkout and we will deliver right on time.'],
        ['question' => 'Which areas does your flower shop serve?', 'answer' => 'We deliver across Greater Kailash, South Delhi, and the wider Delhi NCR. Confirm coverage at checkout.'],
    ],
],

// 69 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Bouquet Delivery Greater Kailash',
    'slug'  => 'bouquet-delivery-greater-kailash',
    'content' => <<<'HTML'
<h2>Beautiful Bouquet Delivery Greater Kailash</h2>
<p>A hand-tied bouquet is a timeless way to show you care, and our bouquet delivery Greater Kailash makes sending one effortless. From romantic roses to cheerful mixed blooms, every bouquet delivery Greater Kailash order arrives fresh, neatly wrapped, and ready to impress across GK-1, GK-2, and South Delhi. Whatever the occasion, dependable bouquet delivery Greater Kailash turns feelings into a beautiful surprise.</p>

<p>At <strong>Sai Flower</strong>, each bouquet delivery Greater Kailash order is crafted by expert florists using daily-fresh stems and delivered by local riders. We arrange, wrap, and dispatch with care, so your bouquet looks polished from the first glance.</p>

<h2>Why Choose Our Bouquet Delivery Greater Kailash</h2>
<p>Great bouquet delivery is about freshness, presentation, and punctuality. Our bouquet delivery Greater Kailash takes care of all three.</p>
<ul>
<li><strong>Hand-tied by florists</strong> — photo-perfect arrangements</li>
<li><strong>Same-day and midnight slots</strong> — across GK and South Delhi</li>
<li><strong>Fresh, daily-sourced blooms</strong> — roses, lilies, and orchids</li>
<li><strong>Local riders</strong> — quick delivery within Greater Kailash</li>
</ul>

<h3>Bouquets for Every Occasion</h3>
<p>From birthdays and anniversaries to congratulations and get-well wishes, bouquet delivery Greater Kailash fits every moment. Choose a classic rose bunch, a vibrant mixed design, or elegant lilies, and add a cake or gift so one bouquet delivery Greater Kailash completes the celebration.</p>

<h3>Fresh and On Time Locally</h3>
<p>Timing and freshness make a bouquet special. Every bouquet delivery Greater Kailash order is prepared close to dispatch and routed through local riders, so it arrives crisp, fragrant, and on schedule.</p>

<h2>Order Bouquet Delivery Greater Kailash Online</h2>
<p>Browse our <a href="/flowers" title="Bouquet delivery Greater Kailash">bouquet collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, bouquet delivery Greater Kailash is fresh, elegant, and reliable — order now and send a stunning bouquet across GK.</p>
HTML,
    'meta_title' => 'Bouquet Delivery Greater Kailash | Sai Flower',
    'meta_description' => 'Order bouquet delivery Greater Kailash with fresh, hand-tied arrangements from Sai Flower. Same-day & midnight slots to GK-1, GK-2 & South Delhi. Send now.',
    'meta_keywords' => 'bouquet delivery greater kailash, bouquet delivery gk, flower delivery greater kailash, florist in greater kailash, same day bouquet delivery, online flower delivery, gk flower shop',
    'faqs' => [
        ['question' => 'Do you offer same-day bouquet delivery in Greater Kailash?', 'answer' => 'Yes. Order before the daily cut-off for same-day bouquet delivery across GK-1, GK-2, and South Delhi, with midnight slots also available.'],
        ['question' => 'Are bouquets hand-tied by florists?', 'answer' => 'Every bouquet is hand-tied by our expert florists using daily-fresh stems, so it looks polished and matches the photo closely.'],
        ['question' => 'What types of bouquets can I order?', 'answer' => 'Choose from rose bouquets, mixed seasonal blooms, lilies, orchids, gerberas, and premium arrangements for every occasion and budget.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a teddy and we will deliver the complete combo together across GK.'],
        ['question' => 'How fresh will the bouquet be?', 'answer' => 'Each bouquet is arranged close to dispatch and packed protectively, so it arrives crisp, fragrant, and long-lasting.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your bouquet delivery.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Greater Kailash, South Delhi, and the wider Delhi NCR. Confirm coverage at checkout.'],
    ],
],

// 70 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Best Florist in Greater Kailash',
    'slug'  => 'best-florist-in-greater-kailash',
    'content' => <<<'HTML'
<h2>The Best Florist in Greater Kailash</h2>
<p>When only the finest will do, you want the best florist in Greater Kailash. Combining daily-fresh blooms, expert arrangement, and fast local delivery, we have earned a name as the best florist in Greater Kailash across GK-1, GK-2, and South Delhi. For every celebration, the best florist in Greater Kailash makes gifting effortless, fresh, and memorable.</p>

<p>At <strong>Sai Flower</strong>, becoming the best florist in Greater Kailash is about consistency. We hand-tie every bouquet with daily-fresh stems and deliver on time, so what you order online is exactly what arrives at the doorstep.</p>

<h2>What Makes Us the Best Florist in Greater Kailash</h2>
<p>A top local florist blends quality, variety, and dependability. That is why customers rank us the best florist in Greater Kailash.</p>
<ul>
<li><strong>Daily-fresh flowers</strong> — roses, lilies, orchids, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across GK and South Delhi</li>
<li><strong>Expert florists</strong> — hand-tied, photo-perfect arrangements</li>
<li><strong>Fast local riders</strong> — quick within Greater Kailash</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>From birthdays and anniversaries to weddings and condolences, the best florist in Greater Kailash has the right bloom ready. Choose romantic roses, elegant orchids, or cheerful gerberas, and add cakes or gifts so the best florist in Greater Kailash covers the whole celebration.</p>

<h3>Premium Quality, Reliable Service</h3>
<p>Being the best means reliability too. Every order is prepared close to dispatch and delivered by local riders, so your bouquet always arrives fresh, fragrant, and on schedule.</p>

<h2>Order From the Best Florist in Greater Kailash</h2>
<p>Explore our <a href="/flowers" title="Best florist in Greater Kailash">flower collection</a>, add a <a href="/cakes" title="Order cakes online Delhi">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. For the best florist in Greater Kailash, choose Sai Flower — order now and send a beautiful surprise across GK.</p>
HTML,
    'meta_title' => 'Best Florist in Greater Kailash | Sai Flower',
    'meta_description' => 'Sai Flower is the best florist in Greater Kailash for fresh bouquets with same-day & midnight delivery to GK-1, GK-2 & South Delhi. Order from a GK florist now.',
    'meta_keywords' => 'best florist in greater kailash, best florist in gk, florist in greater kailash, flower shop greater kailash, flower delivery greater kailash, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What makes Sai Flower the best florist in Greater Kailash?', 'answer' => 'We combine daily-fresh flowers, expert hand-tied arrangements, and fast local delivery across GK-1, GK-2, and South Delhi, with consistent quality and punctuality.'],
        ['question' => 'Do you offer same-day delivery in GK?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available across Greater Kailash.'],
        ['question' => 'What flowers do you offer?', 'answer' => 'Our collection includes roses, lilies, orchids, carnations, gerberas, and mixed seasonal bouquets, plus cakes and gifts.'],
        ['question' => 'Do you cater to all budgets?', 'answer' => 'Absolutely. From affordable everyday bunches to premium and luxury arrangements, there is an option for every budget.'],
        ['question' => 'Can you deliver to GK offices and apartments?', 'answer' => 'Yes. Add the building name, floor, and contact details in the notes and our local rider will deliver smoothly.'],
        ['question' => 'How do I place an order?', 'answer' => 'Browse the bouquets, add to cart, enter delivery details, and pay securely via UPI, card, or wallet.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Greater Kailash, South Delhi, and the wider Delhi NCR. Confirm coverage at checkout.'],
    ],
],

];

echo "=== Seeding keyword pages — Batch 7 (Greater Kailash / GK) ===\n";
$created = 0;
foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    }
}
echo "=== Done. {$created} new page(s) created. ===\n";
