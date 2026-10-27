# Clinic Management System

Aplikasi manajemen klinik berbasis **PHP Native MVC** (arsitektur buatan sendiri, tanpa framework backend), MySQL 8, dan Bootstrap 5.

> **Status:** Phase 1 — Foundation (Authentication, RBAC, Layout, Dashboard)

## Requirements

- PHP >= 8.2 (dikembangkan dengan PHP 8.4) dengan ekstensi: `pdo_mysql`, `mbstring`, `dom`, `xml`, `gd`, `zip`, `json`
- MySQL 8+
- Composer 2

## Installation

```bash
composer install
cp .env.example .env
```

Edit `.env` dan sesuaikan kredensial database serta `APP_URL`.

## Environment

Variabel penting di `.env`:

| Key | Keterangan |
|---|---|
| `APP_ENV` | `local` / `production` |
| `APP_DEBUG` | `true` menampilkan stack trace, `false` untuk production |
| `APP_URL` | Base URL aplikasi (tanpa trailing slash) |
| `DB_*` | Kredensial koneksi MySQL |
| `LOGIN_MAX_ATTEMPTS` / `LOGIN_LOCKOUT_MINUTES` | Rate limiting login |

## Database Setup

Buat database terlebih dahulu:

```sql
CREATE DATABASE clinic_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Migration

```bash
php bin/migrate.php          # menjalankan migration yang belum diterapkan
php bin/migrate.php rollback # rollback batch migration terakhir
```

## Seeder

```bash
php bin/seed.php
```

Seeder akan membuat: 12 role, katalog permission (`module.action`), user Super Admin default, dan system settings default.

## Development Server

```bash
php -S localhost:8000 -t public
```

Akses melalui `http://localhost:8000`.

## Folder Structure

```
app/            Core MVC, Controllers, Models, Services, Middleware, Exceptions
config/         Konfigurasi aplikasi, database, auth, permission catalogue
database/       Migration & seeder
public/         Front controller (index.php) & assets
resources/views Template PHP (layouts, components, per-modul)
routes/         Definisi route (web.php)
storage/        Log, dokumen, export, backup (tidak public)
bin/            CLI script (migrate, seed)
```

## Default Login

```
Email    : admin@clinic.local
Password : Admin@123456
```

> **PERINGATAN:** Kredensial ini hanya untuk pengembangan (development). Wajib diganti sebelum digunakan di lingkungan production.

## Security

- Password di-hash dengan `password_hash` (bcrypt).
- Proteksi CSRF pada seluruh request non-GET (`CsrfMiddleware`).
- Rate limiting percobaan login (default 5x gagal → lockout 15 menit), tercatat di tabel `login_attempts`.
- RBAC berbasis permission (`module.action`), dicek lewat `PermissionMiddleware` — tidak ada role hardcode di controller.
- Session regenerate saat login/logout, session timeout otomatis, cookie `httponly` + `SameSite=Lax`.
- File `.env`, `*.log`, `*.sql`, `*.md` diblokir dari akses langsung via `public/.htaccess`.
- Semua query menggunakan PDO prepared statement (lihat `App\Core\QueryBuilder` dan `App\Core\Model`).
- Log terpisah: `storage/logs/app.log`, `error.log`, `security.log`.

## Backup

Folder `storage/backups/` disiapkan untuk hasil backup database (fitur backup melalui UI akan dibangun pada fase lanjutan). Folder ini tidak dapat diakses publik.

## Troubleshooting

| Masalah | Solusi |
|---|---|
| `Class "Dotenv\Dotenv" not found` | Jalankan `composer install` |
| Error koneksi database | Periksa `DB_*` di `.env` dan pastikan MySQL berjalan |
| Halaman 419 saat submit form | Token CSRF kedaluwarsa/sesi habis, muat ulang halaman |
| Halaman 403 | Akun tidak memiliki permission untuk aksi tersebut |
| Terkunci setelah gagal login berkali-kali | Tunggu sesuai `LOGIN_LOCKOUT_MINUTES` atau hapus baris terkait di tabel `login_attempts` (khusus development) |

## Roadmap Fase Berikutnya

Phase 2 — Master Data (Clinic, Branch, Department, Room, Polyclinic, Doctor, Employee).
