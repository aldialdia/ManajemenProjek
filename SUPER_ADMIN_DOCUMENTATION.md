# Super Admin Role Implementation

## Overview
Sistem sekarang memiliki 2 level role:

### 1. **Super Admin** (System-level Role)
- Role yang berada di level sistem (tabel `users`)
- Memiliki akses penuh ke **SEMUA** project yang ada di sistem
- Dapat melakukan **SEMUA** operasi di semua project tanpa perlu ditambahkan sebagai member
- Tidak perlu ditambahkan ke project untuk bisa mengakses

**Capabilities:**
- ✅ Melihat semua project di sistem
- ✅ Membuat, mengedit, dan menghapus project apapun
- ✅ Mengelola team di semua project
- ✅ Mengakses dan memodifikasi tasks di semua project
- ✅ Upload/download documents di semua project
- ✅ Time tracking di semua project
- ✅ Mengelola comments dan attachments di semua project
- ✅ Mengubah status project apapun (termasuk on_hold)

### 2. **User** (Regular User)
- Role default untuk user biasa
- Hanya bisa mengakses project yang mereka ikuti
- Di dalam project, mereka memiliki role spesifik:
  - **Manager**: Pemilik project, full control dalam project tersebut
  - **Admin**: Dapat mengelola project (kecuali delete)
  - **Member**: Hanya bisa mengerjakan tasks yang di-assign

## Database Schema

### Migration
File: `database/migrations/2026_01_30_153001_add_system_role_to_users_table.php`

Menambahkan kolom `role` di tabel `users`:
```sql
ALTER TABLE users ADD COLUMN role ENUM('super_admin', 'user') DEFAULT 'user';
```

## Akun Super Admin Default

Seeder telah membuat akun super admin default:

**Email:** `superadmin@manajemenprojek.com`  
**Password:** `superadmin123`  
**Role:** `super_admin`

### Cara Login
1. Buka aplikasi
2. Login dengan credentials di atas
3. Super admin akan langsung bisa melihat dan mengakses semua project

## Implementation Details

### Enum: UserRole
File: `app/Enums/UserRole.php`

```php
enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case USER = 'user';
}
```

### Model Updates
File: `app/Models/User.php`

**New Methods:**
- `isSuperAdmin()`: Check if user is super admin
- `isAdmin()`: Alias untuk isSuperAdmin()

**Updated Methods:**
- `isManagerInProject()`: Super admin selalu dianggap manager
- `isAdminInProject()`: Super admin selalu dianggap admin
- `isMemberOfProject()`: Super admin selalu dianggap member

### Policy Updates
File: `app/Policies/ProjectPolicy.php`

Semua policy methods di-update untuk memberikan akses penuh ke super admin:
- `view()`: Super admin bisa view semua project
- `update()`: Super admin bisa update semua project
- `delete()`: Super admin bisa delete semua project
- `restore()`: Super admin bisa restore semua project
- `forceDelete()`: Super admin bisa force delete semua project
- `manageTeam()`: Super admin bisa manage team di semua project

### Controller Updates
File: `app/Http/Controllers/ProjectController.php`

- `index()`: Super admin melihat semua project
- `kanban()`: Super admin melihat semua project di kanban board
- `updateStatus()`: Super admin bisa update status semua project
- `toggleHold()`: Super admin bisa toggle hold semua project

### Dashboard Controller
File: `app/Http/Controllers/DashboardController.php`

Dashboard controller sekarang mendeteksi role user dan menampilkan dashboard yang berbeda:
- **Super Admin**: Melihat `dashboard-super-admin.blade.php` dengan system-wide overview
- **Regular User**: Melihat `dashboard.blade.php` dengan personal overview

## Super Admin Dashboard

Super Admin memiliki dashboard khusus yang berbeda dari user biasa, dengan fokus pada **monitoring dan manajemen sistem secara keseluruhan**.

### Features Dashboard Super Admin

#### 1. **System Health Alerts**
- 🚨 Notifikasi project yang terlambat
- ⚠️ Alert untuk tasks yang overdue
- ℹ️ Informasi tasks yang belum ditugaskan

