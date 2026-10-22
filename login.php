
<?php
session_start();

if (isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'password123') {
        session_regenerate_id(true);

        $_SESSION['username'] = 'admin';
        $_SESSION['role'] = 'Admin';

        header('Location: index.php');
        exit;
    }

    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIM-Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <h1>SIM-Barang</h1>
        <p>Sistem Informasi Manajemen Inventaris Barang Jurusan</p>
    </div>
</header>

<main class="container">
    <section class="panel">
        <h2>Login Pengguna</h2>

        <?php if ($error !== ''): ?>
            <p class="message error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       autocomplete="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>
    </section>
</main>

<footer class="site-footer">
    <p>&copy; 2026 SIM-Barang</p>
</footer>

</body>
</html>
