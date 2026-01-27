# Perubahan Status Project Otomatis

## Ringkasan Perubahan

Sistem status project telah diubah dari manual menjadi otomatis berdasarkan aktivitas dan progress task.

## Logika Status Project

### 1. Status NEW (Baru)
- **Trigger**: Project baru dibuat
- **Kondisi**: Semua task masih berstatus `todo` atau belum ada task sama sekali

### 2. Status IN_PROGRESS (Sedang Berjalan)
- **Trigger**: 
  - Time tracking dimulai pada task (task berubah dari `todo` ke `in_progress`)
  - Ada minimal 1 task dengan status `in_progress` atau `review`
  - Ada task yang sudah `done` tapi belum semua selesai
- **Auto-update**: Ya, ketika task status berubah

### 3. Status DONE (Selesai)
- **Trigger**: Progress project mencapai 100% (semua task berstatus `done`)
- **Auto-update**: Ya, ketika task terakhir di-approve menjadi `done`

### 4. Status ON_HOLD (Ditunda)
- **Trigger**: Manager/Admin menekan button "Tunda Project"
- **Auto-update**: TIDAK, hanya bisa diubah manual via button
- **Efek**: Semua task yang belum selesai akan berubah status menjadi `on_hold`
- **Resume**: Ketika button "Lanjutkan Project" ditekan, project menjadi `in_progress` dan semua task `on_hold` kembali ke `todo`

## File yang Diubah

### 1. View Files
- **resources/views/projects/edit.blade.php**
  - Menghapus kolom status dari form edit project
  - Status tidak lagi bisa diubah manual via form

- **resources/views/projects/show.blade.php**
  - Menambahkan button "Tunda Project" / "Lanjutkan Project"
  - Button hanya muncul untuk Manager/Admin
  - CSS untuk button hold (orange) dan resume (green)

### 2. Controller Files
- **app/Http/Controllers/ProjectController.php**
  - Menambahkan method `toggleHold()` untuk handle button tunda/lanjutkan
  - Method ini mengubah status project dan task terkait

- **app/Http/Controllers/TimeTrackingController.php**
  - Menambahkan `checkAndUpdateStatusBasedOnTasks()` di method:
    - `start()` - ketika timer dimulai
    - `startFromTask()` - ketika timer dimulai dari task detail
    - `completeTask()` - ketika task diselesaikan

### 3. Model Files
- **app/Models/Project.php**
  - Update method `checkAndUpdateStatusBasedOnTasks()`:
    - Tidak auto-update jika status `on_hold`
    - Logika baru sesuai dengan requirement

### 4. Request Validation
- **app/Http/Requests/Project/UpdateProjectRequest.php**
  - Status field menjadi `nullable` (tidak required)
  - Menambahkan field `goals` ke validation

### 5. Routes
- **routes/web.php**
  - Menambahkan route `projects.toggle-hold` untuk button tunda/lanjutkan

## Testing Checklist

- [ ] Project baru dibuat → status `new`
- [ ] Time tracking dimulai → task status `in_progress`, project status `in_progress`
- [ ] Semua task selesai (done) → project status `done`
- [ ] Button "Tunda Project" → project dan task status `on_hold`
- [ ] Button "Lanjutkan Project" → project status `in_progress`, task status kembali `todo`
- [ ] Form edit project tidak memiliki kolom status
- [ ] Auto-update tidak terjadi ketika project `on_hold`
