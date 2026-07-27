<?php
/**
 * Seed: Keyword custom pages — Batch 1 (dynamic_pages)
 * Layout: product_showcase | Tag: sameday
 * Run once: https://saiflower.com/tools/seed_keyword_pages_batch1.php
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

// 1 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Delivery',
    'slug'  => 'flower-delivery',
    'content' => <<<'HTML'
<h2>Reliable Flower Delivery Across Delhi NCR</h2>
<p>When you want to make someone smile, nothing beats fresh blooms at the doorstep. Our flower delivery service brings hand-tied bouquets, seasonal stems, and elegant arrangements to homes and offices without the usual hassle. Whether it is a birthday, an anniversary, or a simple "thinking of you" moment, dependable flower delivery turns an ordinary day into something people remember for years.</p>

<p>At <strong>Sai Flower</strong>, every order is treated like a personal gift. We hand-pick daily-fresh flowers, wrap them with care, and route each flower delivery through trusted riders so your surprise arrives crisp and beautiful. From classic roses to vibrant mixed bunches, our florists build arrangements that look exactly as promised online.</p>

<h2>Why Choose Our Flower Delivery Service</h2>
<p>Choosing the right florist matters. A rushed order can wilt before it reaches the door, but our flower delivery process keeps stems cool, hydrated, and protected against Delhi's heat and monsoon humidity. That attention to detail is why families trust us for repeat flower delivery season after season.</p>
<ul>
<li><strong>Same-day flower delivery</strong> — order before the daily cut-off and we deliver today</li>
<li><strong>Fresh, hand-arranged bouquets</strong> — styled by experienced florists</li>
<li><strong>Wide coverage</strong> — Delhi, Gurgaon, Noida, and nearby NCR pin codes</li>
<li><strong>Secure checkout</strong> — pay easily via UPI, card, or wallet</li>
</ul>

<h3>Occasions Perfect for Sending Flowers</h3>
<p>Flowers fit every celebration. Send a romantic rose bouquet for an anniversary, cheerful gerberas for a birthday, or graceful lilies to say get well soon. Our flower delivery covers weddings, housewarmings, corporate events, and quiet apologies alike. Pair blooms with a cake or gift hamper and let a single flower delivery handle the entire surprise from start to finish.</p>

<h3>Same-Day and Midnight Options</h3>
<p>Forgot an important date? Don't worry. With same-day and midnight flower delivery, you can still surprise your loved ones right on time. Browse the arrangements above, choose a delivery slot at checkout, or message us on WhatsApp to confirm express availability across the city.</p>

<h2>Order Fresh Flower Delivery Online</h2>
<p>Placing an order is simple. Explore our <a href="/flowers" title="Order flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">designer cake</a>, or include a <a href="/gifts" title="Gift hampers Delhi">gift hamper</a>, then check out in minutes. Each flower delivery is prepared shortly before dispatch so freshness is never compromised.</p>

<p>From heartfelt apologies to milestone celebrations, our online flower delivery makes gifting effortless. Choose Sai Flower for blooms that arrive fresh, on time, and ready to impress — and enjoy dependable flower delivery right to the doorstep.</p>
HTML,
    'meta_title' => 'Flower Delivery in Delhi NCR | Sai Flower',
    'meta_description' => 'Fresh flower delivery in Delhi NCR with same-day & midnight options. Hand-tied bouquets from Sai Flower delivered on time. Order flowers online now.',
    'meta_keywords' => 'flower delivery, online flower delivery, same day flower delivery, flower delivery delhi, fresh flowers online, bouquet delivery, send flowers online',
    'faqs' => [
        ['question' => 'Do you offer same-day flower delivery in Delhi NCR?', 'answer' => 'Yes. Place your order before the daily cut-off and we deliver the same day across Delhi, Gurgaon, Noida, and nearby NCR pin codes. You can pick a slot at checkout or message us on WhatsApp for express help.'],
        ['question' => 'How do I place an order for flower delivery online?', 'answer' => 'Browse the bouquets on this page, add your favourite to the cart, enter the recipient address and date, then pay securely via UPI, card, or wallet. Your flowers are prepared fresh just before dispatch.'],
        ['question' => 'Are the flowers fresh when delivered?', 'answer' => 'Absolutely. Sai Flower hand-picks daily-fresh stems and arranges every bouquet close to dispatch time. Protective packaging keeps blooms crisp against heat and humidity during transit.'],
        ['question' => 'Can I send flowers to an office address?', 'answer' => 'Yes, we deliver to homes and offices. Add the company name, floor, and reception details in the delivery notes so our rider can hand over the bouquet smoothly.'],
        ['question' => 'Do you provide midnight flower delivery?', 'answer' => 'Midnight delivery is available in selected pin codes for surprises and special occasions. Add your preferred time in order notes or confirm availability on WhatsApp before ordering.'],
        ['question' => 'Which payment methods can I use?', 'answer' => 'You can pay using UPI, debit or credit cards, and popular wallets through our secure checkout. All transactions are encrypted for your safety.'],
        ['question' => 'Can I combine flowers with a cake or gift?', 'answer' => 'Yes. Add a designer cake or gift hamper from our collections and we will deliver the complete combo together in a single flower delivery where the pin code allows.'],
    ],
],

// 2 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Online Flower Delivery',
    'slug'  => 'online-flower-delivery',
    'content' => <<<'HTML'
<h2>Easy Online Flower Delivery in Delhi NCR</h2>
<p>Gifting flowers has never been simpler. With our online flower delivery service, you can send fresh bouquets to family, friends, and colleagues in just a few clicks. No traffic, no long queues at a shop — just pick an arrangement, choose a date, and let us handle the rest. Smooth online flower delivery means your surprise reaches the doorstep exactly when you want it to.</p>

<p>At <strong>Sai Flower</strong>, we combine a hassle-free website with real florist craftsmanship. Every order placed through our online flower delivery platform is hand-arranged with daily-fresh stems, wrapped neatly, and dispatched with care so it looks just like the photo you selected.</p>

<h2>Benefits of Ordering Flowers Online</h2>
<p>Shopping from home gives you time to compare bouquets, read details, and choose add-ons calmly. Our online flower delivery service is built for convenience, whether you are gifting from the next street or another city.</p>
<ul>
<li><strong>Round-the-clock ordering</strong> — shop anytime, we deliver on schedule</li>
<li><strong>Same-day and midnight slots</strong> — perfect for last-minute surprises</li>
<li><strong>Fresh, florist-made bouquets</strong> — roses, lilies, orchids, and mixed blooms</li>
<li><strong>Wide reach</strong> — Delhi, Gurgaon, Noida, and nearby NCR areas</li>
</ul>

<h3>Perfect for Every Occasion</h3>
<p>From birthdays and anniversaries to get-well wishes and congratulations, online flower delivery fits every moment. Add a cake or a soft teddy for a complete gift, and one order takes care of the whole celebration. Our online flower delivery service also handles corporate gifting and bulk event orders with ease.</p>

<h3>Freshness You Can Count On</h3>
<p>We know flowers are emotional gifts, so freshness is our top priority. Each online flower delivery is prepared shortly before it leaves our studio, keeping petals firm and colours bright. Reliable riders ensure your bouquet arrives on time and in perfect shape.</p>

<h2>How Online Flower Delivery Works</h2>
<p>Explore our <a href="/flowers" title="Order flowers online Delhi">flower range</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift hamper</a>, and check out securely with UPI, card, or wallet. Track your order and relax while we prepare it. Thanks to smooth online flower delivery, gifting feels effortless from start to finish.</p>

<p>Choose Sai Flower for stress-free online flower delivery that blends convenience with genuine florist quality. Order now and send a fresh, heartfelt surprise to someone special today.</p>
HTML,
    'meta_title' => 'Online Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Order online flower delivery in Delhi NCR with same-day & midnight slots. Fresh, florist-made bouquets from Sai Flower delivered on time. Shop now.',
    'meta_keywords' => 'online flower delivery, flower delivery online, send flowers online, order flowers online, same day flower delivery, online florist, bouquet delivery',
    'faqs' => [
        ['question' => 'What is online flower delivery?', 'answer' => 'Online flower delivery lets you order fresh bouquets from a website and have them delivered to any address. You choose the arrangement, date, and time, and Sai Flower prepares and delivers it for you.'],
        ['question' => 'Is same-day online flower delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR. For late orders, midnight and next-day slots are also available in many pin codes.'],
        ['question' => 'Can I schedule delivery for a future date?', 'answer' => 'Definitely. Select your preferred delivery date at checkout and we will prepare the bouquet fresh and deliver it right on schedule.'],
        ['question' => 'Are online flower prices the same as in-store?', 'answer' => 'Our online prices are transparent and competitive. You can view the exact cost, add-ons, and delivery details before you pay, with no hidden charges.'],
        ['question' => 'How will I know my order is delivered?', 'answer' => 'We keep you updated on your order status, and you can reach our team on WhatsApp for real-time confirmation of your online flower delivery.'],
        ['question' => 'Can I add a personal message?', 'answer' => 'Yes. Add a message note during checkout and we will include it with your bouquet so your heartfelt words reach the recipient.'],
        ['question' => 'Do you deliver outside Delhi NCR?', 'answer' => 'Our online checkout covers Delhi NCR. For other cities, message our team on WhatsApp and we will confirm availability and suggest the best option.'],
    ],
],

// 3 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Same Day Flower Delivery',
    'slug'  => 'same-day-flower-delivery',
    'content' => <<<'HTML'
<h2>Fast Same Day Flower Delivery in Delhi NCR</h2>
<p>Some moments simply cannot wait. When you need blooms at the doorstep today, our same day flower delivery service is here to help. Order a fresh bouquet in the morning or afternoon, and we will hand-arrange it and dispatch it within hours. Reliable same day flower delivery is perfect for last-minute birthdays, apologies, and spontaneous surprises.</p>

<p>At <strong>Sai Flower</strong>, speed never means a drop in quality. Every same day flower delivery is prepared with daily-fresh stems, wrapped neatly, and routed through quick, dependable riders so your gift arrives crisp, colourful, and right on time.</p>

<h2>Why Customers Trust Our Same-Day Service</h2>
<p>Ordering flowers at the last minute can be stressful, but we make it smooth. Our florists keep popular roses, lilies, and mixed bunches ready so your urgent order never feels rushed or incomplete.</p>
<ul>
<li><strong>Order before the daily cut-off</strong> — and we deliver today</li>
<li><strong>Fresh, florist-made bouquets</strong> — no compromise on quality</li>
<li><strong>Wide NCR coverage</strong> — Delhi, Gurgaon, Noida, and nearby areas</li>
<li><strong>Live help</strong> — WhatsApp us to confirm express slots</li>
</ul>

<h3>Perfect for Last-Minute Occasions</h3>
<p>Forgot an anniversary or a friend's big day? Same day flower delivery saves the moment. Send a romantic rose bouquet, a cheerful birthday arrangement, or elegant get-well flowers within hours. Add a cake or gift hamper and one same day flower delivery covers the entire surprise.</p>

<h3>Freshness Even at Speed</h3>
<p>Quick does not mean careless. Each bouquet for same day flower delivery is assembled close to dispatch, keeping petals firm and fragrance strong. Protective packaging shields blooms from heat and humidity so they look studio-fresh at the door.</p>

<h2>How to Book Same-Day Delivery</h2>
<p>Pick a bouquet from the showcase above, browse our <a href="/flowers" title="Order flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out securely. Choose the same-day slot, and our team takes over instantly. With Sai Flower, same day flower delivery is fast, fresh, and completely stress-free — order now and surprise someone today.</p>
HTML,
    'meta_title' => 'Same Day Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Need flowers today? Get same day flower delivery in Delhi NCR with fresh, florist-made bouquets from Sai Flower. Order before cut-off & surprise them now.',
    'meta_keywords' => 'same day flower delivery, same day flowers delhi, urgent flower delivery, flowers delivered today, express flower delivery, online flower delivery, bouquet delivery',
    'faqs' => [
        ['question' => 'What is the cut-off time for same day flower delivery?', 'answer' => 'Order before our daily cut-off to guarantee same-day dispatch across Delhi NCR. For orders placed later, we offer midnight or next-day slots. WhatsApp us to confirm the latest timing.'],
        ['question' => 'Which areas are covered for same-day delivery?', 'answer' => 'We deliver the same day across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Enter the delivery pin code at checkout to confirm coverage.'],
        ['question' => 'Are same-day flowers as fresh as regular orders?', 'answer' => 'Yes. Sai Flower arranges every same-day bouquet with daily-fresh stems close to dispatch, so freshness and presentation stay just as high as any scheduled order.'],
        ['question' => 'Can I get same-day delivery for birthdays?', 'answer' => 'Absolutely. Same day flower delivery is ideal for last-minute birthdays. Add a cake or gift and we will deliver the complete surprise together where possible.'],
        ['question' => 'Is there an extra charge for same-day delivery?', 'answer' => 'Any applicable delivery fee is shown transparently at checkout before you pay. There are no hidden charges.'],
        ['question' => 'Can I choose a specific delivery time?', 'answer' => 'You can request a preferred time window at checkout or on WhatsApp. We do our best to accommodate it based on rider availability.'],
        ['question' => 'How do I confirm my same-day order went through?', 'answer' => 'You will receive an order confirmation, and our team is available on WhatsApp for live updates on your same day flower delivery.'],
    ],
],

// 4 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Midnight Flower Delivery',
    'slug'  => 'midnight-flower-delivery',
    'content' => <<<'HTML'
<h2>Surprise Them With Midnight Flower Delivery</h2>
<p>There is something magical about a knock at 12 a.m. with fresh blooms in hand. Our midnight flower delivery service helps you be the first to wish someone on their special day. Whether it is a birthday, an anniversary, or a heartfelt surprise, midnight flower delivery creates a moment your loved one will never forget.</p>

<p>At <strong>Sai Flower</strong>, we specialise in turning ordinary nights into memories. Each midnight flower delivery is hand-arranged with daily-fresh stems and dispatched carefully so the bouquet arrives beautiful, fragrant, and right on the stroke of twelve.</p>

<h2>Why Choose Midnight Delivery</h2>
<p>A midnight surprise shows extra thought and effort. It beats the crowd of morning wishes and makes the celebration feel truly personal. Our midnight flower delivery is trusted for its punctuality and presentation across the city.</p>
<ul>
<li><strong>On-time 12 a.m. arrival</strong> — be the first to celebrate</li>
<li><strong>Fresh, florist-made bouquets</strong> — roses, mixed blooms, and more</li>
<li><strong>Add-ons available</strong> — cakes, chocolates, and soft teddies</li>
<li><strong>NCR coverage</strong> — Delhi, Gurgaon, Noida, and nearby areas</li>
</ul>

<h3>Perfect Occasions for a Midnight Surprise</h3>
<p>Birthdays are the most popular reason for midnight flower delivery, but anniversaries, proposals, and "just because" gestures work beautifully too. Pair a romantic rose bouquet with a midnight cake, and one midnight flower delivery becomes an unforgettable celebration at the doorstep.</p>

<h3>Book Early to Reserve Your Slot</h3>
<p>Midnight slots are limited and fill quickly, especially on weekends and festivals. Placing your order in advance ensures your preferred bouquet and time are secured. Our team confirms every midnight flower delivery so there are no last-minute worries.</p>

<h2>How to Order Midnight Flowers</h2>
<p>Choose a bouquet from the showcase above, browse our <a href="/flowers" title="Order flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Midnight cakes Delhi">midnight cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, then select the midnight slot at checkout. With Sai Flower, midnight flower delivery is smooth, reliable, and delightfully surprising — order now and make their next celebration unforgettable.</p>
HTML,
    'meta_title' => 'Midnight Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Surprise loved ones with midnight flower delivery in Delhi NCR. Fresh bouquets & cakes delivered at 12 a.m. by Sai Flower. Book your midnight slot now.',
    'meta_keywords' => 'midnight flower delivery, 12 am flower delivery, midnight bouquet delivery, birthday flowers midnight, midnight cake and flowers, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What time is midnight flower delivery made?', 'answer' => 'Midnight orders are delivered around 12 a.m., typically between 11:30 p.m. and 12:30 a.m., so you can be the first to wish your loved one on their special day.'],
        ['question' => 'Which areas support midnight flower delivery?', 'answer' => 'Midnight delivery is available in selected pin codes across Delhi, Gurgaon, and Noida. Confirm your pin code at checkout or on WhatsApp before booking.'],
        ['question' => 'Should I book midnight delivery in advance?', 'answer' => 'Yes. Midnight slots are limited and fill fast on weekends and festivals, so we recommend ordering at least a day ahead to secure your slot.'],
        ['question' => 'Can I add a cake to midnight flower delivery?', 'answer' => 'Absolutely. Pair your bouquet with a midnight cake or chocolates and Sai Flower will deliver the complete surprise together at 12 a.m.'],
        ['question' => 'Is there an extra fee for midnight delivery?', 'answer' => 'Any midnight delivery charge is shown clearly at checkout before payment, so you always know the total in advance.'],
        ['question' => 'What occasions suit midnight flower delivery?', 'answer' => 'Birthdays, anniversaries, proposals, and surprise celebrations are perfect for midnight delivery. It adds a memorable, thoughtful touch to any special moment.'],
        ['question' => 'How do I confirm my midnight order?', 'answer' => 'You will get an order confirmation, and our team stays reachable on WhatsApp to confirm timing for your midnight flower delivery.'],
    ],
],

// 5 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Express Flower Delivery',
    'slug'  => 'express-flower-delivery',
    'content' => <<<'HTML'
<h2>Quick Express Flower Delivery in Delhi NCR</h2>
<p>When time is short and the moment matters, our express flower delivery service gets fresh blooms to the doorstep fast. Ideal for last-minute apologies, urgent birthdays, and spur-of-the-moment surprises, express flower delivery combines speed with genuine florist quality so nothing feels rushed or ordinary.</p>

<p>At <strong>Sai Flower</strong>, we have streamlined every step to save you time. Each express flower delivery is hand-arranged with daily-fresh stems and dispatched through priority riders, so your bouquet reaches its destination quickly while still looking polished and beautiful.</p>

<h2>Why Choose Express Delivery</h2>
<p>Life gets busy, and dates slip by. Our express flower delivery is designed for exactly those moments, giving you a dependable way to send love within hours rather than days.</p>
<ul>
<li><strong>Priority dispatch</strong> — your order jumps to the front of the queue</li>
<li><strong>Fresh, florist-made bouquets</strong> — roses, lilies, and mixed blooms</li>
<li><strong>Live coordination</strong> — WhatsApp us for the fastest slots</li>
<li><strong>NCR-wide reach</strong> — Delhi, Gurgaon, Noida, and nearby areas</li>
</ul>

<h3>Perfect for Urgent Gifting</h3>
<p>A forgotten anniversary, a friend in the hospital, or a sudden reason to celebrate — express flower delivery handles them all. Send a heartfelt bouquet quickly, add a cake or chocolates, and one express flower delivery takes care of the entire surprise without delay.</p>

<h3>Speed Without Sacrificing Freshness</h3>
<p>Fast should still be fresh. Every bouquet for express flower delivery is prepared close to dispatch and packed protectively, so heat and travel never dull its charm. Your gift arrives crisp, fragrant, and ready to impress.</p>

<h2>How to Book Express Flowers</h2>
<p>Select a bouquet from the showcase, browse our <a href="/flowers" title="Order flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and choose the fastest slot at checkout. With Sai Flower, express flower delivery is quick, reliable, and beautifully presented — order now and let us rush your surprise to the doorstep.</p>
HTML,
    'meta_title' => 'Express Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Get express flower delivery in Delhi NCR within hours. Fresh, florist-made bouquets rushed to the doorstep by Sai Flower. Order urgent flowers now.',
    'meta_keywords' => 'express flower delivery, urgent flower delivery, fast flower delivery, same day flower delivery, quick bouquet delivery, online flower delivery, flowers delivered today',
    'faqs' => [
        ['question' => 'How fast is express flower delivery?', 'answer' => 'Express orders are prioritised and typically dispatched within a couple of hours, depending on the delivery pin code. WhatsApp us to confirm the fastest possible slot.'],
        ['question' => 'What areas do you cover for express delivery?', 'answer' => 'We offer express flower delivery across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Enter the address at checkout to confirm coverage.'],
        ['question' => 'Are express flowers still fresh?', 'answer' => 'Yes. Sai Flower arranges each express bouquet with daily-fresh stems just before dispatch and packs it protectively, so it arrives crisp and fragrant.'],
        ['question' => 'Can I add a cake to an express order?', 'answer' => 'Absolutely. Add a cake, chocolates, or a soft teddy and we will rush the complete combo together where the pin code allows.'],
        ['question' => 'Is express delivery more expensive?', 'answer' => 'Any priority delivery fee is displayed transparently at checkout before payment, so there are no surprises.'],
        ['question' => 'When should I use express delivery?', 'answer' => 'Express flower delivery is perfect for last-minute birthdays, apologies, get-well wishes, and any surprise that simply cannot wait.'],
        ['question' => 'How do I track my express order?', 'answer' => 'You will receive an order confirmation, and our team provides live updates on WhatsApp for your express flower delivery.'],
    ],
],

// 6 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Best Florist Near Me',
    'slug'  => 'best-florist-near-me',
    'content' => <<<'HTML'
<h2>Looking for the Best Florist Near Me?</h2>
<p>If you have been searching for the best florist near me, your search ends here. We bring together fresh blooms, skilled arrangement, and fast delivery so every bouquet feels personal and premium. As the best florist near me for countless Delhi NCR families, we make gifting flowers easy, reliable, and genuinely heartfelt.</p>

<p>At <strong>Sai Flower</strong>, being the best florist near me is about more than pretty pictures. We hand-pick daily-fresh stems, arrange them with real craftsmanship, and deliver on time so what you order is exactly what arrives at the doorstep.</p>

<h2>What Makes Us the Best Florist Near Me</h2>
<p>A great local florist blends quality, variety, and dependability. Customers choose us as the best florist near me because we never cut corners on freshness or presentation.</p>
<ul>
<li><strong>Daily-fresh flowers</strong> — roses, lilies, orchids, and mixed blooms</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Expert florists</strong> — hand-tied, photo-perfect arrangements</li>
<li><strong>Easy online ordering</strong> — secure UPI, card, and wallet payments</li>
</ul>

<h3>Flowers for Every Occasion</h3>
<p>Whether it is a birthday, anniversary, wedding, or a get-well surprise, the best florist near me should have the right bloom ready. From romantic rose bouquets to cheerful gerberas and elegant orchids, our collection covers every mood and budget, with cakes and gifts to complete the celebration.</p>

<h3>Local Convenience, Premium Quality</h3>
<p>You want a florist who is close, quick, and trustworthy. As the best florist near me, we combine neighbourhood convenience with premium quality and reliable riders, so your bouquet always arrives fresh and on schedule.</p>

<h2>Order From the Best Florist Near Me</h2>
<p>Explore our <a href="/flowers" title="Order flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift hamper</a>, and check out in minutes. When you want the best florist near me for fresh, on-time flowers, choose Sai Flower — order now and send a beautiful surprise to someone you love.</p>
HTML,
    'meta_title' => 'Best Florist Near Me in Delhi NCR | Sai Flower',
    'meta_description' => 'Searching for the best florist near me? Sai Flower delivers fresh, hand-tied bouquets across Delhi NCR with same-day & midnight options. Order online now.',
    'meta_keywords' => 'best florist near me, florist near me, local florist delhi, online florist, flower shop near me, same day flower delivery, best flower shop',
    'faqs' => [
        ['question' => 'What makes Sai Flower the best florist near me?', 'answer' => 'We combine daily-fresh flowers, expert hand-tied arrangements, and reliable same-day delivery across Delhi NCR. Our focus on quality and punctuality makes us a trusted local florist choice.'],
        ['question' => 'Do you deliver flowers near my location?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Enter your address at checkout to confirm delivery to your area.'],
        ['question' => 'Can I order flowers for same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, or choose midnight and scheduled slots for special surprises.'],
        ['question' => 'What types of flowers do you offer?', 'answer' => 'Our collection includes roses, lilies, orchids, carnations, gerberas, and mixed seasonal bouquets, plus cakes and gifts to complete your order.'],
        ['question' => 'Can I see the bouquet before ordering?', 'answer' => 'Yes. Each product page shows clear photos and details so you know exactly what to expect, and our florists match the arrangement closely.'],
        ['question' => 'Do you offer flowers for all budgets?', 'answer' => 'Absolutely. From affordable everyday bunches to premium hand-tied arrangements, there is an option for every budget on our site.'],
        ['question' => 'How do I contact the florist for help?', 'answer' => 'You can reach our team on WhatsApp for recommendations, custom orders, and delivery confirmations at any time.'],
    ],
],

// 7 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Online Florist',
    'slug'  => 'online-florist',
    'content' => <<<'HTML'
<h2>Your Trusted Online Florist in Delhi NCR</h2>
<p>Sending flowers should be simple, and that is exactly what a good online florist makes possible. From fresh roses to elegant orchids, our online florist service lets you pick, personalise, and deliver beautiful bouquets in minutes. Skip the crowded shops — a reliable online florist brings the flower market straight to your screen.</p>

<p>At <strong>Sai Flower</strong>, we pair the ease of digital ordering with true florist artistry. As your online florist, we hand-arrange every bouquet using daily-fresh stems and deliver on time, so what you choose online is exactly what arrives at the doorstep.</p>

<h2>Why Shop With an Online Florist</h2>
<p>An online florist gives you time, choice, and convenience. Browse calmly, compare arrangements, and add thoughtful extras without any pressure.</p>
<ul>
<li><strong>Shop anytime</strong> — order day or night from anywhere</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, hand-tied bouquets</strong> — styled by expert florists</li>
<li><strong>Secure payments</strong> — UPI, cards, and wallets</li>
</ul>

<h3>A Bloom for Every Occasion</h3>
<p>A dependable online florist has the right flower for every celebration. Send romantic roses for an anniversary, cheerful gerberas for a birthday, or serene lilies to say get well soon. Our online florist collection also includes cakes and gift hampers so one order covers the whole surprise.</p>

<h3>Freshness and Reliability</h3>
<p>The best online florist never compromises on freshness. Each bouquet is prepared close to dispatch and packed protectively against heat and humidity, then handed to trusted riders. That is how we keep every delivery crisp, fragrant, and on schedule.</p>

<h2>Order From Your Favourite Online Florist</h2>
<p>Explore our <a href="/flowers" title="Order flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out securely. As your go-to online florist, Sai Flower makes gifting effortless, fresh, and reliable — order now and send a heartfelt bloom to someone special.</p>
HTML,
    'meta_title' => 'Online Florist in Delhi NCR | Sai Flower',
    'meta_description' => 'Sai Flower is your trusted online florist in Delhi NCR. Fresh, hand-tied bouquets with same-day & midnight delivery. Order flowers online today.',
    'meta_keywords' => 'online florist, online flower delivery, florist delhi, flower shop online, send flowers online, same day flower delivery, best florist near me',
    'faqs' => [
        ['question' => 'What is an online florist?', 'answer' => 'An online florist lets you browse and order flowers from a website for delivery to any address. Sai Flower combines easy online ordering with expert, hand-tied arrangements.'],
        ['question' => 'Is same-day delivery available from your online florist?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled options also available.'],
        ['question' => 'How fresh are the flowers?', 'answer' => 'Every bouquet is arranged with daily-fresh stems close to dispatch and packed protectively, so it arrives crisp and fragrant.'],
        ['question' => 'Can I personalise my order?', 'answer' => 'Yes. Add a personal message, choose add-ons like cakes or teddies, and select your preferred delivery date at checkout.'],
        ['question' => 'Which payment options do you accept?', 'answer' => 'Our online florist accepts UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Do you deliver to offices?', 'answer' => 'Yes. Add the company name, floor, and reception details in the notes and our rider will deliver smoothly to the workplace.'],
        ['question' => 'What areas does your online florist cover?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage by entering the address at checkout.'],
    ],
],

// 8 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Fresh Flower Delivery',
    'slug'  => 'fresh-flower-delivery',
    'content' => <<<'HTML'
<h2>Daily Fresh Flower Delivery in Delhi NCR</h2>
<p>Nothing says "I care" like blooms that look and smell garden-fresh. Our fresh flower delivery service brings just-picked stems, hand-tied and beautifully wrapped, right to the doorstep. Whether it is a celebration or a quiet gesture, fresh flower delivery keeps every petal crisp, colourful, and full of life.</p>

<p>At <strong>Sai Flower</strong>, freshness is a promise, not an afterthought. Each fresh flower delivery uses daily-sourced roses, lilies, orchids, and seasonal blooms, arranged close to dispatch so your gift arrives vibrant and long-lasting.</p>

<h2>Why Freshness Matters</h2>
<p>Flowers are emotional gifts, and wilted stems ruin the moment. Our fresh flower delivery process protects quality at every step, from sourcing to the final handover.</p>
<ul>
<li><strong>Daily-sourced stems</strong> — picked for peak freshness</li>
<li><strong>Arranged close to dispatch</strong> — no long shelf time</li>
<li><strong>Protective packaging</strong> — shields blooms from heat and humidity</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
</ul>

<h3>Fresh Flowers for Every Occasion</h3>
<p>From birthdays and anniversaries to get-well wishes and congratulations, fresh flower delivery suits every moment. Choose romantic roses, cheerful gerberas, or graceful lilies, and add a cake or gift hamper so one fresh flower delivery completes the entire surprise.</p>

<h3>Long-Lasting Beauty</h3>
<p>Fresher flowers simply last longer. Because every fresh flower delivery is prepared with just-sourced stems and careful handling, your bouquet stays beautiful for days, letting the recipient enjoy the gift well beyond the first moment.</p>

<h2>Order Fresh Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Order flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out securely with UPI, card, or wallet. With Sai Flower, fresh flower delivery means garden-fresh blooms, expert arrangement, and on-time service — order now and send freshness that truly lasts.</p>
HTML,
    'meta_title' => 'Fresh Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Get fresh flower delivery in Delhi NCR with daily-sourced, hand-tied bouquets from Sai Flower. Same-day & midnight slots. Order fresh flowers online now.',
    'meta_keywords' => 'fresh flower delivery, fresh flowers online, daily fresh flowers, same day flower delivery, online flower delivery, bouquet delivery, fresh roses online',
    'faqs' => [
        ['question' => 'How do you keep flowers fresh during delivery?', 'answer' => 'We source stems daily, arrange each bouquet close to dispatch, and use protective packaging that shields blooms from heat and humidity, so they arrive crisp and fragrant.'],
        ['question' => 'How long will fresh flowers last?', 'answer' => 'With daily-sourced stems and proper care, most bouquets stay beautiful for several days. Trim the stems and change the water regularly to extend their life.'],
        ['question' => 'Is same-day fresh flower delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Are the flowers really arranged fresh?', 'answer' => 'Absolutely. Sai Flower hand-arranges every order shortly before dispatch, so there is no long shelf time and the flowers reach you at their best.'],
        ['question' => 'Can I request specific fresh flowers?', 'answer' => 'Yes. Tell us your preferred blooms or colours on WhatsApp and we will craft a fresh arrangement to match, subject to seasonal availability.'],
        ['question' => 'Do you deliver fresh flowers to offices?', 'answer' => 'Yes. Add the workplace name, floor, and reception details in the notes and our rider will deliver the fresh bouquet smoothly.'],
        ['question' => 'What areas do you cover?', 'answer' => 'We deliver fresh flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 9 ─────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Shop Online',
    'slug'  => 'flower-shop-online',
    'content' => <<<'HTML'
<h2>Shop Fresh Blooms at Our Flower Shop Online</h2>
<p>Why visit a crowded store when you can browse a full flower shop online from home? Our flower shop online brings roses, lilies, orchids, and mixed bouquets to your screen, ready to order in minutes. With a well-stocked flower shop online, sending fresh flowers for any occasion has never been easier.</p>

<p>At <strong>Sai Flower</strong>, our flower shop online blends convenience with real florist craft. Every bouquet is hand-arranged with daily-fresh stems and delivered on time, so your online order looks just as lovely at the doorstep as it does on screen.</p>

<h2>Why Buy From a Flower Shop Online</h2>
<p>An online store gives you variety, calm browsing, and doorstep delivery. Our flower shop online is designed to make gifting quick and stress-free.</p>
<ul>
<li><strong>Wide selection</strong> — bouquets, arrangements, and combos</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Fresh, florist-made blooms</strong> — no compromise on quality</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Flowers and Gifts in One Place</h3>
<p>A complete flower shop online offers more than bouquets. Add a designer cake, chocolates, or a soft teddy to your order and cover the entire celebration in one go. Our flower shop online makes combo gifting simple, whether for a birthday, anniversary, or a thoughtful surprise.</p>

<h3>Reliable Delivery, Every Time</h3>
<p>Ordering from a flower shop online should feel dependable. We prepare each bouquet close to dispatch, pack it protectively, and hand it to trusted riders, so your flowers arrive fresh, fragrant, and right on schedule.</p>

<h2>Start Shopping at Our Flower Shop Online</h2>
<p>Explore our <a href="/flowers" title="Order flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift hamper</a>, and check out securely. For a fresh, reliable flower shop online, choose Sai Flower — order now and send a beautiful bouquet to someone you love.</p>
HTML,
    'meta_title' => 'Flower Shop Online Delhi NCR | Sai Flower',
    'meta_description' => 'Browse our flower shop online for fresh, hand-tied bouquets in Delhi NCR. Same-day & midnight delivery from Sai Flower. Order flowers & gifts online now.',
    'meta_keywords' => 'flower shop online, online flower shop, buy flowers online, online florist, online flower delivery, same day flower delivery, bouquet delivery',
    'faqs' => [
        ['question' => 'What can I buy from your flower shop online?', 'answer' => 'You can shop fresh bouquets, arrangements, and combos with cakes, chocolates, and teddies. Everything is available to order online for delivery across Delhi NCR.'],
        ['question' => 'Does your online flower shop offer same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, and choose midnight or scheduled slots for special occasions.'],
        ['question' => 'Are the flowers fresh?', 'answer' => 'Every bouquet from our flower shop online is hand-arranged with daily-fresh stems close to dispatch and packed protectively for freshness.'],
        ['question' => 'How do I pay on your online flower shop?', 'answer' => 'We accept UPI, debit and credit cards, and popular wallets through a secure, encrypted checkout.'],
        ['question' => 'Can I add a gift to my flower order?', 'answer' => 'Yes. Add a cake, chocolates, or a soft teddy and we will deliver the complete combo together where the pin code allows.'],
        ['question' => 'Which areas does your flower shop online serve?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage by entering the address at checkout.'],
        ['question' => 'Can I schedule delivery for later?', 'answer' => 'Absolutely. Pick your preferred delivery date at checkout and we will prepare and deliver the bouquet right on time.'],
    ],
],

// 10 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Bouquet Delivery',
    'slug'  => 'bouquet-delivery',
    'content' => <<<'HTML'
<h2>Beautiful Bouquet Delivery in Delhi NCR</h2>
<p>A hand-tied bouquet is a timeless way to show you care, and our bouquet delivery service makes sending one effortless. From romantic roses to cheerful mixed blooms, every bouquet delivery arrives fresh, neatly wrapped, and ready to impress. Whatever the occasion, dependable bouquet delivery turns your feelings into a beautiful surprise.</p>

<p>At <strong>Sai Flower</strong>, each bouquet delivery is crafted by expert florists using daily-fresh stems. We arrange, wrap, and dispatch with care so your bouquet looks polished from the very first glance and lasts long after it arrives.</p>

<h2>Why Choose Our Bouquet Delivery</h2>
<p>Great bouquet delivery is about freshness, presentation, and punctuality. We take care of all three so your gift always makes the right impression.</p>
<ul>
<li><strong>Hand-tied by florists</strong> — photo-perfect arrangements</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Fresh, daily-sourced blooms</strong> — roses, lilies, orchids, and more</li>
<li><strong>Secure, easy checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Bouquets for Every Occasion</h3>
<p>From birthdays and anniversaries to congratulations and get-well wishes, bouquet delivery fits every moment. Choose a classic rose bunch, a vibrant mixed arrangement, or elegant lilies, and add a cake or gift so one bouquet delivery completes the whole celebration.</p>

<h3>Fresh and On-Time, Always</h3>
<p>Timing and freshness make a bouquet special. Every bouquet delivery is prepared close to dispatch, packed protectively, and routed through reliable riders, so your flowers arrive crisp, fragrant, and right on schedule.</p>

<h2>Order Bouquet Delivery Online</h2>
<p>Explore our <a href="/flowers" title="Order flowers online Delhi">flower bouquets</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift hamper</a>, and check out in minutes. With Sai Flower, bouquet delivery is fresh, elegant, and reliable — order now and send a stunning bouquet to someone special today.</p>
HTML,
    'meta_title' => 'Bouquet Delivery in Delhi NCR | Sai Flower',
    'meta_description' => 'Order bouquet delivery in Delhi NCR with fresh, hand-tied arrangements from Sai Flower. Same-day & midnight slots available. Send a bouquet online now.',
    'meta_keywords' => 'bouquet delivery, flower bouquet delivery, online bouquet delivery, same day bouquet delivery, rose bouquet delivery, online flower delivery, send bouquet online',
    'faqs' => [
        ['question' => 'Do you offer same-day bouquet delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day bouquet delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Are bouquets hand-tied by florists?', 'answer' => 'Every bouquet is hand-tied by our expert florists using daily-fresh stems, so it looks polished and matches the photo closely.'],
        ['question' => 'What types of bouquets can I order?', 'answer' => 'Choose from rose bouquets, mixed seasonal blooms, lilies, orchids, gerberas, and premium hand-tied arrangements for every occasion and budget.'],
        ['question' => 'Can I add a cake or gift to the bouquet?', 'answer' => 'Yes. Add a designer cake, chocolates, or a soft teddy and Sai Flower will deliver the complete combo together where possible.'],
        ['question' => 'How fresh will the bouquet be?', 'answer' => 'Each bouquet is arranged close to dispatch and packed protectively, so it arrives crisp, fragrant, and long-lasting.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
        ['question' => 'Can I include a personal message?', 'answer' => 'Yes. Add a message note during checkout and we will include it with your bouquet delivery.'],
    ],
],

];

echo "=== Seeding keyword pages — Batch 1 ===\n";
$created = 0;
foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    }
}
echo "=== Done. {$created} new page(s) created. ===\n";
