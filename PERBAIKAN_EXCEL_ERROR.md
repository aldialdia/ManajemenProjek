# Perbaikan Error library Excel

## Masalah
Error `Interface "Maatwebsite\Excel\Concerns\WithMultipleSheets" not found` muncul karena `composer.json` Anda menggunakan versi library Excel yang sangat lawas (`^1.1`), padahal kode Anda menggunakan fitur dari versi 3.x. Versi 1.1 dirilis bertahun-tahun yang lalu dan tidak memiliki interface tersebut.

## Solusi yang Dilakukan
1.  Mengupdate `composer.json`:
    ```json
    "maatwebsite/excel": "^3.1"
    ```
2.  Menjalankan update dependencies:
    ```bash
    composer update maatwebsite/excel -W --ignore-platform-req=ext-gd
    ```
    (Flag `--ignore-platform-req=ext-gd` digunakan untuk melewati pengecekan ekstensi GD di terminal, yang seringkali menyebabkan kegagalan instalasi di Windows/XAMPP meskipun ekstensi tersebut sebenarnya ada).

## Hasil
Package berhasil di-upgrade dari **v1.1.5** ke **v3.1.67**.

## Tindakan Lanjutan (Opsional)
Jika nanti Anda mengalami error terkait gambar atau styling saat export Excel, pastikan ekstensi `gd` (php_gd.dll) sudah diaktifkan di konfigurasi `php.ini` server Anda.
