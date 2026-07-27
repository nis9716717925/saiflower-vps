<?php
require_once __DIR__ . '/../config.php';

$output = "Checking links in homepage_section_items...\n";

$query = $conn->query("SELECT id, title, link FROM homepage_section_items WHERE link IS NOT NULL AND link != '' AND link != '#'");
if ($query) {
    while ($row = $query->fetch_assoc()) {
        $id = $row['id'];
        $title = $row['title'];
        $link = $row['link'];
        
        // Normalize the link to just the slug if it's a relative path starting with /
        $slug = '';
        if (strpos($link, '/') === 0) {
            $slug = ltrim($link, '/');
        } elseif (preg_match('/flower-detail\.php\?slug=([^&]+)/', $link, $matches)) {
            $slug = $matches[1];
        } elseif (preg_match('/cake-detail\.php\?slug=([^&]+)/', $link, $matches)) {
            $slug = $matches[1];
        } elseif (preg_match('/gift-detail\.php\?slug=([^&]+)/', $link, $matches)) {
            $slug = $matches[1];
        } elseif (preg_match('/event-detail\.php\?slug=([^&]+)/', $link, $matches)) {
            $slug = $matches[1];
        } else {
            $slug = basename(parse_url($link, PHP_URL_PATH));
        }

        if (empty($slug)) continue;

        // Check if this slug exists
        $found = false;
        $tables = ['flowers', 'cakes', 'gifts', 'events'];
        foreach ($tables as $t) {
            $stmt = $conn->prepare("SELECT id FROM $t WHERE slug = ? LIMIT 1");
            $stmt->bind_param("s", $slug);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $output .= "BROKEN LINK FOUND: Item ID $id | Title: '$title' | Link: '$link' | Slug: '$slug'\n";
            
            // Try to find the correct slug by title
            $correctSlug = null;
            foreach ($tables as $t) {
                $nameCol = ($t === 'events') ? 'title' : 'name';
                $stmt = $conn->prepare("SELECT slug FROM $t WHERE $nameCol = ? LIMIT 1");
                $stmt->bind_param("s", $title);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows > 0) {
                    $correctSlug = $res->fetch_assoc()['slug'];
                    break;
                }
            }

            if ($correctSlug) {
                $newLink = '/' . $correctSlug;
                $output .= "  -> Found correct product! Updating link to: $newLink\n";
                $update = $conn->prepare("UPDATE homepage_section_items SET link = ? WHERE id = ?");
                $update->bind_param("si", $newLink, $id);
                $update->execute();
            } else {
                // Try fuzzy match
                $fuzzy = "%" . $title . "%";
                foreach ($tables as $t) {
                    $nameCol = ($t === 'events') ? 'title' : 'name';
                    $stmt = $conn->prepare("SELECT slug FROM $t WHERE $nameCol LIKE ? LIMIT 1");
                    $stmt->bind_param("s", $fuzzy);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res->num_rows > 0) {
                        $correctSlug = $res->fetch_assoc()['slug'];
                        break;
                    }
                }
                
                if ($correctSlug) {
                    $newLink = '/' . $correctSlug;
                    $output .= "  -> Fuzzy matched product! Updating link to: $newLink\n";
                    $update = $conn->prepare("UPDATE homepage_section_items SET link = ? WHERE id = ?");
                    $update->bind_param("si", $newLink, $id);
                    $update->execute();
                } else {
                    $output .= "  -> Could not find a matching product in the database for title '$title'.\n";
                }
            }
        }
    }
}

// Clear homepage cache
$cacheFile = __DIR__ . '/../cache/homepage_grid_cache.html';
if (file_exists($cacheFile)) {
    unlink($cacheFile);
    $output .= "Cache cleared.\n";
}

echo nl2br(htmlspecialchars($output));
?>
