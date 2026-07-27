<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once __DIR__ . '/../auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    
    // Extra Images Upload
    $extra_images_arr = [];
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
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
    
    $extra_images_json = empty($extra_images_arr) ? NULL : json_encode($extra_images_arr);
    $extra_images_val = "NULL";
    if ($extra_images_json) {
        $clean_json = $conn->real_escape_string($extra_images_json);
        $extra_images_val = "'$clean_json'";
    }

    // Mid-Grid Image Upload
    $midgrid_image = NULL;
    $midgrid_image_alt = NULL;
    
    if ($layout_type === 'product_showcase') {
        $midgrid_image_alt = isset($_POST['midgrid_image_alt']) ? $conn->real_escape_string(trim($_POST['midgrid_image_alt'])) : '';
        
        if (isset($_FILES['midgrid_image']) && $_FILES['midgrid_image']['error'] === UPLOAD_ERR_OK) {
            $fname = time() . '_mid_' . rand(1000, 9999) . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['midgrid_image']['name']));
            $tPath = $uploadDir . $fname;
            if (move_uploaded_file($_FILES['midgrid_image']['tmp_name'], $tPath)) {
                $midgrid_image = $fname;
            }
        }
    }
    
    $midgrid_image_val = $midgrid_image ? "'$midgrid_image'" : "NULL";
    $midgrid_image_alt_val = $midgrid_image_alt !== NULL ? "'$midgrid_image_alt'" : "NULL";

    $query = "INSERT INTO dynamic_pages (title, short_description, slug, content, meta_title, meta_description, meta_keywords, status, layout_type, page_tag, extra_images, faqs, midgrid_image, midgrid_image_alt) 
              VALUES ('$title', '$short_description', '$slug', '$content', '$meta_title', '$meta_description', '$meta_keywords', $status, '$layout_type', '$page_tag', $extra_images_val, $faqs_json, $midgrid_image_val, $midgrid_image_alt_val)";
              
    if ($conn->query($query)) {
        header("Location: ../pages.php?msg=added");
    } else {
        error_log("Add Page Error: " . $conn->error);
        header("Location: ../pages.php?msg=error");
    }
    exit;
}
header("Location: ../pages.php");
exit;
