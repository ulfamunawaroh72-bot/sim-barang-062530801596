"use strict";

const form = document.querySelector("#form-barang");
const tabel = document.querySelector("#daftar-barang");
const pencarian = document.querySelector("#cari-barang");
const pesan = document.querySelector("#pesan-form");
const infoJumlah = document.querySelector("#info-jumlah");

function tampilkanPesan(teks, jenis = "") {
  if (!pesan) return;

  pesan.textContent = teks;
  pesan.className = jenis ? `message ${jenis}` : "message";
}

function barisData() {
  if (!tabel) return [];

  return Array.from(tabel.rows).filter((baris) => {
    return baris.cells.length > 1;
  });
}

function perbaruiJumlah() {
  if (!infoJumlah) return;

  const baris = barisData();
  const total = baris.filter((baris) => !baris.hidden).length;

  infoJumlah.textContent = `Menampilkan ${total} data barang.`;
}

function terapkanPencarian() {
  if (!pencarian || !tabel) return;

  const kataKunci = pencarian.value.trim().toLowerCase();

  Array.from(tabel.rows).forEach((baris) => {
    const barisPlaceholder = baris.cells.length === 1;

    if (barisPlaceholder) {
      baris.hidden = kataKunci !== "";
      return;
    }

    baris.hidden = !baris.textContent.toLowerCase().includes(kataKunci);
  });

  perbaruiJumlah();
}

if (form) {
  form.addEventListener("submit", function (event) {
    const kode = document.querySelector("#kode").value.trim().toUpperCase();
    const jumlah = Number(document.querySelector("#jumlah").value);
    const harga = Number(document.querySelector("#harga").value);

    if (!/^BRG-\d+$/.test(kode)) {
      event.preventDefault();
      tampilkanPesan(
        "Kode harus berformat BRG- diikuti angka, contoh BRG-004.",
        "error",
      );
      return;
    }

    const kodeDuplikat = barisData().some((baris) => {
      return baris.cells[0].textContent.trim().toUpperCase() === kode;
    });

    if (kodeDuplikat) {
      event.preventDefault();
      tampilkanPesan(
        "Kode barang sudah ada di daftar. Gunakan kode lain.",
        "error",
      );
      return;
    }

    if (!Number.isInteger(jumlah) || jumlah <= 0) {
      event.preventDefault();
      tampilkanPesan(
        "Jumlah harus berupa bilangan bulat lebih dari 0.",
        "error",
      );
      return;
    }

    if (!Number.isInteger(harga) || harga < 0) {
      event.preventDefault();
      tampilkanPesan("Harga harus berupa bilangan bulat minimal 0.", "error");
      return;
    }

    tampilkanPesan("Data sedang diproses...");
    // Formulir tetap dikirim ke simpan.php.
  });

  form.addEventListener("reset", function () {
    tampilkanPesan("");
  });
}

if (pencarian) {
  pencarian.addEventListener("input", terapkanPencarian);
}

perbaruiJumlah();
