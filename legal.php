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
    <?= render_seo('legal.php'); ?>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">

    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Legal', 'item' => 'legal.php']]);
    ?>

<style>
        :root {
            --primary: <?= $pCol ?>;
            --accent: <?= $sCol ?>;
            --bg-site: <?= $bgColor ?>;
            --text-main: <?= $tCol ?>;
            --font-main: <?= $fFam ?>;
            --shadow-md: 0 15px 40px rgba(0,0,0,0.05);
            --white: #ffffff;
        }

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

        /* --- LEGAL GRID --- */
        .legal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        .legal-card {
            background: var(--white);
            padding: 50px 30px;
            border-radius: 35px;
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: var(--shadow-md);
        }

        .legal-card i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 25px;
        }

        .legal-card h3 {
            margin: 0 0 12px 0;
            font-size: 1.7rem;
            color: var(--primary);
            font-weight: 700;
        }

        .legal-card p {
            font-size: 1rem;
            color: #777;
            margin: 0;
            line-height: 1.5;
        }

        .legal-card:hover {
            transform: translateY(-12px);
            border-color: var(--primary);
            background: #fdfdfd;
        }

        /* --- TRUST BANNER --- */
        .trust-banner {
            background: var(--primary);
            color: white;
            padding: 60px 30px;
            border-radius: 40px;
            text-align: center;
            margin-bottom: 80px;
        }

        .trust-banner h2 { color: white; margin-top: 0; font-size: 2.2rem; }
        .trust-banner p { opacity: 0.9; max-width: 800px; margin: 0 auto 30px; }

        @media(max-width:768px){
            .page-header-simple { padding: 40px 20px; }
            .legal-grid { grid-template-columns: 1fr; gap: 20px; }
            .legal-card { padding: 40px 20px; }
            .trust-banner { padding: 40px 20px; border-radius: 30px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <header class="page-header-simple">
        <h1>Legal & Compliance</h1>
        <p>Transparency is the root of our relationship. Find all our policies regarding your orders and data here.</p>
    </header>

    <div class="legal-grid">
        <a href="terms.php" class="legal-card">
            <i class="fas fa-file-invoice"></i>
            <h3>Terms of Service</h3>
            <p>Rules and regulations for using saiflower.com and purchasing our floral arrangements.</p>
        </a>

        <a href="delivery-policy.php" class="legal-card">
            <i class="fas fa-truck-fast"></i>
            <h3>Delivery Policy</h3>
            <p>Details on Delhi NCR delivery slots, midnight shipping, and timing buffers.</p>
        </a>

        <a href="privacy.php" class="legal-card">
            <i class="fas fa-user-shield"></i>
            <h3>Privacy Policy</h3>
            <p>How we handle your personal data and protect your shopping experience.</p>
        </a>

        <a href="refund-policy.php" class="legal-card">
            <i class="fas fa-hand-holding-dollar"></i>
            <h3>Refund Policy</h3>
            <p>Our guidelines for order cancellations, replacements, and quality issues.</p>
        </a>

        <a href="grievnce.php" class="legal-card">
            <i class="fas fa-scale-balanced"></i>
            <h3>Grievance</h3>
            <p>A dedicated channel to report issues or seek resolution for unsatisfactory services.</p>
        </a>
    </div>

    <div class="trust-banner">
        <h2>Shopping with Confidence</h2>
        <p>We are dedicated to making your floral gifting experience perfect. If you have questions about any of our policies, reach out to us directly.</p>
        <div style="display: flex; justify-content: center; gap: 25px; flex-wrap: wrap;">
            <a href="tel:+918802004527" style="color:white; text-decoration:none; font-weight:bold;"><i class="fas fa-phone"></i> +91 88020 04527</a>
            <a href="mailto:saiflower03@gmail.com" style="color:white; text-decoration:none; font-weight:bold;"><i class="fas fa-envelope"></i> saiflower03@gmail.com</a>
        </div>
    </div>
</div>

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