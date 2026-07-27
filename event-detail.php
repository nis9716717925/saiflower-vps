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

// 2. GET EVENT DETAILS (by slug or ID)
$event = null;
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    $stmt = $conn->prepare("SELECT * FROM events WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
} elseif (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
}

if (!$event) {
    header("Location: events.php");
    exit;
}

enforce_canonical_product_url('event', $event);
set_page_canonical_url(get_product_canonical_url('event', $event));
$id = $event['id'];
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> | Sai Flowers Events</title>
    <?= render_canonical_link() ?>
    <?= render_seo_theme_extras() ?>

    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
    <?php 
    require_once __DIR__ . '/includes/schema_helper.php';
    $schemaEvent = $event;
    if(isset($event['cover_image']) && empty($schemaEvent['image'])) {
        $schemaEvent['image'] = $event['cover_image'];
    }
    echo generate_product_json_ld($schemaEvent, 'event');
    echo generate_simple_breadcrumb_json_ld([
        ['name' => 'Events', 'item' => 'events.php'],
        ['name' => $event['title'], 'item' => product_url(['type' => 'event', 'slug' => $event['slug'] ?? '', 'id' => $event['id']])]
    ]);
    ?>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" href="/favicon.png" type="image/x-icon">

    <style>
        .serif { font-family: 'Playfair Display', serif; }
        .sans { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 font-sans antialiased">

<?php include __DIR__ . '/partials/navbar.php'; ?>

<!-- HERO SECTION -->
<div class="relative h-[70vh] w-full overflow-hidden">
    <?php $imgSrc = !empty($event['cover_image']) ? $event['cover_image'] : ($event['image'] ?? ''); ?>
    <img src="<?= get_image_url($imgSrc, '') ?>" 
         onerror="this.src='https://images.unsplash.com/photo-1519225495806-7d5225a0d16a?q=80&w=1600'" 
         alt="<?= htmlspecialchars($event['title']) ?>"
         class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
    
    <div class="absolute bottom-0 left-0 w-full p-6 md:p-12 lg:p-20">
        <div class="container mx-auto">
            <span class="inline-block px-4 py-1.5 mb-4 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-white text-xs font-bold uppercase tracking-widest">Premium Event Decor</span>
            <h1 class="serif text-2xl md:text-6xl lg:text-7xl font-bold text-white mb-4 drop-shadow-xl"><?= htmlspecialchars($event['title']) ?></h1>
            <div class="flex items-center gap-6 text-white/80 text-sm md:text-base">
                <span class="flex items-center gap-2"><i class="fas fa-tag"></i> Full Venue Styling</span>
                <span class="flex items-center gap-2"><i class="fas fa-calendar-check"></i> Custom Date</span>
            </div>
        </div>
    </div>
</div>

<div class="container mx-auto px-6 -mt-20 relative z-10 pb-20">
    <div class="grid lg:grid-cols-12 gap-10">
        
        <!-- MAIN CONTENT (Left) -->
        <div class="lg:col-span-8 space-y-10">
            <div class="bg-white rounded-[2rem] p-8 md:p-12 shadow-xl border border-slate-100">
                <div class="grid md:grid-cols-2 gap-10 mb-10 border-b border-slate-100 pb-10">
                    <div>
                        <h3 class="serif text-3xl font-bold text-slate-800 mb-4">The Vision</h3>
                        <div class="text-slate-600 leading-relaxed text-lg">
                            <?php if(!empty($event['description'])): ?>
                                <?= nl2br(htmlspecialchars($event['description'])) ?>
                            <?php else: ?>
                                <p class="mb-4">Our client envisioned a breathtaking atmosphere that combined traditional values with modern aesthetics. The goal was to transform a standard venue into a magical garden of eden.</p>
                                <p>Using over 5,000 fresh blooms and custom lighting structures, we created an immersive experience that guests would remember forever.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h3 class="serif text-3xl font-bold text-slate-800 mb-4">Our Approach</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 mt-1 flex-shrink-0"><i class="fas fa-palette text-xs"></i></div>
                                <div><strong class="block text-slate-800">Custom Palette</strong><span class="text-slate-500 text-sm">Curated pastels to match the theme.</span></div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 mt-1 flex-shrink-0"><i class="fas fa-archway text-xs"></i></div>
                                <div><strong class="block text-slate-800">Structural Art</strong><span class="text-slate-500 text-sm">12ft floral arch as center stage.</span></div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 mt-1 flex-shrink-0"><i class="fas fa-lightbulb text-xs"></i></div>
                                <div><strong class="block text-slate-800">Ambient Lighting</strong><span class="text-slate-500 text-sm">Warm tones to enhance textures.</span></div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- GALLERY GRID -->
                <h3 class="serif text-2xl font-bold text-slate-800 mb-6">Visual Highlights</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Main Image REUSE -->
                    <div class="group relative rounded-2xl overflow-hidden aspect-square cursor-pointer">
                        <img src="<?= get_image_url($imgSrc, '') ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110" alt="<?= htmlspecialchars($event['title']) ?>" onclick="window.open(this.src)">
                    </div>
                    
                    <?php
                    // Fetch random gallery images
                    $gq = mysqli_query($conn, "SELECT image FROM gallery ORDER BY RAND() LIMIT 5");
                    while($g = mysqli_fetch_assoc($gq)):
                    ?>
                    <div class="group relative rounded-2xl overflow-hidden aspect-square cursor-pointer">
                        <img src="<?= get_image_url($g['image'], '') ?>" 
                             class="w-full h-full object-cover transition duration-700 group-hover:scale-110" 
                             alt="<?= htmlspecialchars($event['title']) ?> gallery"
                             onclick="window.open(this.src)"
                             onerror="this.style.display='none'">
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- FAQ SECTION -->
            <?php if(!empty($event['faqs'])): 
                $event_faqs = json_decode($event['faqs'], true);
                if(is_array($event_faqs) && count($event_faqs) > 0):
            ?>
            <div class="bg-white rounded-[2rem] p-8 md:p-12 shadow-lg border border-slate-100">
                <h3 class="serif text-2xl font-bold text-slate-800 mb-6 flex items-center justify-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-sm"><i class="fas fa-question"></i></span>
                    Frequently Asked Questions
                </h3>
                <div class="space-y-4">
                    <?php foreach($event_faqs as $f): ?>
                        <div class="group border border-slate-100 rounded-2xl overflow-hidden hover:border-primary/20 transition-colors">
                            <button class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-700 hover:text-primary bg-slate-50/50" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('i').classList.toggle('rotate-180')">
                                <span><?= htmlspecialchars($f['question']) ?></span>
                                <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300 transform"></i>
                            </button>
                            <div class="hidden p-5 pt-0 text-slate-500 text-sm leading-relaxed bg-slate-50/50">
                                <div class="border-t border-slate-100 pt-4 mt-2">
                                    <?= nl2br(htmlspecialchars($f['answer'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; endif; ?>
        </div>

        <!-- SIDEBAR (Right) -->
        <div class="lg:col-span-4 space-y-8">
            <div class="bg-white rounded-[2rem] p-8 shadow-xl border border-slate-100 sticky top-24">
                <div class="text-center mb-6">
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Interested in this style?</span>
                    <h3 class="serif text-3xl font-bold text-slate-900 mt-2">Book This Look</h3>
                </div>

                <div class="space-y-4 mb-8">
                    <div class="flex justify-between p-4 bg-slate-50 rounded-xl">
                        <span class="text-slate-500 text-sm">Service</span>
                        <span class="font-bold text-slate-800 text-sm">Full Decor</span>
                    </div>
                    <div class="flex justify-between p-4 bg-slate-50 rounded-xl">
                        <span class="text-slate-500 text-sm">Typical Setup</span>
                        <span class="font-bold text-slate-800 text-sm">2-3 Days</span>
                    </div>
                    <div class="flex justify-between p-4 bg-slate-50 rounded-xl">
                        <span class="text-slate-500 text-sm">Consultation</span>
                        <span class="font-bold text-green-600 text-sm">FREE</span>
                    </div>
                </div>

                <a href="https://wa.me/918802004527?text=I'm interested in the event style: <?= urlencode($event['title']) ?>" 
                   target="_blank" 
                   class="block w-full bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold py-4 rounded-xl text-center transition-all shadow-lg hover:shadow-green-500/30 flex items-center justify-center gap-2">
                    <i class="fab fa-whatsapp text-xl"></i> Chat on WhatsApp
                </a>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>