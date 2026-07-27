<?php
/**
 * Seed: Keyword custom pages — Batch 5 (dynamic_pages)
 * Layout: product_showcase | Tag: sameday
 * Run once: https://saiflower.com/tools/seed_keyword_pages_batch5.php
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

// 41 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Premium Florist Delhi',
    'slug'  => 'premium-florist-delhi',
    'content' => <<<'HTML'
<h2>Experience Luxury With a Premium Florist Delhi</h2>
<p>When the occasion demands elegance, you deserve a premium florist Delhi can rely on. Combining the finest blooms, designer arrangements, and flawless delivery, a premium florist Delhi turns flowers into a statement of class. Whether it is a grand celebration or refined corporate gifting, a premium florist Delhi delivers beauty on another level.</p>

<p>At <strong>Sai Flower</strong>, being a premium florist Delhi means uncompromising quality. We hand-craft every arrangement with top-grade, daily-fresh blooms and elegant wrapping, so your gift feels luxurious from the very first glance.</p>

<h2>Why Choose a Premium Florist Delhi</h2>
<p>Premium is about detail, design, and dependability. As a trusted premium florist Delhi, we perfect every element of your order.</p>
<ul>
<li><strong>Top-grade blooms</strong> — imported and exotic varieties available</li>
<li><strong>Designer wrapping</strong> — luxury boxes, ribbons, and fine paper</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Bespoke arrangements</strong> — tailored to your occasion</li>
</ul>

<h3>Flowers for Refined Moments</h3>
<p>Anniversaries, proposals, corporate honours, and milestone birthdays call for a premium florist Delhi. Choose lush roses, elegant orchids, or a curated designer mix, and add a premium cake or hamper so a premium florist Delhi completes a truly grand gesture.</p>

<h3>Luxury You Can Trust</h3>
<p>Premium means reliable, too. Every arrangement is prepared close to dispatch, packed with care, and delivered by trusted riders, so it arrives fresh, structured, and picture-perfect.</p>

<h2>Order From a Premium Florist Delhi</h2>
<p>Explore our <a href="/flowers" title="Premium florist Delhi online">premium collection</a>, add a <a href="/cakes" title="Order premium cakes online Delhi">designer cake</a> or <a href="/gifts" title="Luxury gifts Delhi">luxury gift</a>, and check out securely. With Sai Flower, a premium florist Delhi brings elegance, freshness, and on-time delivery — order now and make the moment extraordinary.</p>
HTML,
    'meta_title' => 'Premium Florist Delhi | Sai Flower',
    'meta_description' => 'Sai Flower is a premium florist Delhi trusts for designer bouquets & top-grade blooms with same-day & midnight delivery. Order from a premium florist now.',
    'meta_keywords' => 'premium florist delhi, luxury florist delhi, premium flower bouquet, designer bouquet delhi, florist in delhi, same day flower delivery, online florist',
    'faqs' => [
        ['question' => 'What makes Sai Flower a premium florist in Delhi?', 'answer' => 'We use top-grade, daily-fresh and imported blooms, designer wrapping, and bespoke arrangements, delivered reliably across Delhi NCR for a truly luxurious experience.'],
        ['question' => 'Do you offer designer and custom bouquets?', 'answer' => 'Yes. Our florists create bespoke, designer arrangements tailored to your occasion, colour palette, and budget. Contact us on WhatsApp for custom requests.'],
        ['question' => 'Is same-day premium delivery available?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery, with midnight and scheduled slots also available.'],
        ['question' => 'Are premium bouquets good for corporate gifting?', 'answer' => 'Absolutely. Premium and luxury arrangements are ideal for corporate honours, client gifting, and formal celebrations. We also handle bulk orders.'],
        ['question' => 'What flowers are used in premium arrangements?', 'answer' => 'We use premium roses, orchids, lilies, and exotic imported varieties, arranged in curated designer combinations.'],
        ['question' => 'Can I add luxury gifts?', 'answer' => 'Yes. Pair your bouquet with a designer cake, chocolates, or a premium hamper and we will deliver the complete combo together.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 42 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Affordable Flower Delivery',
    'slug'  => 'affordable-flower-delivery',
    'content' => <<<'HTML'
<h2>Budget-Friendly, Affordable Flower Delivery</h2>
<p>Beautiful flowers should not cost a fortune, and our affordable flower delivery proves it. Enjoy fresh, hand-tied bouquets at friendly prices, delivered right to the doorstep. Whether it is a birthday, anniversary, or a simple gesture, affordable flower delivery lets you gift generously without stretching your budget. Quality and value meet in every affordable flower delivery.</p>

<p>At <strong>Sai Flower</strong>, affordable flower delivery never means lower quality. We use daily-fresh blooms and expert arrangement, so even our budget bouquets look elegant and arrive beautifully presented.</p>

<h2>Why Choose Our Affordable Flower Delivery</h2>
<p>Great value is about balancing price and quality. Our affordable flower delivery gives you both, on every order.</p>
<ul>
<li><strong>Wallet-friendly prices</strong> — fresh bouquets for every budget</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Fresh, hand-tied blooms</strong> — no compromise on quality</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Beautiful Blooms Without Overspending</h3>
<p>From cheerful carnations to mixed seasonal bunches, affordable flower delivery offers plenty of lovely, budget-smart choices. Add a small cake or gift, and one affordable flower delivery still makes a complete, heartfelt surprise.</p>

<h3>Fresh and On Time, Even on a Budget</h3>
<p>Value should never mean wilted stems. Each affordable flower delivery is prepared close to dispatch and packed protectively, so your bouquet arrives crisp, colourful, and long-lasting.</p>

<h2>Order Affordable Flower Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Affordable flower delivery Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, affordable flower delivery is fresh, elegant, and easy on the wallet — order now and send a beautiful surprise for less.</p>
HTML,
    'meta_title' => 'Affordable Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Enjoy affordable flower delivery in Delhi NCR with fresh, budget-friendly bouquets from Sai Flower. Same-day & midnight slots. Order affordable flowers now.',
    'meta_keywords' => 'affordable flower delivery, cheap flower delivery, budget bouquet, low cost flowers, fresh flower delivery, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'Are affordable bouquets still fresh?', 'answer' => 'Yes. Sai Flower uses daily-fresh blooms and expert arrangement for every order, so even budget bouquets look elegant and arrive fresh.'],
        ['question' => 'What is the starting price for flowers?', 'answer' => 'We offer wallet-friendly bunches at everyday rates, with exact prices shown transparently on each product before you pay.'],
        ['question' => 'Is same-day affordable delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Which flowers are most budget-friendly?', 'answer' => 'Carnations, gerberas, and mixed seasonal bunches offer beautiful blooms at friendly prices.'],
        ['question' => 'Can I still add a cake or gift?', 'answer' => 'Yes. Add a small cake or gift and we will deliver the complete combo together within your budget.'],
        ['question' => 'Are there any hidden charges?', 'answer' => 'No. All costs, including any delivery fee, are shown clearly at checkout before you pay.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 43 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower Delivery with Cake',
    'slug'  => 'flower-delivery-with-cake',
    'content' => <<<'HTML'
<h2>Perfect Pairing: Flower Delivery with Cake</h2>
<p>Some gifts are simply better together, and flower delivery with cake is the ultimate celebration combo. Fresh blooms and a delicious cake arrive together, doubling the joy for any occasion. Whether it is a birthday, anniversary, or surprise, flower delivery with cake makes the moment extra special. One order of flower delivery with cake covers everything.</p>

<p>At <strong>Sai Flower</strong>, every flower delivery with cake is prepared with daily-fresh blooms and freshly baked cakes. We coordinate both so they arrive together, fresh, beautiful, and ready to celebrate.</p>

<h2>Why Choose Flower Delivery with Cake</h2>
<p>Combining flowers and cake saves time and delights twice. Our flower delivery with cake makes celebrating effortless.</p>
<ul>
<li><strong>Two gifts, one order</strong> — flowers plus a fresh cake</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Wide cake range</strong> — chocolate, truffle, butterscotch, and more</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Ideal for Every Celebration</h3>
<p>Birthdays, anniversaries, and congratulations all shine brighter with flower delivery with cake. Pair a rose bouquet with a chocolate cake, or mixed blooms with a butterscotch treat. However you combine them, flower delivery with cake creates a complete celebration at the door.</p>

<h3>Fresh Flowers, Fresh Cake, On Time</h3>
<p>Both freshness and timing matter. Each flower delivery with cake is prepared close to dispatch and packed with care, so your blooms stay crisp and your cake arrives fresh and delicious.</p>

<h2>Order Flower Delivery with Cake Online</h2>
<p>Browse our <a href="/flowers" title="Flower delivery with cake Delhi">flowers</a>, choose a <a href="/cakes" title="Order cakes online">cake</a>, or add a <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, flower delivery with cake is fresh, delicious, and reliable — order now and double the joy.</p>
HTML,
    'meta_title' => 'Flower Delivery with Cake Delhi NCR | Sai Flower',
    'meta_description' => 'Order flower delivery with cake in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh bouquets & cakes together. Send flowers with cake now.',
    'meta_keywords' => 'flower delivery with cake, cake and flower delivery, flowers and cake combo, birthday flowers and cake, same day flower delivery, online flower delivery, gift combo delivery',
    'faqs' => [
        ['question' => 'Can I order flowers and cake together?', 'answer' => 'Yes. Add a bouquet and a cake to the same order and Sai Flower will deliver them together, fresh and beautifully presented.'],
        ['question' => 'What cake flavours are available?', 'answer' => 'We offer chocolate, truffle, butterscotch, black forest, and more. Browse the cakes section to choose your favourite.'],
        ['question' => 'Is same-day flower and cake delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Can I get midnight flower and cake delivery?', 'answer' => 'Yes, in selected pin codes. Perfect for birthday surprises right at 12 a.m.'],
        ['question' => 'Will the cake arrive fresh?', 'answer' => 'Yes. Cakes are freshly prepared and packed with care so they arrive fresh and delicious alongside your flowers.'],
        ['question' => 'Can I add a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your flower and cake combo.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 44 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Cake and Flower Delivery',
    'slug'  => 'cake-and-flower-delivery',
    'content' => <<<'HTML'
<h2>Celebrate Together With Cake and Flower Delivery</h2>
<p>Nothing says celebration like cake and flower delivery arriving side by side. Fresh blooms paired with a delicious cake create a complete, joyful surprise for any occasion. Whether it is a birthday, anniversary, or special milestone, cake and flower delivery doubles the delight. One order of cake and flower delivery handles the whole celebration effortlessly.</p>

<p>At <strong>Sai Flower</strong>, every cake and flower delivery is prepared with daily-fresh blooms and freshly baked cakes. We coordinate both to arrive together, fresh, beautiful, and ready to make the day memorable.</p>

<h2>Why Choose Our Cake and Flower Delivery</h2>
<p>Bundling cake and flowers is convenient and thoughtful. Our cake and flower delivery makes gifting simple and complete.</p>
<ul>
<li><strong>Two gifts in one</strong> — a bouquet plus a fresh cake</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Delicious cake range</strong> — chocolate, truffle, butterscotch, and more</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Perfect for Every Occasion</h3>
<p>Birthdays, anniversaries, and congratulations feel complete with cake and flower delivery. Pair roses with a truffle cake, or gerberas with butterscotch. However you mix them, cake and flower delivery creates a celebration at the doorstep.</p>

<h3>Fresh and On Time, Together</h3>
<p>Freshness and timing are everything. Each cake and flower delivery is prepared close to dispatch and packed with care, so the flowers stay crisp and the cake arrives fresh and tasty.</p>

<h2>Order Cake and Flower Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Cake and flower delivery Delhi">flowers</a>, pick a <a href="/cakes" title="Order cakes online">cake</a>, or add a <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, cake and flower delivery is fresh, delicious, and reliable — order now and celebrate in style.</p>
HTML,
    'meta_title' => 'Cake and Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Order cake and flower delivery in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh bouquets & cakes together. Send cake and flowers now.',
    'meta_keywords' => 'cake and flower delivery, flower delivery with cake, flowers and cake combo, birthday cake and flowers, same day flower delivery, online flower delivery, gift combo delivery',
    'faqs' => [
        ['question' => 'Can I order a cake and flowers together?', 'answer' => 'Yes. Add a bouquet and a cake to the same order and Sai Flower will deliver them together, fresh and beautifully presented.'],
        ['question' => 'What cakes can I choose from?', 'answer' => 'We offer chocolate, truffle, butterscotch, black forest, and more. Browse the cakes section to pick your favourite.'],
        ['question' => 'Is same-day cake and flower delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Do you offer midnight combo delivery?', 'answer' => 'Yes, in selected pin codes, perfect for birthday surprises right at 12 a.m.'],
        ['question' => 'Will both items arrive fresh?', 'answer' => 'Yes. Flowers are arranged close to dispatch and cakes are freshly prepared, both packed with care for freshness.'],
        ['question' => 'Can I include a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your combo.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 45 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Flower and Chocolate Delivery',
    'slug'  => 'flower-and-chocolate-delivery',
    'content' => <<<'HTML'
<h2>Sweeten the Moment With Flower and Chocolate Delivery</h2>
<p>Fresh blooms and rich chocolates make an irresistible pair, and flower and chocolate delivery brings them together beautifully. Perfect for romance, celebrations, and heartfelt surprises, flower and chocolate delivery delights every sense. Whether it is love or gratitude, flower and chocolate delivery turns an ordinary day into something sweet and memorable.</p>

<p>At <strong>Sai Flower</strong>, every flower and chocolate delivery is prepared with daily-fresh blooms and quality chocolates. We coordinate both to arrive together, fresh, elegant, and ready to impress.</p>

<h2>Why Choose Flower and Chocolate Delivery</h2>
<p>Combining flowers and chocolates makes gifting doubly delightful. Our flower and chocolate delivery is simple, thoughtful, and complete.</p>
<ul>
<li><strong>Two treats in one</strong> — a bouquet plus chocolates</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Popular chocolate brands</strong> — boxed and assorted options</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Perfect for Romance and Celebration</h3>
<p>Anniversaries, proposals, and birthdays feel extra special with flower and chocolate delivery. Pair red roses with a box of chocolates for romance, or mixed blooms with assorted treats for a birthday. However you mix them, flower and chocolate delivery creates a sweet surprise.</p>

<h3>Fresh and On Time, Together</h3>
<p>Freshness and timing matter. Each flower and chocolate delivery is prepared close to dispatch and packed with care, so your flowers stay crisp and your chocolates arrive in perfect condition.</p>

<h2>Order Flower and Chocolate Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Flower and chocolate delivery Delhi">flowers</a>, add <a href="/gifts" title="Chocolates and gifts Delhi">chocolates or gifts</a>, or include a <a href="/cakes" title="Order cakes online">cake</a>, and check out in minutes. With Sai Flower, flower and chocolate delivery is fresh, sweet, and reliable — order now and delight someone special.</p>
HTML,
    'meta_title' => 'Flower and Chocolate Delivery Delhi | Sai Flower',
    'meta_description' => 'Order flower and chocolate delivery in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh bouquets & chocolates together. Send flowers now.',
    'meta_keywords' => 'flower and chocolate delivery, flowers and chocolates combo, bouquet with chocolates, romantic gift combo, same day flower delivery, online flower delivery, gift combo delivery',
    'faqs' => [
        ['question' => 'Can I order flowers with chocolates?', 'answer' => 'Yes. Add a bouquet and chocolates to the same order and Sai Flower will deliver them together, fresh and beautifully presented.'],
        ['question' => 'What chocolates are available?', 'answer' => 'We offer popular boxed and assorted chocolates. Browse the gifts section to choose your preferred option.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Is this combo good for anniversaries?', 'answer' => 'Yes. Red roses with a box of chocolates make a wonderfully romantic anniversary or proposal gift.'],
        ['question' => 'Will the chocolates arrive in good condition?', 'answer' => 'Yes. Chocolates are packed with care and delivered promptly so they arrive in perfect condition alongside your flowers.'],
        ['question' => 'Can I add a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your combo.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 46 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Bouquet with Teddy',
    'slug'  => 'bouquet-with-teddy',
    'content' => <<<'HTML'
<h2>Adorable Gifting With a Bouquet with Teddy</h2>
<p>Nothing melts hearts quite like a bouquet with teddy. Combining fresh flowers with a soft, cuddly teddy bear, this charming combo is perfect for romance, birthdays, and sweet surprises. A bouquet with teddy brings smiles instantly, especially for someone who loves a little extra cuteness. One order of bouquet with teddy delivers double the affection.</p>

<p>At <strong>Sai Flower</strong>, every bouquet with teddy pairs daily-fresh blooms with a lovable teddy bear. We coordinate both to arrive together, fresh, cute, and ready to delight.</p>

<h2>Why Choose a Bouquet with Teddy</h2>
<p>Flowers plus a teddy make a gift both beautiful and huggable. Our bouquet with teddy is a delightful, memorable choice.</p>
<ul>
<li><strong>Two gifts in one</strong> — fresh flowers plus a soft teddy</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Cute teddy options</strong> — various sizes and colours</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Perfect for Sweet Occasions</h3>
<p>Valentine's Day, birthdays, and romantic surprises shine with a bouquet with teddy. Pair red roses with a plush teddy for love, or mixed blooms with a cute bear for a birthday. However you mix them, a bouquet with teddy creates an adorable surprise.</p>

<h3>Fresh Flowers, Cuddly Teddy, On Time</h3>
<p>Both freshness and cuteness matter. Each bouquet with teddy is prepared close to dispatch and packed with care, so the flowers stay crisp and the teddy arrives soft and huggable.</p>

<h2>Order a Bouquet with Teddy Online</h2>
<p>Browse our <a href="/flowers" title="Bouquet with teddy Delhi">flowers</a>, add a <a href="/gifts" title="Teddy and gifts Delhi">teddy or gift</a>, or include a <a href="/cakes" title="Order cakes online">cake</a>, and check out in minutes. With Sai Flower, a bouquet with teddy is fresh, cute, and reliable — order now and send double the love.</p>
HTML,
    'meta_title' => 'Bouquet with Teddy Delivery Delhi | Sai Flower',
    'meta_description' => 'Order a bouquet with teddy in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh flowers plus a cuddly teddy. Send a bouquet with teddy now.',
    'meta_keywords' => 'bouquet with teddy, flowers with teddy, teddy and flowers combo, valentine gift combo, same day flower delivery, online flower delivery, gift combo delivery',
    'faqs' => [
        ['question' => 'Can I order flowers with a teddy?', 'answer' => 'Yes. Add a bouquet and a teddy to the same order and Sai Flower will deliver them together, fresh and adorable.'],
        ['question' => 'What teddy sizes are available?', 'answer' => 'We offer teddies in various sizes and colours. Browse the gifts section to choose your preferred option.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Is this combo good for Valentine\'s Day?', 'answer' => 'Yes. Red roses with a plush teddy make a wonderfully romantic Valentine\'s Day or anniversary gift.'],
        ['question' => 'Will the teddy be soft and good quality?', 'answer' => 'Yes. Our teddies are soft, cuddly, and packed with care so they arrive in perfect condition alongside your flowers.'],
        ['question' => 'Can I add a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your combo.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 47 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Gift Combo Delivery',
    'slug'  => 'gift-combo-delivery',
    'content' => <<<'HTML'
<h2>Complete Surprises With Gift Combo Delivery</h2>
<p>Why send one gift when you can send several together? Gift combo delivery bundles fresh flowers with cakes, chocolates, or teddies for a complete, impressive surprise. Perfect for birthdays, anniversaries, and celebrations, gift combo delivery makes gifting effortless. One order of gift combo delivery covers everything in a single, thoughtful package.</p>

<p>At <strong>Sai Flower</strong>, every gift combo delivery pairs daily-fresh blooms with your chosen add-ons. We coordinate each item to arrive together, fresh, beautiful, and ready to celebrate.</p>

<h2>Why Choose Gift Combo Delivery</h2>
<p>Combos offer more value and more impact. Our gift combo delivery makes celebrating simple, generous, and complete.</p>
<ul>
<li><strong>Multiple gifts in one</strong> — flowers plus cake, chocolates, or teddy</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Flexible combos</strong> — mix and match your favourites</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Perfect for Every Celebration</h3>
<p>Birthdays, anniversaries, and congratulations feel complete with gift combo delivery. Pair roses with a cake and chocolates, or mixed blooms with a teddy. However you build it, gift combo delivery creates a memorable surprise at the doorstep.</p>

<h3>Fresh and On Time, Together</h3>
<p>Coordination is key. Each gift combo delivery is prepared close to dispatch and packed with care, so every item arrives fresh, intact, and right on schedule.</p>

<h2>Order Gift Combo Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Gift combo delivery Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift combos Delhi">gift</a>, and check out in minutes. With Sai Flower, gift combo delivery is fresh, complete, and reliable — order now and send the perfect all-in-one surprise.</p>
HTML,
    'meta_title' => 'Gift Combo Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Order gift combo delivery in Delhi NCR with same-day & midnight slots from Sai Flower. Flowers with cake, chocolates or teddy. Send a gift combo now.',
    'meta_keywords' => 'gift combo delivery, flower gift combo, flowers cake chocolate combo, bouquet with teddy, same day flower delivery, online flower delivery, cake and flower delivery',
    'faqs' => [
        ['question' => 'What is included in a gift combo?', 'answer' => 'A gift combo bundles fresh flowers with add-ons like cakes, chocolates, or teddies. You can mix and match to build the perfect surprise.'],
        ['question' => 'Can I customise my combo?', 'answer' => 'Yes. Add your preferred bouquet plus any combination of cake, chocolates, or a teddy to the same order.'],
        ['question' => 'Is same-day combo delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight slots also available.'],
        ['question' => 'Do you offer midnight combo delivery?', 'answer' => 'Yes, in selected pin codes, perfect for birthday and anniversary surprises at 12 a.m.'],
        ['question' => 'Will all items arrive fresh and intact?', 'answer' => 'Yes. Every item is packed with care and coordinated to arrive together, fresh and in perfect condition.'],
        ['question' => 'Can I add a message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your gift combo.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 48 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Same Day Bouquet Delivery',
    'slug'  => 'same-day-bouquet-delivery',
    'content' => <<<'HTML'
<h2>Fast Same Day Bouquet Delivery in Delhi NCR</h2>
<p>When you need a beautiful bouquet today, our same day bouquet delivery has you covered. Order in the morning or afternoon, and we hand-tie and dispatch your bouquet within hours. Ideal for last-minute birthdays, apologies, and surprises, same day bouquet delivery ensures your gift arrives fresh and right on time. Reliable same day bouquet delivery makes every moment count.</p>

<p>At <strong>Sai Flower</strong>, speed never lowers quality. Every same day bouquet delivery is prepared with daily-fresh stems, hand-tied by florists, and routed through quick, dependable riders so it arrives crisp and beautiful.</p>

<h2>Why Choose Our Same Day Bouquet Delivery</h2>
<p>Last-minute gifting should feel easy. Our same day bouquet delivery keeps popular blooms ready so your urgent order never feels rushed.</p>
<ul>
<li><strong>Order before the cut-off</strong> — and we deliver today</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Wide NCR coverage</strong> — Delhi, Gurgaon, Noida, and nearby areas</li>
<li><strong>Live help</strong> — WhatsApp us for express slots</li>
</ul>

<h3>Perfect for Last-Minute Occasions</h3>
<p>Forgot a special date? Same day bouquet delivery saves the moment. Send a romantic rose bunch, a cheerful birthday arrangement, or elegant lilies within hours. Add a cake or gift, and one same day bouquet delivery covers the entire surprise.</p>

<h3>Freshness Even at Speed</h3>
<p>Quick does not mean careless. Each bouquet for same day bouquet delivery is assembled close to dispatch and packed protectively, so petals stay firm and fragrant on arrival.</p>

<h2>Book Same Day Bouquet Delivery Online</h2>
<p>Pick a bouquet from our <a href="/flowers" title="Same day bouquet delivery Delhi">collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and choose the same-day slot. With Sai Flower, same day bouquet delivery is fast, fresh, and stress-free — order now and surprise someone today.</p>
HTML,
    'meta_title' => 'Same Day Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Get same day bouquet delivery in Delhi NCR with fresh, hand-tied arrangements from Sai Flower. Order before cut-off & surprise them today. Book now.',
    'meta_keywords' => 'same day bouquet delivery, same day flower delivery, urgent bouquet delivery, bouquet delivered today, express flower delivery, online flower delivery, bouquet delivery',
    'faqs' => [
        ['question' => 'What is the cut-off for same day bouquet delivery?', 'answer' => 'Order before our daily cut-off to guarantee same-day dispatch across Delhi NCR. For later orders, midnight or next-day slots apply. WhatsApp us to confirm timing.'],
        ['question' => 'Which areas are covered?', 'answer' => 'We deliver the same day across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Enter your pin code at checkout to confirm.'],
        ['question' => 'Are same-day bouquets fresh?', 'answer' => 'Yes. Every same-day bouquet is hand-tied with daily-fresh stems close to dispatch, so freshness and presentation stay high.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Absolutely. Add a cake, chocolates, or a teddy and we will deliver the complete combo together where possible.'],
        ['question' => 'Is there an extra charge for same-day delivery?', 'answer' => 'Any applicable delivery fee is shown transparently at checkout before you pay.'],
        ['question' => 'Can I choose a delivery time?', 'answer' => 'You can request a preferred time window at checkout or on WhatsApp, and we will do our best to accommodate it.'],
        ['question' => 'How do I confirm my order?', 'answer' => 'You will receive an order confirmation, and our team is available on WhatsApp for live updates.'],
    ],
],

// 49 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Midnight Bouquet Delivery',
    'slug'  => 'midnight-bouquet-delivery',
    'content' => <<<'HTML'
<h2>Surprise Them With Midnight Bouquet Delivery</h2>
<p>Be the first to make someone smile with midnight bouquet delivery. A fresh bouquet arriving right at 12 a.m. turns any birthday, anniversary, or surprise into an unforgettable memory. With reliable midnight bouquet delivery, you can celebrate the moment the clock strikes twelve. One midnight bouquet delivery creates a magical start to their special day.</p>

<p>At <strong>Sai Flower</strong>, every midnight bouquet delivery is hand-tied with daily-fresh blooms and dispatched carefully so it arrives beautiful, fragrant, and right on time at midnight.</p>

<h2>Why Choose Midnight Bouquet Delivery</h2>
<p>A midnight surprise shows extra thought and beats the morning rush of wishes. Our midnight bouquet delivery is trusted for punctuality and presentation.</p>
<ul>
<li><strong>On-time 12 a.m. arrival</strong> — be first to celebrate</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, mixed blooms, and more</li>
<li><strong>Add-ons available</strong> — cakes, chocolates, and teddies</li>
<li><strong>NCR coverage</strong> — Delhi, Gurgaon, Noida, and nearby areas</li>
</ul>

<h3>Perfect for Special Surprises</h3>
<p>Birthdays are the most popular reason for midnight bouquet delivery, but anniversaries and proposals work beautifully too. Pair a romantic rose bouquet with a midnight cake, and one midnight bouquet delivery becomes an unforgettable celebration.</p>

<h3>Book Early to Reserve Your Slot</h3>
<p>Midnight slots are limited and fill quickly on weekends and festivals. Ordering in advance secures your bouquet and time. Our team confirms every midnight bouquet delivery so there are no last-minute worries.</p>

<h2>Order Midnight Bouquet Delivery Online</h2>
<p>Choose a bouquet from our <a href="/flowers" title="Midnight bouquet delivery Delhi">collection</a>, add a <a href="/cakes" title="Midnight cakes Delhi">midnight cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, then select the midnight slot. With Sai Flower, midnight bouquet delivery is smooth and delightful — order now and make their celebration unforgettable.</p>
HTML,
    'meta_title' => 'Midnight Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Surprise loved ones with midnight bouquet delivery in Delhi NCR. Fresh bouquets & cakes at 12 a.m. from Sai Flower. Book your midnight bouquet slot now.',
    'meta_keywords' => 'midnight bouquet delivery, midnight flower delivery, 12 am bouquet delivery, birthday bouquet midnight, same day bouquet delivery, online flower delivery, bouquet delivery',
    'faqs' => [
        ['question' => 'What time is midnight bouquet delivery made?', 'answer' => 'Midnight orders are delivered around 12 a.m., typically between 11:30 p.m. and 12:30 a.m., so you can be the first to wish your loved one.'],
        ['question' => 'Which areas support midnight bouquet delivery?', 'answer' => 'Midnight delivery is available in selected pin codes across Delhi, Gurgaon, and Noida. Confirm your pin code at checkout or on WhatsApp.'],
        ['question' => 'Should I book in advance?', 'answer' => 'Yes. Midnight slots are limited and fill fast on weekends and festivals, so we recommend ordering at least a day ahead.'],
        ['question' => 'Can I add a cake to midnight bouquet delivery?', 'answer' => 'Absolutely. Pair your bouquet with a midnight cake or chocolates and we will deliver the complete surprise together at 12 a.m.'],
        ['question' => 'Is there an extra fee for midnight delivery?', 'answer' => 'Any midnight delivery charge is shown clearly at checkout before payment.'],
        ['question' => 'What occasions suit midnight bouquet delivery?', 'answer' => 'Birthdays, anniversaries, and surprise celebrations are perfect for midnight delivery.'],
        ['question' => 'How do I confirm my order?', 'answer' => 'You will get an order confirmation, and our team stays reachable on WhatsApp to confirm timing.'],
    ],
],

// 50 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Surprise Flower Delivery',
    'slug'  => 'surprise-flower-delivery',
    'content' => <<<'HTML'
<h2>Make Their Day With Surprise Flower Delivery</h2>
<p>There is nothing like the joy of an unexpected bouquet, and surprise flower delivery captures that magic perfectly. Whether it is a birthday, anniversary, or a simple "just because," surprise flower delivery brings instant smiles. Fresh blooms arriving out of the blue show thoughtfulness like nothing else. One surprise flower delivery can turn an ordinary day into a cherished memory.</p>

<p>At <strong>Sai Flower</strong>, every surprise flower delivery is hand-tied with daily-fresh blooms and delivered discreetly and on time, so the moment stays a wonderful secret until the doorbell rings.</p>

<h2>Why Choose Surprise Flower Delivery</h2>
<p>A surprise adds emotion and delight to any gift. Our surprise flower delivery is designed to make the reveal seamless and memorable.</p>
<ul>
<li><strong>Discreet, well-timed delivery</strong> — keep the surprise intact</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Fresh, hand-tied bouquets</strong> — roses, lilies, and mixes</li>
<li><strong>Add-ons available</strong> — cakes, chocolates, and teddies</li>
</ul>

<h3>Perfect for Every Special Moment</h3>
<p>From romantic gestures to cheer-up moments, surprise flower delivery suits every occasion. Send a rose bouquet to your partner, or cheerful blooms to a friend. Add a cake or gift, and one surprise flower delivery becomes a complete, joyful shock of happiness.</p>

<h3>Fresh and Perfectly Timed</h3>
<p>Timing makes a surprise special. Each surprise flower delivery is prepared close to dispatch and packed protectively, so your blooms arrive crisp, fragrant, and right when you planned.</p>

<h2>Order Surprise Flower Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Surprise flower delivery Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, surprise flower delivery is fresh, thoughtful, and reliable — order now and surprise someone you love.</p>
HTML,
    'meta_title' => 'Surprise Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Plan a surprise flower delivery in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh, hand-tied bouquets delivered on time. Surprise them now.',
    'meta_keywords' => 'surprise flower delivery, surprise bouquet, unexpected flowers, same day flower delivery, midnight flower delivery, online flower delivery, bouquet delivery',
    'faqs' => [
        ['question' => 'How do you keep the delivery a surprise?', 'answer' => 'We deliver discreetly at your chosen time. You can add delivery instructions at checkout so the surprise stays intact until the doorbell rings.'],
        ['question' => 'Can I schedule a surprise for a specific time?', 'answer' => 'Yes. Choose your preferred delivery slot at checkout, or WhatsApp us to coordinate the perfect surprise timing.'],
        ['question' => 'Is same-day surprise delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery, with midnight slots also available for extra impact.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Add a cake, chocolates, or a teddy and we will deliver the complete surprise combo together.'],
        ['question' => 'Can I send flowers without revealing my name?', 'answer' => 'Yes. You can choose to keep the sender anonymous or add a signed message at checkout.'],
        ['question' => 'Will the flowers be fresh?', 'answer' => 'Every bouquet is arranged close to dispatch and packed protectively, so it arrives crisp, fragrant, and long-lasting.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

];

echo "=== Seeding keyword pages — Batch 5 ===\n";
$created = 0;
foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    }
}
echo "=== Done. {$created} new page(s) created. ===\n";
