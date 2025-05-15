<?php require_once 'auth.php'; ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary" href="index.php">📘 MiniBlog</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link fw-medium" href="index.php">🏠 Főoldal</a>
        </li>
        <?php if (isAdmin()): ?>
          <li class="nav-item">
            <a class="nav-link fw-medium" href="#" @click.prevent="showForm = !showForm">✍️ Új bejegyzés</a>
          </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (isLoggedIn()): ?>
          <li class="nav-item me-2">
            <span class="text-muted small">👤 Üdv, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm btn-outline-secondary" href="logout.php">🚪 Kijelentkezés</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-sm btn-outline-primary" href="auth_form.php">🔐 Belépés / Regisztráció</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
