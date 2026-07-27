<?php
require_once __DIR__ . '/config.php';

// 1. FETCH THEME SETTINGS
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);

$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#ffffff';
$tCol = $settings['theme_text_color'] ?? '#333333';
$fFam = $settings['theme_font'] ?? "'Playfair Display', serif";

// Maintenance Logic
if (($settings['maintenance_mode'] ?? 0) == 1) {
    header("Location: maintenance.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . "/partials/tailwind_config.php"; ?>
    <meta charset="UTF-8">
    
    <?php require_once __DIR__ . '/includes/seo_helper.php'; ?>
    <?= render_seo('about.php'); ?>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'About Us', 'item' => 'about.php']]);
    ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "AboutPage",
      "mainEntity": {
        "@type": "Organization",
        "name": "Sai Flowers",
        "description": "Sai Flowers is a premium floral boutique based in New Delhi, specializing in hand-crafted bouquets and luxury event styling."
      }
    }
    </script>

<style>
        :root {
            --primary: <?= $pCol ?>;
            --accent: <?= $sCol ?>;
            --bg-site: <?= $bgColor ?>;
            --text-main: <?= $tCol ?>;
            --font-main: <?= $fFam ?>;
            --shadow-md: 0 10px 25px rgba(0,0,0,0.08);
        }

        body {
            font-family: var(--font-main), sans-serif;
            margin: 0;
            color: var(--text-main);
            background: var(--bg-site);
            line-height: 1.8;
            overflow-x: hidden; /* Prevent horizontal scroll from negative margins */
        }

        h1, h2, h3, h4 { font-family: var(--font-main); }
        .container { max-width: 1200px; margin: auto; padding: 0 20px; } /* Slightly reduced padding */
        .section { padding: 80px 0; }

        /* --- NAVBAR RESET --- */
        nav, .navbar-container {
            position: relative !important;
            width: 100%;
            z-index: 1000;
            background: #ffffff !important;
        }

        /* --- HERO --- */
        .page-header {
            text-align: center; 
            margin-bottom: 40px;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1490750967868-88aa4486c946?q=80&w=1400');
            background-size: cover; 
            background-position: center; 
            color: white;
            padding: 100px 20px;
            border-radius: 0 0 50px 50px;
        }

        /* --- PHILOSOPHY --- */
        .philosophy-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .philosophy-card { background: white; padding: 40px; border-radius: 30px; border-left: 8px solid var(--primary); box-shadow: var(--shadow-md); }

        /* --- TIMELINE --- */
        .timeline { position: relative; max-width: 800px; margin: 50px auto; padding: 20px 0; }
        .timeline::after { content: ''; position: absolute; width: 2px; background: var(--accent); top: 0; bottom: 0; left: 50%; margin-left: -1px; }
        .timeline-item { padding: 10px 40px; position: relative; width: 50%; box-sizing: border-box; }
        .timeline-item::after { content: ''; position: absolute; width: 20px; height: 20px; right: -10px; background-color: white; border: 4px solid var(--primary); top: 15px; border-radius: 50%; z-index: 1; }
        .left { left: 0; text-align: right; }
        .right { left: 50%; }
        .right::after { left: -10px; }
        .date { font-weight: bold; color: var(--primary); font-size: 1.2rem; }

        /* --- PROMISE BOXES --- */
        .why-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-top: 40px; }
        .why-box { 
            background: white; padding: 40px 30px; border-radius: 25px; 
            text-align: center; border: 1px solid #f0f0f0; transition: 0.4s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
        .why-icon { width: 60px; height: 60px; background: #f8fcf9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 1.5rem; color: var(--primary); }

        /* --- FAQ --- */
        .faq-wrapper { max-width: 850px; margin: 40px auto 0; }
        .faq-item { background: white; border-radius: 15px; margin-bottom: 15px; border: 1px solid #f0f0f0; overflow: hidden; transition: 0.3s; }
        .faq-question { padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-weight: 600; color: var(--primary); }
        .faq-answer { max-height: 0; overflow: hidden; padding: 0 25px; color: #666; transition: 0.3s ease-out; }
        .faq-item.active .faq-answer { max-height: 300px; padding-bottom: 20px; }
        .faq-item.active { border-color: var(--primary); }

        @media(max-width:768px){
            .section { padding: 50px 0; } /* Balanced Top/Bottom Padding */
            .page-header { padding: 90px 20px 60px; border-radius: 0 0 35px 35px; margin-bottom: 10px; }
            
            .philosophy-grid { grid-template-columns: 1fr; gap: 40px; }
            .philosophy-card { padding: 30px 25px; margin: 0 10px; }

            .timeline::after { left: 25px; } /* Tighter timeline */
            .timeline-item { width: 100%; padding-left: 60px; padding-right: 15px; text-align: left !important; margin-bottom: 25px;}
            .timeline-item::after { left: 15px; }
            .right { left: 0%; }
            
            /* Promise Horizontal Scroll - Fixed Margins */
            .why-grid {
                display: flex;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                gap: 15px;
                padding: 10px 15px 35px 15px; /* Better padding for scroll feel */
                margin: 0 -20px; /* Compensate for container padding */
                scrollbar-width: none;
            }
            .why-grid::-webkit-scrollbar { display: none; }
            .why-box { flex: 0 0 85%; scroll-snap-align: center; }
            .swipe-indicator { display: block !important; text-align: center; color: var(--accent); font-size: 0.8rem; margin-top: -15px; animation: bounceX 2s infinite; margin-bottom: 20px;}
        }

        @keyframes bounceX { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(5px); } }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<header class="page-header">
    <div class="container">
        <h1 style="font-size: clamp(2.5rem, 8vw, 4rem); margin: 0;">Behind the Blooms</h1>
        <p style="opacity: 0.9; font-size: 1.1rem;">A decade of passion, creativity, and floral excellence.</p>
    </div>
</header>

<section class="section container">
    <div class="philosophy-grid">
        <div>
            <h2 style="font-size: clamp(2rem, 5vw, 2.8rem); line-height: 1.2; margin-bottom: 20px;">We don't just sell flowers; <span style="color: var(--primary);">we deliver emotions.</span></h2>
            <p style="font-size: 1.1rem; color: #555;">Every bouquet that leaves our studio is a hand-crafted masterpiece. We believe that nature’s beauty should be accessible and tailored to your unique story.</p>
        </div>
        <div class="philosophy-card">
            <h3 style="margin-top:0;">Our Mission</h3>
            <p>To redefine the floral experience in New Delhi by combining sustainable sourcing with avant-garde design. Our focus remains on quality, artistry, and the lasting smile on the recipient's face.</p>
        </div>
    </div>
</section>

<section class="section" style="background: #fafafa; border-top: 1px solid #f0f0f0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 50px;">Our Evolution</h2>
        <div class="timeline">
            <div class="timeline-item left">
                <div class="date">2015</div>
                <h4>Founding Vision</h4>
                <p>Started as a boutique studio with a dream to bring international floral varieties to Delhi.</p>
            </div>
            <div class="timeline-item right">
                <div class="date">2018</div>
                <h4>Event Styling</h4>
                <p>Recognized as a leading luxury event and wedding floral decor specialist in NCR.</p>
            </div>
            <div class="timeline-item left">
                <div class="date">2024</div>
                <h4>Going Digital</h4>
                <p>Launched online custom bouquet building to reach our clients across the country.</p>
            </div>
             <div class="timeline-item right">
                <div class="date">2026</div>
                <h4>Sai Flowers Online</h4>
                <p>Expanded with a new digital experience at saiflowers.com</p>
            </div>
        </div>
    </div>
</section>

<section class="section container">
    <h2 style="text-align: center; font-size: clamp(1.8rem, 5vw, 2.5rem); margin-bottom: 10px;">The Sai Flowers Promise</h2>
    <p style="text-align: center; color: #888; margin-bottom: 30px;">Why thousands trust us with their special moments.</p>
    
    <div class="why-grid">
        <div class="why-box">
            <div class="why-icon"><i class="fas fa-leaf"></i></div>
            <h3>Grown with Integrity</h3>
            <p>Direct sourcing from premium farms ensures that your flowers stay fresh for much longer.</p>
        </div>
        <div class="why-box">
            <div class="why-icon"><i class="fas fa-magic"></i></div>
            <h3>Artisan Design</h3>
            <p>Every petal is placed with intent by florists who treat their work as high art.</p>
        </div>
        <div class="why-box">
            <div class="why-icon"><i class="fas fa-heart"></i></div>
            <h3>Customer Love</h3>
            <p>Dedicated tracking and support because we value the trust you place in us.</p>
        </div>
    </div>
    <div class="swipe-indicator" style="display:none;">
        <i class="fas fa-hand-pointer"></i> Swipe to explore
    </div>
</section>

<section class="section" style="background: #ffffff; border-top: 1px solid #f0f0f0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 10px;">Common Questions</h2>
        <p style="text-align: center; color: #888; margin-bottom: 40px;">Find quick answers about our brand and heritage.</p>
        <div class="faq-wrapper">
            <?php 
            $faq_res = mysqli_query($conn, "SELECT * FROM faqs WHERE (page = 'about' OR page = 'general') AND status = 1 LIMIT 6");
            if($faq_res && mysqli_num_rows($faq_res) > 0):
                while($f = mysqli_fetch_assoc($faq_res)): ?>
                <div class="faq-item" onclick="this.classList.toggle('active')">
                    <div class="faq-question">
                        <?= htmlspecialchars($f['question']) ?>
                        <i class="fas fa-plus faq-icon" style="font-size: 0.8rem;"></i>
                    </div>
                    <div class="faq-answer"><?= nl2br(htmlspecialchars($f['answer'])) ?></div>
                </div>
            <?php endwhile; else: ?>
                <p style="text-align:center; color:#999;">Explore our collections to see our quality in action.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/partials/commutes_map_embed.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
    const themeChannel = new BroadcastChannel('theme_sync');
    themeChannel.onmessage = (e) => {
        const d = e.data;
        const root = document.documentElement;
        root.style.setProperty('--primary', d.p);
        root.style.setProperty('--accent', d.s);
        root.style.setProperty('--bg-site', d.bg);
        root.style.setProperty('--text-main', d.t);
        document.body.style.fontFamily = d.f;
        document.querySelectorAll('h1, h2, h3, h4').forEach(h => h.style.fontFamily = d.f);
    };
</script>

</body>
</html>