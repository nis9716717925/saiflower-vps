<?php
/**
 * Seed: Keyword custom pages — Batch 2 (dynamic_pages)
 * Layout: product_showcase | Tag: sameday
 * Run once: https://saiflower.com/tools/seed_keyword_pages_batch2.php
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

// 11 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Rose Bouquet Delivery',
    'slug'  => 'rose-bouquet-delivery',
    'content' => <<<'HTML'
<h2>Elegant Rose Bouquet Delivery in Delhi NCR</h2>
<p>Few gifts speak from the heart like roses, and our rose bouquet delivery makes sending them effortless. Whether you want deep red roses for romance or soft pastels for a gentle thank you, every rose bouquet delivery arrives fresh, hand-tied, and beautifully wrapped. It is the simplest way to turn a quiet feeling into a memorable moment.</p>

<p>At <strong>Sai Flower</strong>, each rose bouquet delivery is crafted by expert florists using daily-fresh roses. We select firm, fragrant blooms, arrange them close to dispatch, and route your order through trusted riders so the bouquet looks stunning at the doorstep.</p>

<h2>Why Choose Our Rose Bouquet Delivery</h2>
<p>Roses deserve care from farm to door. Our rose bouquet delivery protects that freshness at every step, so your gift feels as premium as the emotion behind it.</p>
<ul>
<li><strong>Daily-fresh roses</strong> — red, pink, white, yellow, and mixed shades</li>
<li><strong>Same-day and midnight slots</strong> — across Delhi NCR</li>
<li><strong>Hand-tied by florists</strong> — elegant, photo-perfect wrapping</li>
<li><strong>Secure checkout</strong> — UPI, cards, and wallets</li>
</ul>

<h3>Roses for Every Emotion</h3>
<p>Red roses say "I love you," yellow roses celebrate friendship, and white roses convey respect and peace. Whatever the message, rose bouquet delivery carries it beautifully. Add a cake, chocolates, or a soft teddy, and a single rose bouquet delivery becomes a complete, heartfelt surprise.</p>

<h3>Fresh, Fragrant, On Time</h3>
<p>Timing matters as much as beauty. Each rose bouquet delivery is prepared shortly before dispatch and packed to shield petals from heat and humidity, so your roses arrive crisp, richly coloured, and long-lasting.</p>

<h2>Order Rose Bouquet Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Order rose bouquets online Delhi">rose bouquets</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, rose bouquet delivery is fresh, romantic, and reliable — order now and send a stunning rose bouquet to someone you adore.</p>
HTML,
    'meta_title' => 'Rose Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send fresh rose bouquet delivery in Delhi NCR with same-day & midnight slots. Hand-tied red & mixed roses from Sai Flower. Order a rose bouquet online now.',
    'meta_keywords' => 'rose bouquet delivery, red rose bouquet, rose delivery online, romantic flower bouquet, same day flower delivery, online flower delivery, bouquet delivery',
    'faqs' => [
        ['question' => 'Do you offer same-day rose bouquet delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day rose bouquet delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'What rose colours can I choose?', 'answer' => 'You can pick red, pink, white, yellow, or mixed roses. Each colour carries a different meaning, and our florists can help you choose the right one.'],
        ['question' => 'Are the roses fresh?', 'answer' => 'Sai Flower uses daily-fresh, firm, fragrant roses arranged close to dispatch and packed protectively, so your bouquet arrives crisp and long-lasting.'],
        ['question' => 'How many roses come in a bouquet?', 'answer' => 'We offer arrangements ranging from small bunches to premium 50 or 100-rose bouquets. Choose the size that fits your occasion and budget.'],
        ['question' => 'Can I add a cake or teddy to my rose bouquet?', 'answer' => 'Absolutely. Add a cake, chocolates, or a soft teddy and we will deliver the complete combo together where the pin code allows.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver rose bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
        ['question' => 'Can I include a romantic message?', 'answer' => 'Yes. Add a personal message at checkout and we will include it with your rose bouquet delivery.'],
    ],
],

// 12 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Premium Flower Bouquet',
    'slug'  => 'premium-flower-bouquet',
    'content' => <<<'HTML'
<h2>Send a Stunning Premium Flower Bouquet</h2>
<p>When the occasion calls for something special, a premium flower bouquet makes an unforgettable impression. Featuring larger stems, richer colours, and designer wrapping, each premium flower bouquet is built to wow. Whether it is a milestone celebration or a grand romantic gesture, a premium flower bouquet says you chose nothing but the best.</p>

<p>At <strong>Sai Flower</strong>, every premium flower bouquet is hand-crafted by skilled florists using top-grade, daily-fresh blooms. We focus on flawless stems, balanced design, and elegant presentation so your gift feels luxurious from the very first glance.</p>

<h2>What Makes a Bouquet Truly Premium</h2>
<p>A premium flower bouquet is more than extra flowers — it is superior quality and thoughtful design. Every detail is chosen to elevate the gift.</p>
<ul>
<li><strong>Top-grade blooms</strong> — imported and exotic varieties available</li>
<li><strong>Designer wrapping</strong> — luxury paper, boxes, and ribbons</li>
<li><strong>Fuller arrangements</strong> — generous, statement-making stems</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
</ul>

<h3>Perfect for Special Moments</h3>
<p>Anniversaries, proposals, big birthdays, and corporate honours all deserve a premium flower bouquet. Choose lush roses, elegant orchids, or a curated designer mix, and add a cake or gift so one premium flower bouquet completes a truly grand surprise.</p>

<h3>Luxury You Can Trust</h3>
<p>Premium means reliable, too. Each premium flower bouquet is arranged close to dispatch, packed with care, and delivered by trusted riders, so it arrives fresh, structured, and picture-perfect at the doorstep.</p>

<h2>Order a Premium Flower Bouquet Online</h2>
<p>Explore our <a href="/flowers" title="Order premium bouquets online Delhi">premium bouquets</a>, add a <a href="/cakes" title="Order cakes online">designer cake</a> or <a href="/gifts" title="Gift hampers Delhi">luxury gift</a>, and check out securely. With Sai Flower, a premium flower bouquet brings elegance, freshness, and on-time delivery — order now and make the moment extraordinary.</p>
HTML,
    'meta_title' => 'Premium Flower Bouquet Delivery | Sai Flower',
    'meta_description' => 'Order a premium flower bouquet in Delhi NCR with designer wrapping & top-grade blooms from Sai Flower. Same-day & midnight delivery. Shop premium bouquets now.',
    'meta_keywords' => 'premium flower bouquet, luxury flower bouquet, designer bouquet, premium roses, exotic flower bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What makes a bouquet premium?', 'answer' => 'A premium flower bouquet uses top-grade, daily-fresh blooms, fuller arrangements, and designer wrapping. Sai Flower focuses on flawless stems and elegant presentation for a luxurious look.'],
        ['question' => 'Can I get a premium bouquet delivered same day?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'What flowers are used in premium bouquets?', 'answer' => 'Premium bouquets feature roses, orchids, lilies, and exotic imported varieties, arranged in curated designer combinations.'],
        ['question' => 'Do premium bouquets come in a box?', 'answer' => 'Yes. We offer luxury boxes, hat-boxes, and designer paper wrapping with ribbons, depending on the arrangement you choose.'],
        ['question' => 'Can I add gifts to a premium bouquet?', 'answer' => 'Absolutely. Pair it with a designer cake, chocolates, or a premium hamper and we will deliver the complete luxury combo together.'],
        ['question' => 'Are premium bouquets suitable for corporate gifting?', 'answer' => 'Yes. A premium flower bouquet is ideal for corporate honours, client gifting, and formal celebrations. Contact us for bulk orders.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver premium bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 13 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Luxury Flower Bouquet',
    'slug'  => 'luxury-flower-bouquet',
    'content' => <<<'HTML'
<h2>Indulge Them With a Luxury Flower Bouquet</h2>
<p>For moments that deserve grandeur, nothing compares to a luxury flower bouquet. Overflowing with premium blooms and finished with elegant wrapping, each luxury flower bouquet is a statement of love, success, and celebration. When you want to truly impress, a luxury flower bouquet delivers beauty on a whole new level.</p>

<p>At <strong>Sai Flower</strong>, every luxury flower bouquet is designed by expert florists using the finest daily-fresh and imported blooms. We craft opulent arrangements with balanced shape and refined detailing so your gift feels as exclusive as the occasion.</p>

<h2>The Hallmarks of a Luxury Bouquet</h2>
<p>A luxury flower bouquet stands apart through quality, scale, and presentation. Every element is chosen to create a lasting impression.</p>
<ul>
<li><strong>Finest blooms</strong> — premium roses, orchids, and exotic flowers</li>
<li><strong>Opulent design</strong> — generous, lush, and beautifully balanced</li>
<li><strong>Signature wrapping</strong> — luxury boxes, silk ribbons, and fine paper</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
</ul>

<h3>For Life's Grandest Occasions</h3>
<p>Milestone anniversaries, dream proposals, and prestigious achievements call for a luxury flower bouquet. Choose a lavish rose arrangement or an exotic orchid design, and add a premium cake or hamper so one luxury flower bouquet crowns the celebration in style.</p>

<h3>Exclusive Quality, Dependable Service</h3>
<p>Luxury should never feel uncertain. Each luxury flower bouquet is prepared close to dispatch, packed with meticulous care, and delivered by trusted riders, arriving fresh, flawless, and ready to take their breath away.</p>

<h2>Order a Luxury Flower Bouquet Online</h2>
<p>Explore our <a href="/flowers" title="Order luxury bouquets online Delhi">luxury bouquets</a>, add a <a href="/cakes" title="Order premium cakes online">premium cake</a> or <a href="/gifts" title="Luxury gift hampers Delhi">gift hamper</a>, and check out securely. With Sai Flower, a luxury flower bouquet means unmatched elegance, freshness, and on-time delivery — order now and celebrate in grand style.</p>
HTML,
    'meta_title' => 'Luxury Flower Bouquet Delivery | Sai Flower',
    'meta_description' => 'Order a luxury flower bouquet in Delhi NCR with the finest blooms & signature wrapping from Sai Flower. Same-day & midnight delivery. Shop luxury bouquets now.',
    'meta_keywords' => 'luxury flower bouquet, premium flower bouquet, exotic flower bouquet, luxury roses, designer bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What is a luxury flower bouquet?', 'answer' => 'A luxury flower bouquet features the finest premium and exotic blooms in an opulent, lush arrangement with signature wrapping. Sai Flower designs each one for a truly exclusive impression.'],
        ['question' => 'Can I order a luxury bouquet for same-day delivery?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, with midnight and scheduled slots also available.'],
        ['question' => 'Which flowers are used in luxury bouquets?', 'answer' => 'Luxury bouquets use premium roses, orchids, lilies, and exotic imported varieties, arranged in generous, designer combinations.'],
        ['question' => 'Do luxury bouquets come with special packaging?', 'answer' => 'Yes. We use luxury boxes, silk ribbons, and fine designer paper to give every arrangement a refined, exclusive finish.'],
        ['question' => 'Can I add premium gifts to a luxury bouquet?', 'answer' => 'Absolutely. Pair it with a premium cake, chocolates, or a luxury hamper and we will deliver the complete combo together.'],
        ['question' => 'Is a luxury bouquet good for anniversaries?', 'answer' => 'Yes. A luxury flower bouquet is perfect for milestone anniversaries, proposals, and grand celebrations that deserve something extraordinary.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver luxury bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 14 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Anniversary Flowers',
    'slug'  => 'anniversary-flowers',
    'content' => <<<'HTML'
<h2>Celebrate Love With Anniversary Flowers</h2>
<p>An anniversary marks the beautiful journey two people share, and nothing captures that emotion like anniversary flowers. From passionate red roses to elegant mixed arrangements, the right anniversary flowers turn a special date into an unforgettable memory. Whether it is your first year or your fiftieth, anniversary flowers say what words sometimes cannot.</p>

<p>At <strong>Sai Flower</strong>, we design anniversary flowers with daily-fresh blooms and heartfelt care. Each arrangement is hand-tied by expert florists and delivered on time, so your gift arrives fresh, romantic, and ready to make the day shine.</p>

<h2>Why Flowers Are the Perfect Anniversary Gift</h2>
<p>Flowers are timeless symbols of love and commitment. Thoughtfully chosen anniversary flowers rekindle memories and create new ones with a single beautiful gesture.</p>
<ul>
<li><strong>Romantic roses</strong> — red, pink, and mixed shades of love</li>
<li><strong>Elegant arrangements</strong> — lilies, orchids, and designer bouquets</li>
<li><strong>Same-day and midnight delivery</strong> — surprise them right on time</li>
<li><strong>Combos available</strong> — pair with cakes, chocolates, and gifts</li>
</ul>

<h3>Choose the Right Blooms</h3>
<p>Red roses express deep passion, while pink and white blooms convey grace and gratitude. For a personal touch, mix their favourite flowers into custom anniversary flowers. Add a heart-shaped cake, and one order of anniversary flowers becomes a complete romantic celebration.</p>

<h3>Fresh and On Time for Your Big Day</h3>
<p>An anniversary should never be dulled by wilted stems. Every arrangement of anniversary flowers is prepared close to dispatch and packed protectively, so your bouquet arrives crisp, fragrant, and beautiful, whether at home or the office.</p>

<h2>Order Anniversary Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Order anniversary flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Order anniversary cakes online">cake</a> or <a href="/gifts" title="Anniversary gifts Delhi">gift hamper</a>, and check out in minutes. With Sai Flower, anniversary flowers bring romance, freshness, and reliable delivery — order now and celebrate your love story in full bloom.</p>
HTML,
    'meta_title' => 'Anniversary Flowers Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send romantic anniversary flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh roses & designer bouquets. Order anniversary flowers now.',
    'meta_keywords' => 'anniversary flowers, anniversary bouquet, romantic flower bouquet, anniversary flower delivery, red rose bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What are the best anniversary flowers?', 'answer' => 'Red roses are the classic choice for romance, while pink roses, lilies, and orchids add elegance. Sai Flower can help you build a custom arrangement based on your partner\'s taste.'],
        ['question' => 'Can I get anniversary flowers delivered at midnight?', 'answer' => 'Yes. Midnight delivery is available in selected pin codes, so you can surprise your partner right at 12 a.m. on your special day.'],
        ['question' => 'Do you offer same-day anniversary flower delivery?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Can I combine anniversary flowers with a cake?', 'answer' => 'Yes. Add a heart-shaped or designer cake, chocolates, or a gift, and we will deliver the complete romantic combo together.'],
        ['question' => 'Can I personalise the anniversary bouquet?', 'answer' => 'Yes. Tell us your partner\'s favourite flowers or colours on WhatsApp and we will craft a custom arrangement, subject to availability.'],
        ['question' => 'Which flowers suit a milestone anniversary?', 'answer' => 'For big milestones, a premium or luxury arrangement with 50 or 100 roses makes a grand statement. We can tailor it to your budget.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver anniversary flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 15 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Birthday Flower Delivery',
    'slug'  => 'birthday-flower-delivery',
    'content' => <<<'HTML'
<h2>Make Their Day Special With Birthday Flower Delivery</h2>
<p>Birthdays call for colour, joy, and a little surprise, and birthday flower delivery brings all three to the doorstep. From cheerful gerberas to vibrant mixed bouquets, the right birthday flower delivery instantly lifts the mood and shows how much you care. Near or far, birthday flower delivery lets you be part of the celebration.</p>

<p>At <strong>Sai Flower</strong>, every birthday flower delivery is hand-arranged with daily-fresh blooms and delivered on time. We combine bright, joyful designs with reliable service so your surprise arrives fresh, festive, and perfectly timed.</p>

<h2>Why Choose Our Birthday Flower Delivery</h2>
<p>A birthday surprise should feel effortless for you and magical for them. Our birthday flower delivery is built for exactly that.</p>
<ul>
<li><strong>Bright, cheerful bouquets</strong> — gerberas, roses, lilies, and mixes</li>
<li><strong>Same-day and midnight slots</strong> — be first to wish them</li>
<li><strong>Cake and gift combos</strong> — complete the celebration in one order</li>
<li><strong>NCR-wide coverage</strong> — Delhi, Gurgaon, Noida, and nearby areas</li>
</ul>

<h3>Flowers That Match the Mood</h3>
<p>For a lively friend, choose vibrant mixed blooms; for someone elegant, pick roses or orchids. Whatever their style, birthday flower delivery makes the moment personal. Add a cake and a soft teddy, and one birthday flower delivery becomes a full party at the door.</p>

<h3>Fresh, Festive, and On Time</h3>
<p>Timing is everything on a birthday. Each birthday flower delivery is prepared close to dispatch and packed protectively, so your bouquet arrives crisp, colourful, and ready to celebrate, whether at home or the workplace.</p>

<h2>Order Birthday Flower Delivery Online</h2>
<p>Browse our <a href="/flowers" title="Order birthday flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order birthday cakes online">birthday cake</a> or <a href="/gifts" title="Birthday gifts Delhi">gift</a>, and check out in minutes. With Sai Flower, birthday flower delivery is joyful, fresh, and reliable — order now and make their birthday truly unforgettable.</p>
HTML,
    'meta_title' => 'Birthday Flower Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send birthday flower delivery in Delhi NCR with same-day & midnight slots from Sai Flower. Fresh, cheerful bouquets & cake combos. Order birthday flowers now.',
    'meta_keywords' => 'birthday flower delivery, birthday flowers, birthday bouquet, midnight flower delivery, birthday flowers and cake, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'Can I get midnight birthday flower delivery?', 'answer' => 'Yes. Midnight delivery is available in selected pin codes, so you can be the first to wish them at 12 a.m. with fresh flowers.'],
        ['question' => 'Do you offer same-day birthday flower delivery?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi, Gurgaon, Noida, and nearby NCR areas.'],
        ['question' => 'Which flowers are best for birthdays?', 'answer' => 'Bright, cheerful blooms like gerberas, mixed roses, and lilies are popular for birthdays. Sai Flower can match the arrangement to the person\'s style.'],
        ['question' => 'Can I add a cake to birthday flowers?', 'answer' => 'Yes. Add a birthday cake, chocolates, or a soft teddy and we will deliver the complete surprise together where possible.'],
        ['question' => 'Can I send birthday flowers to an office?', 'answer' => 'Yes. Add the workplace name, floor, and reception details in the notes and our rider will deliver smoothly.'],
        ['question' => 'Can I include a birthday message?', 'answer' => 'Of course. Add a personal message at checkout and we will include it with your birthday flower delivery.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver birthday flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 16 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Romantic Flower Bouquet',
    'slug'  => 'romantic-flower-bouquet',
    'content' => <<<'HTML'
<h2>Express Your Love With a Romantic Flower Bouquet</h2>
<p>Love deserves a grand gesture, and a romantic flower bouquet says it beautifully. Whether it is a first date, an anniversary, or a heartfelt apology, a romantic flower bouquet turns emotion into something you can hold. With rich roses and dreamy arrangements, a romantic flower bouquet makes hearts skip a beat.</p>

<p>At <strong>Sai Flower</strong>, every romantic flower bouquet is hand-crafted with daily-fresh blooms and delivered on time. We design each arrangement to feel intimate and elegant, so your gesture lands exactly the way you imagined.</p>

<h2>Why a Romantic Bouquet Works Every Time</h2>
<p>Flowers speak the language of love effortlessly. A thoughtful romantic flower bouquet shows attention, affection, and genuine feeling in one stunning gift.</p>
<ul>
<li><strong>Passionate roses</strong> — red, pink, and mixed shades</li>
<li><strong>Dreamy add-ons</strong> — orchids, lilies, and soft fillers</li>
<li><strong>Same-day and midnight delivery</strong> — surprise them on time</li>
<li><strong>Perfect combos</strong> — cakes, chocolates, and teddies</li>
</ul>

<h3>Set the Mood</h3>
<p>Deep red roses convey passion, while blush tones whisper tenderness. For a personal touch, weave their favourite blooms into a custom romantic flower bouquet. Add a heart-shaped cake and chocolates, and one romantic flower bouquet becomes an evening to remember.</p>

<h3>Fresh, Elegant, and Reliable</h3>
<p>Romance should never wilt. Each romantic flower bouquet is prepared close to dispatch and packed protectively, so it arrives fresh, fragrant, and beautifully structured, ready to sweep them off their feet.</p>

<h2>Order a Romantic Flower Bouquet Online</h2>
<p>Explore our <a href="/flowers" title="Order romantic bouquets online Delhi">romantic bouquets</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Romantic gifts Delhi">gift</a>, and check out securely. With Sai Flower, a romantic flower bouquet brings passion, freshness, and on-time delivery — order now and say "I love you" in full bloom.</p>
HTML,
    'meta_title' => 'Romantic Flower Bouquet Delivery | Sai Flower',
    'meta_description' => 'Send a romantic flower bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh roses & dreamy arrangements. Order a romantic bouquet now.',
    'meta_keywords' => 'romantic flower bouquet, love flowers, red rose bouquet, anniversary flowers, romantic roses, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What flowers make a bouquet romantic?', 'answer' => 'Red and pink roses are the most romantic, often paired with orchids, lilies, and soft fillers. Sai Flower can craft a custom romantic arrangement for you.'],
        ['question' => 'Can I get a romantic bouquet delivered at midnight?', 'answer' => 'Yes. Midnight delivery is available in selected pin codes, perfect for surprising your partner at 12 a.m.'],
        ['question' => 'Do you offer same-day delivery for romantic bouquets?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Can I add chocolates or a teddy?', 'answer' => 'Yes. Pair your bouquet with chocolates, a cake, or a soft teddy and we will deliver the complete romantic combo together.'],
        ['question' => 'Is a romantic bouquet good for a proposal?', 'answer' => 'Definitely. A lush red rose arrangement makes a proposal unforgettable. Contact us for a premium or luxury custom design.'],
        ['question' => 'Can I add a love note?', 'answer' => 'Yes. Add your personal message at checkout and we will include it with your romantic flower bouquet.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver romantic bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 17 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Wedding Flower Bouquet',
    'slug'  => 'wedding-flower-bouquet',
    'content' => <<<'HTML'
<h2>Stunning Wedding Flower Bouquet for Your Big Day</h2>
<p>A wedding is a once-in-a-lifetime celebration, and the right wedding flower bouquet makes it even more magical. From the bride's hand bouquet to elegant décor blooms, a beautifully designed wedding flower bouquet ties the whole look together. Fresh, fragrant, and photo-perfect, a wedding flower bouquet becomes part of memories that last forever.</p>

<p>At <strong>Sai Flower</strong>, every wedding flower bouquet is crafted by experienced florists using premium, daily-fresh blooms. We match your theme, colours, and style so each arrangement feels personal, refined, and truly wedding-worthy.</p>

<h2>Why Choose Us for Wedding Flowers</h2>
<p>Weddings demand precision and beauty. Our wedding flower bouquet service blends artistry with dependable delivery so your day runs smoothly.</p>
<ul>
<li><strong>Custom designs</strong> — matched to your theme and colour palette</li>
<li><strong>Premium blooms</strong> — roses, orchids, lilies, and exotic flowers</li>
<li><strong>Bridal and décor options</strong> — hand bouquets, garlands, and more</li>
<li><strong>Reliable delivery</strong> — on time across Delhi NCR</li>
</ul>

<h3>Blooms for Every Wedding Moment</h3>
<p>From the bride's toss bouquet to bridesmaid bunches and stage florals, a coordinated wedding flower bouquet elevates every scene. Choose classic roses for timeless romance or orchids for modern elegance, and let one wedding flower bouquet theme flow through your celebration.</p>

<h3>Fresh and Flawless on the Day</h3>
<p>Wedding flowers must look perfect for hours. Each wedding flower bouquet is prepared close to the event and handled with care, so blooms stay fresh, vibrant, and stunning in every photograph.</p>

<h2>Order Your Wedding Flower Bouquet</h2>
<p>Explore our <a href="/flowers" title="Order wedding flowers online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">celebration cake</a> or <a href="/gifts" title="Wedding gifts Delhi">gift</a>, and reach out for custom orders. With Sai Flower, a wedding flower bouquet brings elegance, freshness, and reliable service — contact us now to plan your dream wedding florals.</p>
HTML,
    'meta_title' => 'Wedding Flower Bouquet Delivery | Sai Flower',
    'meta_description' => 'Order a custom wedding flower bouquet in Delhi NCR with premium blooms from Sai Flower. Bridal & décor florals delivered fresh & on time. Enquire now.',
    'meta_keywords' => 'wedding flower bouquet, bridal bouquet, wedding flowers delhi, wedding flower decoration, premium flower bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'Can I get a custom wedding flower bouquet?', 'answer' => 'Yes. Sai Flower designs custom bridal and décor bouquets matched to your theme, colours, and style. Contact us with your requirements for a tailored quote.'],
        ['question' => 'Which flowers are best for weddings?', 'answer' => 'Roses, orchids, lilies, and exotic blooms are popular for weddings. We help you choose based on your colour palette and budget.'],
        ['question' => 'Do you provide bridal hand bouquets?', 'answer' => 'Yes. We create bridal hand bouquets, bridesmaid bunches, and stage florals as part of our wedding flower service.'],
        ['question' => 'How far in advance should I order?', 'answer' => 'For weddings, we recommend booking as early as possible so we can plan blooms, design, and delivery precisely for your date.'],
        ['question' => 'Can you handle bulk wedding flower orders?', 'answer' => 'Absolutely. We handle bulk and décor orders for weddings across Delhi NCR. Contact us on WhatsApp to discuss your event.'],
        ['question' => 'Will the flowers stay fresh through the event?', 'answer' => 'Yes. Each wedding bouquet is prepared close to the event and handled with care, so blooms stay fresh and photo-perfect for hours.'],
        ['question' => 'Which areas do you serve for weddings?', 'answer' => 'We serve weddings across Delhi, Gurgaon, Noida, and nearby NCR areas. Confirm your venue details when you enquire.'],
    ],
],

// 18 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Congratulations Flowers',
    'slug'  => 'congratulations-flowers',
    'content' => <<<'HTML'
<h2>Celebrate Success With Congratulations Flowers</h2>
<p>Every achievement deserves applause, and congratulations flowers are the perfect way to cheer someone on. Whether it is a new job, a promotion, a graduation, or a new home, bright and joyful congratulations flowers show pride and support. When words feel small, congratulations flowers say "well done" in full colour.</p>

<p>At <strong>Sai Flower</strong>, every arrangement of congratulations flowers is hand-tied with daily-fresh blooms and delivered on time. We design uplifting, vibrant bouquets so your good wishes arrive fresh, cheerful, and impossible to miss.</p>

<h2>Why Send Congratulations Flowers</h2>
<p>Flowers celebrate milestones in a warm, personal way. A thoughtful bunch of congratulations flowers turns a proud moment into a memory the recipient will treasure.</p>
<ul>
<li><strong>Bright, celebratory blooms</strong> — gerberas, roses, lilies, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Perfect for work or home</strong> — offices, homes, and events</li>
<li><strong>Combos available</strong> — pair with cakes and gifts</li>
</ul>

<h3>The Right Flowers for the Occasion</h3>
<p>Vibrant mixed blooms suit exciting wins, while elegant roses and orchids fit formal achievements. For a workplace success, a neat desk bouquet works beautifully. Add a cake, and one order of congratulations flowers becomes a full celebration of their hard-earned win.</p>

<h3>Fresh, Cheerful, and On Time</h3>
<p>A milestone moment deserves flowers at their best. Each bunch of congratulations flowers is prepared close to dispatch and packed protectively, so it arrives crisp, colourful, and ready to spread joy.</p>

<h2>Order Congratulations Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Order congratulations flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Congratulations gifts Delhi">gift</a>, and check out in minutes. With Sai Flower, congratulations flowers bring cheer, freshness, and reliable delivery — order now and celebrate their success in style.</p>
HTML,
    'meta_title' => 'Congratulations Flowers Delivery | Sai Flower',
    'meta_description' => 'Send congratulations flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Bright, cheerful bouquets for every success. Order online now.',
    'meta_keywords' => 'congratulations flowers, congratulations bouquet, success flowers, new job flowers, celebration flowers, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What are the best congratulations flowers?', 'answer' => 'Bright, cheerful blooms like gerberas, mixed roses, and lilies are ideal for celebrating success. Sai Flower can tailor the bouquet to the occasion.'],
        ['question' => 'Can I send congratulations flowers to an office?', 'answer' => 'Yes. Add the company name, floor, and reception details in the notes and our rider will deliver smoothly to the workplace.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi, Gurgaon, Noida, and nearby NCR areas.'],
        ['question' => 'What occasions suit congratulations flowers?', 'answer' => 'New jobs, promotions, graduations, new homes, and business milestones are all perfect reasons to send congratulations flowers.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the flowers with a cake, chocolates, or a gift hamper and we will deliver the complete combo together.'],
        ['question' => 'Can I include a congratulatory message?', 'answer' => 'Of course. Add your message at checkout and we will include it with the bouquet.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver congratulations flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 19 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Get Well Soon Flowers',
    'slug'  => 'get-well-soon-flowers',
    'content' => <<<'HTML'
<h2>Send Comfort With Get Well Soon Flowers</h2>
<p>When someone you care about is unwell, a warm gesture can lift their spirits instantly. Get well soon flowers bring brightness, hope, and a reminder that they are loved. With gentle colours and fresh, uplifting blooms, get well soon flowers turn a difficult day into a little brighter one. Sending get well soon flowers is a simple way to say "I'm thinking of you."</p>

<p>At <strong>Sai Flower</strong>, every arrangement of get well soon flowers is hand-tied with daily-fresh, cheerful blooms and delivered on time. We choose soothing, positive colours so your wishes arrive fresh, comforting, and full of warmth.</p>

<h2>Why Flowers Help Recovery</h2>
<p>Studies and everyday experience agree that flowers boost mood and comfort. A thoughtful bunch of get well soon flowers brings positivity to a hospital room or home.</p>
<ul>
<li><strong>Soothing, bright blooms</strong> — yellows, whites, and soft pastels</li>
<li><strong>Same-day and scheduled delivery</strong> — across Delhi NCR</li>
<li><strong>Hospital and home delivery</strong> — with care and discretion</li>
<li><strong>Add-ons available</strong> — fruit baskets, cards, and gifts</li>
</ul>

<h3>Choosing the Right Blooms</h3>
<p>Gentle yellows and whites feel calm and hopeful, while cheerful gerberas add energy. Avoid overpowering fragrances for hospital rooms. For extra comfort, pair get well soon flowers with a fruit basket, and one order of get well soon flowers delivers both cheer and nourishment.</p>

<h3>Fresh and Gentle, Delivered on Time</h3>
<p>Comfort should arrive promptly. Each bunch of get well soon flowers is prepared close to dispatch and packed protectively, so it reaches the bedside fresh, soft, and uplifting.</p>

<h2>Order Get Well Soon Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Order get well soon flowers online Delhi">flowers</a>, add a <a href="/gifts" title="Get well gifts Delhi">gift or fruit basket</a>, and check out in minutes. With Sai Flower, get well soon flowers bring warmth, freshness, and reliable delivery — order now and send healing wishes to someone special.</p>
HTML,
    'meta_title' => 'Get Well Soon Flowers Delivery | Sai Flower',
    'meta_description' => 'Send get well soon flowers in Delhi NCR with same-day delivery from Sai Flower. Fresh, comforting bouquets for hospital or home. Order well wishes online now.',
    'meta_keywords' => 'get well soon flowers, get well flowers, recovery flowers, hospital flower delivery, cheerful bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What flowers are best for get well wishes?', 'answer' => 'Bright, gentle blooms like yellow and white roses, gerberas, and lilies work best. For hospital rooms, choose mild fragrances so they stay comfortable.'],
        ['question' => 'Can you deliver flowers to a hospital?', 'answer' => 'Yes. Provide the hospital name, ward, room number, and patient name in the notes and our rider will deliver with care and discretion.'],
        ['question' => 'Is same-day get well flower delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi, Gurgaon, Noida, and nearby NCR areas.'],
        ['question' => 'Can I add a fruit basket or gift?', 'answer' => 'Yes. Pair the flowers with a fruit basket, card, or gift and we will deliver the complete comfort combo together.'],
        ['question' => 'Should I avoid strong-smelling flowers?', 'answer' => 'For hospital rooms, mild fragrances are best as some patients are sensitive. Our florists can recommend gentle, soothing options.'],
        ['question' => 'Can I add a personal note?', 'answer' => 'Yes. Add your get well message at checkout and we will include it with the bouquet.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver get well soon flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 20 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Sympathy Flowers',
    'slug'  => 'sympathy-flowers',
    'content' => <<<'HTML'
<h2>Offer Comfort With Sympathy Flowers</h2>
<p>In moments of loss, words often fall short, but sympathy flowers speak gently on your behalf. A tasteful arrangement of sympathy flowers conveys compassion, respect, and heartfelt support to a grieving family. When you cannot always be there in person, sympathy flowers carry your condolences with grace and dignity.</p>

<p>At <strong>Sai Flower</strong>, every arrangement of sympathy flowers is prepared with daily-fresh, serene blooms and delivered with care. We design calm, respectful bouquets in soft tones so your message of comfort arrives fresh, tasteful, and sincere.</p>

<h2>Why Sympathy Flowers Matter</h2>
<p>Flowers offer quiet comfort during difficult times. A thoughtful arrangement of sympathy flowers shows you share in the family's sorrow and stand with them.</p>
<ul>
<li><strong>Serene, respectful blooms</strong> — white lilies, roses, and orchids</li>
<li><strong>Timely delivery</strong> — to homes and prayer meetings</li>
<li><strong>Discreet, careful handling</strong> — with dignity and sensitivity</li>
<li><strong>Tasteful arrangements</strong> — wreaths, bunches, and baskets</li>
</ul>

<h3>Choosing Appropriate Blooms</h3>
<p>White flowers symbolise peace and remembrance, making them ideal sympathy flowers. Soft pastels also convey gentle comfort. For a respectful tribute, choose lilies or white roses, and our florists arrange each order of sympathy flowers with quiet elegance.</p>

<h3>Delivered With Care and Respect</h3>
<p>Sensitive moments call for reliable, gentle service. Each arrangement of sympathy flowers is prepared close to dispatch and delivered promptly, so your condolences reach the family fresh, dignified, and on time.</p>

<h2>Order Sympathy Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Order sympathy flowers online Delhi">flower collection</a> and check out securely, or contact us for wreaths and custom tributes. With Sai Flower, sympathy flowers bring comfort, freshness, and respectful delivery — order now to share your condolences with grace.</p>
HTML,
    'meta_title' => 'Sympathy Flowers Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send sympathy flowers in Delhi NCR with respectful, same-day delivery from Sai Flower. Serene white lilies & roses for condolences. Order sympathy flowers now.',
    'meta_keywords' => 'sympathy flowers, condolence flowers, funeral flowers, white lily bouquet, remembrance flowers, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What flowers are appropriate for sympathy?', 'answer' => 'White lilies, white roses, and orchids are traditional sympathy flowers symbolising peace and remembrance. Soft pastel blooms also convey gentle comfort.'],
        ['question' => 'Can you deliver sympathy flowers the same day?', 'answer' => 'Yes. Order before the daily cut-off for same-day delivery across Delhi NCR, so your condolences reach the family on time.'],
        ['question' => 'Do you offer funeral wreaths?', 'answer' => 'Yes. We prepare wreaths, standing arrangements, and tasteful bunches. Contact us on WhatsApp for custom tribute requirements.'],
        ['question' => 'Can flowers be delivered to a prayer meeting?', 'answer' => 'Absolutely. Provide the venue address and timing in the notes and we will deliver discreetly and on time.'],
        ['question' => 'Can I add a condolence message?', 'answer' => 'Yes. Add a respectful message at checkout and we will include it with the arrangement.'],
        ['question' => 'How quickly can you arrange sympathy flowers?', 'answer' => 'We understand the urgency of these moments and can often arrange same-day delivery. WhatsApp us for the fastest possible dispatch.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver sympathy flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

];

echo "=== Seeding keyword pages — Batch 2 ===\n";
$created = 0;
foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    }
}
echo "=== Done. {$created} new page(s) created. ===\n";
