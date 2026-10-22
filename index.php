
<?php
session_start();
require_once 'koneksi.php';

// Pastikan pengguna sudah login.
if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$pesan = '';
$jenisPesan = '';
$ringkasan = null;

// Proses formulir di file index.php yang sama.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = strtoupper(trim($_POST['kode'] ?? ''));
    $nama = trim($_POST['nama'] ?? '');
    $kategori = filter_var(
        $_POST['kategori'] ?? null,
        FILTER_VALIDATE_INT
    );
    $jumlah = filter_var(
        $_POST['jumlah'] ?? null,
        FILTER_VALIDATE_INT
    );
    $harga = filter_var(
        $_POST['harga'] ?? null,
        FILTER_VALIDATE_INT
    );
    $kondisi = trim($_POST['kondisi'] ?? '');

    // Validasi data formulir.
    if (!preg_match('/^BRG-\d+$/', $kode)) {
        $pesan = 'Kode barang harus berformat BRG-004.';
    } elseif ($nama === '') {
        $pesan = 'Nama barang wajib diisi.';
    } elseif (
        $kategori === false ||
        $kategori === null ||
        $kategori <= 0
    ) {
        $pesan = 'Silakan pilih kategori barang.';
    } elseif (
        $jumlah === false ||
        $jumlah === null ||
        $jumlah <= 0
    ) {
        $pesan = 'Jumlah harus berupa bilangan bulat lebih dari 0.';
    } elseif (
        $harga === false ||
        $harga === null ||
        $harga < 0
    ) {
        $pesan = 'Harga satuan tidak boleh negatif.';
    } elseif (!in_array(
        $kondisi,
        ['Baik', 'Rusak Ringan', 'Rusak Berat'],
        true
    )) {
        $pesan = 'Kondisi barang tidak valid.';
    } else {
        try {
            // Pastikan kategori benar-benar tersedia.
            $stmtKategori = $pdo->prepare(
                'SELECT nama_kategori
                 FROM kategori
                 WHERE id_kategori = ?'
            );
            $stmtKategori->execute([$kategori]);
            $namaKategori = $stmtKategori->fetchColumn();

            if ($namaKategori === false) {
                $pesan = 'Kategori tidak ditemukan.';
            } else {
                // Hitung estimasi nilai aset.
                $totalEstimasi = $jumlah * $harga;

                // Simpan data barang ke database.
                $sql = 'INSERT INTO barang
                    (
                        kode_barang,
                        nama_barang,
                        id_kategori,
                        jumlah,
                        kondisi,
                        harga_satuan
                    )
                    VALUES (?, ?, ?, ?, ?, ?)';

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $kode,
                    $nama,
                    $kategori,
                    $jumlah,
                    $kondisi,
                    $harga
                ]);

                // Siapkan ringkasan untuk ditampilkan.
                $ringkasan = [
                    'kode' => $kode,
                    'nama' => $nama,
                    'kategori' => $namaKategori,
                    'jumlah' => $jumlah,
                    'kondisi' => $kondisi,
                    'harga' => $harga,
                    'total' => $totalEstimasi
                ];

                $pesan = 'Barang berhasil ditambahkan ke database!';
                $jenisPesan = 'success';
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $pesan = 'Kode barang sudah digunakan.';
            } else {
                $pesan = 'Gagal menyimpan barang. Periksa koneksi dan struktur database.';
            }
            $jenisPesan = 'error';
        }
    }

    if ($ringkasan === null && $jenisPesan === '') {
        $jenisPesan = 'error';
    }
}

// Ambil kategori dari database.
$kategoriList = $pdo->query(
    'SELECT id_kategori, nama_kategori
     FROM kategori
     ORDER BY nama_kategori'
)->fetchAll();

// Ambil seluruh data barang untuk tabel inventaris.
$sqlBarang = 'SELECT
        b.kode_barang,
        b.nama_barang,
        k.nama_kategori,
        b.jumlah,
        b.kondisi,
        b.harga_satuan
    FROM barang b
    INNER JOIN kategori k
        ON b.id_kategori = k.id_kategori
    ORDER BY b.id_barang DESC';

$barangList = $pdo->query($sqlBarang)->fetchAll();

// Hitung jumlah jenis barang dan total stok.
$totalJenis = count($barangList);
$totalStok = 0;
$totalNilaiAset = 0;

foreach ($barangList as $item) {
    $totalStok += (int) $item['jumlah'];
    $totalNilaiAset +=
        (int) $item['jumlah'] * (float) $item['harga_satuan'];
}

