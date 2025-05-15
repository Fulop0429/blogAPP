<?php
require 'db.php';
require_once 'auth.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "Hibás kérés: nincs poszt ID megadva.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isLoggedIn()) {
    $content = trim($_POST['comment']);
    if ($content !== '') {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$id, currentUserId(), $content]);
        header("Location: post.php?id=$id");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_action']) && isLoggedIn()) {
    if ($_POST['like_action'] === 'like') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO likes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$id, currentUserId()]);
    } elseif ($_POST['like_action'] === 'unlike') {
        $stmt = $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$id, currentUserId()]);
    }
    header("Location: post.php?id=$id");
    exit;
}

$stmt = $pdo->prepare("
  SELECT posts.*, categories.name AS category_name
  FROM posts
  LEFT JOIN categories ON posts.category_id = categories.id
  WHERE posts.id = ?
");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    echo "A poszt nem található.";
    exit;
}

$stmt = $pdo->prepare("
  SELECT comments.*, users.username
  FROM comments
  JOIN users ON comments.user_id = users.id
  WHERE post_id = ?
  ORDER BY created_at DESC
");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->execute([$id]);
$totalLikes = $stmt->fetchColumn();

$userLiked = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$id, currentUserId()]);
    $userLiked = $stmt->fetchColumn() > 0;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($post['title']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <a href="index.php" class="btn btn-sm btn-outline-secondary mb-3">← Vissza</a>

  <h1><?= htmlspecialchars($post['title']) ?></h1>
  <p>
    <small class="text-muted"><?= $post['created_at'] ?></small>
    <?php if ($post['category_name']): ?>
      <span class="badge bg-primary ms-2"><?= htmlspecialchars($post['category_name']) ?></span>
    <?php endif; ?>
  </p>

  <hr>
  <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

  <form method="POST" class="mb-4">
    <?php if (isLoggedIn()): ?>
      <input type="hidden" name="like_action" value="<?= $userLiked ? 'unlike' : 'like' ?>">
      <button class="btn btn-sm <?= $userLiked ? 'btn-danger' : 'btn-outline-primary' ?>" type="submit">
        ❤️ <?= $userLiked ? 'Unlike' : 'Like' ?> (<?= $totalLikes ?>)
      </button>
    <?php else: ?>
      <span class="text-muted">❤️ <?= $totalLikes ?> like</span> – <a href="auth_form.php">Bejelentkezés</a>
    <?php endif; ?>
  </form>

  <h4>Hozzászólások</h4>
  <?php foreach ($comments as $comment): ?>
    <div class="border rounded p-2 mb-2">
      <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
      <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
      <small class="text-muted"><?= $comment['created_at'] ?></small>
    </div>
  <?php endforeach; ?>

  <?php if (isLoggedIn()): ?>
    <form method="POST" class="mt-3">
      <textarea name="comment" rows="3" class="form-control" placeholder="Írd meg a véleményed..." required></textarea>
      <button type="submit" class="btn btn-primary mt-2">Hozzászólás</button>
    </form>
  <?php else: ?>
    <p><a href="auth_form.php">Jelentkezz be</a>, hogy hozzászólhass.</p>
  <?php endif; ?>
</div>
</body>
</html>
