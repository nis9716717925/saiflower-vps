<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once __DIR__ . '/../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string(trim($_POST['title']));
    $short_description = $conn->real_escape_string(trim($_POST['short_description'] ?? ''));
    $slug = $conn->real_escape_string(trim($_POST['slug']));
    $content = $conn->real_escape_string(trim($_POST['content']));
    $meta_title = $conn->real_escape_string(trim($_POST['meta_title']));
    $meta_description = $conn->real_escape_string(trim($_POST['meta_description']));
    $meta_keywords = $conn->real_escape_string(trim($_POST['meta_keywords']));
    $layout_type = $conn->real_escape_string(trim($_POST['layout_type'] ?? 'event_info'));
    $page_tag = $conn->real_escape_string(trim($_POST['page_tag'] ?? ''));
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Ensure new columns exist (fail silently if they already exist or errors)
    try { $conn->query("ALTER TABLE dynamic_pages ADD COLUMN faqs LONGTEXT NULL"); } catch (Exception $e) {}
    try { $conn->query("ALTER TABLE dynamic_pages ADD COLUMN midgrid_image VARCHAR(255) NULL"); } catch (Exception $e) {}
    try { $conn->query("ALTER TABLE dynamic_pages ADD COLUMN midgrid_image_alt VARCHAR(255) NULL"); } catch (Exception $e) {}

    // FAQs Process
    $faqs_arr = [];
    $faq_questions = $_POST['faq_question'] ?? [];
    $faq_answers = $_POST['faq_answer'] ?? [];

    for ($i = 0; $i < count($faq_questions); $i++) {
        $q = trim($faq_questions[$i]);
        $a = trim($faq_answers[$i] ?? '');
        if (!empty($q) && !empty($a)) {
            $faqs_arr[] = [
                'question' => $conn->real_escape_string($q),
                'answer' => $conn->real_escape_string($a)
            ];
        }
    }
    $faqs_json = empty($faqs_arr) ? "NULL" : "'" . $conn->real_escape_string(json_encode($faqs_arr)) . "'";
    
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    
    // Fetch old extra images to clean up orphaned files later
    $oldExRes = $conn->query("SELECT extra_images FROM dynamic_pages WHERE id = $id");
    $oldImages = [];
    if ($oldExRes && $oldEx = $oldExRes->fetch_assoc()) {
        if (!empty($oldEx['extra_images'])) {
            $oldArr = json_decode($oldEx['extra_images'], true);
            if ($oldArr && is_array($oldArr)) {
                foreach ($oldArr as $imgObj) {
                    $oldImages[] = is_array($imgObj) ? $imgObj['image'] : $imgObj;
                }
            }
        }
    }

    $extra_images_arr = [];
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    // 1. Process existing images that were kept
    $existing_images = $_POST['existing_extra_image'] ?? [];
    $existing_descs = $_POST['existing_extra_desc'] ?? [];
    $existing_links = $_POST['existing_extra_link'] ?? [];
    $remove_images = $_POST['remove_extra_image'] ?? [];
    
    foreach ($existing_images as $index => $imgName) {
        if (in_array($imgName, $remove_images)) {
            continue; // Skip, will be deleted as an orphan
        }
        $extra_images_arr[] = [
            'image' => $conn->real_escape_string(trim($imgName)),
            'desc' => $conn->real_escape_string(trim($existing_descs[$index] ?? '')),
            'link' => $conn->real_escape_string(trim($existing_links[$index] ?? ''))
        ];
    }
    
    // 2. Process newly uploaded images
    if (isset($_FILES['new_extra_image']) && is_array($_FILES['new_extra_image']['name'])) {
        $new_descs = $_POST['new_extra_desc'] ?? [];
        $new_links = $_POST['new_extra_link'] ?? [];
        
        foreach ($_FILES['new_extra_image']['name'] as $i => $name) {
            if ($_FILES['new_extra_image']['error'][$i] === UPLOAD_ERR_OK) {
                $fname = time() . '_' . rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($name));
                $tPath = $uploadDir . $fname;
                
                if (move_uploaded_file($_FILES['new_extra_image']['tmp_name'][$i], $tPath)) {
                    $extra_images_arr[] = [
                        'image' => $fname,
                        'desc' => $conn->real_escape_string(trim($new_descs[$i] ?? '')),
                        'link' => $conn->real_escape_string(trim($new_links[$i] ?? ''))
                    ];
                }
            }
        }
    }
    
    // Clean up orphaned old images
    $keptImages = array_column($extra_images_arr, 'image');
    foreach ($oldImages as $oldImg) {
        if (!in_array($oldImg, $keptImages)) {
            $imgPath = $uploadDir . basename($oldImg);
            if (file_exists($imgPath)) unlink($imgPath);
        }
    }
    
    // 3. Serialize to JSON and prepare query part
    if (!empty($extra_images_arr)) {
        $extra_images_json = json_encode($extra_images_arr);
        $clean_json = $conn->real_escape_string($extra_images_json);
        $extraImageQuery = ", extra_images = '$clean_json'";
    } else {
        $extraImageQuery = ", extra_images = NULL";
    }

    // 4. Mid-Grid Image Upload Process
    $oldMidRes = $conn->query("SELECT midgrid_image FROM dynamic_pages WHERE id = $id");
    $oldMidImg = '';
    if ($oldMidRes && $row = $oldMidRes->fetch_assoc()) {
        $oldMidImg = $row['midgrid_image'];
    }

    $midgrid_image = $oldMidImg; // Default to old image
    $midgrid_image_alt = isset($_POST['midgrid_image_alt']) ? $conn->real_escape_string(trim($_POST['midgrid_image_alt'])) : '';

    if ($layout_type === 'product_showcase') {
        if (isset($_POST['remove_midgrid_image']) && $_POST['remove_midgrid_image'] == '1') {
            if ($oldMidImg && file_exists($uploadDir . basename($oldMidImg))) {
                unlink($uploadDir . basename($oldMidImg));
            }
            $midgrid_image = NULL;
        }

        if (isset($_FILES['midgrid_image']) && $_FILES['midgrid_image']['error'] === UPLOAD_ERR_OK) {
            $fname = time() . '_mid_' . rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['midgrid_image']['name']));
            $tPath = $uploadDir . $fname;
            if (move_uploaded_file($_FILES['midgrid_image']['tmp_name'], $tPath)) {
                if ($oldMidImg && file_exists($uploadDir . basename($oldMidImg))) {
                    unlink($uploadDir . basename($oldMidImg)); 
                }
                $midgrid_image = $fname;
            }
        }
    }
    
    $midgrid_image_db = $midgrid_image ? "'$midgrid_image'" : "NULL";
    $midgrid_image_alt_db = "'$midgrid_image_alt'";

    $query = "UPDATE dynamic_pages SET 
              title = '$title',
              short_description = '$short_description', 
              slug = '$slug', 
              content = '$content', 
              meta_title = '$meta_title', 
              meta_description = '$meta_description', 
              meta_keywords = '$meta_keywords', 
              layout_type = '$layout_type',
              page_tag = '$page_tag',
              status = $status,
              faqs = $faqs_json,
              midgrid_image = $midgrid_image_db,
              midgrid_image_alt = $midgrid_image_alt_db
              $extraImageQuery
              WHERE id = $id";
              
    if ($conn->query($query)) {
        header("Location: ../pages.php?msg=updated");
    } else {
        error_log("Edit Page Error: " . $conn->error);
        header("Location: ../pages.php?msg=error");
    }
    exit;
}
header("Location: ../pages.php");
exit;
