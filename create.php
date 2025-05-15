<?php
require 'db.php';
require_once 'auth.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO posts (title, content, category_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$title, $content, $category_id]);

    header("Location: index.php");
    exit;
}
?>
