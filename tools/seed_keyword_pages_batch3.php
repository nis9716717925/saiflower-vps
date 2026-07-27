<?php
/**
 * Seed: Keyword custom pages — Batch 3 (dynamic_pages)
 * Layout: product_showcase | Tag: sameday
 * Run once: https://saiflower.com/tools/seed_keyword_pages_batch3.php
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

// 21 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Thank You Flowers',
    'slug'  => 'thank-you-flowers',
    'content' => <<<'HTML'
<h2>Say It Beautifully With Thank You Flowers</h2>
<p>Gratitude feels warmer when it comes with blooms. Thank you flowers are a graceful way to show appreciation to family, friends, colleagues, or anyone who made a difference. Whether it is a small favour or a big-hearted gesture, thank you flowers turn a simple "thanks" into a moment that truly lands. A thoughtful bunch of thank you flowers says more than words alone.</p>

<p>At <strong>Sai Flower</strong>, every arrangement of thank you flowers is hand-tied with daily-fresh blooms and delivered on time. We craft cheerful, elegant bouquets so your appreciation arrives fresh, bright, and full of warmth.</p>

<h2>Why Send Thank You Flowers</h2>
<p>A gift of flowers shows effort and sincerity. Thoughtfully chosen thank you flowers make the recipient feel genuinely valued and remembered.</p>
<ul>
<li><strong>Warm, cheerful blooms</strong> — roses, gerberas, lilies, and mixes</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Perfect for home or office</strong> — personal or professional thanks</li>
<li><strong>Combos available</strong> — add cakes, chocolates, and gifts</li>
</ul>

<h3>Choose the Right Gesture</h3>
<p>Bright mixed blooms suit a joyful thank you, while elegant roses fit a heartfelt one. For a mentor or client, a refined arrangement works best. Pair thank you flowers with chocolates, and one order of thank you flowers becomes a complete token of appreciation.</p>

<h3>Fresh and On Time, Every Time</h3>
<p>Gratitude is best expressed promptly. Each bunch of thank you flowers is prepared close to dispatch and packed protectively, so it arrives crisp, colourful, and ready to brighten someone's day.</p>

<h2>Order Thank You Flowers Online</h2>
<p>Browse our <a href="/flowers" title="Order thank you flowers online Delhi">flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Thank you gifts Delhi">gift</a>, and check out in minutes. With Sai Flower, thank you flowers bring warmth, freshness, and reliable delivery — order now and show your appreciation in full bloom.</p>
HTML,
    'meta_title' => 'Thank You Flowers Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send thank you flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, cheerful bouquets to show appreciation. Order online now.',
    'meta_keywords' => 'thank you flowers, appreciation flowers, thank you bouquet, gratitude flowers, cheerful bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What flowers say thank you best?', 'answer' => 'Bright, warm blooms like mixed roses, gerberas, and lilies are ideal for expressing thanks. Sai Flower can tailor the arrangement to the person and occasion.'],
        ['question' => 'Can I send thank you flowers to an office?', 'answer' => 'Yes. Add the company name, floor, and reception details in the notes and our rider will deliver smoothly to the workplace.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi, Gurgaon, Noida, and nearby NCR areas.'],
        ['question' => 'Can I add chocolates or a gift?', 'answer' => 'Yes. Pair the flowers with chocolates, a cake, or a gift hamper and we will deliver the complete combo together.'],
        ['question' => 'Are thank you flowers suitable for clients?', 'answer' => 'Definitely. A refined, elegant arrangement makes a professional and gracious thank you for clients and mentors.'],
        ['question' => 'Can I include a thank you note?', 'answer' => 'Of course. Add your personal message at checkout and we will include it with the bouquet.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver thank you flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 22 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Love Flowers',
    'slug'  => 'love-flowers',
    'content' => <<<'HTML'
<h2>Express Your Heart With Love Flowers</h2>
<p>Love deserves to be shown, and love flowers do it beautifully. Whether it is a new spark, a lasting bond, or a sweet apology, love flowers turn emotion into a gift you can hold. With romantic roses and dreamy arrangements, love flowers whisper what your heart wants to say. Sending love flowers is the simplest way to make someone feel adored.</p>

<p>At <strong>Sai Flower</strong>, every bouquet of love flowers is hand-crafted with daily-fresh blooms and delivered on time. We design intimate, elegant arrangements so your feelings arrive fresh, fragrant, and full of romance.</p>

<h2>Why Love Flowers Never Fail</h2>
<p>Flowers are the universal language of love. A thoughtful gift of love flowers shows attention, affection, and genuine care in one stunning gesture.</p>
<ul>
<li><strong>Romantic roses</strong> — red, pink, and mixed shades</li>
<li><strong>Dreamy designs</strong> — orchids, lilies, and soft fillers</li>
<li><strong>Same-day and midnight delivery</strong> — surprise them on time</li>
<li><strong>Perfect combos</strong> — cakes, chocolates, and teddies</li>
</ul>

<h3>Blooms for Every Love Story</h3>
<p>Red roses declare passion, pink blooms convey tenderness, and mixed bouquets celebrate joyful togetherness. For a personal touch, add their favourite flowers to custom love flowers. Pair with a heart-shaped cake, and one order of love flowers becomes an unforgettable romantic surprise.</p>

<h3>Fresh, Romantic, and Reliable</h3>
<p>Love should always feel fresh. Each bouquet of love flowers is prepared close to dispatch and packed protectively, so it arrives crisp, fragrant, and beautifully arranged, ready to melt their heart.</p>

<h2>Order Love Flowers Online</h2>
<p>Explore our <a href="/flowers" title="Order love flowers online Delhi">romantic flowers</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Romantic gifts Delhi">gift</a>, and check out securely. With Sai Flower, love flowers bring passion, freshness, and on-time delivery — order now and say "I love you" in full bloom.</p>
HTML,
    'meta_title' => 'Love Flowers Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send love flowers in Delhi NCR with same-day & midnight delivery from Sai Flower. Romantic roses & dreamy bouquets. Order love flowers online today.',
    'meta_keywords' => 'love flowers, romantic flower bouquet, red rose bouquet, i love you flowers, anniversary flowers, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What are the best love flowers?', 'answer' => 'Red roses are the classic symbol of love, often paired with pink roses, orchids, and lilies. Sai Flower can craft a custom romantic arrangement for you.'],
        ['question' => 'Can I get love flowers delivered at midnight?', 'answer' => 'Yes. Midnight delivery is available in selected pin codes, perfect for surprising your partner right at 12 a.m.'],
        ['question' => 'Do you offer same-day delivery for love flowers?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Can I add chocolates or a teddy?', 'answer' => 'Yes. Pair the flowers with chocolates, a cake, or a soft teddy and we will deliver the complete romantic combo together.'],
        ['question' => 'Are love flowers good for a proposal?', 'answer' => 'Definitely. A lush red rose arrangement makes a proposal unforgettable. Contact us for a premium or luxury design.'],
        ['question' => 'Can I add a love note?', 'answer' => 'Yes. Add your personal message at checkout and we will include it with your love flowers.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver love flowers across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 23 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Red Rose Bouquet',
    'slug'  => 'red-rose-bouquet',
    'content' => <<<'HTML'
<h2>Timeless Romance With a Red Rose Bouquet</h2>
<p>Nothing captures love quite like a red rose bouquet. Bold, passionate, and eternally elegant, a red rose bouquet is the ultimate way to say "I love you." Whether for an anniversary, a proposal, or Valentine's Day, a fresh red rose bouquet speaks straight to the heart. It remains the most loved gift for romance across every generation.</p>

<p>At <strong>Sai Flower</strong>, every red rose bouquet is hand-tied with daily-fresh, velvety red roses. We select firm, fragrant stems and arrange them close to dispatch, so your bouquet arrives rich in colour and stunning in shape.</p>

<h2>Why a Red Rose Bouquet Is Special</h2>
<p>Red roses symbolise deep love and desire. A carefully arranged red rose bouquet turns strong feelings into an unforgettable gesture.</p>
<ul>
<li><strong>Velvety red roses</strong> — daily-fresh and fragrant</li>
<li><strong>Choice of sizes</strong> — from a dozen to 50 or 100 roses</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Elegant wrapping</strong> — designer paper, boxes, and ribbons</li>
</ul>

<h3>Perfect for Romantic Occasions</h3>
<p>A red rose bouquet is ideal for anniversaries, proposals, first dates, and heartfelt apologies. For a grand statement, choose a 50 or 100-rose arrangement. Add chocolates and a cake, and one red rose bouquet becomes a complete romantic surprise.</p>

<h3>Fresh, Bold, and On Time</h3>
<p>Romance deserves flawless flowers. Each red rose bouquet is prepared close to dispatch and packed protectively, so the petals stay firm, deep red, and long-lasting on arrival.</p>

<h2>Order a Red Rose Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order red rose bouquets online Delhi">rose bouquets</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Romantic gifts Delhi">gift</a>, and check out securely. With Sai Flower, a red rose bouquet brings passion, freshness, and reliable delivery — order now and sweep them off their feet.</p>
HTML,
    'meta_title' => 'Red Rose Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send a fresh red rose bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Velvety roses for love & romance. Order a red rose bouquet now.',
    'meta_keywords' => 'red rose bouquet, red roses delivery, romantic flower bouquet, rose bouquet delivery, love flowers, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'How many roses come in a red rose bouquet?', 'answer' => 'We offer sizes from a classic dozen to grand 50 and 100-rose arrangements. Choose the size that suits your occasion and budget.'],
        ['question' => 'Are the red roses fresh?', 'answer' => 'Yes. Sai Flower uses daily-fresh, velvety red roses arranged close to dispatch and packed protectively, so they arrive firm and long-lasting.'],
        ['question' => 'Can I get a red rose bouquet at midnight?', 'answer' => 'Yes. Midnight delivery is available in selected pin codes, perfect for anniversaries and surprises.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Can I add chocolates or a cake?', 'answer' => 'Yes. Pair the bouquet with chocolates, a cake, or a teddy and we will deliver the complete romantic combo together.'],
        ['question' => 'Is a red rose bouquet good for a proposal?', 'answer' => 'Definitely. A large red rose arrangement makes any proposal unforgettable. Contact us for a premium custom design.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver red rose bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 24 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Mixed Flower Bouquet',
    'slug'  => 'mixed-flower-bouquet',
    'content' => <<<'HTML'
<h2>Brighten Any Day With a Mixed Flower Bouquet</h2>
<p>When you want colour, variety, and joy in one gift, a mixed flower bouquet is the perfect choice. Combining roses, lilies, gerberas, and seasonal blooms, a mixed flower bouquet bursts with personality and charm. Ideal for birthdays, thank-yous, and just-because moments, a mixed flower bouquet suits almost every occasion and every recipient.</p>

<p>At <strong>Sai Flower</strong>, every mixed flower bouquet is hand-arranged with daily-fresh blooms in balanced, eye-catching colour combinations. We design each bouquet close to dispatch, so it arrives vibrant, fresh, and full of life.</p>

<h2>Why Choose a Mixed Bouquet</h2>
<p>Variety makes a gift feel rich and thoughtful. A well-designed mixed flower bouquet offers texture, colour, and fragrance that a single bloom cannot match.</p>
<ul>
<li><strong>Colourful variety</strong> — roses, lilies, gerberas, and more</li>
<li><strong>Suits every occasion</strong> — birthdays, thanks, and celebrations</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Combos available</strong> — add cakes, chocolates, and gifts</li>
</ul>

<h3>A Bouquet for Every Personality</h3>
<p>Bright combinations suit lively, cheerful people, while soft pastel mixes feel gentle and elegant. Because a mixed flower bouquet blends many blooms, it works beautifully for anyone. Add a cake or gift, and one mixed flower bouquet becomes a complete, joyful surprise.</p>

<h3>Fresh, Vibrant, and On Time</h3>
<p>Colour is only beautiful when it is fresh. Each mixed flower bouquet is prepared close to dispatch and packed protectively, so every petal arrives crisp, bright, and long-lasting.</p>

<h2>Order a Mixed Flower Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order mixed bouquets online Delhi">flower bouquets</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, a mixed flower bouquet brings colour, freshness, and reliable delivery — order now and send a burst of joy to someone special.</p>
HTML,
    'meta_title' => 'Mixed Flower Bouquet Delivery | Sai Flower',
    'meta_description' => 'Send a colourful mixed flower bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh seasonal blooms. Order a mixed bouquet online now.',
    'meta_keywords' => 'mixed flower bouquet, colourful bouquet, seasonal flower bouquet, mixed roses and lilies, birthday bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What flowers are in a mixed bouquet?', 'answer' => 'A mixed flower bouquet typically blends roses, lilies, gerberas, carnations, and seasonal blooms in a balanced colour combination chosen by our florists.'],
        ['question' => 'Can I choose the colours?', 'answer' => 'Yes. Tell us your preferred colour theme on WhatsApp and we will design the mix accordingly, subject to seasonal availability.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi, Gurgaon, Noida, and nearby NCR areas.'],
        ['question' => 'What occasions suit a mixed bouquet?', 'answer' => 'Mixed bouquets are versatile and suit birthdays, thank-yous, congratulations, and just-because gestures.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the bouquet with a cake, chocolates, or a gift and we will deliver the complete combo together.'],
        ['question' => 'How fresh will the bouquet be?', 'answer' => 'Each mixed bouquet is arranged close to dispatch and packed protectively, so it arrives crisp, vibrant, and long-lasting.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver mixed bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 25 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Orchid Bouquet',
    'slug'  => 'orchid-bouquet',
    'content' => <<<'HTML'
<h2>Elegant Sophistication With an Orchid Bouquet</h2>
<p>For a gift that feels refined and exotic, nothing beats an orchid bouquet. Graceful, long-lasting, and effortlessly luxurious, an orchid bouquet stands out from ordinary flowers. Whether for a milestone, a corporate gift, or a special someone with elegant taste, an orchid bouquet makes a sophisticated impression that lingers.</p>

<p>At <strong>Sai Flower</strong>, every orchid bouquet is hand-arranged with daily-fresh, premium orchids. We design clean, elegant arrangements close to dispatch, so your bouquet arrives crisp, exotic, and beautifully structured.</p>

<h2>Why Choose an Orchid Bouquet</h2>
<p>Orchids symbolise luxury, beauty, and strength. A thoughtfully arranged orchid bouquet conveys class and admiration in a way few flowers can.</p>
<ul>
<li><strong>Premium orchids</strong> — exotic, elegant, and long-lasting</li>
<li><strong>Refined designs</strong> — minimalist and sophisticated wrapping</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Perfect for gifting</strong> — personal and corporate occasions</li>
</ul>

<h3>A Bloom That Makes a Statement</h3>
<p>Orchids suit those who appreciate understated luxury. Choose an all-orchid arrangement for pure elegance, or a mixed design for extra colour. For formal gifting, an orchid bouquet feels premium and tasteful, and pairs beautifully with a fine cake or hamper.</p>

<h3>Fresh, Exotic, and Long-Lasting</h3>
<p>Orchids are prized for their staying power. Each orchid bouquet is prepared close to dispatch and packed carefully, so blooms arrive fresh, firm, and gorgeous, delighting the recipient for days.</p>

<h2>Order an Orchid Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order orchid bouquets online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Premium gifts Delhi">gift</a>, and check out securely. With Sai Flower, an orchid bouquet brings elegance, freshness, and reliable delivery — order now and gift sophistication in full bloom.</p>
HTML,
    'meta_title' => 'Orchid Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send an elegant orchid bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, exotic orchids for classy gifting. Order online now.',
    'meta_keywords' => 'orchid bouquet, orchid flower delivery, exotic flower bouquet, premium flower bouquet, elegant bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'How long do orchid bouquets last?', 'answer' => 'Orchids are known for their staying power and often last longer than many flowers. With fresh stems and proper care, an orchid bouquet stays beautiful for many days.'],
        ['question' => 'Are orchid bouquets good for corporate gifts?', 'answer' => 'Yes. Orchids feel premium and tasteful, making an orchid bouquet ideal for corporate gifting, client appreciation, and formal occasions.'],
        ['question' => 'Is same-day orchid bouquet delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Can I get mixed orchids and other flowers?', 'answer' => 'Yes. Choose an all-orchid arrangement or a mixed design with roses and lilies for extra colour. Our florists can customise it.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the orchid bouquet with a fine cake or hamper and we will deliver the complete combo together.'],
        ['question' => 'Are the orchids fresh?', 'answer' => 'Every orchid bouquet is arranged with daily-fresh, premium orchids close to dispatch and packed carefully for freshness.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver orchid bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 26 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Lily Bouquet',
    'slug'  => 'lily-bouquet',
    'content' => <<<'HTML'
<h2>Graceful Beauty With a Lily Bouquet</h2>
<p>Elegant, fragrant, and timeless, a lily bouquet brings a sense of calm beauty to any occasion. Whether for a celebration, a get-well wish, or a heartfelt tribute, a lily bouquet conveys grace and sincerity. With their large, striking blooms and gentle fragrance, a lily bouquet makes a memorable and sophisticated gift.</p>

<p>At <strong>Sai Flower</strong>, every lily bouquet is hand-arranged with daily-fresh lilies in soft, elegant designs. We prepare each bouquet close to dispatch, so it arrives fresh, fragrant, and beautifully shaped.</p>

<h2>Why Choose a Lily Bouquet</h2>
<p>Lilies symbolise purity, devotion, and refined beauty. A thoughtfully arranged lily bouquet suits both joyful and solemn moments with equal grace.</p>
<ul>
<li><strong>Fresh, fragrant lilies</strong> — white, pink, and coloured varieties</li>
<li><strong>Elegant arrangements</strong> — solo lilies or mixed designs</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Versatile gifting</strong> — celebrations, comfort, and tributes</li>
</ul>

<h3>A Lily for Every Sentiment</h3>
<p>White lilies express peace and remembrance, while pink and coloured lilies celebrate joy and admiration. Because a lily bouquet suits so many emotions, it is a versatile choice. Add a cake or gift, and one lily bouquet becomes a complete, graceful gesture.</p>

<h3>Fresh, Fragrant, and On Time</h3>
<p>Lilies are at their best when fresh. Each lily bouquet is prepared close to dispatch and packed protectively, so blooms arrive firm, fragrant, and stunning at the doorstep.</p>

<h2>Order a Lily Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order lily bouquets online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out securely. With Sai Flower, a lily bouquet brings elegance, freshness, and reliable delivery — order now and gift graceful beauty in full bloom.</p>
HTML,
    'meta_title' => 'Lily Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send an elegant lily bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, fragrant lilies for every occasion. Order online now.',
    'meta_keywords' => 'lily bouquet, lily flower delivery, white lily bouquet, elegant flower bouquet, fragrant flowers, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What do lily bouquets symbolise?', 'answer' => 'Lilies symbolise purity, devotion, and refined beauty. White lilies convey peace and remembrance, while coloured lilies celebrate joy and admiration.'],
        ['question' => 'Are lily bouquets fragrant?', 'answer' => 'Yes. Many lilies have a gentle, pleasant fragrance. If you need a mild scent for a hospital room, let us know and we will advise.'],
        ['question' => 'Is same-day lily bouquet delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Can I mix lilies with other flowers?', 'answer' => 'Yes. Choose an all-lily arrangement or a mixed design with roses and orchids. Our florists can customise it for you.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the lily bouquet with a cake or gift and we will deliver the complete combo together.'],
        ['question' => 'Are the lilies fresh?', 'answer' => 'Every lily bouquet is arranged with daily-fresh lilies close to dispatch and packed protectively for freshness.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver lily bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 27 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Carnation Bouquet',
    'slug'  => 'carnation-bouquet',
    'content' => <<<'HTML'
<h2>Charming and Cheerful Carnation Bouquet</h2>
<p>Ruffled, colourful, and wonderfully long-lasting, a carnation bouquet is a delightful way to share warmth. Perfect for birthdays, thank-yous, and heartfelt wishes, a carnation bouquet blends beauty with meaning. With their soft texture and wide range of shades, a carnation bouquet suits nearly every occasion and budget.</p>

<p>At <strong>Sai Flower</strong>, every carnation bouquet is hand-arranged with daily-fresh carnations in cheerful, balanced designs. We prepare each bouquet close to dispatch, so it arrives fresh, full, and beautifully colourful.</p>

<h2>Why Choose a Carnation Bouquet</h2>
<p>Carnations symbolise love, admiration, and gratitude. A thoughtfully arranged carnation bouquet delivers charm and sentiment in one lovely gift.</p>
<ul>
<li><strong>Long-lasting carnations</strong> — pink, red, white, and mixed shades</li>
<li><strong>Great value</strong> — full, beautiful blooms at a friendly price</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Versatile gifting</strong> — birthdays, thanks, and more</li>
</ul>

<h3>A Carnation for Every Message</h3>
<p>Pink carnations express gratitude, red conveys admiration, and white symbolises pure affection. Because a carnation bouquet comes in so many colours, it fits any sentiment. Add a cake or gift, and one carnation bouquet becomes a complete, cheerful surprise.</p>

<h3>Fresh, Full, and On Time</h3>
<p>Carnations are loved for lasting long. Each carnation bouquet is prepared close to dispatch and packed protectively, so blooms arrive fresh, ruffled, and vibrant on delivery.</p>

<h2>Order a Carnation Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order carnation bouquets online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, a carnation bouquet brings charm, freshness, and reliable delivery — order now and send cheerful blooms to someone special.</p>
HTML,
    'meta_title' => 'Carnation Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send a cheerful carnation bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, long-lasting carnations. Order online now.',
    'meta_keywords' => 'carnation bouquet, carnation flower delivery, colourful bouquet, affordable flower bouquet, mixed flower bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What do carnation colours mean?', 'answer' => 'Pink carnations express gratitude, red conveys admiration, and white symbolises pure affection. Sai Flower can mix shades to suit your message.'],
        ['question' => 'Do carnations last long?', 'answer' => 'Yes. Carnations are among the longest-lasting flowers. With fresh stems and proper care, a carnation bouquet stays beautiful for many days.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'Are carnation bouquets affordable?', 'answer' => 'Yes. Carnations offer full, beautiful blooms at a friendly price, making them a great value gifting choice.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the carnation bouquet with a cake or gift and we will deliver the complete combo together.'],
        ['question' => 'Are the carnations fresh?', 'answer' => 'Every carnation bouquet is arranged with daily-fresh carnations close to dispatch and packed protectively for freshness.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver carnation bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 28 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Gerbera Bouquet',
    'slug'  => 'gerbera-bouquet',
    'content' => <<<'HTML'
<h2>Spread Joy With a Vibrant Gerbera Bouquet</h2>
<p>Few flowers radiate happiness like gerberas, and a gerbera bouquet is pure sunshine in a wrap. Bold, colourful, and cheerful, a gerbera bouquet instantly lifts the mood. Perfect for birthdays, congratulations, and get-well wishes, a gerbera bouquet brings a burst of energy to any celebration or ordinary day.</p>

<p>At <strong>Sai Flower</strong>, every gerbera bouquet is hand-arranged with daily-fresh gerberas in bright, playful combinations. We prepare each bouquet close to dispatch, so it arrives fresh, colourful, and beaming with cheer.</p>

<h2>Why Choose a Gerbera Bouquet</h2>
<p>Gerberas symbolise happiness, positivity, and warmth. A thoughtfully arranged gerbera bouquet delivers instant joy in a rainbow of colours.</p>
<ul>
<li><strong>Bright gerberas</strong> — red, orange, pink, yellow, and mixed</li>
<li><strong>Cheerful designs</strong> — playful, colourful arrangements</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Great for all ages</strong> — kids, friends, and family</li>
</ul>

<h3>A Bloom That Makes People Smile</h3>
<p>Gerberas are the go-to flower for happy occasions. Their big, bold faces suit birthdays, new beginnings, and cheer-up moments. Because a gerbera bouquet is so lively, it fits nearly everyone. Add a cake or gift, and one gerbera bouquet becomes a complete, joyful surprise.</p>

<h3>Fresh, Colourful, and On Time</h3>
<p>Cheerful flowers must arrive at their best. Each gerbera bouquet is prepared close to dispatch and packed protectively, so blooms stay bright, firm, and full of life on delivery.</p>

<h2>Order a Gerbera Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order gerbera bouquets online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, a gerbera bouquet brings joy, freshness, and reliable delivery — order now and send a splash of happiness to someone special.</p>
HTML,
    'meta_title' => 'Gerbera Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send a vibrant gerbera bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, cheerful gerberas. Order a gerbera bouquet online now.',
    'meta_keywords' => 'gerbera bouquet, gerbera flower delivery, colourful bouquet, birthday flowers, cheerful bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What do gerberas symbolise?', 'answer' => 'Gerberas symbolise happiness, positivity, and warmth. Their bright, bold blooms make a gerbera bouquet perfect for cheerful occasions.'],
        ['question' => 'What colours do gerberas come in?', 'answer' => 'Gerberas come in red, orange, pink, yellow, and mixed shades. Tell us your preferred colours and we will design the bouquet accordingly.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'What occasions suit a gerbera bouquet?', 'answer' => 'Gerberas are perfect for birthdays, congratulations, get-well wishes, and cheer-up gestures for all ages.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the gerbera bouquet with a cake or gift and we will deliver the complete combo together.'],
        ['question' => 'Are the gerberas fresh?', 'answer' => 'Every gerbera bouquet is arranged with daily-fresh gerberas close to dispatch and packed protectively for freshness.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver gerbera bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 29 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Sunflower Bouquet',
    'slug'  => 'sunflower-bouquet',
    'content' => <<<'HTML'
<h2>Bring Sunshine With a Sunflower Bouquet</h2>
<p>Bold, bright, and full of warmth, a sunflower bouquet is like a ray of sunshine delivered to the door. Symbolising happiness, positivity, and loyalty, a sunflower bouquet instantly brightens any room. Perfect for birthdays, congratulations, and cheer-up moments, a sunflower bouquet makes a striking and joyful gift.</p>

<p>At <strong>Sai Flower</strong>, every sunflower bouquet is hand-arranged with daily-fresh sunflowers in cheerful, eye-catching designs. We prepare each bouquet close to dispatch, so it arrives fresh, golden, and radiant.</p>

<h2>Why Choose a Sunflower Bouquet</h2>
<p>Sunflowers stand for warmth, admiration, and optimism. A thoughtfully arranged sunflower bouquet delivers big, bright happiness in a single gesture.</p>
<ul>
<li><strong>Fresh sunflowers</strong> — bold, golden, and cheerful</li>
<li><strong>Striking designs</strong> — solo or mixed with complementary blooms</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Uplifting gifting</strong> — birthdays, thanks, and encouragement</li>
</ul>

<h3>A Bloom That Radiates Positivity</h3>
<p>Sunflowers are perfect when you want to say "you brighten my life." Their sunny faces suit happy occasions and encouraging moments alike. Because a sunflower bouquet is so uplifting, it fits many recipients. Add a cake or gift, and one sunflower bouquet becomes a complete, joyful surprise.</p>

<h3>Fresh, Golden, and On Time</h3>
<p>Sunshine should arrive at its brightest. Each sunflower bouquet is prepared close to dispatch and packed protectively, so blooms stay firm, golden, and radiant on delivery.</p>

<h2>Order a Sunflower Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order sunflower bouquets online Delhi">flower collection</a>, add a <a href="/cakes" title="Order cakes online">cake</a> or <a href="/gifts" title="Gift hampers Delhi">gift</a>, and check out in minutes. With Sai Flower, a sunflower bouquet brings warmth, freshness, and reliable delivery — order now and send sunshine to someone special.</p>
HTML,
    'meta_title' => 'Sunflower Bouquet Delivery Delhi NCR | Sai Flower',
    'meta_description' => 'Send a bright sunflower bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Fresh, golden sunflowers. Order a sunflower bouquet online now.',
    'meta_keywords' => 'sunflower bouquet, sunflower delivery, cheerful bouquet, birthday flowers, yellow flower bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What do sunflowers symbolise?', 'answer' => 'Sunflowers symbolise happiness, warmth, loyalty, and optimism, making a sunflower bouquet a wonderfully uplifting gift.'],
        ['question' => 'Can I mix sunflowers with other flowers?', 'answer' => 'Yes. Choose an all-sunflower design or mix them with roses and seasonal blooms for extra colour. Our florists can customise it.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Absolutely. Order before the daily cut-off for same-day delivery across Delhi NCR.'],
        ['question' => 'What occasions suit a sunflower bouquet?', 'answer' => 'Sunflowers are perfect for birthdays, congratulations, encouragement, and cheer-up moments.'],
        ['question' => 'Can I add a cake or gift?', 'answer' => 'Yes. Pair the sunflower bouquet with a cake or gift and we will deliver the complete combo together.'],
        ['question' => 'Are the sunflowers fresh?', 'answer' => 'Every sunflower bouquet is arranged with daily-fresh sunflowers close to dispatch and packed protectively for freshness.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver sunflower bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

// 30 ────────────────────────────────────────────────────────────────────
[
    'title' => 'Exotic Flower Bouquet',
    'slug'  => 'exotic-flower-bouquet',
    'content' => <<<'HTML'
<h2>Make a Statement With an Exotic Flower Bouquet</h2>
<p>When ordinary flowers will not do, an exotic flower bouquet delivers rare beauty and wow factor. Featuring unusual blooms, striking shapes, and bold colours, an exotic flower bouquet stands out from every other gift. Perfect for milestones, luxury gifting, and admirers of the extraordinary, an exotic flower bouquet leaves a lasting impression.</p>

<p>At <strong>Sai Flower</strong>, every exotic flower bouquet is hand-designed with premium, daily-fresh and imported blooms. We craft distinctive arrangements close to dispatch, so your bouquet arrives fresh, dramatic, and unforgettable.</p>

<h2>Why Choose an Exotic Bouquet</h2>
<p>Exotic flowers convey uniqueness, luxury, and bold admiration. A carefully designed exotic flower bouquet shows you went beyond the expected.</p>
<ul>
<li><strong>Rare, imported blooms</strong> — orchids, anthuriums, and more</li>
<li><strong>Striking designs</strong> — bold shapes and vivid colours</li>
<li><strong>Same-day and midnight delivery</strong> — across Delhi NCR</li>
<li><strong>Luxury gifting</strong> — milestones and premium occasions</li>
</ul>

<h3>A Bouquet Like No Other</h3>
<p>For someone who has seen every classic bunch, an exotic flower bouquet feels refreshingly new. Choose dramatic tropical blooms or an artistic designer mix. Because an exotic flower bouquet is so distinctive, it suits grand gestures, and pairs beautifully with a premium cake or hamper.</p>

<h3>Fresh, Rare, and On Time</h3>
<p>Rare blooms deserve careful handling. Each exotic flower bouquet is prepared close to dispatch and packed with extra care, so every unique stem arrives fresh, firm, and stunning.</p>

<h2>Order an Exotic Flower Bouquet Online</h2>
<p>Browse our <a href="/flowers" title="Order exotic bouquets online Delhi">flower collection</a>, add a <a href="/cakes" title="Order premium cakes online">premium cake</a> or <a href="/gifts" title="Luxury gifts Delhi">gift</a>, and check out securely. With Sai Flower, an exotic flower bouquet brings rare beauty, freshness, and reliable delivery — order now and gift something truly extraordinary.</p>
HTML,
    'meta_title' => 'Exotic Flower Bouquet Delivery | Sai Flower',
    'meta_description' => 'Send a rare exotic flower bouquet in Delhi NCR with same-day & midnight delivery from Sai Flower. Premium imported blooms. Order an exotic bouquet online now.',
    'meta_keywords' => 'exotic flower bouquet, exotic flowers delivery, premium flower bouquet, luxury flower bouquet, orchid bouquet, same day flower delivery, online flower delivery',
    'faqs' => [
        ['question' => 'What flowers are in an exotic bouquet?', 'answer' => 'An exotic flower bouquet features rare and imported blooms such as orchids, anthuriums, birds of paradise, and other striking varieties, subject to availability.'],
        ['question' => 'Are exotic bouquets more expensive?', 'answer' => 'Exotic and imported blooms are premium, so prices are higher than standard bouquets. The exact cost is shown transparently before you pay.'],
        ['question' => 'Is same-day delivery available?', 'answer' => 'Yes, in many cases. Because exotic blooms are specialised, we recommend WhatsApp-ing us to confirm same-day availability for your chosen design.'],
        ['question' => 'Can I customise an exotic bouquet?', 'answer' => 'Absolutely. Tell us your preferences on WhatsApp and our florists will craft a custom exotic arrangement, subject to seasonal availability.'],
        ['question' => 'Can I add a premium gift?', 'answer' => 'Yes. Pair the bouquet with a premium cake or luxury hamper and we will deliver the complete combo together.'],
        ['question' => 'Are the exotic flowers fresh?', 'answer' => 'Every exotic flower bouquet is arranged with premium, daily-fresh and imported blooms close to dispatch and packed with extra care.'],
        ['question' => 'Which areas do you deliver to?', 'answer' => 'We deliver exotic bouquets across Delhi, Gurgaon, Noida, and nearby NCR pin codes. Confirm coverage at checkout.'],
    ],
],

];

echo "=== Seeding keyword pages — Batch 3 ===\n";
$created = 0;
foreach ($pages as $page) {
    if (insert_page($conn, $page)) {
        $created++;
    }
}
echo "=== Done. {$created} new page(s) created. ===\n";
