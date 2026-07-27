<?php
// actions/submit_review.php

// 1. Show errors for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 2. Connect to Database
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; 

// 3. Process Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/csrf_helper.php';
    csrf_verify_or_die();

    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? 'Anonymous');
    $rating = (int)($_POST['rating'] ?? 5); // Default to 5 if missing
    $comment = mysqli_real_escape_string($conn, $_POST['comment'] ?? '');

    // Insert into DB
    $sql = "INSERT INTO reviews (name, rating, comment) VALUES ('$name', '$rating', '$comment')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: /reviews.php?status=success");
        exit();
    } else {
        die("Error saving review: " . mysqli_error($conn));
    }
} else {
    header("Location: /reviews.php");
    exit();
}
?>