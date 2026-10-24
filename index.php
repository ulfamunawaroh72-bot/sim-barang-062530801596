
<?php
session_start();
require_once 'koneksi.php';

// Periksa login
if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

// Amankan output HTML
function h($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// Notifikasi dari proses hapus
$flashMessage = $_SESSION['flash_message'] ?? '';
$flashType = $_SESSION['flash_type'] ?? '';

unset($_SESSION['flash_message'], $_SESSION['flash_type']);

$pesan = '';
$jenisPesan = '';
$ringkasan = null;

// Proses tambah barang
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = strtoupper(trim($_POST['kode'] ?? ''));
    $nama = trim($_POST['nama'] ?? '');
    $kategori = filter_var(
        $_POST['kategori'] ?? '',
        FILTER_VALIDATE_INT
    );
    $jumlahInput = trim($_POST['jumlah'] ?? '');
    $kondisi = trim($_POST['kondisi'] ?? '');
    $hargaInput = trim($_POST['harga'] ?? '');

    if (!preg_match('/^BRG-\d+$/', $kode)) {
        $pesan = 'Kode harus berformat BRG-001.';
        $jenisPesan = 'error';
    } elseif ($nama === '') {
        $pesan = 'Nama barang wajib diisi.';
        $jenisPesan = 'error';
    } elseif (
        $kategori === false ||
        $kategori === null ||
        $kategori < 1
    ) {
        $pesan = 'Silakan pilih kategori barang.';
        $jenisPesan = 'error';
    } elseif (
        $jumlahInput === '' ||
        !ctype_digit($jumlahInput) ||
        (int) $jumlahInput < 1
    ) {
        $pesan = 'Jumlah harus berupa bilangan bulat lebih dari 0.';
        $jenisPesan = 'error';
    } elseif (
        $hargaInput === '' ||
        !ctype_digit($hargaInput)
    ) {
        $pesan = 'Harga harus berupa angka bulat minimal 0.';
        $jenisPesan = 'error';
    } elseif (!in_array(
        $kondisi,
        ['Baik', 'Rusak Ringan', 'Rusak Berat'],
        true
    )) {
        $pesan = 'Kondisi barang tidak valid.';
        $jenisPesan = 'error';
    } else {
        $jumlah = (int) $jumlahInput;
        $harga = (int) $hargaInput;

        try {
            $cekKategori = $pdo->prepare(
                'SELECT id_kategori
                 FROM kategori
                 WHERE id_kategori = ?'
            );
            $cekKategori->execute([$kategori]);

            if (!$cekKategori->fetch()) {
                $pesan = 'Kategori tidak ditemukan.';
                $jenisPesan = 'error';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO barang
                    (kode_barang, nama_barang, id_kategori,
                     jumlah, kondisi, harga_satuan)
                    VALUES (?, ?, ?, ?, ?, ?)'
                );

                $stmt->execute([
                    $kode,
                    $nama,
                    $kategori,
                    $jumlah,
                    $kondisi,
                    $harga
                ]);

                $ringkasan = [
                    'kode' => $kode,
                    'nama' => $nama,
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'total' => $jumlah * $harga
                ];

                $pesan = 'Data barang berhasil ditambahkan.';
                $jenisPesan = 'success';
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $pesan = 'Kode barang sudah digunakan.';
            } else {
                $pesan = 'Data gagal disimpan. Periksa kembali database.';
            }

            $jenisPesan = 'error';
        }
    }
}

// Ambil kategori
$stmtKategori = $pdo->query(
    'SELECT id_kategori, nama_kategori
     FROM kategori
     ORDER BY nama_kategori'
);
$daftarKategori = $stmtKategori->fetchAll();

// Ambil barang dan nama kategori
$sqlBarang = "SELECT
                b.id_barang,
                b.kode_barang,
                b.nama_barang,
                b.id_kategori,
                b.jumlah,
                b.kondisi,
                b.harga_satuan,
                k.nama_kategori
              FROM barang b
              LEFT JOIN kategori k
                ON b.id_kategori = k.id_kategori
              ORDER BY b.id_barang DESC";

$stmtBarang = $pdo->query($sqlBarang);
$daftarBarang = $stmtBarang->fetchAll();

// Hitung ringkasan dashboard
$totalJenis = count($daftarBarang);
$totalStok = 0;
$totalAset = 0;
$totalBarangBaik = 0;

