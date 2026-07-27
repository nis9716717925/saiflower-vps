<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth_check.php';

$pageTitle = "Add New Section";
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
                        <h2 class="text-2xl font-bold text-gray-800">Add Section</h2>
                        <p class="text-gray-500 text-sm mt-1">Create a new content block for the homepage</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-3xl px-4 md:px-0 mx-auto mb-8">
            <div class="form-card">
                <form action="actions/add_section.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                    
                    <div class="form-body">
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-heading w-5 text-gray-400"></i> Section Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Featured Collection" autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-align-left w-5 text-gray-400"></i> Subtitle (Optional)</label>
                            <input type="text" name="subtitle" class="form-control" placeholder="e.g. Curated just for you">
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fas fa-layer-group w-5 text-gray-400"></i> Content Type</label>
                            <div class="select-wrapper">
                                <select name="type" class="form-control select">
                                    <option value="cta_banner">CTA Text Banner (Call to Action text)</option>
                                    <option value="reviews">Testimonials (Customer Reviews)</option>
                                    <option value="newsletter">Promo Strip (Text highlights)</option>
                                    <option value="image_slider">Full-Width Image Slider (Like Hero Slider)</option>
                                    <option value="calendar">Celebrations Calendar (Visual date events)</option>
                                    <option value="grid_square">Square Master Grid (Premium Wiggle Effect)</option>
                                    <option value="grid_rect">Rectangular Master Grid (Premium Wiggle Effect)</option>
                                    <option value="grid_circle">Circular Master Grid (Premium Wiggle Effect)</option>
                                    <option value="grid_heart">Heart Master Grid (Premium Wiggle Effect)</option>
                                    <option value="heart_carousel">Heart Carousel (4 Items swipable)</option>
                                    <option value="circle_carousel">Circle Carousel (swipable circles)</option>
                                </select>
                            </div>
                            <div class="help-text">Select how this section should look and behave.</div>
                        </div>
                    </div>

                    <div class="form-header flex flex-col md:flex-row justify-end gap-3 bg-gray-50 border-t border-gray-100">
                        <a href="homepage.php" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-semibold hover:bg-gray-100 transition text-center">Cancel</a>
                        <button type="submit" class="px-8 py-2.5 rounded-xl bg-primary text-white font-semibold shadow-lg hover:shadow-xl hover:translate-y-[-1px] transition text-center">
                            <i class="fas fa-plus-circle mr-2"></i> Create Section
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>