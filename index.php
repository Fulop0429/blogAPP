<?php
require 'db.php';
require_once 'auth.php';

// Lekérjük az összes kategóriát a szűrőhöz
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$selectedCategory = $_GET['category'] ?? '';

if ($selectedCategory) {
    $stmt = $pdo->prepare("
        SELECT posts.*, categories.name AS category_name
        FROM posts
        JOIN categories ON posts.category_id = categories.id
        WHERE category_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$selectedCategory]);
} else {
    $stmt = $pdo->query("
        SELECT posts.*, categories.name AS category_name
        FROM posts
        LEFT JOIN categories ON posts.category_id = categories.id
        ORDER BY created_at DESC
    ");
}
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Mini Blog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/alpinejs" defer></script>
</head>
<body>
<div class="container py-4" x-data="{ showForm: false }">
  <?php include 'navbar.php'; ?>


<div class="mb-4">
  <form method="get" class="d-flex align-items-center gap-3 flex-wrap">
    <label for="category" class="form-label fw-bold mb-0">📂 Szűrés kategóriára:</label>

    <div class="input-group" style="min-width: 250px;">
      <select name="category" id="category" class="form-select border-primary shadow-sm" onchange="this.form.submit()">
        <option value=""> Összes kategória </option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div> 

    <?php if ($selectedCategory): ?>
      <a href="index.php" class="btn btn-outline-secondary btn-sm">❌ Szűrés törlése</a>
    <?php endif; ?>
  </form>
</div>

  <?php if (isAdmin()): ?>

    <form x-show="showForm" action="create.php" method="POST" class="mb-4 border p-3 rounded" x-transition>
      <button class="btn btn-primary mb-3" @click="showForm = !showForm">
        <span x-show="showForm">➖ Bezár</span>
      </button>

      <input type="text" name="title" placeholder="Cím" class="form-control mb-2" required>
      <textarea name="content" rows="5" placeholder="Tartalom..." class="form-control mb-2" required></textarea>

      <select name="category_id" class="form-select mb-2" required>
        <option value="">Válassz kategóriát</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="btn btn-success">Mentés</button>
    </form>
  <?php endif; ?>

  <!-- Bejegyzések -->
  <hr>
  <?php foreach ($posts as $post): ?>
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
        <small class="text-muted"><?= $post['created_at'] ?></small>
        <?php if (!empty($post['category_name'])): ?>
          <span class="badge bg-primary ms-2"><?= htmlspecialchars($post['category_name']) ?></span>
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>...</p>
        <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-secondary">📖 Megnyitás</a>

        <?php if (isAdmin()): ?>
          <a href="edit.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">✏️ Szerkesztés</a>
          <a href="delete.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-danger"
             @click.prevent="if (confirm('Biztos törlöd?')) window.location.href = 'delete.php?id=<?= $post['id'] ?>'">🗑 Törlés</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
