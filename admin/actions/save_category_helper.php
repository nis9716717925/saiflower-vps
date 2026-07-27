<?php
/**
 * Helper function to handle multiple inline category creations
 * @param mysqli $conn The database connection
 * @param mixed $category_inputs The category inputs from $_POST['category_ids'] (array)
 * @return string Comma-separated string of category IDs (e.g. ",1,2,")
 */
function handle_multiple_categories($conn, $category_inputs) {
    if (empty($category_inputs)) return "";
    if (!is_array($category_inputs)) $category_inputs = [$category_inputs];

    $finalIds = [];
    foreach ($category_inputs as $input) {
        if (empty($input)) continue;

        // If it's numeric, it's an existing ID
        if (is_numeric($input)) {
            $finalIds[] = intval($input);
            continue;
        }

        // Otherwise, it's a new name string (Select2 tag mode)
        $categoryName = trim($input);
        if (empty($categoryName)) continue;

        // Check if a category with this name already exists (case-insensitive)
        $stmt = $conn->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $categoryName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $finalIds[] = intval($row['id']);
            $stmt->close();
            continue;
        }
        if($stmt) $stmt->close();

        // If it doesn't exist, create it as Active (status=1) with default sort_order=0
        $insertStmt = $conn->prepare("INSERT INTO categories (name, status, sort_order) VALUES (?, 1, 0)");
        $insertStmt->bind_param("s", $categoryName);
        if ($insertStmt->execute()) {
            $finalIds[] = intval($insertStmt->insert_id);
            $insertStmt->close();
        } else {
            if($insertStmt) $insertStmt->close();
        }
    }

    if (empty($finalIds)) return "";
    return ',' . implode(',', $finalIds) . ',';
}

/**
 * Legacy wrapper for single category if needed elsewhere
 */
function handle_inline_category($conn, $category_input) {
    $res = handle_multiple_categories($conn, [$category_input]);
    return intval(trim($res, ','));
}
?>
