<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// 1. THEME INTELLIGENCE: FETCH SETTINGS
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);

$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#fafafa';
$tCol = $settings['theme_text_color'] ?? '#333333';
$fFam = $settings['theme_font'] ?? "'Playfair Display', serif";

// 2. GET TAG & FETCH IMAGES
$tag = trim($_GET['tag'] ?? '');
if ($tag === '') {
    header("Location: gallery.php");
    exit;
}

$tagSafe = mysqli_real_escape_string($conn, $tag);
$sql = "SELECT * FROM gallery WHERE tag='$tagSafe' AND status=1 ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if(!$result){
    die('DB Error: ' . mysqli_error($conn));
}

// 3. MAINTENANCE CHECK
if (($settings['maintenance_mode'] ?? 0) == 1) {
    header("Location: maintenance.php"); exit;
}

set_page_canonical_url(seo_site_base_url() . '/gallery-by-tag?tag=' . rawurlencode($tag));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?= render_canonical_link() ?>
    <title><?= htmlspecialchars($tag); ?> Gallery | Sai Flowers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    /* --- 1. THEME INTELLIGENCE SYNC --- */
    :root {
        --primary: <?= $pCol ?>;
        --accent: <?= $sCol ?>;
        --bg-site: <?= $bgColor ?>;
        --text-main: <?= $tCol ?>;
        --font-main: <?= $fFam ?>;
    }

    body { 
        font-family: 'Poppins', sans-serif; 
        background-color: var(--bg-site); 
        margin: 0; padding-top: 100px; 
        color: var(--text-main); 
        transition: background 0.3s ease;
    }

    h1, h2, h3 { font-family: var(--font-main); }

    .container { max-width: 1200px; margin: auto; padding: 0 20px; }

    /* HEADER */
    .gallery-header { text-align: center; margin-bottom: 60px; }
    .gallery-header h1 { font-size: clamp(2.5rem, 5vw, 4rem); color: var(--primary); margin: 0; }
    .tag-breadcrumb { font-weight: bold; color: var(--accent); text-transform: uppercase; letter-spacing: 2px; font-size: 0.8rem; }

    /* GRID LAYOUT */
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        padding-bottom: 60px;
    }

    .card {
        background: #fff;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(0,0,0,0.05);
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid #f0f0f0;
    }
    .card:hover { transform: translateY(-10px); border-color: var(--accent); }

    .card img {
        width: 100%;
        height: 350px;
        object-fit: cover;
        display: block;
        transition: 0.5s;
    }
    .card:hover img { transform: scale(1.05); }

    .card-body { padding: 20px; text-align: center; }
    .card h3 { margin: 0; font-size: 1.2rem; color: var(--primary); }

    /* --- FAQ SECTION --- */
    .faq-section { margin-top: 60px; padding: 60px 0; border-top: 1px solid #eee; }
    .faq-item { background: white; border-radius: 15px; margin: 0 auto 12px; max-width: 800px; border: 1px solid #eee; overflow: hidden; cursor: pointer; }
    .faq-q { padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; color: var(--primary); }
    .faq-a { max-height: 0; overflow: hidden; transition: 0.4s ease; padding: 0 25px; color: #666; }
    .faq-item.active .faq-a { max-height: 200px; padding-bottom: 20px; }

    /* --- FLOATING ICONS --- */
    .floating-icons { position: fixed; bottom: 30px; right: 30px; display: flex; flex-direction: column; gap: 15px; z-index: 9999; }
    .float-btn { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); transition: 0.3s; animation: jump 2s infinite; }
    .float-btn:hover { transform: translateY(-5px); }

    .float-btn.whatsapp { background: #25D366; }
    .float-btn.whatsapp i { color: #fff; font-size: 30px; }

    .float-btn.call { background: var(--primary); color: #fff; font-size: 24px; animation-delay: 1s; }
    @keyframes jump { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

    @media(max-width:768px){ 
        .grid { grid-template-columns: 1fr; }
        .gallery-header h1 { font-size: 2.2rem; }
    }
</style>
</head>

<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <div class="gallery-header">
        <span class="tag-breadcrumb">Portfolio Collection</span>
        <h1><?= htmlspecialchars($tag); ?> Gallery</h1>
    </div>

    <div class="grid">
        <?php if(mysqli_num_rows($result)): ?>
            <?php while($g=mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <img src="/uploads/<?= htmlspecialchars($g['image']); ?>" 
                         onerror="this.src='https://images.unsplash.com/photo-1519225495806-7d5225a0d16a?q=80&w=800'"
                         alt="<?= htmlspecialchars($g['title']); ?>">
                    <div class="card-body">
                        <h3><?= htmlspecialchars($g['title']); ?></h3>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; grid-column: 1 / -1; padding: 100px 0;">
                <i class="fas fa-camera-retro" style="font-size: 3rem; color: #ddd; margin-bottom: 20px;"></i>
                <p>No masterpieces found in this category yet.</p>
                <a href="gallery.php" style="color: var(--primary); font-weight: bold;">← Back to Main Gallery</a>
            </div>
        <?php endif; ?>
    </div>

    <section class="faq-section">
        <h2 style="text-align: center; color: var(--primary); margin-bottom: 40px;">Gallery & Service FAQ</h2>
        <div class="container">
            <?php 
            $faq_res = mysqli_query($conn, "SELECT * FROM faqs WHERE (page = 'gallery' OR page = 'general') AND status = 1 LIMIT 5");
            if(mysqli_num_rows($faq_res) > 0):
                while($f = mysqli_fetch_assoc($faq_res)): ?>
                <div class="faq-item" onclick="this.classList.toggle('active')">
                    <div class="faq-q">
                        <?= htmlspecialchars($f['question']) ?>
                        <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: var(--accent);"></i>
                    </div>
                    <div class="faq-a"><?= nl2br(htmlspecialchars($f['answer'])) ?></div>
                </div>
            <?php endwhile; endif; ?>
        </div>
    </section>
</div>

<div class="floating-icons">
    <a href="https://wa.me/918802004527" class="float-btn whatsapp" target="_blank"><i class="fab fa-whatsapp"></i></a>
    <a href="tel:+918802004527" class="float-btn call"><i class="fas fa-phone-alt"></i></a>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
    // --- MASTER THEME SYNC ---
    const themeChannel = new BroadcastChannel('theme_sync');
    themeChannel.onmessage = (e) => {
        const d = e.data;
        const root = document.documentElement;
        root.style.setProperty('--primary', d.p);
        root.style.setProperty('--accent', d.s);
        root.style.setProperty('--bg-site', d.bg);
        root.style.setProperty('--text-main', d.t);
        document.body.style.fontFamily = d.f;
        document.querySelectorAll('h1, h2, h3').forEach(h => h.style.fontFamily = d.f);
    };
</script>

</body>
</html>