<?php
// includes/faq-module.php
// 1. Determine Current Page Identifier
$current_page = basename($_SERVER['PHP_SELF']);

// Handle specific entity details
if ($current_page == 'flower-detail.php' && isset($_GET['id'])) {
    $current_page = 'flower-' . intval($_GET['id']);
} elseif ($current_page == 'event-detail.php' && isset($_GET['id'])) {
    $current_page = 'event-' . intval($_GET['id']);
} elseif ($current_page == 'cake-detail.php' && isset($_GET['id'])) {
    $current_page = 'cake-' . intval($_GET['id']);
} elseif ($current_page == 'gift-detail.php' && isset($_GET['id'])) {
    $current_page = 'gift-' . intval($_GET['id']);
} else {
    // Handle core generic pages to match admin slugs
    switch ($current_page) {
        case 'index.php': $current_page = 'home'; break;
        case 'about.php': $current_page = 'about-us'; break;
        case 'contact.php': $current_page = 'contact-us'; break;
        case 'gallery.php': $current_page = 'gallery'; break;
        case 'cart.php': $current_page = 'cart'; break;
        case 'checkout.php': $current_page = 'checkout'; break;
        case 'services.php': $current_page = 'services'; break;
    }
}

// 2. Fetch FAQs for Current Page
// We check for exact match OR 'home' if it's the home page (handled above)
// If logic requires 'Global' FAQs to appear everywhere, we can adjust the OR clause.
// For now, it's specific to the page + Home fallback if desired, but user asked for "All website pages", suggesting specificity.
// Let's strictly match the page identifier managed in Admin.

$esc_page = mysqli_real_escape_string($conn, $current_page);
$faq_sql = mysqli_query($conn, "SELECT question, answer FROM faqs WHERE page='$esc_page' AND status=1");
$schema_data = [];
?>

<?php if(mysqli_num_rows($faq_sql) > 0): ?>
<section class="section" id="faq-section">
    <div class="container" style="max-width:800px;">
        <h2 class="section-title">Common Questions</h2>
        <p class="section-subtitle">Everything you need to know about our floral services.</p>

        <div class="faq-list">
            <?php while($f = mysqli_fetch_assoc($faq_sql)): 
                // Prepare Schema Data
                $schema_data[] = [
                    "@type" => "Question",
                    "name" => $f['question'],
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => strip_tags($f['answer'])
                    ]
                ];
            ?>
            <details class="faq-item">
                <summary class="faq-question">
                    <?= htmlspecialchars($f['question']) ?>
                    <span class="icon">+</span>
                </summary>
                <div class="faq-answer">
                    <p><?= nl2br(htmlspecialchars($f['answer'])) ?></p>
                </div>
            </details>
            <?php endwhile; ?>
        </div>
    </div>

    <style>
        /* Local FAQ Styles ensuring compatibility */
        .faq-item {
            background: white; border-radius: 12px; margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); border: 1px solid #eee; overflow: hidden;
        }
        .faq-question {
            padding: 20px; font-weight: 600; cursor: pointer; list-style: none;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 1.05rem; color: var(--text-dark); background: #fafafa;
        }
        .faq-question::-webkit-details-marker { display: none; }
        .faq-question:hover { background: #f0f0f0; }
        .faq-answer { padding: 20px; color: #555; line-height: 1.6; background: white; border-top: 1px solid #eee; }
        .faq-item[open] .faq-question { background: var(--primary-light); color: var(--primary); }
        .faq-item[open] .icon { transform: rotate(45deg); }
        .icon { font-size: 1.5rem; transition: 0.3s; font-weight: 300; }
    </style>
</section>

<!-- JSON-LD FAQ SCHEMA -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": <?php echo json_encode($schema_data); ?>
}
</script>

<?php endif; ?>