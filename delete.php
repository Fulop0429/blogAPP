<?php
require 'db.php';
require_once 'auth.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;
?>