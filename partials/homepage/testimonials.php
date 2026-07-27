<?php
/**
 * Testimonials — customer review slider (scroll-snap carousel with arrows + auto-scroll).
 */
$lxTestimonials = [
    ['name' => 'Priya Sharma', 'where' => 'Lajpat Nagar, Delhi', 'rating' => 5, 'source' => 'fab fa-google', 'text' => 'Ordered a red rose bouquet for my parents\' anniversary at 2 PM and it reached them by evening — fresher than anything I\'ve bought from a store. The wrapping was gorgeous.'],
    ['name' => 'Rohit Verma', 'where' => 'Gurugram', 'rating' => 5, 'source' => 'fab fa-google', 'text' => 'Sai Flower handled the entire décor for my sister\'s wedding — stage backdrop, centrepieces, everything. Guests kept asking who the florist was. Truly professional team.'],
    ['name' => 'Anjali Mehta', 'where' => 'South Extension, Delhi', 'rating' => 5, 'source' => 'fab fa-instagram', 'text' => 'The midnight birthday surprise was perfect. Cake and orchids arrived at 12 sharp with a handwritten note. My husband was speechless. Will order again and again!'],
    ['name' => 'Karan Singh', 'where' => 'Noida', 'rating' => 5, 'source' => 'fab fa-google', 'text' => 'I\'ve tried the big gifting sites — nothing matches the freshness here. You can tell the flowers are cut the same day. Checkout was quick and delivery updates were on point.'],
    ['name' => 'Sneha Kapoor', 'where' => 'Defence Colony, Delhi', 'rating' => 5, 'source' => 'fab fa-facebook', 'text' => 'Our office orders weekly arrangements from Sai Flower for the reception. Consistently beautiful, always on time, and the team is a pleasure to work with.'],
    ['name' => 'Amit Malhotra', 'where' => 'Dwarka, Delhi', 'rating' => 5, 'source' => 'fab fa-google', 'text' => 'Sent a sympathy arrangement on short notice. They were thoughtful about the flower choice and delivered within hours. Small gestures like this matter — thank you.'],
];
?>
<section class="lx-testimonials" aria-labelledby="lx-testimonials-title">
    <div class="lx-testimonials__inner">
        <div class="lx-section-head">
            <span class="lx-kicker">Loved Across Delhi NCR</span>
            <h2 id="lx-testimonials-title">What Our Customers Say</h2>
            <p>Real words from the people we've helped celebrate, apologise, surprise and remember.</p>
        </div>

        <div class="lx-testimonials__wrap">
            <button type="button" class="lx-testimonials__nav" id="lxTestimonialsPrev" aria-label="Previous testimonials" aria-controls="lxTestimonialsTrack">
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <div class="lx-testimonials__track hide-scrollbar" id="lxTestimonialsTrack" role="list" aria-label="Customer testimonials">
                <?php foreach ($lxTestimonials as $t):
                    $initials = '';
                    foreach (array_slice(explode(' ', $t['name']), 0, 2) as $word) {
                        $initials .= mb_substr($word, 0, 1);
                    }
                ?>
                <figure class="lx-testimonial" role="listitem">
                    <div class="lx-testimonial__stars" aria-label="Rated <?= (int) $t['rating'] ?> out of 5">
                        <?php for ($s = 0; $s < (int) $t['rating']; $s++): ?><i class="fas fa-star" aria-hidden="true"></i><?php endfor; ?>
                    </div>
                    <blockquote class="lx-testimonial__text">"<?= htmlspecialchars($t['text']) ?>"</blockquote>
                    <figcaption class="lx-testimonial__meta">
                        <span class="lx-testimonial__avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></span>
                        <span>
                            <span class="lx-testimonial__name"><?= htmlspecialchars($t['name']) ?></span>
                            <span class="lx-testimonial__where"><?= htmlspecialchars($t['where']) ?></span>
                        </span>
                        <i class="lx-testimonial__source <?= htmlspecialchars($t['source']) ?>" aria-hidden="true"></i>
                    </figcaption>
                </figure>
                <?php endforeach; ?>
            </div>
            <button type="button" class="lx-testimonials__nav" id="lxTestimonialsNext" aria-label="Next testimonials" aria-controls="lxTestimonialsTrack">
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </div>

        <p class="lx-testimonials__summary">
            <span><i class="fas fa-star" style="color:#e2a53a" aria-hidden="true"></i> Rated <strong>4.8 / 5</strong></span>
            <span>·</span>
            <span>Trusted by <strong>10,000+</strong> happy customers</span>
            <span>·</span>
            <span>Serving Delhi NCR since <strong>1998</strong></span>
        </p>
    </div>
</section>
