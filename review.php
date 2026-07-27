<?php 
require_once __DIR__ . '/config.php'; 

// 1. THEME INTELLIGENCE: FETCH SETTINGS
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);

$pCol = $settings['theme_primary'] ?? '#2f6f4e';
$sCol = $settings['theme_secondary'] ?? '#d4af37';
$bgColor = $settings['theme_bg_color'] ?? '#fff1f5';
$tCol = $settings['theme_text_color'] ?? '#333333';
$fFam = $settings['theme_font'] ?? "'Playfair Display', serif";

// 2. FETCH DATA (Trim tags for consistency)
$faqsResult = mysqli_query($conn, "SELECT *, TRIM(page) as clean_page FROM faqs WHERE status = 1 ORDER BY page ASC");
$tagQuery = mysqli_query($conn, "SELECT DISTINCT TRIM(page) as clean_page FROM faqs WHERE status = 1");

if (($settings['maintenance_mode'] ?? 0) == 1) { header("Location: maintenance.php"); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <?= render_canonical_link() ?>
    <title>Help Center | Sai Flowers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
     
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <style>
        /* --- THEME SYNC --- */
        :root {
            --primary: <?= $pCol ?>;
            --accent: <?= $sCol ?>;
            --bg-site: <?= $bgColor ?>;
            --text-main: <?= $tCol ?>;
            --font-main: <?= $fFam ?>;
        }

        body { 
            font-family: 'Poppins', sans-serif; background-color: var(--bg-site);
            margin: 0; color: var(--text-main); line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, summary, .tag-btn { font-family: var(--font-main); }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 5px; }
        @media (min-width: 768px) {
            .container { padding: 0 20px; }
        }

        .search-container { position: relative; max-width: 600px; margin: 30px auto; }
        input.search-input { 
            width: 100%; padding: 18px 25px 18px 55px !important; border-radius: 50px; 
            border: 2px solid #eee; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            outline: none; transition: 0.3s; box-sizing: border-box;
        }
        .search-icon { position: absolute; left: 22px; top: 50%; transform: translateY(-50%); color: var(--primary); }

        /* TAGS */
        .tag-filters { text-align: center; margin-bottom: 40px; display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; }
        .tag-btn { 
            padding: 10px 22px; border-radius: 50px; background: white; border: 1.5px solid #eee;
            cursor: pointer; transition: 0.3s; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; color: #777;
        }
        .tag-btn.active { background: var(--primary) !important; color: white !important; border-color: var(--primary) !important; }

        /* ACCORDION - FIXED HIDING */
        .faq-item { background: #fff; border-radius: 20px; margin-bottom: 15px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03); border: 1.5px solid #f0f0f0; }
        
        /* THIS IS THE CRITICAL FIX */
        .faq-item.hidden { display: none !important; } 
        
        summary { padding: 24px 30px; font-size: 1.1rem; font-weight: 700; cursor: pointer; color: var(--primary); display: flex; justify-content: space-between; align-items: center; list-style: none; }
        summary::after { content: '\f067'; font-family: 'Font Awesome 6 Free'; font-weight: 900; color: var(--accent); }
        details[open] summary::after { content: '\f068'; }

        .faq-content { padding: 30px; font-size: 1rem; color: #555; background: #fff; border-radius: 0 0 20px 20px; }
        .badge { font-size: 10px; background: #f0f0f0; color: #888; padding: 4px 10px; border-radius: 50px; margin-right: 12px; }

        #noResults { text-align: center; padding: 50px; display: none; color: #aaa; }
    </style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<div class="container">
    <div class="header-box" style="text-align:center;">
        <h1 style="color:var(--primary); font-size: 3.5rem; margin:0;">How can we help?</h1>
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="faqSearch" class="search-input" placeholder="Search keywords...">
        </div>
    </div>

    <div class="tag-filters">
        <button class="tag-btn active" onclick="applyFilters('all', this)">All</button>
        <?php while($t = mysqli_fetch_assoc($tagQuery)): 
            $tagName = strtolower(trim($t['clean_page']));
        ?>
            <button class="tag-btn" onclick="applyFilters('<?= $tagName ?>', this)">
                <?= ucfirst($tagName) ?>
            </button>
        <?php endwhile; ?>
    </div>

    <div id="faqList">
        <?php while($f = mysqli_fetch_assoc($faqsResult)): ?>
            <details class="faq-item" data-tag="<?= strtolower(trim($f['clean_page'])) ?>">
                <summary>
                    <span>
                        <span class="badge"><?= strtoupper($f['clean_page']) ?></span>
                        <?= htmlspecialchars($f['question']) ?>
                    </span>
                </summary>
                <div class="faq-content"><?= nl2br(htmlspecialchars($f['answer'])) ?></div>
            </details>
        <?php endwhile; ?>
    </div>

    <div id="noResults"><h3>No matching results.</h3></div>
</div>

<script>
    let currentTag = 'all';

    function applyFilters(tag, btn) {
        // 1. Update Active State
        if(btn) {
            document.querySelectorAll('.tag-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        currentTag = tag;
        
        // 2. Run Logic
        const query = document.getElementById('faqSearch').value.toLowerCase();
        const items = document.querySelectorAll('.faq-item');
        let visibleCount = 0;

        items.forEach(item => {
            const itemTag = item.getAttribute('data-tag');
            const itemText = item.innerText.toLowerCase();

            const matchesTag = (currentTag === 'all' || itemTag === currentTag);
            const matchesSearch = itemText.includes(query);

            if (matchesTag && matchesSearch) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
                item.removeAttribute('open'); 
            }
        });

        document.getElementById('noResults').style.display = (visibleCount === 0) ? 'block' : 'none';
    }

    // Bind search event
    document.getElementById('faqSearch').addEventListener('input', () => {
        applyFilters(currentTag, null);
    });

    // Theme Sync
    const bc = new BroadcastChannel('theme_sync');
    bc.onmessage = (e) => {
        const d = e.data;
        document.documentElement.style.setProperty('--primary', d.p);
        document.documentElement.style.setProperty('--accent', d.s);
        document.body.style.fontFamily = d.f;
    };
</script>
</body>
</html>