<!DOCTYPE html>
<html lang="en">
<head>
    <?php 
    $pageTitle = $pageTitle ?? 'Admin Panel';
    // Adjust path to partials depending on where this is included from
    // Assumes inclusion from admin root files like admin/flowers.php
    include __DIR__ . '/../partials/head.php'; 
    ?>
</head>
<body class="admin-body">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
