<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!function_exists('verify_csrf_token')) {
        require_once '../includes/csrf_helper.php';
    }
    if (!verify_csrf_token()) {
        die("CSRF Validation Failed");
    }

    $name     = trim($_POST['name']);
    $rating   = intval($_POST['rating']);
    $text     = trim($_POST['review_text']);
    $platform = trim($_POST['platform']);

    // Prepared Statement
    $stmt = $conn->prepare("INSERT INTO reviews (name, rating, review_text, platform, status) VALUES (?, ?, ?, ?, 1)");
    if ($stmt) {
        $stmt->bind_param("siss", $name, $rating, $text, $platform);
        if($stmt->execute()) {
            header("Location: reviews.php?msg=added");
            exit;
        } else {
            $msg = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg = "Database Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Add Review'; include 'partials/head.php'; ?>
    <style>
        :root {
            --primary: #326e54;
            --accent: #d4af37;
            --bg: #f4f7f6;
            --text: #333;
        }

        .admin-main {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 40px;
        }

        .card { 
            background: white; 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 600px; 
            height: fit-content;
        }

        h2 { 
            margin-top: 0; 
            color: var(--primary); 
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .subtitle {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 700; 
            font-size: 0.85rem; 
            color: #444;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"], 
        textarea, 
        select { 
            width: 100%; 
            padding: 14px; 
            margin-bottom: 20px; 
            border: 1px solid #ddd; 
            border-radius: 12px; 
            box-sizing: border-box; 
            font-size: 16px; /* Prevents iOS auto-zoom */
            transition: 0.3s;
            background: #fff;
            font-family: inherit;
        }

        input:focus, textarea:focus, select:focus { 
            outline: none; 
            border-color: var(--primary); 
            box-shadow: 0 0 0 3px rgba(50, 110, 84, 0.1); 
        }

        /* Star Rating UI */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 20px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 2rem;
            color: #e0e0e0;
            cursor: pointer;
            transition: 0.2s;
            margin: 0;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: var(--accent); }

        .btn-submit { 
            background: var(--primary); 
            color: white; 
            padding: 16px; 
            border: none; 
            width: 100%; 
            border-radius: 50px; 
            cursor: pointer; 
            font-size: 1rem; 
            font-weight: 700;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(50, 110, 84, 0.2);
            margin-top: 10px;
        }

        .btn-submit:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(50, 110, 84, 0.3);
        }

        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            color: #888; 
            text-decoration: none; 
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-link:hover { color: var(--primary); }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            /* body { padding: 20px 10px; } Removed body padding override */
            .card { padding: 30px 20px; }
            h2 { font-size: 1.3rem; }
            .star-rating label { font-size: 1.6rem; }
        }
    </style>
</head>
<body class="admin-body">

<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">

<div class="card">
    <h2><i class="fas fa-star-half-alt"></i> Add New Review</h2>
    <p class="subtitle">Publish client testimonials to build trust.</p>

    <?php if($msg): ?>
        <div style="background: #fff5f5; color: #c53030; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.85rem; border: 1px solid #feb2b2;">
            <i class="fas fa-exclamation-circle"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php 
        if (!function_exists('csrf_field')) { require_once '../includes/csrf_helper.php'; }
        csrf_field(); 
        ?>
        
        <label>Client Name</label>
        <input type="text" name="name" placeholder="e.g. Priya Kapoor" required>

        <label>Rating Score</label>
        <div class="star-rating">
            <input type="radio" id="star5" name="rating" value="5" checked><label for="star5" class="fas fa-star"></label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4" class="fas fa-star"></label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3" class="fas fa-star"></label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2" class="fas fa-star"></label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1" class="fas fa-star"></label>
        </div>

        <label>Review Text</label>
        <textarea name="review_text" placeholder="Paste the client's testimonial here..." required style="min-height:120px;"></textarea>

        <label>Source Platform</label>
        <select name="platform">
            <option value="Google">Google Business</option>
            <option value="Facebook">Facebook Page</option>
            <option value="Direct">Website Direct</option>
            <option value="WhatsApp">WhatsApp Message</option>
            <option value="Instagram">Instagram DM</option>
        </select>

        <button type="submit" class="btn-submit">Publish Review</button>
    </form>
    
    <a href="reviews.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Cancel and Go Back
    </a>
</div>

</main>
</body>
</html>