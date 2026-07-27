<?php
// admin/includes/image_handler.php

/**
 * Uploads an image and converts it to WebP format for high performance.
 * * @param string $fileInputName The name attribute of the <input type="file">
 * @param string $targetDir Relative or absolute path to upload folder
 * @param int $quality WebP quality (0-100)
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadAndConvertToWebP($fileInputName, $targetDir, $quality = 80) {
    // Prevent crashes on large image processing
    ini_set('memory_limit', '256M');

    // 0. Check for GD Library
    if (!extension_loaded('gd') || !function_exists('imagewebp')) {
        return ['success' => false, 'error' => 'GD Library with WebP support is missing on this server.'];
    }
    
    // 1. Basic Validation
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] != 0) {
        return ['success' => false, 'error' => 'No file uploaded or error occurred.'];
    }

    $file = $_FILES[$fileInputName];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $validExts = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $validExts)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, WEBP allowed.'];
    }

    // 2. Ensure target directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // 3. Generate Secure Unique Filename
    $name = pathinfo($file['name'], PATHINFO_FILENAME);
    $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $name);
    $newName = uniqid('img_') . '_' . $cleanName . '.webp';
    $targetPath = rtrim($targetDir, '/') . '/' . $newName;

    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';

    // 4. Handle Existing WebP files directly
    if ($ext == 'webp' || $mime == 'image/webp') {
        if(move_uploaded_file($file['tmp_name'], $targetPath)){
            return ['success' => true, 'filename' => $newName];
        }
        return ['success' => false, 'error' => 'Failed to move WebP file.'];
    }

    // 5. Create Image Resource based on True MIME Type (with fallback to extension)
    $image = false;
    
    if ($mime == 'image/jpeg' || (empty($mime) && in_array($ext, ['jpg', 'jpeg']))) {
        $image = @imagecreatefromjpeg($file['tmp_name']);
    } elseif ($mime == 'image/png' || (empty($mime) && $ext == 'png')) {
        $image = @imagecreatefrompng($file['tmp_name']);
        if ($image !== false) {
            // Crucial: Handle PNG Transparency
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
    }

    if (!$image) {
        return ['success' => false, 'error' => 'Could not process source image. The file may be corrupted or its extension does not match its actual format.'];
    }

    // 6. Convert and Save as WebP
    if (imagewebp($image, $targetPath, $quality)) {
        imagedestroy($image); // Free up server memory
        return ['success' => true, 'filename' => $newName];
    } else {
        imagedestroy($image);
        return ['success' => false, 'error' => 'WebP conversion failed. Check server GD permissions.'];
    }
}

/**
 * Converts a temporary uploaded file directly to WebP.
 * Useful for multiple file uploads where we iterate manually.
 */
function uploadAndConvertToWebPFromTemp($tmpPath, $desiredFilename, $targetDir, $quality = 80) {
    // 1. Validate Source
    if (!file_exists($tmpPath)) {
        return ['success' => false, 'error' => 'Source file not found.'];
    }

    // 2. Determine Extension from desired filename (or inspect mime type if needed)
    $ext = strtolower(pathinfo($desiredFilename, PATHINFO_EXTENSION));
    
    // 3. Create Image Resource
    $image = null;
    $info = getimagesize($tmpPath);
    $mime = $info['mime'] ?? '';

    if ($mime == 'image/jpeg') {
        $image = @imagecreatefromjpeg($tmpPath);
    } elseif ($mime == 'image/png') {
        $image = @imagecreatefrompng($tmpPath);
        if ($image !== false) {
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
    } elseif ($mime == 'image/webp') {
        // If already WebP, just move it
        $targetPath = rtrim($targetDir, '/') . '/' . $desiredFilename;
        if(copy($tmpPath, $targetPath)) { // copy instead of move_uploaded_file for safety with array
             return ['success' => true, 'filename' => $desiredFilename];
        }
        return ['success' => false, 'error' => 'Failed to copy WebP file.'];
    }

    if (!$image) {
        return ['success' => false, 'error' => 'Unsupported image format or corrupt file.'];
    }

    // 4. Save as WebP
    $targetPath = rtrim($targetDir, '/') . '/' . $desiredFilename;
    if (imagewebp($image, $targetPath, $quality)) {
        imagedestroy($image);
        return ['success' => true, 'filename' => $desiredFilename];
    } else {
        imagedestroy($image);
        return ['success' => false, 'error' => 'WebP conversion failed.'];
    }
}
?>