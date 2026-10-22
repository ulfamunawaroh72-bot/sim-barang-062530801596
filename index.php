
<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM-Barang | Inventaris Jurusan</title>
    <link rel="stylesheet" href="style.css">
    <script src="main.js" defer></script>
</head>
<body>

<header class="site-header">
    <div class="container">
        <h1>SIM-Barang</h1>
        <p>Sistem Informasi Manajemen Inventaris Barang Jurusan</p>
    </div>
</header>

<nav class="site-nav" aria-label="Navigasi utama">
    <div class="container nav-links">
        <a href="#beranda">Beranda</a>
        <a href="#data-barang">Data Barang</a>
        <a href="#tambah-barang">Tambah Barang</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container" id="beranda">

    <section class="panel">
        <h2>Selamat Datang, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>!</h2>
        <p>Role pengguna: <?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></p>
    </section>

    <section class="panel" id="data-barang">
        <h2>Data Inventaris Barang</h2>
        <p>Daftar barang contoh yang tersedia di lingkungan jurusan.</p>

        <div class="search-group">
            <label for="cari-barang">Cari Barang</label>
            <input type="search" id="cari-barang"
                   placeholder="Cari kode, nama, kategori, atau kondisi...">
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                    </tr>
                </thead>
                <tbody id="daftar-barang">
                    <tr>
                        <td>BRG-001</td>
                        <td>Komputer</td>
                        <td>Elektronik</td>
                        <td>10</td>
                        <td>Baik</td>
                    </tr>
                    <tr>
                        <td>BRG-002</td>
                        <td>Meja</td>
                        <td>Furniture</td>
                        <td>15</td>
                        <td>Baik</td>
                    </tr>
                    <tr>
                        <td>BRG-003</td>
                        <td>Printer</td>
                        <td>Elektronik</td>
                        <td>3</td>
                        <td>Perlu Perbaikan</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="table-info" id="info-jumlah"></p>
    </section>

    <section class="panel" id="tambah-barang">
        <h2>Tambah Data Barang</h2>
        <p>Isi formulir berikut untuk memproses data barang.</p>

        <form id="form-barang" action="simpan.php" method="post">
            <div class="form-group">
                <label for="kode">Kode Barang</label>
                <input type="text" id="kode" name="kode"
                       placeholder="Contoh: BRG-004"
                       pattern="BRG-[0-9]+"
                       required>
            </div>

            <div class="form-group">
                <label for="nama">Nama Barang</label>
                <input type="text" id="nama" name="nama"
                       placeholder="Masukkan nama barang" required>
            </div>

            <div class="form-group">
                <label for="kategori">Kategori Barang</label>
                <select id="kategori" name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Elektronik">Elektronik</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Alat Tulis">Alat Tulis</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Barang</label>
                <input type="number" id="jumlah" name="jumlah"
                       min="1" step="1" required>
            </div>

            <div class="form-group">
                <label for="kondisi">Kondisi Barang</label>
                <select id="kondisi" name="kondisi" required>
                    <option value="">-- Pilih Kondisi --</option>
                    <option value="Baik">Baik</option>
                    <option value="Rusak Ringan">Rusak Ringan</option>
                    <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="harga">Harga Satuan (Rp)</label>
                <input type="number" id="harga" name="harga"
                       min="1" step="1" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Proses Barang</button>
                <button type="reset" class="btn-secondary">Reset</button>
            </div>

            <p id="pesan" class="message" role="status"></p>
        </form>
    </section>

</main>

<footer class="site-footer">
    <p>&copy; 2026 SIM-Barang — Sistem Pengelolaan Inventaris Barang.</p>
</footer>

</body>
</html>
