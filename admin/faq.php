<?php



require_once __DIR__ . '/auth_check.php';
require_once '../config.php';
// csrf_verify_or_die(); // Moved to action blocks below


$msg = "";
$edit_mode = false;
$faq_data = ['question' => '', 'answer' => '', 'page' => 'home', 'status' => 1];

// --- SMART DISCOVERY LOGIC ---
$page_options = [];
$core = ['Home', 'About Us', 'Contact Us', 'Services', 'Gallery', 'Cart', 'Checkout'];
foreach($core as $c) { 
    $page_options[] = ['val' => strtolower(str_replace(' ', '-', $c)), 'label' => "Page: $c"]; 
}

$flowers = mysqli_query($conn, "SELECT id, name FROM flowers ORDER BY name ASC");
if($flowers && mysqli_num_rows($flowers) > 0) {
    while($f = mysqli_fetch_assoc($flowers)) {
        $page_options[] = ['val' => "flower-" . $f['id'], 'label' => "Flower: " . $f['name']];
    }
}

$cakes = mysqli_query($conn, "SELECT id, name FROM cakes ORDER BY name ASC");
if($cakes && mysqli_num_rows($cakes) > 0) {
    while($c = mysqli_fetch_assoc($cakes)) {
        $page_options[] = ['val' => "cake-" . $c['id'], 'label' => "Cake: " . $c['name']];
    }
}

$gifts = mysqli_query($conn, "SELECT id, name FROM gifts ORDER BY name ASC");
if($gifts && mysqli_num_rows($gifts) > 0) {
    while($g = mysqli_fetch_assoc($gifts)) {
        $page_options[] = ['val' => "gift-" . $g['id'], 'label' => "Gift: " . $g['name']];
    }
}

$check_events = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
if(mysqli_num_rows($check_events) > 0) {
    $events = mysqli_query($conn, "SELECT id, title FROM events ORDER BY title ASC");
    while($e = mysqli_fetch_assoc($events)) {
        $page_options[] = ['val' => "event-" . $e['id'], 'label' => "Event: " . $e['title']];
    }
}

// HANDLE SUBMIT
if (isset($_POST['save_faq'])) {
    csrf_verify_or_die();
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $q = $_POST['question'][0] ?? '';
        $a = $_POST['answer'][0] ?? '';
        $p = $_POST['page'][0] ?? ''; 
        $s = isset($_POST['status'][0]) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ?, page = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $q, $a, $p, $s, $id);
        $stmt->execute();
        $stmt->close();
        $msg = "FAQ Updated Successfully!";
    } else {
        $count = 0;
        $stmt = $conn->prepare("INSERT INTO faqs (question, answer, page, status) VALUES (?, ?, ?, ?)");
        foreach ($_POST['question'] as $key => $val) {
            $q = $_POST['question'][$key] ?? '';
            $a = $_POST['answer'][$key] ?? '';
            $p = $_POST['page'][$key] ?? ''; 
            $s = isset($_POST['status'][$key]) ? 1 : 0;
            if (!empty($q)) {
                $stmt->bind_param("sssi", $q, $a, $p, $s);
                $stmt->execute();
                $count++;
            }
        }
        $stmt->close();
        $msg = "$count FAQ(s) Added Successfully!";
    }
}

// HANDLE DELETE
if (isset($_GET['del'])) {
    csrf_verify_or_die();
    $id = intval($_GET['del']);
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: faq.php"); exit;
}

