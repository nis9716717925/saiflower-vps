<?php
/**
 * About Us — brand story with collage visual, experience badge and feature checklist.
 * Carries the full SEO copy previously shown in the plain welcome block.
 */
require_once __DIR__ . '/../../includes/url_helper.php';
?>
<section class="lx-about hp-seo" aria-labelledby="lx-about-title">
    <div class="lx-about__inner">
        <div class="lx-about__visual">
            <div class="lx-about__badge">
                <span class="lx-about__badge-dot"><i class="fas fa-award" aria-hidden="true"></i></span>
                <span>25+ Years Of<br>Experience</span>
            </div>
            <div class="lx-about__main">
                <img src="<?= htmlspecialchars(get_image_url('uploads/sections/img_69c12ca4d6812_img69b5447deae89WhatsAppImage20260314at31518PM.webp')) ?>"
                     alt="Sai Flower handcrafted bouquet"
                     width="480" height="580"
                     loading="lazy" decoding="async">
            </div>
            <div class="lx-about__side">
                <span class="lx-about__side-img">
                    <img src="<?= htmlspecialchars(get_image_url('uploads/sections/img_69affbff9fce1_img69a6ad335b957WhatsAppImage20260303at23841PM.webp')) ?>"
                         alt="Elegant white rose bouquet"
                         width="240" height="240"
                         loading="lazy" decoding="async">
                </span>
                <span class="lx-about__side-img">
                    <img src="<?= htmlspecialchars(get_image_url('uploads/sections/img_69b00d7d6b073_img69a6aa4b1d253WhatsAppImage20260303at23112PM.webp')) ?>"
                         alt="Premium red rose bouquet"
                         width="240" height="240"
                         loading="lazy" decoding="async">
                </span>
            </div>
        </div>

        <div>
            <p class="lx-about__kicker">About Us</p>
            <h2 id="lx-about-title" class="lx-about__title">Welcome to Sai Flower</h2>
            <div class="lx-about__text">
                <p>Since 1998, Sai Flower has been handcrafting fresh floral arrangements for Delhi and the NCR. From our shop on Lodhi Road, we source premium roses, orchids, lilies, and seasonal blooms to create bouquets that feel personal — whether you are celebrating a birthday, marking an anniversary, or simply sending a thoughtful surprise.</p>
                <p>Our <a href="/flowers">online flower shop</a> makes ordering easy. Choose from curated bouquets, custom arrangements, and add-ons like <a href="/cakes">cakes</a> and <a href="/gifts">gift hampers</a>. We offer same-day delivery across Delhi, with express and midnight options available on select products.</p>
                <p>Beyond everyday gifting, Sai Flower specialises in <a href="/flowers">wedding flowers</a>, corporate events, and large-scale décor. Our team works closely with clients to design stage backdrops, table centrepieces, bridal bouquets, and venue styling that matches your vision and budget.</p>
                <p>Every arrangement is made to order with freshly cut flowers and careful packaging so it arrives looking its best. Browse our <a href="/gallery">floral gallery</a> for inspiration, read tips on our <a href="/blog">blog</a>, or <a href="/contact">contact us</a> — we are happy to help with delivery areas, timing, and custom requests. See our <a href="/delivery-policy">delivery policy</a> for full details.</p>
            </div>
            <ul class="lx-about__features">
                <li><i class="fas fa-check" aria-hidden="true"></i> Same-Day &amp; Midnight Delivery</li>
                <li><i class="fas fa-check" aria-hidden="true"></i> Freshly Cut, Handcrafted Blooms</li>
                <li><i class="fas fa-check" aria-hidden="true"></i> Fair Prices &amp; Easy Ordering</li>
                <li><i class="fas fa-check" aria-hidden="true"></i> Weddings, Events &amp; Décor</li>
            </ul>
            <a href="/about.php" class="lx-btn-gold">Discover More <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>
