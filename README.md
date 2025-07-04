# 🔙 Backend API – Laravel 11

Ini adalah bagian backend dari Sistem Informasi Bengkel Han’s Motor. Backend ini dibuat menggunakan Laravel 11 dan menyediakan RESTful API.

---

## 🧰 Teknologi
- Laravel 11
- PHP 8.3
- MySQL
- Laravel Sanctum (Autentikasi Token)
- Spatie Laravel Permission (Role & Akses)

---

## 📦 Instalasi

### 1. Clone & Masuk Folder
```bash
git clone https://github.com/username/sistem-informasi-bengkel.git
cd sistem-informasi-bengkel/hans-backend
```

### 2. Install Dependency
```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi Database
Edit file `.env`:
```
DB_DATABASE=bengkel
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi & Seeder
```bash
php artisan migrate
php artisan db:seed
```

### 5. Jalankan Server
```bash
php artisan serve
```

---

## 🔐 Autentikasi
- Endpoint Login: `POST /api/login`
- Token disimpan di frontend dan dikirim via header:
  ```
  Authorization: Bearer {token}
  ```
- Role: super_admin, pemilik, admin, kasir

---

## 📁 Struktur Penting
- `app/Http/Controllers/Api/` – API controller
- `routes/api.php` – Routing endpoint API
- `database/seeders/` – Seeder data awal user & role

---

## 🚫 File di .gitignore
```
/vendor
.env
*.log
```