<?php
// includes/image_helper.php
// Small helper that returns HTML for a responsive <picture> element using WebP when available.

function picture_with_webp($relative_path, $alt = '', $attrs = '') {
    // $relative_path expected to be path relative to web root (e.g. /uploads/176796...jpg)
    $doc_root = $_SERVER['DOCUMENT_ROOT'];
    $abs = $doc_root . $relative_path;
    $webp = preg_replace('/\.[^.]+$/', '.webp', $abs);
    $webp_url = preg_replace('#^' . preg_quote($doc_root) . '#', '', $webp);

    // If webp exists, return a picture element
    if (file_exists($webp)) {
        return "<picture>\n  <source srcset=\"$webp_url\" type=\"image/webp\">\n  <img src=\"$relative_path\" alt=\"" . htmlspecialchars($alt) . "\" $attrs>\n</picture>";
    }

    // Fallback: just return img tag
    return "<img src=\"$relative_path\" alt=\"" . htmlspecialchars($alt) . "\" $attrs>";
}
