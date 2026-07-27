<?php
require_once 'config.php';
$res = $conn->query("SELECT id, title, slug, status FROM dynamic_pages ORDER BY id DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