// Fungsi untuk mengamankan output HTML.
function h($nilai): string
{
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
}
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
        <h2>Selamat Datang, <?= h($username) ?>!</h2>
        <p>Role pengguna: <?= h($role) ?></p>
    </section>

    <?php if ($pesan !== ''): ?>
        <section class="panel">
            <p class="message <?= h($jenisPesan) ?>" role="status">
                <?= h($pesan) ?>
            </p>
        </section>
    <?php endif; ?>

    <?php if ($ringkasan !== null): ?>
        <section class="panel" id="ringkasan">
            <h2>Ringkasan Pemrosesan Barang</h2>
            <p>Data berikut berhasil divalidasi dan disimpan.</p>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                            <th>Kondisi</th>
                            <th>Harga Satuan</th>
                            <th>Total Estimasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= h($ringkasan['kode']) ?></td>
                            <td><?= h($ringkasan['nama']) ?></td>
                            <td><?= h($ringkasan['kategori']) ?></td>
                            <td>
                                <?= number_format(
                                    $ringkasan['jumlah'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>
                            <td><?= h($ringkasan['kondisi']) ?></td>
                            <td>
                                Rp <?= number_format(
                                    $ringkasan['harga'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>
                            <td>
                                Rp <?= number_format(
                                    $ringkasan['total'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p>
                <strong>Perhitungan total estimasi:</strong>
                <?= number_format(
                    $ringkasan['jumlah'],
                    0,
                    ',',
                    '.'
                ) ?>
                × Rp <?= number_format(
                    $ringkasan['harga'],
                    0,
                    ',',
                    '.'
                ) ?>
                = <strong>Rp <?= number_format(
                    $ringkasan['total'],
                    0,
                    ',',
                    '.'
                ) ?></strong>
            </p>
        </section>
    <?php endif; ?>

    <section class="panel">
        <h2>Ringkasan Inventaris</h2>
        <p>Total jenis barang: <strong><?= $totalJenis ?></strong></p>
        <p>Total stok barang: <strong><?= $totalStok ?></strong></p>
        <p>
            Total estimasi nilai aset:
            <strong>Rp <?= number_format(
                $totalNilaiAset,
                0,
                ',',
                '.'
            ) ?></strong>
        </p>
    </section>

    <section class="panel" id="data-barang">
        <h2>Data Inventaris Barang</h2>
        <p>Daftar barang yang tersimpan di database.</p>

        <div class="search-group">
            <label for="cari-barang">Cari Barang</label>
            <input
                type="search"
                id="cari-barang"
                placeholder="Cari kode, nama, kategori, atau kondisi..."
            >
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
                        <th>Harga Satuan</th>
                    </tr>
                </thead>
                <tbody id="daftar-barang">
                    <?php if (count($barangList) > 0): ?>
                        <?php foreach ($barangList as $barang): ?>
                            <tr>
                                <td><?= h($barang['kode_barang']) ?></td>
                                <td><?= h($barang['nama_barang']) ?></td>
                                <td><?= h($barang['nama_kategori']) ?></td>
                                <td>
                                    <?= number_format(
                                        (int) $barang['jumlah'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>
                                <td><?= h($barang['kondisi']) ?></td>
                                <td>
                                    Rp <?= number_format(
                                        (float) $barang['harga_satuan'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                Belum ada data barang. Silakan tambahkan barang.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <p class="table-info" id="info-jumlah">
            Menampilkan <?= $totalJenis ?> data barang.
        </p>
    </section>

    <section class="panel" id="tambah-barang">
        <h2>Tambah Data Barang</h2>
        <p>
            Isi formulir berikut untuk menyimpan barang ke database.
        </p>

        <form
            id="form-barang"
            action="index.php#tambah-barang"
            method="post"
        >
            <div class="form-group">
                <label for="kode">Kode Barang</label>
                <input
                    type="text"
                    id="kode"
                    name="kode"
                    placeholder="Contoh: BRG-006"
                    pattern="BRG-[0-9]+"
                    title="Gunakan format BRG-006"
                    required
                >
            </div>

            <div class="form-group">
                <label for="nama">Nama Barang</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Masukkan nama barang"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kategori">Kategori Barang</label>
                <select id="kategori" name="kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategoriList as $kategori): ?>
                        <option value="<?= (int) $kategori['id_kategori'] ?>">
                            <?= h($kategori['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah Barang</label>
                <input
                    type="number"
                    id="jumlah"
                    name="jumlah"
                    min="1"
                    step="1"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kondisi">Kondisi Barang</label>
                <select id="kondisi" name="kondisi" required>
                    <option value="">-- Pilih Kondisi --</option>
                    <option value="Baik">Baik</option>
                    <option value="Rusak Ringan">Rusak Ringan</option>
                    <option value="Rusak Berat">Rusak Berat</option>
                </select>
            </div>

            <div class="form-group">
                <label for="harga">Harga Satuan (Rp)</label>
                <input
                    type="number"
                    id="harga"
                    name="harga"
                    min="0"
                    step="1"
                    required
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Tambah Barang
                </button>
                <button type="reset" class="btn-secondary">
                    Reset
                </button>
            </div>

            <p id="pesan-form" class="message" role="status"></p>
        </form>
    </section>

</main>

<footer class="site-footer">
    <p>&copy; 2026 SIM-Barang — Sistem Pengelolaan Inventaris Barang.</p>
</footer>

</body>
</html>
