<?php
require 'db.php';
require_once 'auth.php';;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($mode === 'register') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->execute([$username, $hashed]);
            $_SESSION['user'] = [
                'id' => $pdo->lastInsertId(),
                'username' => $username,
                'role' => 'user'
            ];
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $error = "A felhasználónév már foglalt!";
        }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header("Location: index.php");
            exit;
        } else {
            $error = "Hibás belépési adatok!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
  <meta charset="UTF-8">
  <title>Bejelentkezés / Regisztráció</title>
  <script src="https://unpkg.com/alpinejs" defer></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5" x-data="{ mode: 'login', showPass: false }">
  <h2 x-text="mode === 'login' ? 'Bejelentkezés' : 'Regisztráció'" class="mb-4"></h2>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" class="border p-4 rounded shadow-sm">
    <input type="hidden" name="mode" :value="mode">

    <div class="mb-3">
      <label class="form-label">Felhasználónév</label>
      <input type="text" name="username" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Jelszó</label>
      <div class="input-group">
        <input :type="showPass ? 'text' : 'password'" name="password" class="form-control" required>
        <button type="button" class="btn btn-outline-secondary" @click="showPass = !showPass" tabindex="-1">
          <span x-text="showPass ? '🙈' : '👁️'"></span>
        </button>
      </div>
    </div>

    <button type="submit" class="btn btn-primary w-100" x-text="mode === 'login' ? 'Belépés' : 'Regisztráció'"></button>
  </form>

  <div class="mt-3 text-center">
    <template x-if="mode === 'login'">
      <p>Még nincs fiókod? <a href="#" @click.prevent="mode = 'register'">Regisztrálj!</a></p>
    </template>
    <template x-if="mode === 'register'">
      <p>Van már fiókod? <a href="#" @click.prevent="mode = 'login'">Lépj be!</a></p>
    </template>
  </div>
</div>
</body>
</html>