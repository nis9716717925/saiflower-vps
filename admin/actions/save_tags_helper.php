<?php
/**
 * Helper function to automatically save new tags to the tags table
 * @param mysqli $conn The database connection
 * @param string|array $tags The tags to process (either an array from $_POST['tags'] or a comma-separated string)
 */
function auto_save_tags($conn, $tags) {
    if (empty($tags)) return;

    // Convert string to array if necessary
    if (is_string($tags)) {
        // Remove trailing/leading commas and whitespace if present
        $tags = trim($tags, " \t\n\r\0\x0B,");
        if (empty($tags)) return;
        $tagArray = explode(',', $tags);
    } else if (is_array($tags)) {
        $tagArray = $tags;
    } else {
        return;
    }

    $tagArray = array_map('trim', $tagArray);
    $tagArray = array_filter($tagArray); // remove empty tags

    foreach ($tagArray as $tagName) {
        $tagName = strtolower($tagName);
        if (empty($tagName)) continue;

        // Check if tag exists
        $stmt = $conn->prepare("SELECT id FROM tags WHERE LOWER(name) = ? LIMIT 1");
        $stmt->bind_param("s", $tagName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If it doesn't exist, insert it
        if ($result->num_rows === 0) {
            $insertStmt = $conn->prepare("INSERT INTO tags (name, status) VALUES (?, 1)");
            // Store the original case for insertion, though we checked in lowercase
            // Actually, we'll store the lowercased version since tag fetching is case-insensitive
            $insertStmt->bind_param("s", $tagName);
            $insertStmt->execute();
            $insertStmt->close();
        }
        $stmt->close();
    }
}
?>