#### 2. **System-wide Statistics**
- **Total Projects**: Dengan breakdown Active, On Hold, dan Completed
- **Total Tasks**: Dengan completion rate
- **Total Users**: Active users dan total clients

#### 3. **Visual Analytics**
- **Monthly Trends Chart**: Grafik trend project dan task 6 bulan terakhir
- **Project Status Distribution**: Pie chart distribusi status project

#### 4. **Recent Projects**
- List 8 project terbaru di sistem
- Menampilkan status, jumlah member, dan waktu dibuat

#### 5. **Top Active Users**
- Ranking 5 user paling aktif bulan ini
- Berdasarkan total jam kerja (time entries)
- Menampilkan avatar dan total jam

#### 6. **Projects Requiring Attention**
- List project yang memerlukan perhatian:
  - Project yang On Hold
  - Project yang terlambat (overdue)
- Highlight dengan warna merah untuk easy identification

### Dashboard Comparison

| Feature | Super Admin Dashboard | Regular User Dashboard |
|---------|----------------------|------------------------|
| **Focus** | System-wide overview | Personal tasks & projects |
| **Statistics** | All projects, all users | User's projects & tasks |
| **Projects** | All system projects | Only assigned projects |
| **Charts** | Monthly trends, status distribution | Weekly productivity, task distribution |
| **Alerts** | System health issues | Personal deadlines |
| **User Info** | Top active users | My tasks |
| **Color Theme** | Red (admin theme) | Purple/Blue (user theme) |


## Usage Examples

### Checking Super Admin in Code
```php
// Check if user is super admin
if (auth()->user()->isSuperAdmin()) {
    // Super admin specific logic
}

// Check if user can manage project (works for both super admin and project manager)
if (auth()->user()->isManagerInProject($project)) {
    // Manager or super admin logic
}
```

### Creating New Super Admin
```php
use App\Enums\UserRole;
use App\Models\User;

User::create([
    'name' => 'New Super Admin',
    'email' => 'newsuperadmin@example.com',
    'status' => 'active',
    'role' => UserRole::SUPER_ADMIN,
    'password' => bcrypt('password123'),
]);
```

### Changing User to Super Admin
```php
$user = User::find($userId);
$user->role = UserRole::SUPER_ADMIN;
$user->save();
```

### Changing Super Admin to Regular User
```php
$user = User::find($userId);
$user->role = UserRole::USER;
$user->save();
```

## Security Considerations

1. **Limited Super Admin Accounts**: Hanya buat akun super admin untuk orang yang benar-benar membutuhkan akses penuh
2. **Strong Passwords**: Gunakan password yang kuat untuk akun super admin
3. **Audit Logging**: Pertimbangkan untuk menambahkan logging untuk semua aksi super admin
4. **Regular Review**: Review secara berkala siapa saja yang memiliki akses super admin

## Testing

### Test Super Admin Access
1. Login sebagai super admin
2. Cek apakah bisa melihat semua project di halaman Projects
3. Cek apakah bisa edit project yang bukan miliknya
4. Cek apakah bisa manage team di project lain
5. Cek apakah bisa delete project apapun

### Test Regular User Access
1. Login sebagai user biasa
2. Pastikan hanya melihat project yang diikuti
3. Pastikan tidak bisa edit project yang bukan miliknya
4. Pastikan tidak bisa manage team di project lain

## Migration Commands

```bash
# Run migration
php artisan migrate

# Run seeder to create super admin
php artisan db:seed --class=UserSeeder

# Rollback if needed
php artisan migrate:rollback
```

## Future Enhancements

Beberapa enhancement yang bisa ditambahkan:
1. **Activity Log**: Log semua aksi super admin
2. **Super Admin Dashboard**: Dashboard khusus dengan statistik sistem
3. **User Management**: Interface untuk manage user roles
4. **Permission Management**: Granular permissions untuk super admin
5. **Multi-level Admin**: Admin dengan permission terbatas
