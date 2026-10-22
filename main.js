const form = document.querySelector("#tambah-barang form");
const tabel = document.querySelector("#data-barang tbody");
const kolomCari = document.querySelector("#cari-barang");

if (form && tabel) {
  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const kode = document.querySelector("#kode").value.trim();
    const nama = document.querySelector("#nama").value.trim();
    const kategori = document.querySelector("#kategori").value;
    const jumlah = document.querySelector("#jumlah").value;
    const kondisi = document.querySelector("#kondisi").value;

    // Validasi kode: BRG- diikuti angka
    if (!/^BRG-\d+$/.test(kode)) {
      alert("Kode barang harus menggunakan format BRG-001.");
      return;
    }

    // Periksa kode duplikat
    const kodeSudahAda = Array.from(tabel.rows).some(function (baris) {
      return (
        baris.cells[0].textContent.trim().toUpperCase() === kode.toUpperCase()
      );
    });

    if (kodeSudahAda) {
      alert("Kode barang sudah digunakan. Gunakan kode lain.");
      return;
    }

    // Validasi jumlah
    if (
      jumlah === "" ||
      !Number.isFinite(Number(jumlah)) ||
      Number(jumlah) <= 0
    ) {
      alert("Jumlah barang harus berupa angka lebih dari 0.");
      return;
    }

    // Buat baris baru
    const baris = tabel.insertRow();
    [kode, nama, kategori, jumlah, kondisi].forEach(function (data) {
      const sel = baris.insertCell();
      sel.textContent = data;
    });

    form.reset();
    alert("Barang berhasil ditambahkan ke tabel!");
  });
}

// Fitur pencarian langsung
if (kolomCari && tabel) {
  kolomCari.addEventListener("input", function () {
    const kataKunci = kolomCari.value.toLowerCase();

    Array.from(tabel.rows).forEach(function (baris) {
      const teksBaris = baris.textContent.toLowerCase();
      baris.hidden = !teksBaris.includes(kataKunci);
    });
  });
}
