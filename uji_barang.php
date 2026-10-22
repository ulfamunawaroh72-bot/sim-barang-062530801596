
<?php
require_once __DIR__ . '/Barang.php';

$barang = new Barang('BRG-004', 'Laptop', 5);

echo '<h2>Hasil Pengujian Class Barang</h2>';

echo htmlspecialchars(
    $barang->tampilkanData(),
    ENT_QUOTES,
    'UTF-8'
);
