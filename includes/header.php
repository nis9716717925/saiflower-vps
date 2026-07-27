<?php
// header.php

// 1. Load Config & Database
require_once dirname(__DIR__) . '/config.php'; 

// 2. Fetch Settings
$settingsQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$settings = mysqli_fetch_assoc($settingsQuery);
if (!$settings) { $settings = []; }

// 3. Set Season & Current Page
$season = $settings['seasonal_theme'] ?? 'Default';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($settings['site_title'] ?? 'Sai Flowers'); ?></title>
    
    <meta name="description" content="<?php echo htmlspecialchars($settings['hero_subtitle'] ?? 'Premium Flower Delivery & Event Decoration in New Delhi. Fresh bouquets, wedding decor, and more.'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-site-verification" content="oEBcHRMrRNo96XZCuu6ZOEkuHhKUlNSCmU8fVkC8h-0" />
    
    <?php echo $settings['header_scripts'] ?? ''; ?>

    <style>
        /* =========================================
           1. CORE STYLES (From your Navbar.php)
           ========================================= */
        body { margin: 0; font-family:Arial, sans-serif; background:#fff; color:#333; }
        
        .navbar {
            position:absolute; top:0; left:0; width:100%; z-index:1000;
            background:transparent; transition: all 0.4s ease;
        }

        .navbar.scrolled {
            position:fixed; background:rgba(255, 255, 255, 0.98); 
            backdrop-filter:blur(14px); box-shadow:0 10px 30px rgba(0,0,0,0.06);
        }

        .nav-container {
            max-width:1250px; margin:auto; padding:12px 20px;
            display:flex; justify-content:space-between; align-items:center;
        }

        .logo { 
            font-size:24px; font-weight:bold; color:#2f6f4e; flex-shrink: 0;
            display: flex; align-items: center; gap: 10px;
        }
        .logo a { text-decoration:none; color:inherit; display: flex; align-items: center; }
        .logo img { height: 40px; width: auto; } /* Dynamic Logo Size */

        /* SEARCH BAR */
        .nav-search { 
            margin: 0 15px; flex-grow: 1; max-width: 300px; position: relative; 
        }
        .nav-search form {
            display: flex; align-items: center; background: rgba(255, 255, 255, 0.9);
            border-radius: 30px; padding: 2px 15px; border: 1px solid rgba(47, 111, 78, 0.3);
        }
        .nav-search form::before { content: "🔍"; font-size: 14px; margin-right: 8px; opacity: 0.6; }
        .nav-search input { border: none; background: transparent; padding: 10px 0; outline: none; width: 100%; font-size: 14px; color: #333; }

        #searchSuggestions {
            position: absolute; top: 110%; left: 0; width: 100%;
            background: #fff; border-radius: 12px; overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1); display: none; z-index: 9999;
        }
        .suggestion-item { padding: 12px 20px; cursor: pointer; border-bottom: 1px solid #f5f5f5; font-size: 14px; color: #333; }

        /* NAV LINKS */
        .nav-links { list-style:none; display:flex; gap:25px; margin:0; padding:0; }
        .nav-links li a { text-decoration:none; color:#333; font-weight:500; font-size: 15px; }
        .nav-links a.active { color:#2f6f4e; font-weight: 600; }
        .nav-links a:hover { color:#2f6f4e; }

        /* MOBILE MENU */
        .menu-toggle { display:none; font-size:26px; cursor:pointer; color: #2f6f4e; }

        @media (max-width: 768px) {
            .menu-toggle { display:block; }
            .nav-search { max-width: 150px; } 

            .nav-links {
                display:none; flex-direction:column; 
                background: #ffffff; 
                position:absolute; top:60px; right:20px; width:200px;
                border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.15);
                padding:15px;
            }

            .nav-links.show { display:flex; }
            
            .nav-links li { border-bottom: 1px solid #f0f0f0; width: 100%; }
            .nav-links li a { 
                padding: 12px 0; 
                color: #1a1a1a !important; 
                font-weight: 700 !important; 
                font-size: 16px; 
                text-align: center;
                display: block;
            }
        }

        /* =========================================
           2. SEASONAL THEME ENGINE
           ========================================= */
        
        /* VALENTINE THEME (Red/Pink) */
        body.season-Valentine { background: linear-gradient(180deg, #fff0f5 0%, #ffe4e1 100%); }
        .season-Valentine .logo { color: #D32F2F; }
        .season-Valentine .nav-links a.active, .season-Valentine .nav-links a:hover { color: #D32F2F; }
        .season-Valentine .menu-toggle { color: #D32F2F; }
        .season-Valentine .nav-search form { border-color: rgba(211, 47, 47, 0.3); }

        /* DIWALI THEME (Gold/Orange) */
        body.season-Diwali { background: linear-gradient(180deg, #fff8e1 0%, #ffecb3 100%); }
        .season-Diwali .logo { color: #FF6F00; }
        .season-Diwali .nav-links a.active, .season-Diwali .nav-links a:hover { color: #FF6F00; }
        .season-Diwali .menu-toggle { color: #FF6F00; }
        .season-Diwali .nav-search form { border-color: rgba(255, 111, 0, 0.3); }
    </style>
</head>

<body class="season-<?= htmlspecialchars($season) ?>">

<nav class="navbar" id="mainNavbar">
    <div class="nav-container">
        
        <div class="logo">
            <a href="/index.php">
                <?php
                $logoPath = 'uploads/logo_transparent.png';
                if (strpos($logoPath, 'uploads/') !== 0 && !empty($logoPath)) {
                    $logoPath = 'uploads/' . $logoPath;
                }
                ?>
                <?php if (!empty($logoPath) && file_exists(__DIR__ . '/../' . $logoPath)): ?>
                    <img src="/<?= htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($settings['site_title'] ?? 'Sai Flowers') ?> logo" width="220" height="64" loading="eager" class="h-10 w-auto object-contain">
                <?php else: ?>
                    <?php echo htmlspecialchars($settings['site_title'] ?? 'Sai Flowers'); ?>
                <?php endif; ?>
            </a>
        </div>

        <div class="nav-search">
            <form action="/search-results.php" method="GET" autocomplete="off">
                <label class="sr-only" for="searchInput">Search site</label>
                <input type="text" name="q" id="searchInput" placeholder="Search..." required aria-label="Search site">
            </form>
            <div id="searchSuggestions"></div>
        </div>

        <div class="menu-toggle" onclick="toggleMenu()">☰</div>

        <ul class="nav-links" id="navLinks">
            <li><a href="/index.php" class="<?= $currentPage == 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="/gallery.php" class="<?= $currentPage == 'gallery.php' ? 'active' : '' ?>">Gallery</a></li>
            <li><a href="/events.php" class="<?= $currentPage == 'events.php' ? 'active' : '' ?>">Events</a></li>
            <li><a href="/flowers.php" class="<?= $currentPage == 'flowers.php' ? 'active' : '' ?>">Flowers</a></li>
            <li><a href="/about.php" class="<?= $currentPage == 'about.php' ? 'active' : '' ?>">About</a></li>
            <li><a href="/blog.php" class="<?= $currentPage == 'blog.php' ? 'active' : '' ?>">Blog</a></li>
            <li><a href="/sitemap.php" class="<?= $currentPage == 'sitemap.php' ? 'active' : '' ?>">Sitemap</a></li>
        </ul>
    </div>
</nav>

<script>
// MENU TOGGLE
function toggleMenu() {
    document.getElementById('navLinks').classList.toggle('show');
}

// SCROLL EFFECT
window.onscroll = function() {
    var nav = document.getElementById('mainNavbar');
    if (window.pageYOffset > 50) nav.classList.add("scrolled");
    else nav.classList.remove("scrolled");
};

// SEARCH LOGIC
const searchInput = document.getElementById('searchInput');
const suggestionsBox = document.getElementById('searchSuggestions');

if(searchInput) {
    searchInput.addEventListener('input', function() {
        const val = this.value.trim();
        if (val.length < 2) { suggestionsBox.style.display = 'none'; return; }
        
        fetch(`/search-results.php?ajax=1&q=${encodeURIComponent(val)}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    suggestionsBox.innerHTML = data.map(item => 
                        `<div class="suggestion-item" onclick="selectSuggestion('${item.replace(/'/g, "\\'")}')">${item}</div>`
                    ).join('');
                    suggestionsBox.style.display = 'block';
                } else { suggestionsBox.style.display = 'none'; }
            });
    });
}

function selectSuggestion(val) {
    searchInput.value = val;
    suggestionsBox.style.display = 'none';
    searchInput.form.submit();
}
</script>