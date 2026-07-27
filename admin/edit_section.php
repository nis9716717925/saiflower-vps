<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';

if(!isset($_GET['id'])) {
    header("Location: homepage.php");
    exit;
}

$id = (int)$_GET['id'];
$res = mysqli_query($conn, "SELECT * FROM homepage_sections WHERE id=$id");
if(!$row = mysqli_fetch_assoc($res)) {
    header("Location: homepage.php?error=Section not found");
    exit;
}

$pageTitle = "Edit Section";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/partials/head.php'; ?>
    <style>
        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            overflow: hidden;
        }
        .form-header {
            padding: 24px 30px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
        }
        .form-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            color: #4b5563;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #fff;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(50, 110, 84, 0.1);
            outline: none;
        }
        .select-wrapper {
            position: relative;
        }
        .select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            font-size: 0.8rem;
        }
        .form-control.select {
            appearance: none;
            cursor: pointer;
        }
        .help-text {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-header">
            <div class="header-title">
                 <div class="flex items-center gap-3">
                    <a href="homepage.php" class="btn-action" title="Back"><i class="fas fa-arrow-left"></i></a>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Edit Section</h2>
                        <p class="text-gray-500 text-sm mt-1">Updating content block for the homepage</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-3xl px-4 md:px-0 mx-auto mb-8">
            <div class="form-card">
                <form action="actions/update_section.php" method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                    
                    <div class="form-body">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-heading w-5 text-gray-400"></i> Section Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-align-left w-5 text-gray-400"></i> Subtitle (Optional)</label>
                            <input type="text" name="subtitle" value="<?= htmlspecialchars($row['subtitle'] ?? '') ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-layer-group w-5 text-gray-400"></i> Content Type</label>
                            <div class="select-wrapper">
                                <select name="type" class="form-control select">
                                    <option value="carousel" <?= $row['type']=='carousel'?'selected':'' ?>>Product Carousel (Scrollable list of items)</option>
                                    <option value="grid" <?= $row['type']=='grid'?'selected':'' ?>>Polaroid Grid (6 Items Grid)</option>
                                    <option value="banner" <?= $row['type']=='banner'?'selected':'' ?>>Full Width Banner (Single large image)</option>
                                    <option value="split_banner" <?= $row['type']=='split_banner'?'selected':'' ?>>Split Banner (2 Side-by-side images)</option>
                                    <option value="cta_banner" <?= $row['type']=='cta_banner'?'selected':'' ?>>CTA Text Banner (Call to Action text)</option>
                                    <option value="reviews" <?= $row['type']=='reviews'?'selected':'' ?>>Testimonials (Customer Reviews)</option>
                                    <option value="newsletter" <?= $row['type']=='newsletter'?'selected':'' ?>>Promo Strip (Text highlights)</option>
                                    <option value="image_slider" <?= $row['type']=='image_slider'?'selected':'' ?>>Full-Width Image Slider (Like Hero Slider)</option>
                                    <option value="calendar" <?= $row['type']=='calendar'?'selected':'' ?>>Celebrations Calendar (Visual date events)</option>
                                    <option value="grid_square" <?= $row['type']=='grid_square'?'selected':'' ?>>Square Master Grid (Premium Wiggle Effect)</option>
                                    <option value="grid_rect" <?= $row['type']=='grid_rect'?'selected':'' ?>>Rectangular Master Grid (Premium Wiggle Effect)</option>
                                    <option value="grid_circle" <?= $row['type']=='grid_circle'?'selected':'' ?>>Circular Master Grid (Premium Wiggle Effect)</option>
                                    <option value="grid_heart" <?= $row['type']=='grid_heart'?'selected':'' ?>>Heart Master Grid (Premium Wiggle Effect)</option>
                                    <option value="heart_carousel" <?= $row['type']=='heart_carousel'?'selected':'' ?>>Heart Carousel (4 Items swipable)</option>
                                    <option value="circle_carousel" <?= $row['type']=='circle_carousel'?'selected':'' ?>>Circle Carousel (swipable circles)</option>
                                </select>
                            </div>
                            <div class="help-text">Select how this section should look and behave.</div>
                        </div>
                    </div>

                    <div class="form-header flex flex-col md:flex-row justify-between gap-3 bg-gray-50 border-t border-gray-100 items-center">
                        <a href="actions/delete_section.php?id=<?= $id ?>&csrf_token=<?= htmlspecialchars(generate_csrf_token()) ?>" 
                           onclick="return confirm('Delete this section? Warning: All items in this section will also be deleted.')" 
                           class="text-red-500 hover:text-red-700 text-sm font-semibold px-4 w-full md:w-auto text-center md:text-left mb-3 md:mb-0">
                           <i class="fas fa-trash mr-1"></i> Delete Section
                        </a>
                        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                            <a href="homepage.php" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-semibold hover:bg-gray-100 transition text-center">Cancel</a>
                            <button type="submit" class="px-8 py-2.5 rounded-xl bg-primary text-white font-semibold shadow-lg hover:shadow-xl hover:translate-y-[-1px] transition text-center">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>