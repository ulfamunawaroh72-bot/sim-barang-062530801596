
<?php

class Barang
{
    public string $kode;
    public string $nama;
    public int $jumlah;

    public function __construct(string $kode, string $nama, int $jumlah)
    {
        $this->kode = $kode;
        $this->nama = $nama;
        $this->jumlah = $jumlah;
    }

    public function tampilkanData(): string
    {
        return "Kode: {$this->kode}, "
            . "Nama: {$this->nama}, "
            . "Jumlah: {$this->jumlah}";
    }
}
