<?php
$page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* DESKTOP SIDEBAR */
    .admin-sidebar {
        width: 260px;
        background: #fff;
        height: 100vh;
        position: fixed;
        left: 0; top: 0;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #eee;
        z-index: 1000;
        transition: 0.3s;
    }

    .admin-brand { padding: 25px; border-bottom: 1px solid #f9f9f9; }

    .admin-nav {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .admin-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        text-decoration: none;
        color: #555;
        font-weight: 600;
        border-radius: 12px;
        font-size: 0.9rem;
        transition: 0.2s;
    }

    .admin-nav a i { width: 20px; text-align: center; font-size: 1.1rem; }
    .admin-nav a:hover { background: #f4f7f6; color: var(--primary); }
    .admin-nav a.active { background: var(--primary); color: #fff; box-shadow: 0 4px 12px rgba(50, 110, 84, 0.2); }

    .nav-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: #bbb;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 15px 0 5px 15px;
    }

    /* MOBILE OPTIMIZATIONS (Max-width 992px) */
    @media (max-width: 992px) {
        .admin-sidebar {
            width: 100%;
            height: 70px;
            top: auto;
            bottom: 0;
            flex-direction: row;
            border-right: none;
            border-top: 1px solid #eee;
            box-shadow: 0 -5px 25px rgba(0,0,0,0.08);
            padding-bottom: env(safe-area-inset-bottom);
        }

        .admin-brand, .nav-label, .logout-link { display: none !important; }

        .admin-nav {
            flex-direction: row;
            justify-content: space-around;
            align-items: center;
            width: 100%;
            padding: 0 5px;
            overflow: visible;
        }

        .admin-nav a {
            padding: 8px 0;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            border-radius: 0;
            font-size: 0.65rem;
            color: #888;
        }

        .admin-nav a i { font-size: 1.3rem; margin: 0; }
        .admin-nav a.active { background: transparent; color: var(--primary); box-shadow: none; }
        
        .admin-nav a.active::after {
            content: '';
            width: 4px; height: 4px;
            background: var(--primary);
            border-radius: 50%;
            margin-top: 2px;
        }

        .mobile-hide { display: none !important; }
        #mobileMoreBtn { display: flex !important; }
    }

    /* MOBILE SLIDE-UP MENU */
    #mobileMenuOverlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        display: none;
        backdrop-filter: blur(3px);
    }

    #mobileMoreMenu {
        position: fixed;
        bottom: -100%;
        left: 0;
        width: 100%;
        background: #fff;
        z-index: 2001;
        border-radius: 25px 25px 0 0;
        padding: 20px;
        transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 85vh;
        overflow-y: auto;
    }

    #mobileMoreMenu.active { bottom: 0; }
    
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-top: 20px;
        padding-bottom: 30px;
    }

    .menu-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        color: #444;
        padding: 15px 5px;
        border-radius: 15px;
        background: #f8f9fa;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
    }

    .menu-item i { font-size: 1.2rem; color: var(--primary); }

    /* SEARCH SUGGESTIONS */
    .suggestion-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        text-decoration: none;
        color: #333;
        border-bottom: 1px solid #eee;
        transition: 0.2s;
        font-size: 0.8rem;
    }
    .suggestion-item:last-child { border-bottom: none; }
    .suggestion-item:hover { background: #f4f7f6; }
    .suggestion-img {
        width: 35px; height: 35px; border-radius: 8px; object-fit: cover; background: #eee;
    }
    .suggestion-details { display: flex; flex-direction: column; }
    .suggestion-title { font-weight: 600; }
    .suggestion-type { font-size: 0.65rem; color: #888; text-transform: uppercase; }
</style>

<nav class="admin-sidebar">
    <div class="admin-brand">
        <div style="display:flex; align-items:center; justify-content:center; padding-bottom:10px;">
            <?php 
            if(!isset($settings) && isset($conn)) {
                $sQ = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
                $settings = mysqli_fetch_assoc($sQ);
            }
            // Admin path adjustment: we are in admin/, so uploads is ../uploads/
            $logoPath = 'uploads/logo_transparent.png';
            if(strpos($logoPath, 'uploads/') !== 0 && !empty($logoPath)) $logoPath = 'uploads/' . $logoPath;
            $adminLogoPath = '../' . $logoPath;
            ?>
            
            <?php if (!empty($logoPath) && file_exists(__DIR__ . '/../../' . $logoPath)): ?>
                <img src="/<?= htmlspecialchars($logoPath) ?>" alt="Admin" style="max-height: 70px; width: auto;">
            <?php else: ?>
                <div style="display:flex; align-items:center; gap:10px; color:var(--primary); font-weight:800; font-size:1.2rem;">
                    <i class="fas fa-seedling"></i>
                    <span>Sai Admin</span>
                </div>
            <?php endif; ?>
        </div>
        <div style="font-size:0.65rem; color:#bbb; margin-top:5px; margin-left:32px; letter-spacing:1.5px; font-weight:700;">
            POWERED BY <span style="color:var(--accent);">HEXA</span>
        </div>
    </div>
    
    <div class="mobile-hide" style="padding: 15px 15px 0 15px; position: relative;">
        <form action="search.php" method="GET" style="display: flex; background: #f4f7f6; border-radius: 12px; padding: 5px 10px; align-items: center;">
            <i class="fas fa-search" style="color: #888; font-size: 0.9rem;"></i>
            <input type="text" name="q" class="admin-search-input" placeholder="Search..." style="border: none; background: transparent; padding: 8px; width: 100%; outline: none; font-size: 0.85rem; color: #333;" autocomplete="off" required>
        </form>
        <div class="search-suggestions-container" style="display:none; position: absolute; top: 100%; left: 15px; right: 15px; background: #fff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 9999; margin-top: 5px; max-height: 300px; overflow-y: auto;"></div>
    </div>

    <div class="admin-nav">
        <a href="dashboard.php" class="<?= $page=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-th-large"></i> <span>Dashboard</span>
        </a>
        <a href="orders.php" class="<?= $page=='orders.php'?'active':'' ?>">
            <i class="fas fa-shopping-bag"></i> <span>Orders</span>
        </a>

        <div class="nav-label">Shop</div>
        <a href="flowers.php" class="<?= $page=='flowers.php'?'active':'' ?>">
            <i class="fas fa-leaf"></i> <span>Flowers</span>
        </a>
        <a href="cakes.php" class="<?= $page=='cakes.php'?'active':'' ?>">
            <i class="fas fa-birthday-cake"></i> <span>Cakes</span>
        </a>
        <a href="gifts.php" class="<?= $page=='gifts.php'?'active':'' ?>">
            <i class="fas fa-gift"></i> <span>Gifts</span>
        </a>
       
        <a href="tool-price-update.php" class="<?= $page=='tool-price-update.php'?'active':'' ?>">
            <i class="fas fa-percentage"></i> <span>Price Updater</span>
        </a>
        <a href="addons.php" class="<?= $page=='addons.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-gift"></i> <span>Add-ons</span>
        </a>
        
        <a href="promo-codes.php" class="<?= $page=='promo-codes.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-ticket-alt"></i> <span>Promotions</span>
        </a>

        <div class="nav-label">Content</div>
        <a href="pages.php" class="<?= $page=='pages.php'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> <span>Custom Pages</span>
        </a>
        <a href="events.php" class="<?= $page=='events.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-calendar-alt"></i> <span>Events</span>
        </a>

        <a href="gallery.php" class="<?= $page=='gallery.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-images"></i> <span>Gallery</span>
        </a>
       <!-- <a href="reviews.php" class="<?= $page=='reviews.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-star"></i> <span>Reviews</span>-->
        </a>
        <a href="blog.php" class="<?= $page=='blog.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-pen-nib"></i> <span>Blog</span>
        </a>
        <a href="tags.php" class="<?= $page=='tags.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-tags"></i> <span>Tags</span>
        </a>

        <div class="nav-label">Settings</div>
        <a href="seo.php" class="<?= $page=='seo.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-search"></i> <span>SEO</span>
        </a>
        <a href="faq.php" class="<?= $page=='faq.php'?'active':'' ?> mobile-hide">
            <i class="fas fa-question-circle"></i> <span>FAQs</span>
        </a>
       

        <a href="javascript:void(0)" id="mobileMoreBtn" style="display:none;" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i> <span>More</span>
        </a>

        <a href="logout.php" class="logout-link" style="margin-top:auto; color:#dc3545; border-top: 1px solid #f9f9f9; border-radius:0;">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</nav>

<div id="mobileMenuOverlay" onclick="toggleMobileMenu()"></div>
<div id="mobileMoreMenu">
    <div style="width:40px; height:5px; background:#ddd; border-radius:10px; margin:0 auto 20px;"></div>
    <div style="margin-bottom: 20px; position: relative;">
        <form action="search.php" method="GET" style="display: flex; background: #f4f7f6; border-radius: 12px; padding: 5px 10px; align-items: center;">
            <i class="fas fa-search" style="color: #888; font-size: 0.9rem;"></i>
            <input type="text" name="q" class="admin-search-input" placeholder="Search..." style="border: none; background: transparent; padding: 10px; width: 100%; outline: none; font-size: 0.9rem; color: #333;" autocomplete="off" required>
        </form>
        <div class="search-suggestions-container" style="display:none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ddd; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); z-index: 9999; margin-top: 5px; max-height: 250px; overflow-y: auto;"></div>
    </div>
    <h3 style="margin:0; font-size:1.1rem;">Explore Hub</h3>
    <div class="menu-grid">
        <a href="pages.php" class="menu-item"><i class="fas fa-file-alt"></i>Custom Pages</a>
        <a href="orders.php" class="menu-item"><i class="fas fa-shopping-bag"></i>Orders</a>
        <a href="addons.php" class="menu-item"><i class="fas fa-gift"></i>Add-ons</a>
        <a href="events.php" class="menu-item"><i class="fas fa-calendar-alt"></i>Events</a>
        <a href="cakes.php" class="menu-item"><i class="fas fa-birthday-cake"></i>Cakes</a>
        <a href="gifts.php" class="menu-item"><i class="fas fa-gift"></i>Gifts</a>
        <a href="gallery.php" class="menu-item"><i class="fas fa-images"></i>Gallery</a>
        <a href="promo-codes.php" class="menu-item"><i class="fas fa-ticket-alt"></i>Promotions</a>
        <a href="blog.php" class="menu-item"><i class="fas fa-pen-nib"></i>Blog</a>
        <a href="tags.php" class="menu-item"><i class="fas fa-tags"></i>Tags</a>
        <a href="seo.php" class="menu-item"><i class="fas fa-search"></i>SEO</a>
        <a href="faq.php" class="menu-item"><i class="fas fa-question-circle"></i>FAQs</a>
        <a href="logout.php" class="menu-item" style="color:#dc3545;"><i class="fas fa-sign-out-alt" style="color:#dc3545;"></i>Logout</a>
    </div>
</div>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMoreMenu');
    const overlay = document.getElementById('mobileMenuOverlay');
    if (menu.classList.contains('active')) {
        menu.classList.remove('active');
        setTimeout(() => overlay.style.display = 'none', 400);
    } else {
        overlay.style.display = 'block';
        setTimeout(() => menu.classList.add('active'), 10);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInputs = document.querySelectorAll('.admin-search-input');
    
    searchInputs.forEach(input => {
        let debounceTimer;
        // The container is the sibling element of the form
        const container = input.closest('div').querySelector('.search-suggestions-container');
        
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            
            if (query.length < 2) {
                container.style.display = 'none';
                return;
            }
            
            debounceTimer = setTimeout(() => {
                fetch(`ajax_admin_search.php?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        container.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const a = document.createElement('a');
                                a.href = item.link;
                                a.className = 'suggestion-item';
                                a.innerHTML = `
                                    <img src="${item.image}" class="suggestion-img" onerror="this.onerror=null; this.src='https://placehold.co/100x100/f8faf9/326e54?text=${item.type.charAt(0).toUpperCase() + item.type.slice(1)}'">
                                    <div class="suggestion-details">
                                        <span class="suggestion-title">${item.name}</span>
                                        <span class="suggestion-type">${item.type}</span>
                                    </div>
                                `;
                                container.appendChild(a);
                            });
                            
                            // Keep 'View all results' link
                            const viewAll = document.createElement('a');
                            viewAll.href = `search.php?q=${encodeURIComponent(query)}`;
                            viewAll.className = 'suggestion-item';
                            viewAll.style.justifyContent = 'center';
                            viewAll.style.color = 'var(--primary)';
                            viewAll.style.fontWeight = 'bold';
                            viewAll.innerHTML = `View all results <i class="fas fa-arrow-right" style="margin-left: 5px;"></i>`;
                            container.appendChild(viewAll);
                            
                            container.style.display = 'block';
                        } else {
                            container.innerHTML = '<div style="padding: 15px; text-align: center; color: #888; font-size: 0.8rem;">No suggestions found</div>';
                            container.style.display = 'block';
                        }
                    })
                    .catch(err => console.error(err));
            }, 300);
        });
        
        // Hide when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !container.contains(e.target)) {
                container.style.display = 'none';
            }
        });
    });
});
</script>