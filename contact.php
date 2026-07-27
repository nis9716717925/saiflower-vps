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
    <?= render_seo('contact.php'); ?>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Contact Us', 'item' => 'contact.php']]);
    ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ContactPage",
      "mainEntity": {
        "@type": "Florist",
        "name": "Sai Flowers",
        "image": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/favicon.png",
        "telephone": "+91-8802004527",
        "email": "saiflower03@gmail.com",
        "address": {
          "@type": "PostalAddress",
          "streetAddress": "Shop No 1, Sai Mandir, Lodhi Rd, Gokalpuri, Institutional Area, Lodi Colony",
          "addressLocality": "New Delhi",
          "postalCode": "110003",
          "addressCountry": "IN"
        },
        "geo": {
          "@type": "GeoCoordinates",
          "latitude": 28.5900572,
          "longitude": 77.2253415
        }
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
            --white: #ffffff;
            --whatsapp: #25D366;
        }

        /* --- GLOBAL RESET TO FIX NAVBAR GAP --- */
        body {
            font-family: var(--font-main), sans-serif;
            margin: 0 !important;
            padding: 0 !important;
            color: var(--text-main);
            background: var(--bg-site);
            line-height: 1.8;
            overflow-x: hidden;
        }

        h1, h2, h3, h4 { font-family: var(--font-main); }
        .container { max-width: 1200px; margin: auto; padding: 0 20px; }
        
        /* --- NAVBAR RESET --- */
        nav, .navbar-container {
            position: relative !important;
            width: 100%;
            z-index: 1000;
            background: #ffffff !important;
            border-bottom: 1px solid #f0f0f0;
        }

        /* --- CLEAN HEADER (NO BACKGROUND IMAGE) --- */
        .page-header-simple {
            text-align: center; 
            padding: 60px 20px 40px;
            background: var(--bg-site);
        }

        .page-header-simple h1 { 
            font-size: clamp(2.2rem, 8vw, 3.5rem); 
            color: var(--primary); 
            margin: 0; 
            font-weight: bold;
        }

        .page-header-simple p { 
            color: #666; 
            font-size: 1.1rem; 
            max-width: 800px; 
            margin: 15px auto 0; 
        }

        /* --- CONTACT WRAPPER --- */
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr;
            gap: 50px;
            background: var(--white);
            padding: 50px;
            border-radius: 35px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05);
            align-items: start;
            border: 1px solid rgba(0,0,0,0.02);
            margin-bottom: 60px;
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
        }

        /* SOCIAL MEDIA STRIP */
        .social-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; margin-top: 30px; }
        .social-card {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            padding: 20px; border-radius: 20px; background: #fafafa;
            text-decoration: none; color: var(--text-main); transition: 0.3s;
            border: 1px solid #eee;
        }
        .social-card i { font-size: 1.5rem; }
        .social-card:hover { transform: translateY(-5px); border-color: var(--primary); background: #fff; }

        /* DIRECTORY SECTION */
        .directory-section { background: white; padding: 60px 40px; border-radius: 35px; margin-bottom: 60px; border: 1px solid #f0f0f0; }
        .dir-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; }
        .dir-group h4 { color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 20px; border-bottom: 2px solid var(--bg-site); padding-bottom: 10px; }
        .dir-group ul { list-style: none; padding: 0; margin: 0; }
        .dir-group ul li { margin-bottom: 12px; }
        .dir-group ul li a { text-decoration: none; color: #666; font-size: 0.95rem; transition: 0.2s; }
        .dir-group ul li a:hover { color: var(--primary); padding-left: 5px; }

        /* FAQ */
        .faq-item { background: var(--white); border-radius: 20px; margin-bottom: 15px; border: 1px solid #eee; overflow: hidden; cursor: pointer; }
        .faq-q { padding: 22px 30px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; color: var(--primary); }
        .faq-a { max-height: 0; overflow: hidden; transition: 0.4s; padding: 0 30px; color: #666; }
        .faq-item.active .faq-a { max-height: 250px; padding-bottom: 25px; }

        @media(max-width:900px){
            .page-header-simple { padding: 40px 20px; }
            .contact-wrapper { grid-template-columns: 1fr; padding: 30px; }
            .dir-grid { grid-template-columns: 1fr 1fr; gap: 20px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <header class="page-header-simple">
        <h1>Let's Start a Conversation</h1>
        <p>Find us on social media or reach out directly for bespoke floral arrangements.</p>
    </header>

    <div class="contact-wrapper">
        <div class="info-box">
            <h3 style="color:var(--primary); margin-top:0;">Official Channels</h3>
            <p style="font-size: 0.95rem; color:#666; margin-top:10px;">Reach us by phone, email, or WhatsApp. To place an order, use Add to Cart or Buy Now on any product — every order appears in our order desk.</p>
            
            <div style="margin-top:30px;">
                <p style="font-size: 0.95rem; color:#666;"><strong>Studio:</strong> Shop No 1, Sai Mandir, Lodhi Rd, Gokalpuri, Institutional Area, Lodi Colony, New Delhi, Delhi 110003</p>
                <p style="font-size: 0.95rem; color:#666;"><strong>Phone:</strong> <a href="tel:+918802004527" style="color:inherit; text-decoration:none;">+91 88020 04527</a></p>
                <p style="font-size: 0.95rem; color:#666;"><strong>Support:</strong> <a href="mailto:saiflower03@gmail.com" style="color:inherit; text-decoration:none;">saiflower03@gmail.com</a></p>
            </div>

            <div class="social-grid">
                <a href="https://www.instagram.com/saiflowerofficial?igsh=MWUwM3UwY3Q4bWc5bg%3D%3D" class="social-card"><i class="fab fa-instagram" style="color: #E1306C;"></i><span style="font-size:0.75rem; font-weight:700;">Instagram</span></a>
                <a href="https://www.facebook.com/people/Sai-Flower/pfbid02xh4jFwjL4XzuB7GqE3G5GictcdAZZok3aWQKL74MNmoFmZeUsDkQK9kJ69DJ9h8Yl/?rdid=bFi0U04r0Fk0dfKh&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1FGrZnF9bi%2F%3Fref%3D1" class="social-card"><i class="fab fa-facebook" style="color: #1877F2;"></i><span style="font-size:0.75rem; font-weight:700;">Facebook</span></a>
                <a href="https://wa.me/918802004527" class="social-card"><i class="fab fa-whatsapp" style="color: #25D366;"></i><span style="font-size:0.75rem; font-weight:700;">WhatsApp</span></a>
                <a href="https://x.com/saiflower03" class="social-card"><i class="fab fa-twitter" style="color: #1DA1F2;"></i><span style="font-size:0.75rem; font-weight:700;">Twitter</span></a>
            </div>
        </div>
    </div>

    <div class="directory-section">
        <h3 style="text-align: center; margin-bottom: 40px; color: var(--primary);">Explore our Universe</h3>
        <div class="dir-grid">
            <div class="dir-group">
                <h4>Collections</h4>
                <ul>
                    <li><a href="flowers.php">Shop Flowers</a></li>
                    <li><a href="cakes.php">Shop Cakes</a></li>
                    <li><a href="gifts.php">Shop Gifts</a></li>
                    <li><a href="events.php">Event Portfolios</a></li>
                    <li><a href="gallery.php">Floral Art Gallery</a></li>
                </ul>
            </div>
            <div class="dir-group">
                <h4>Information</h4>
                <ul>
                    <li><a href="about.php">Our Heritage</a></li>
                    <li><a href="blog.php">Bloom Blog</a></li>
                    <li><a href="sitemap.php">Sitemap</a></li>
                </ul>
            </div>
            <div class="dir-group">
                <h4>Customer Care</h4>
                <ul>
                    <li><a href="delivery-policy.php">Delivery Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="grievnce.php">Grievance</a></li>
                    <li><a href="refund-policy.php">Refund Policy</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="faq-section">
        <h2 style="text-align: center; color: var(--primary); margin-bottom: 40px;">Common Questions</h2>
        <div style="max-width: 850px; margin: 0 auto;">
            <?php 
            $faq_res = mysqli_query($conn, "SELECT * FROM faqs WHERE (page = 'contact' OR page = 'general') AND status = 1 LIMIT 4");
            if(mysqli_num_rows($faq_res) > 0):
                while($f = mysqli_fetch_assoc($faq_res)): ?>
                    <div class="faq-item" onclick="this.classList.toggle('active')">
                        <div class="faq-q"><?= htmlspecialchars($f['question']) ?><i class="fas fa-plus" style="font-size: 0.8rem; color: var(--accent);"></i></div>
                        <div class="faq-a"><?= nl2br(htmlspecialchars($f['answer'])) ?></div>
                    </div>
                <?php endwhile; 
            endif; ?>
        </div>
    </div>

</div>

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