// HANDLE EDIT
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res && $res->num_rows > 0) { 
        $faq_data = $res->fetch_assoc(); 
        $edit_mode = true; 
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Manage FAQs'; include 'partials/head.php'; ?>
    <style>
        :root { 
            --primary: #326e54; 
            --bg: #f4f7f6; 
            --sidebar-width: 260px;
        }

        /* --- THE ULTIMATE MOBILE FIT FIX --- */
        * { box-sizing: border-box; }
        
        html, body { 
            width: 100vw; 
            max-width: 100%;
            overflow-x: hidden; /* This kills the side scroll */
            margin: 0; 
            padding: 0; 
            background: var(--bg);
            position: relative;
        }

        /* Desktop Layout Offset */
        .admin-main { 
            margin-left: var(--sidebar-width); 
            padding: 25px 20px; 
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width)); 
            transition: all 0.3s ease;
            display: block;
        }

        .faq-layout { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start; width: 100%; }
        .form-side { flex: 1; min-width: 320px; }
        .list-side { flex: 1.5; min-width: 320px; }

        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .faq-entry { border: 1px solid #eee; padding: 15px; border-radius: 12px; margin-bottom: 15px; background: #fff; }
        
        .form-input { 
            width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; 
            border-radius: 10px; font-size: 16px; box-sizing: border-box; 
        }
        .badge-loc { 
            background:#eef6f1; color:var(--primary); font-size:0.75rem; 
            padding: 4px 10px; border-radius: 50px; cursor: pointer; font-weight: 700; 
        }

        /* --- MOBILE OVERRIDES --- */
        @media (max-width: 992px) {
            .admin-main { 
                margin-left: 0 !important; 
                margin-bottom: 100px !important; 
                padding: 15px; 
                width: 100% !important;
                max-width: 100vw !important;
            }

            .faq-layout { flex-direction: column; width: 100%; }
            .form-side, .list-side { min-width: 100%; width: 100%; flex: none; }

            .admin-table thead { display: none; }
            .admin-table tr { 
                display: block; background: #fff; border: 1px solid #eee; 
                border-radius: 12px; margin-bottom: 15px; padding: 15px; 
                width: 100%;
            }
            .admin-table td { 
                display: flex; justify-content: space-between; align-items: center; 
                width: 100% !important; border: none; padding: 8px 0; text-align: left; 
            }
            .admin-table td[data-label]::before { 
                content: attr(data-label) ": "; font-weight: 800; color: #888; font-size: 0.7rem; 
            }
            .admin-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>

<body class="admin-body">
    <?php include 'partials/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap;">
            <div>
                <h2 style="margin:0;"><i class="fas fa-question-circle"></i> FAQ Manager</h2>
                <p style="color:#888; font-size: 0.85rem;">Assign questions to pages or specific products.</p>
            </div>
            <?php if($edit_mode): ?>
                <a href="faq.php" class="btn" style="background:#888;"><i class="fas fa-arrow-left"></i> Cancel Edit</a>
            <?php endif; ?>
        </div>

        <?php if($msg): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?= $msg ?>
            </div>
        <?php endif; ?>

        <div class="faq-layout">
            <div class="form-side">
                <div class="card">
                    <form method="POST" id="faq-form">
                        <?php csrf_field(); ?>
                        <?php if($edit_mode): ?><input type="hidden" name="id" value="<?= $faq_data['id'] ?>"><?php endif; ?>
                        
                        <div id="faq-container">
                            <div class="faq-entry">
                                <label style="font-weight:700; font-size:0.8rem; color:#666;">LOCATION</label>
                                <input type="text" name="page[]" list="pages_list" class="form-input page-selector" 
                                       oninput="filterFaqsByPage(this.value)" 
                                       value="<?= htmlspecialchars($faq_data['page']) ?>" placeholder="Select Page or Product" required>

                                <label style="font-weight:700; font-size:0.8rem; color:#666;">QUESTION</label>
                                <input type="text" name="question[]" class="form-input" value="<?= htmlspecialchars($faq_data['question']) ?>" required>
                                
                                <label style="font-weight:700; font-size:0.8rem; color:#666;">ANSWER</label>
                                <textarea name="answer[]" class="form-input" rows="3" required><?= htmlspecialchars($faq_data['answer']) ?></textarea>
                                
                                <label style="display:flex; align-items:center; cursor:pointer; font-size: 0.85rem;">
                                    <input type="checkbox" name="status[0]" <?= $faq_data['status'] ? 'checked' : '' ?> value="1" style="width:auto; margin-right:8px;"> Active
                                </label>
                            </div>
                        </div>

                        <?php if(!$edit_mode): ?>
                        <button type="button" onclick="addFaqRow()" class="btn" style="background:#f4f7f6; color:#555; width:100%; margin-bottom:10px; border:1px solid #eee;">
                            <i class="fas fa-plus"></i> Add Row
                        </button>
                        <?php endif; ?>

                        <button type="submit" name="save_faq" class="btn" style="width:100%; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: bold; cursor: pointer;">
                            <i class="fas fa-save"></i> <?= $edit_mode ? 'Update FAQ' : 'Publish FAQ(s)' ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="list-side">
                <div class="table-wrapper" style="background: white; border-radius: 15px; overflow: hidden; border: 1px solid #eee;">
                    <div id="filter-label" style="padding: 12px; background: #fafafa; border-bottom: 1px solid #eee; font-size: 0.75rem; font-weight: 800; color: var(--primary);">
                        VIEWING: ALL CATEGORIES
                    </div>
                    <table class="admin-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #fdfdfd; border-bottom: 1px solid #eee;">
                                <th style="padding: 15px; text-align: left; font-size: 0.8rem; color: #888;">Target</th>
                                <th style="padding: 15px; text-align: left; font-size: 0.8rem; color: #888;">Content</th>
                                <th width="80" style="padding: 15px; text-align: left; font-size: 0.8rem; color: #888;">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $res = mysqli_query($conn, "SELECT * FROM faqs ORDER BY id DESC");
                            while($row = mysqli_fetch_assoc($res)): 
                            ?>
                            <tr class="faq-row" data-page="<?= htmlspecialchars($row['page']) ?>" style="border-bottom: 1px solid #f9f9f9;">
                                <td data-label="Location" style="padding: 15px;">
                                    <span class="badge-loc" onclick="applyQuickFilter('<?= $row['page'] ?>')">
                                        <?= htmlspecialchars($row['page']) ?>
                                    </span>
                                </td>
                                <td data-label="Entry" style="padding: 15px;">
                                    <div style="font-weight:700; font-size:0.9rem;"><?= htmlspecialchars($row['question']) ?></div>
                                    <div style="font-size:0.8rem; color:#888;"><?= mb_strimwidth(htmlspecialchars($row['answer']), 0, 60, "...") ?></div>
                                </td>
                                <td style="padding: 15px;">
                                    <div style="display:flex; gap:10px;">
                                        <a href="faq.php?edit=<?= $row['id'] ?>" style="color:var(--primary);"><i class="fas fa-edit"></i></a>
                                        <a href="faq.php?del=<?= $row['id'] ?>&csrf_token=<?= csrf_token() ?>" onclick="return confirm('Delete?')" style="color:#dc3545;"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <datalist id="pages_list">
        <?php foreach($page_options as $opt): ?>
            <option value="<?= htmlspecialchars($opt['val']) ?>"><?= htmlspecialchars($opt['label']) ?></option>
        <?php endforeach; ?>
    </datalist>

    <script>
    let rowCount = 1;
    function addFaqRow() {
        const container = document.getElementById('faq-container');
        const first = document.querySelector('.faq-entry');
        const clone = first.cloneNode(true);
        const curPage = document.querySelector('.page-selector').value;
        clone.querySelectorAll('input[type="text"], textarea').forEach(i => {
            if(!i.classList.contains('page-selector')) i.value = '';
            else i.value = curPage;
        });
        clone.querySelector('input[type="checkbox"]').name = `status[${rowCount}]`;
        container.appendChild(clone);
        rowCount++;
    }

    function applyQuickFilter(val) {
        document.querySelector('.page-selector').value = val;
        filterFaqsByPage(val);
    }

    function filterFaqsByPage(v) {
        const rows = document.querySelectorAll('.faq-row');
        const label = document.getElementById('filter-label');
        if(!v) {
            rows.forEach(r => r.style.display = '');
            label.innerText = "VIEWING: ALL CATEGORIES";
            return;
        }
        let count = 0;
        rows.forEach(r => {
            if(r.getAttribute('data-page') === v) {
                r.style.display = '';
                count++;
            } else { r.style.display = 'none'; }
        });
        label.innerText = "FILTER: " + v.toUpperCase() + " (" + count + ")";
    }
    </script>
</body>
</html>