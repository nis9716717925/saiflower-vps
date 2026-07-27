<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= render_canonical_link() ?>
    <title>Privacy Policy | Sai Flowers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    require_once __DIR__ . '/includes/schema_helper.php';
    echo generate_simple_breadcrumb_json_ld([['name' => 'Privacy Policy', 'item' => 'privacy.php']]);
    ?>
    <?php include __DIR__ . '/partials/tailwind_config.php'; ?>
     
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="favicon.png" type="image/x-icon">
    <style>
        /* MATCHING YOUR SITE UI */
        body { 
            font-family: Arial, sans-serif; 
            background: #fafafa; 
            margin: 0; 
            color: #444;
            line-height: 1.6;
            overflow-x: hidden;
        }
        h1 { 
            text-align: center; 
            color: #2f6f4e; 
            padding-top: 40px; 
            margin-top: 0;
            margin-bottom: 30px;
        }
        .content-box {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
        }
        @media (min-width: 768px) {
            .content-box {
                padding: 50px;
            }
        }
        h2 {
            color: #2f6f4e;
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        p, li {
            font-size: 15px;
            margin-bottom: 15px;
        }
        ul {
            padding-left: 20px;
        }
        .contact-box {
            background-color: #f4f9f6; /* Light green tint */
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2f6f4e;
            margin-top: 30px;
        }
        strong { color: #333; }
    </style>
    <style>
    /* ===== GLOBAL FLOATING ICON FIX ===== */
    .floating-icons {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        z-index: 9999;
    }
    .float-btn {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        transition: transform 0.3s ease;
    }
    .float-btn:hover {
        transform: translateY(-5px);
    }
    
    /* WhatsApp Specifics */
    .float-btn.whatsapp {
        background: #25D366;
    }
    .float-btn.whatsapp svg {
        width: 30px; 
        height: 30px; 
        fill: #fff; /* Turns the black icon white */
    }
    
    /* Call Button Specifics */
    .float-btn.call {
        background: #2f6f4e;
        color: #fff;
        font-size: 24px;
    }
</style>
</head>
<body>

<?php include __DIR__ . '/partials/navbar.php'; ?>

<h1>Privacy Policy</h1>

<div class="content-box">
    <p><strong>Last Updated: January 2026</strong></p>
    
    <p>At <strong>Sai Flowers</strong>, we are committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you visit our website or store.</p>

    <h2>1. Information We Collect</h2>
    <p>We may collect personal information that you voluntarily provide to us when you:</p>
    <ul>
        <li>Place an order for flowers or event decoration.</li>
        <li>Contact us via phone, email, or WhatsApp.</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <p>We use the information we collect to:</p>
    <ul>
        <li>Process and deliver your floral orders.</li>
        <li>Communicate with you regarding your event decoration bookings.</li>
        <li>Respond to your inquiries and customer service requests.</li>
        <li>Improve our website and service offerings.</li>
    </ul>

    <h2>3. Information Sharing</h2>
    <p>We do not sell, trade, or rent your personal information to others. We may share your delivery address and phone number with our trusted delivery personnel strictly for the purpose of fulfilling your order.</p>

    <h2>4. Cookies</h2>
    <p>Our website may use "cookies" to enhance user experience. You may choose to set your web browser to refuse cookies, or to alert you when cookies are being sent.</p>

    <h2>5. Contact Us</h2>
    <p>If you have any questions about this Privacy Policy, please contact us at:</p>
    
    <div class="contact-box">
        <p><strong>Sai Flowers</strong><br>
        RZ-44A/1, Street No.1, Main Palam-Dabri Road,<br>
        Vaishali, Sector 7 Dwarka, New Delhi 110045</p>
        
        <p><strong>Phone:</strong> +91 8802004527<br>
        <strong>Email:</strong> info@saiflowers.com</p>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>