# 🚀 Parameter Fintech

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/tailwindcss-%2338B2AC.svg?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Alpine.js](https://img.shields.io/badge/alpine.js-%238BC0D0.svg?style=for-the-badge&logo=alpine.js&logoColor=white)
![Vite](https://img.shields.io/badge/vite-%23646CFF.svg?style=for-the-badge&logo=vite&logoColor=white)

**Parameter Fintech** adalah sistem manajemen keuangan dan penagihan modern yang dirancang khusus untuk kebutuhan pengelolaan bisnis (ISP/Radius). Aplikasi ini menggabungkan kemudahan manajemen data pelanggan dengan sistem akuntansi dan penggajian yang otomatis.

---

## ✨ Fitur Utama

### 📊 Dashboard Utama (Admin)
- **Financial Overview**: Pantau saldo kas real-time, dana cadangan, dan setoran yang menunggu verifikasi.
- **Auto-Journaling**: Pencatatan transaksi otomatis ke Buku Kas Besar setelah verifikasi setoran.

### 👥 Manajemen Pelanggan & Tagihan
- **Data Pelanggan**: Kelola informasi pelanggan secara terpusat.
- **Invoice Otomatis**: Generate tagihan bulanan dan kelola status pembayaran secara efisien.

### 💰 Penggajian (Payroll)
- **Manajemen Gaji**: Pengaturan gaji pokok untuk setiap staf/teknisi.
- **Bonus Aktivasi**: Penghitungan bonus otomatis berdasarkan aktivasi pelanggan baru.
- **Slip Gaji Digital**: Staf dapat melihat riwayat gaji mereka melalui antarmuka mobile.

### 📈 Laporan & Dana Cadangan
- **Bagi Hasil**: Sistem pembagian keuntungan yang transparan.
- **Riwayat Transaksi**: Lacak setiap arus kas masuk dan keluar dengan detail.

### 📱 Antarmuka Mobile (Staf/Kolektor)
- **Mobile First Design**: Antarmuka premium yang dioptimalkan untuk penggunaan di lapangan.
- **Setor Tunai**: Laporkan setoran harian langsung dari lokasi.
- **Lapor Gangguan**: Komunikasi cepat terkait isu di lapangan.
- **Akses Dashboard**: Akses cepat kembali ke panel admin bagi user dengan role Admin/Owner.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11.x (PHP 8.2+)
- **Frontend**: Blade Template, Tailwind CSS, Alpine.js
- **Build Tool**: Vite
- **Database**: MySQL / MariaDB
- **UI System**: Premium Typography (Outfit Font), Glassmorphism Effects, Responsive Design.

---

## ⚙️ Persyaratan Sistem

- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL 8.0+

---

## 🚀 Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/18wisnu/parameter-fintech.git
   cd parameter-fintech
   ```

2. **Instal Dependensi**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Atur konfigurasi database Anda di file `.env`.*

4. **Migrasi Database**
   ```bash
   php artisan migrate
   ```

5. **Build Aset Frontend**
   ```bash
   npm run build
   ```

6. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   ```

---

## 🔐 Hak Akses (Roles)

1. **Owner/Admin**: Akses penuh ke semua fitur finansial, laporan, manajemen user, dan menu setoran.
2. **Teknisi/Kolektor**: Akses khusus fitur mobile (setoran, lapor gangguan, slip gaji).

---

## 📄 Lisensi

Aplikasi ini bersifat internal dan berlisensi di bawah kepemilikan **Parameter Fintech**.