foreach ($daftarBarang as $item) {
    $totalStok += (int) $item['jumlah'];
    $totalAset +=
        (int) $item['jumlah'] * (float) $item['harga_satuan'];

    if ($item['kondisi'] === 'Baik') {
        $totalBarangBaik++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIM-Barang | Sistem Informasi Barang</title>
    <link rel="stylesheet" href="style.css">
    <script src="main.js" defer></script>
</head>
<body>

<header class="header">
    <div class="container">
        <h1>SIM-Barang</h1>
        <p>Sistem Informasi Manajemen Inventaris Barang</p>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <a href="index.php">Dashboard</a>
        <a href="#daftar-inventaris">Data Barang</a>
        <a href="#tambah-barang">Tambah Barang</a>

        <span class="user-info">
            Login: <?= h($_SESSION['username']) ?>
            (<?= h($_SESSION['role']) ?>)
        </span>

        <a href="logout.php">Logout</a>
    </div>
</nav>

<main class="container">

    <!-- DASHBOARD -->
    <section class="dashboard">
        <h2>Dashboard Inventaris</h2>
        <p>Ringkasan data inventaris barang saat ini.</p>

        <div class="summary-grid">
            <article class="summary-card">
                <h3>Total Jenis Barang</h3>
                <p><?= $totalJenis ?></p>
            </article>

            <article class="summary-card">
                <h3>Total Stok Barang</h3>
                <p><?= $totalStok ?></p>
            </article>

            <article class="summary-card">
                <h3>Barang Kondisi Baik</h3>
                <p><?= $totalBarangBaik ?></p>
            </article>

            <article class="summary-card">
                <h3>Estimasi Nilai Aset</h3>
                <p>Rp <?= number_format($totalAset, 0, ',', '.') ?></p>
            </article>
        </div>
    </section>

    <!-- FORM TAMBAH BARANG -->
    <section id="tambah-barang" class="form-section">
        <h2>Tambah Data Barang</h2>

        <?php if ($pesan !== ''): ?>
            <p class="message <?= h($jenisPesan) ?>" role="status">
                <?= h($pesan) ?>
            </p>
        <?php endif; ?>

        
<?php if ($ringkasan !== null): ?>
    <div class="result-card">
        <h3>Ringkasan Barang yang Ditambahkan</h3>

        <div class="result-row">
            <span>Kode Barang</span>
            <strong><?= h($ringkasan['kode']) ?></strong>
        </div>

        <div class="result-row">
            <span>Nama Barang</span>
            <strong><?= h($ringkasan['nama']) ?></strong>
        </div>

        <div class="result-row">
            <span>Jumlah Barang</span>
            <strong><?= (int) $ringkasan['jumlah'] ?> unit</strong>
        </div>

        <div class="result-row">
            <span>Harga Satuan</span>
            <strong>Rp <?= number_format((float) $ringkasan['harga'], 0, ',', '.') ?></strong>
        </div>

        <div class="result-total">
            <span>Total Estimasi</span>
            <strong>Rp <?= number_format((float) $ringkasan['total'], 0, ',', '.') ?></strong>
        </div>
    </div>
<?php endif; ?>


        <form id="form-barang" action="index.php#tambah-barang" method="post">
            <div class="form-group">
                <label for="kode">Kode Barang</label>
                <input
                    type="text"
                    id="kode"
                    name="kode"
                    placeholder="Contoh: BRG-007"
                    pattern="BRG-[0-9]+"
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

                    <?php foreach ($daftarKategori as $kat): ?>
                        <option value="<?= (int) $kat['id_kategori'] ?>">
                            <?= h($kat['nama_kategori']) ?>
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
                    placeholder="Masukkan jumlah"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kondisi">Kondisi Barang</label>
                <select id="kondisi" name="kondisi" required>
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
                    placeholder="Contoh: 100000"
                    required
                >
            </div>

            <p id="pesan-form" class="message" role="status"></p>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Simpan Barang
                </button>

                <button type="reset" class="btn-secondary">
                    Reset
                </button>
            </div>
        </form>
    </section>

    <!-- TABEL INVENTARIS -->
    <section id="daftar-inventaris" class="table-section">
        <h2>Daftar Inventaris Barang</h2>

        <?php if ($flashMessage !== ''): ?>
            <p class="message <?= h($flashType) ?>" role="status">
                <?= h($flashMessage) ?>
            </p>
        <?php endif; ?>

        <div class="search-box">
            <label for="cari-barang">Cari Barang</label>
            <input
                type="search"
                id="cari-barang"
                placeholder="Cari kode, nama, atau kategori..."
            >
            <p id="info-jumlah">
                Menampilkan <?= $totalJenis ?> data barang.
            </p>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                        <th>Harga Satuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="daftar-barang">
                    <?php if (count($daftarBarang) > 0): ?>
                        <?php foreach ($daftarBarang as $barang): ?>
                            <tr>
                                <td><?= h($barang['kode_barang']) ?></td>
                                <td><?= h($barang['nama_barang']) ?></td>
                                <td><?= h($barang['nama_kategori'] ?? '-') ?></td>
                                <td><?= (int) $barang['jumlah'] ?></td>
                                <td><?= h($barang['kondisi']) ?></td>
                                <td>
                                    Rp <?= number_format(
                                        (float) $barang['harga_satuan'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a
                                            class="btn-primary"
                                            href="edit.php?kode=<?= urlencode($barang['kode_barang']) ?>"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="hapus.php"
                                            method="post"
                                            onsubmit="return confirm('Yakin ingin menghapus barang ini?')"
                                        >
                                            <input
                                                type="hidden"
                                                name="kode"
                                                value="<?= h($barang['kode_barang']) ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-secondary"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                Belum ada data barang. Silakan tambahkan barang.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<footer class="footer">
    <div class="container">
        <p>
            &copy; <?= date('Y') ?> SIM-Barang -
            Sistem Informasi Inventaris
        </p>
    </div>
</footer>

</body>
</html>
