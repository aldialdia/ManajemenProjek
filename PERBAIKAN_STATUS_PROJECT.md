# Perbaikan Status Project - 27 Januari 2026

## Ringkasan Perbaikan

Berikut adalah 3 perbaikan yang telah dilakukan sesuai permintaan:

---

## ✅ Perbaikan 1: Button Tunda Project - Posisi dan Tampilan

### Perubahan:
- **Posisi**: Button dipindahkan dari header actions ke sebelah kanan badge tanggal
- **Tampilan**: Diubah dari icon button menjadi text button
- **Text**: 
  - "Tunda Project" (orange) - untuk menunda project
  - "Lanjutkan Project" (green) - untuk melanjutkan project yang ditunda

### File yang Diubah:
- `resources/views/projects/show.blade.php`
  - Memindahkan button dari `info-card-actions` ke dalam `info-card-badges`
  - Mengubah dari icon button (`<i class="fas fa-pause">`) menjadi text button
  - Update CSS dari `.btn-hold-sm` dan `.btn-resume-sm` menjadi `.btn-toggle-hold`, `.btn-hold`, `.btn-resume`

### CSS Baru:
```css
.btn-toggle-hold {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    color: white;
}

.btn-hold {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    box-shadow: 0 2px 6px rgba(249, 115, 22, 0.3);
}

.btn-resume {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    box-shadow: 0 2px 6px rgba(34, 197, 94, 0.3);
}
```

---

## ✅ Perbaikan 2: Hapus ON_HOLD dari Laporan

### Perubahan:
Status `on_hold` dihapus dari semua chart dan statistik di laporan karena:
- Status `on_hold` hanya untuk **PROJECT**, bukan untuk **TASK**
- Ketika project ditunda, task otomatis menjadi `on_hold` sementara
- Ketika project dilanjutkan, task kembali ke status `todo`
- Status `on_hold` bersifat temporary dan tidak perlu ditampilkan di laporan

### File yang Diubah:
- `app/Http/Controllers/ReportController.php`
  - Menghapus `on_hold` dari array `$tasksByStatus` (2 tempat: method `index` dan `export`)
  - Menghapus `on_hold` dari array `$timeDistribution`
  - Menghapus case `'on_hold' => 'On Hold'` dari match expression di `$recentActivities` (2 tempat)

### Lokasi Perubahan:
1. **Line 133-163**: `$tasksByStatus` di method `index()` - hapus on_hold
2. **Line 186-193**: `$timeDistribution` di method `index()` - hapus on_hold
3. **Line 236-243**: Match expression di `$recentActivities` - hapus on_hold case
4. **Line 346-376**: `$tasksByStatus` di method `export()` - hapus on_hold
5. **Line 425-432**: Match expression di export `$recentActivities` - hapus on_hold case

### Catatan:
- Enum `TaskStatus::ON_HOLD` masih ada di `app/Enums/TaskStatus.php` karena digunakan secara internal
- Status ini hanya digunakan sementara ketika project di-hold
- Tidak ditampilkan di UI laporan untuk menghindari kebingungan

---

## ✅ Perbaikan 3: Pastikan Hanya Admin dan Manager yang Bisa Tunda Project

### Perubahan:
Memastikan authorization yang ketat untuk fitur tunda/lanjutkan project:

### File yang Diubah:

#### 1. **View** (`resources/views/projects/show.blade.php`)
```blade
@if(auth()->user()->isManagerInProject($project) || auth()->user()->isAdmin())
    <form action="{{ route('projects.toggle-hold', $project) }}" method="POST">
        ...
    </form>
@endif
```
- Button hanya muncul untuk Manager atau Admin
- Double check: `isManagerInProject()` OR `isAdmin()`

#### 2. **Controller** (`app/Http/Controllers/ProjectController.php`)
```php
// Check if user is manager/admin in project OR system admin
if (!$user->isManagerInProject($project) && !$user->isAdmin()) {
    abort(403, 'Hanya manager atau admin yang dapat menunda/melanjutkan project.');
}
```
- Authorization check di backend
- Mencegah akses langsung via URL/API
- Error 403 jika bukan manager atau admin

### Authorization Flow:
1. **Member biasa**: ❌ Tidak bisa lihat button, tidak bisa akses endpoint
2. **Manager project**: ✅ Bisa lihat button dan tunda/lanjutkan project
3. **System Admin**: ✅ Bisa lihat button dan tunda/lanjutkan semua project

---

## 📝 Testing Checklist

- [ ] Button "Tunda Project" muncul di sebelah kanan tanggal
- [ ] Button menggunakan text, bukan icon
- [ ] Button hanya muncul untuk Manager/Admin
- [ ] Member biasa tidak bisa lihat button
- [ ] Laporan tidak menampilkan status "On Hold" di chart
- [ ] Chart "Status Tugas" hanya menampilkan: Done, In Progress, Review, To Do
- [ ] Chart "Distribusi Waktu Kerja" tidak ada On Hold
- [ ] Authorization berfungsi: member tidak bisa akses endpoint toggle-hold

---

## 🎯 Hasil Akhir

### Button Tunda Project:
```
[Status Badge] [Tanggal Badge] [Tunda Project / Lanjutkan Project]
```

### Laporan - Status yang Ditampilkan:
- ✅ Done (Selesai)
- ✅ In Progress (Sedang Berjalan)
- ✅ Review (Pending Approval)
- ✅ To Do (Belum Dikerjakan)
- ❌ On Hold (DIHAPUS dari laporan)

### Authorization:
- ✅ Manager Project → Bisa tunda/lanjutkan
- ✅ System Admin → Bisa tunda/lanjutkan
- ❌ Member Biasa → Tidak bisa tunda/lanjutkan
