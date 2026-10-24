
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

// Ambil kode barang
$kode = trim($_GET['kode'] ?? $_POST['kode'] ?? '');

if ($kode === '') {
    header('Location: index.php');
    exit;
}

// Ambil data barang
$stmt = $pdo->prepare(
    'SELECT * FROM barang WHERE kode_barang = ?'
);
$stmt->execute([$kode]);
$barang = $stmt->fetch();

if (!$barang) {
    header('Location: index.php');
    exit;
}

// Ambil daftar kategori
$stmtKategori = $pdo->query(
    'SELECT id_kategori, nama_kategori
     FROM kategori
     ORDER BY nama_kategori'
);
$kategoriList = $stmtKategori->fetchAll();

$pesan = '';

// Proses perubahan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');

    $kategori = filter_var(
        $_POST['kategori'] ?? '',
        FILTER_VALIDATE_INT
    );

    $jumlahInput = trim($_POST['jumlah'] ?? '');
    $kondisi = trim($_POST['kondisi'] ?? '');
    $hargaInput = trim($_POST['harga'] ?? '');

    if ($nama === '') {
        $pesan = 'Nama barang wajib diisi.';
    } elseif (
        $kategori === false ||
        $kategori === null ||
        $kategori < 1
    ) {
        $pesan = 'Silakan pilih kategori yang valid.';
    } elseif (
        $jumlahInput === '' ||
        !ctype_digit($jumlahInput)
    ) {
        $pesan = 'Jumlah harus berupa bilangan bulat minimal 0.';
    } elseif (
        $hargaInput === '' ||
        !ctype_digit($hargaInput)
    ) {
        $pesan = 'Harga harus berupa bilangan bulat minimal 0.';
    } elseif (!in_array(
        $kondisi,
        ['Baik', 'Rusak Ringan', 'Rusak Berat'],
        true
    )) {
        $pesan = 'Kondisi barang tidak valid.';
    } else {
        // Periksa kategori
        $cekKategori = $pdo->prepare(
            'SELECT id_kategori
             FROM kategori
             WHERE id_kategori = ?'
        );
        $cekKategori->execute([$kategori]);

        if (!$cekKategori->fetch()) {
            $pesan = 'Kategori tidak ditemukan.';
        } else {
            try {
                $update = $pdo->prepare(
                    'UPDATE barang
                     SET nama_barang = ?,
                         id_kategori = ?,
                         jumlah = ?,
                         kondisi = ?,
                         harga_satuan = ?
                     WHERE kode_barang = ?'
                );

                $update->execute([
                    $nama,
                    $kategori,
                    (int) $jumlahInput,
                    $kondisi,
                    (int) $hargaInput,
                    $kode
                ]);

                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $pesan = 'Data gagal diperbarui. Periksa kembali database.';
            }
        }
    }

    // Pertahankan input jika validasi gagal
    $barang['nama_barang'] = $nama;
    $barang['jumlah'] = $jumlahInput;
    $barang['kondisi'] = $kondisi;
    $barang['harga_satuan'] = $hargaInput;

    if (is_int($kategori) && $kategori > 0) {
        $barang['id_kategori'] = $kategori;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Barang | SIM-Barang</title>

    <link rel="stylesheet" href="style.css">

    <style>
        /* Tombol edit dengan bentuk yang serasi */
        .form-actions .btn-primary,
        .form-actions .btn-kembali {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            border: none;
            border-radius: 7px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.5;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Tombol Simpan Perubahan */
        .form-actions .btn-primary {
            background-color: #1e416b;
            color: #ffffff;
        }

        .form-actions .btn-primary:hover {
            background-color: #142d4e;
        }

        /* Tombol Kembali: bentuk sama, warna berbeda */
        .form-actions .btn-kembali {
            background-color: #64748b;
            color: #ffffff;
        }

        .form-actions .btn-kembali:hover {
            background-color: #475569;
        }

        .form-actions .btn-primary:focus-visible,
        .form-actions .btn-kembali:focus-visible {
            outline: 2px solid #93c5fd;
            outline-offset: 3px;
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container">
        <h1>SIM-Barang</h1>
        <p>Edit Data Inventaris Barang</p>
    </div>
</header>

<nav class="navbar">
    <div class="container">
        <a href="index.php">Dashboard</a>

        <span class="user-info">
            Login: <?= h($_SESSION['username']) ?>
            (<?= h($_SESSION['role']) ?>)
        </span>
    </div>
</nav>

<main class="container">
    <section class="form-section">

        <h2>Edit Data Barang</h2>
        <p>Perbarui informasi barang sesuai kebutuhan.</p>

        <?php if ($pesan !== ''): ?>
            <p class="message error" role="alert">
                <?= h($pesan) ?>
            </p>
        <?php endif; ?>

        <form action="edit.php" method="post">

            <input
                type="hidden"
                name="kode"
                value="<?= h($barang['kode_barang']) ?>"
            >

            <div class="form-group">
                <label for="kode">Kode Barang</label>
                <input
                    type="text"
                    id="kode"
                    value="<?= h($barang['kode_barang']) ?>"
                    readonly
                >
            </div>

            <div class="form-group">
                <label for="nama">Nama Barang</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="<?= h($barang['nama_barang']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kategori">Kategori Barang</label>
                <select id="kategori" name="kategori" required>
                    <?php foreach ($kategoriList as $kat): ?>
                        <option
                            value="<?= (int) $kat['id_kategori'] ?>"
                            <?= (int) $barang['id_kategori'] === (int) $kat['id_kategori']
                                ? 'selected'
                                : '' ?>
                        >
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
                    min="0"
                    step="1"
                    value="<?= h($barang['jumlah']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kondisi">Kondisi Barang</label>
                <select id="kondisi" name="kondisi" required>
                    <?php
                    $kondisiList = [
                        'Baik',
                        'Rusak Ringan',
                        'Rusak Berat'
                    ];

                    foreach ($kondisiList as $opsi):
                    ?>
                        <option
                            value="<?= h($opsi) ?>"
                            <?= $barang['kondisi'] === $opsi
                                ? 'selected'
                                : '' ?>
                        >
                            <?= h($opsi) ?>
                        </option>
                    <?php endforeach; ?>
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
                    value="<?= h($barang['harga_satuan']) ?>"
                    required
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Simpan Perubahan
                </button>

                <a href="index.php" class="btn-kembali">
                    &larr; Kembali
                </a>
            </div>

        </form>
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
