
<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$kode = strtoupper(trim($_POST['kode'] ?? ''));
$nama = trim($_POST['nama'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$kondisi = trim($_POST['kondisi'] ?? '');

$jumlah = filter_var($_POST['jumlah'] ?? null, FILTER_VALIDATE_INT);
$harga = filter_var($_POST['harga'] ?? null, FILTER_VALIDATE_INT);

$errors = [];

if (!preg_match('/^BRG-\d+$/', $kode)) {
    $errors[] = 'Kode harus berformat BRG- diikuti angka.';
}

if ($nama === '' || $kategori === '' || $kondisi === '') {
    $errors[] = 'Nama, kategori, dan kondisi wajib diisi.';
}

if ($jumlah === false || $jumlah === null || $jumlah <= 0) {
    $errors[] = 'Jumlah harus berupa bilangan bulat lebih dari 0.';
}

if ($harga === false || $harga === null || $harga <= 0) {
    $errors[] = 'Harga satuan harus berupa bilangan bulat lebih dari 0.';
}

function h($nilai) {
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

$total = 0;

if (empty($errors)) {
    $total = $jumlah * $harga;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Barang - SIM-Barang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <h1>SIM-Barang</h1>
        <p>Ringkasan Data Barang</p>
    </div>
</header>

<main class="container">
    <section class="panel">
        <?php if (!empty($errors)): ?>
            <h2>Data Belum Valid</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <h2>Data Barang Berhasil Diproses</h2>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Kondisi</th>
                            <th>Harga Satuan</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= h($kode) ?></td>
                            <td><?= h($nama) ?></td>
                            <td><?= h($kategori) ?></td>
                            <td><?= number_format($jumlah, 0, ',', '.') ?></td>
                            <td><?= h($kondisi) ?></td>
                            <td>Rp <?= number_format($harga, 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($total, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p>
                <strong>Perhitungan:</strong>
                <?= number_format($jumlah, 0, ',', '.') ?>
                × Rp <?= number_format($harga, 0, ',', '.') ?>
                = Rp <?= number_format($total, 0, ',', '.') ?>
            </p>
        <?php endif; ?>

        <p><a href="index.html">Kembali ke Form Barang</a></p>
    </section>
</main>

<footer class="site-footer">
    <p>&copy; 2026 SIM-Barang</p>
</footer>

</body>
</html>
