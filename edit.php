<?php
require 'db.php';
require_once 'auth.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();

if (!$post) {
    echo "Bejegyzés nem található!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category_id = $_POST['category_id'] ?? null;

    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, category_id = ? WHERE id = ?");
    $stmt->execute([$title, $content, $category_id, $id]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Szerkesztés</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h2>Szerkesztés</h2>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Cím</label>
      <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Tartalom</label>
      <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($post['content']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Kategória</label>
      <select name="category_id" class="form-select" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $post['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Mentés</button>
    <a href="index.php" class="btn btn-secondary">Mégse</a>
  </form>
</div>
</body>
</html>
