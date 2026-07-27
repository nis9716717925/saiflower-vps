<?php



require_once __DIR__ . '/auth_check.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$stmt = $conn->prepare("SELECT id, title, slug, status, created_at, image FROM blogs ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php $pageTitle = 'Manage Blogs'; include 'partials/head.php'; ?>

<style>
:root{
--primary:#326e54;
--accent:#d4af37;
--bg:#f4f7f6;
}

*{box-sizing:border-box;}
html,body{width:100%;overflow-x:hidden;margin:0;background:var(--bg);font-family:Inter,sans-serif;}

.admin-main{width:100%;padding:20px 15px;}

.admin-header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
gap:15px;
flex-wrap:wrap;
}

.table-wrapper{
background:#fff;
border-radius:15px;
overflow:hidden;
box-shadow:0 10px 30px rgba(0,0,0,.05);
border:1px solid #eee;
}

.admin-table{width:100%;border-collapse:collapse;}
.admin-table th{
background:#fafafa;
padding:14px;
text-align:left;
font-size:.75rem;
color:#999;
text-transform:uppercase;
border-bottom:1px solid #eee;
}
.admin-table td{
padding:14px;
border-bottom:1px solid #f2f2f2;
vertical-align:middle;
}

.blog-thumb{
width:60px;
height:45px;
object-fit:cover;
border-radius:8px;
}

.slug{
font-size:.75rem;
color:#888;
margin-top:4px;
}

.badge{
padding:5px 12px;
border-radius:50px;
font-size:.7rem;
font-weight:700;
text-transform:uppercase;
}
.badge-success{background:#e9f7ef;color:#27ae60;}
.badge-warning{background:#fff4e5;color:#f39c12;}

.btn-action{
display:inline-flex;
align-items:center;
justify-content:center;
width:35px;
height:35px;
border-radius:10px;
border:1px solid #eee;
color:#555;
text-decoration:none;
background:#fff;
transition:.2s;
}
.btn-action:hover{background:var(--primary);color:#fff;border-color:var(--primary);}
.btn-delete:hover{background:#fee2e2;color:#dc2626;border-color:#fecaca;}

/* RESPONSIVE TABLE */
@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .admin-header > div { width: 100%; }
    .admin-header .btn { width: 100%; text-align: center; }

    .admin-table, .admin-table thead, .admin-table tbody, .admin-table th, .admin-table td, .admin-table tr { 
        display: block; 
    }
    
    .admin-table thead tr { 
        position: absolute;
        top: -9999px;
        left: -9999px;
    }
    
    .admin-table tr { border: 1px solid #ccc; margin-bottom: 15px; border-radius: 8px; overflow: hidden; }
    
    .admin-table td { 
        border: none;
        border-bottom: 1px solid #eee; 
        position: relative;
        padding-left: 50%; 
        text-align: right;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        min-height: 45px;
    }
    
    .admin-table td:before { 
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        width: 45%; 
        padding-right: 10px; 
        white-space: nowrap;
        text-align: left;
        font-weight: bold;
        color: #555;
        font-size: 0.8rem;
    }
    
    /* Label Headers */
    .admin-table td:nth-of-type(1):before { content: "Cover"; }
    .admin-table td:nth-of-type(2):before { content: "Article"; }
    .admin-table td:nth-of-type(3):before { content: "Status"; }
    .admin-table td:nth-of-type(4):before { content: "Date"; }
    .admin-table td:nth-of-type(5):before { content: "Actions"; }

    .admin-table td:nth-of-type(2) { text-align: right; display: block; }
}
</style>
</head>

<body class="admin-body">
<?php include 'partials/sidebar.php'; ?>

<main class="admin-main">

<div class="admin-header">
<div>
<h2 style="margin:0;">Blog Posts</h2>
<p style="color:#888;font-size:.8rem;">Manage all articles</p>
</div>
<a href="add-blog.php" class="btn">
<i class="fas fa-plus"></i> Write New
</a>
</div>

<div class="table-wrapper">
<table class="admin-table">
<thead>
<tr>
<th width="90">Cover</th>
<th>Article</th>
<th>Status</th>
<th>Date</th>
<th width="120" style="text-align:right;">Actions</th>
</tr>
</thead>

<tbody>
<?php if(mysqli_num_rows($result)>0): ?>
<?php while($row=mysqli_fetch_assoc($result)): ?>
<tr>

<td>
<img src="/uploads/<?= htmlspecialchars($row['image']) ?>"
onerror="this.src='https://via.placeholder.com/100'"
class="blog-thumb">
</td>

<td>
<strong><?= htmlspecialchars($row['title']) ?></strong>
<div class="slug">
/blog/<?= htmlspecialchars($row['slug'] ?: 'no-slug') ?>
</div>
</td>

<td>
<span class="badge <?= $row['status']?'badge-success':'badge-warning' ?>">
<?= $row['status']?'Published':'Draft' ?>
</span>
</td>

<td>
<span style="font-size:.8rem;color:#888">
<?= date('M d, Y',strtotime($row['created_at'])) ?>
</span>
</td>

<td>
<div style="display:flex;gap:8px;justify-content:flex-end;">
<a href="edit-blog.php?id=<?= $row['id'] ?>" class="btn-action">
<i class="fas fa-edit"></i>
</a>

<a href="actions/delete_blog.php?id=<?= $row['id'] ?>&csrf_token=<?php echo csrf_token(); ?>"
onclick="return confirm('Delete permanently?')"
class="btn-action btn-delete">
<i class="fas fa-trash"></i>
</a>
</div>
</td>

</tr>
<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="5" style="text-align:center;padding:60px;color:#aaa;">
No blog posts yet
</td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>

</main>
</body>
</html>
