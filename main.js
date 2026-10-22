"use strict";

const form = document.querySelector("#form-barang");
const tabel = document.querySelector("#daftar-barang");
const pencarian = document.querySelector("#cari-barang");
const pesan = document.querySelector("#pesan");
const infoJumlah = document.querySelector("#info-jumlah");

function tampilkanPesan(teks, jenis) {
  pesan.textContent = teks;
  pesan.className = `message ${jenis}`;
}

function perbaruiJumlah() {
  const total = Array.from(tabel.rows).filter((baris) => !baris.hidden).length;

  infoJumlah.textContent = `Menampilkan ${total} data barang.`;
}

function terapkanPencarian() {
  const kataKunci = pencarian.value.trim().toLowerCase();

  Array.from(tabel.rows).forEach(function (baris) {
    baris.hidden = !baris.textContent.toLowerCase().includes(kataKunci);
  });

  perbaruiJumlah();
}

form.addEventListener("submit", function (event) {
  const kode = document.querySelector("#kode").value.trim().toUpperCase();
  const jumlahTeks = document.querySelector("#jumlah").value;
  const hargaTeks = document.querySelector("#harga").value;
  const jumlah = Number(jumlahTeks);
  const harga = Number(hargaTeks);

  if (!/^BRG-\d+$/.test(kode)) {
    event.preventDefault();
    tampilkanPesan("Kode harus berformat BRG- diikuti angka.", "error");
    return;
  }

  const kodeDuplikat = Array.from(tabel.rows).some(function (baris) {
    return baris.cells[0].textContent.trim().toUpperCase() === kode;
  });

  if (kodeDuplikat) {
    event.preventDefault();
    tampilkanPesan(
      "Kode sudah ada pada tabel contoh. Gunakan kode lain.",
      "error",
    );
    return;
  }

  if (!Number.isInteger(jumlah) || jumlah <= 0) {
    event.preventDefault();
    tampilkanPesan("Jumlah harus berupa bilangan bulat lebih dari 0.", "error");
    return;
  }

  if (!Number.isInteger(harga) || harga <= 0) {
    event.preventDefault();
    tampilkanPesan(
      "Harga satuan harus berupa bilangan bulat lebih dari 0.",
      "error",
    );
  }
});

pencarian.addEventListener("input", terapkanPencarian);

form.addEventListener("reset", function () {
  pesan.textContent = "";
  pesan.className = "message";
});

perbaruiJumlah();
