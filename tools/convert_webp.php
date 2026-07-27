<?php
// Bulk WebP Converter Tool
require_once __DIR__ . '/../config.php';

// Only allow execution from browser or CLI easily
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

$uploadDir = __DIR__ . '/../uploads/';
$convertedCount = 0;
$skippedCount = 0;
$failedCount = 0;

function convertToWebp($sourceFile, $targetFile, $quality = 80) {
    $info = @getimagesize($sourceFile);
    if (!$info) return false;

    $mime = $info['mime'];
    if ($mime == 'image/jpeg') {
        $image = @imagecreatefromjpeg($sourceFile);
    } elseif ($mime == 'image/png') {
        $image = @imagecreatefrompng($sourceFile);
        if ($image !== false) {
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
    } else {
        return false;
    }

    if (!$image) return false;

    $success = imagewebp($image, $targetFile, $quality);
    imagedestroy($image);
    return $success;
}

if (!is_dir($uploadDir)) {
    die("Uploads directory not found.");
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadDir));

echo "<h1>WebP Converter</h1>\n";
echo "Starting conversion...<br>\n";

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $ext = strtolower($file->getExtension());
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $sourcePath = $file->getPathname();
            
            // Don't convert if already a webp version exists
            $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $sourcePath);
            if ($sourcePath === $webpPath) {
                // If the regex failed somehow
                $webpPath = $sourcePath . '.webp';
            }
            
            if (file_exists($webpPath)) {
                $skippedCount++;
                continue;
            }

            if (convertToWebp($sourcePath, $webpPath)) {
                $convertedCount++;
            } else {
                $failedCount++;
            }
        }
    }
}

echo "Conversion Finished!<br>\n";
echo "Converted: $convertedCount<br>\n";
echo "Skipped (Already Exists): $skippedCount<br>\n";
echo "Failed: $failedCount<br>\n";
echo "You can now safely rename database entries to point to .webp, or just rely on <picture> tags to load them alongside!<br>\n";
?>
