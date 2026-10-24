
<?php
session_start();
require_once 'koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['username'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

// Hanya menerima permintaan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Ambil kode barang
$kode = strtoupper(trim($_POST['kode'] ?? ''));

if ($kode === '') {
    $_SESSION['flash_message'] = 'Kode barang tidak valid.';
    $_SESSION['flash_type'] = 'error';

    header('Location: index.php#daftar-inventaris');
    exit;
}

try {
    // Cari barang berdasarkan kode
    $stmt = $pdo->prepare(
        'SELECT id_barang, nama_barang
         FROM barang
         WHERE kode_barang = ?'
    );
    $stmt->execute([$kode]);
    $barang = $stmt->fetch();

    if (!$barang) {
        $_SESSION['flash_message'] = 'Barang tidak ditemukan.';
        $_SESSION['flash_type'] = 'error';

        header('Location: index.php#daftar-inventaris');
        exit;
    }

    // Periksa riwayat barang masuk
    $cekMasuk = $pdo->prepare(
        'SELECT COUNT(*)
         FROM barang_masuk
         WHERE id_barang = ?'
    );
    $cekMasuk->execute([$barang['id_barang']]);
    $adaRiwayatMasuk = (int) $cekMasuk->fetchColumn();

    // Periksa riwayat barang keluar
    $cekKeluar = $pdo->prepare(
        'SELECT COUNT(*)
         FROM barang_keluar
         WHERE id_barang = ?'
    );
    $cekKeluar->execute([$barang['id_barang']]);
    $adaRiwayatKeluar = (int) $cekKeluar->fetchColumn();

    if ($adaRiwayatMasuk > 0 || $adaRiwayatKeluar > 0) {
        $_SESSION['flash_message'] =
            'Barang ' . $barang['nama_barang'] .
            ' tidak dapat dihapus karena masih memiliki riwayat barang masuk atau keluar.';
        $_SESSION['flash_type'] = 'error';

        header('Location: index.php#daftar-inventaris');
        exit;
    }

    // Hapus barang jika tidak memiliki riwayat
    $hapus = $pdo->prepare(
        'DELETE FROM barang WHERE id_barang = ?'
    );
    $hapus->execute([$barang['id_barang']]);

    $_SESSION['flash_message'] =
        'Barang ' . $barang['nama_barang'] .
        ' berhasil dihapus.';
    $_SESSION['flash_type'] = 'success';

} catch (PDOException $e) {
    $_SESSION['flash_message'] =
        'Barang gagal dihapus. Periksa hubungan data di database.';
    $_SESSION['flash_type'] = 'error';
}

header('Location: index.php#daftar-inventaris');
exit;
