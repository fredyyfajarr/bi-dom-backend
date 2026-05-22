# BI DOM Backend

Backend REST API untuk sistem **Business Intelligence DOM Social Hub**. Repository ini menangani autentikasi, import data transaksi CSV, dashboard analytics, invoice, inventory forecasting, manajemen produk dan resep, serta export laporan PDF.

Project ini dirancang sebagai backend untuk repository frontend: [`bi-dom-frontend`](https://github.com/fredyyfajarr/bi-dom-frontend).

---

## Ringkasan Fitur

- **Authentication & Authorization**
  - Login menggunakan Laravel Sanctum.
  - Role user: `manager` dan `kasir`.
  - Proteksi endpoint menggunakan middleware auth dan role.

- **Dashboard Business Intelligence**
  - KPI revenue, total COGS, net profit, profit margin, dan jumlah transaksi.
  - Grafik revenue tahunan dan bulanan berdasarkan kategori.
  - Data transaksi terbaru dan detail transaksi.
  - Top product analysis.
  - Donut chart komposisi kategori.
  - Advanced analytics: daily revenue, peak hour, stacked category trend, dan market basket analysis.
  - Drill-down detail jam ramai.

- **Import Data Transaksi**
  - Upload file CSV dari data POS.
  - Mendukung data itemized per produk dalam satu struk.
  - Data import langsung diproses ke transaksi dan detail transaksi.

- **Invoice / Transaction Ledger**
  - Menyediakan daftar transaksi untuk kebutuhan kasir dan manager.
  - Mendukung pencarian, filter tanggal, sorting, dan pagination dari frontend.

- **Product & Recipe Management**
  - CRUD produk.
  - Penyimpanan resep / material yang digunakan oleh setiap produk.

- **Inventory Forecasting**
  - Monitoring stok bahan baku.
  - Alert stok kritis.
  - Estimasi kebutuhan stok berbasis transaksi 30 hari terakhir untuk prediksi 7 hari ke depan.
  - Update stok masuk secara manual.

- **PDF Report Export**
  - Export laporan PDF menggunakan DomPDF.

---

## Tech Stack

- PHP `^8.3`
- Laravel `^13.0`
- Laravel Sanctum
- MySQL 8 / MariaDB
- DomPDF
- Composer
- Vite
- Tailwind CSS

---

## Struktur Endpoint Utama

Base URL lokal:

```txt
http://127.0.0.1:8000/api/v1
```

### Public Route

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/login` | Login user dan generate token Sanctum |

### Protected Route: Manager & Kasir

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/user` | Mengambil data user aktif |
| POST | `/logout` | Logout dan hapus token aktif |
| POST | `/import` | Upload CSV transaksi |
| POST | `/import-csv` | Alias upload CSV transaksi |
| GET | `/invoices` | Daftar invoice / transaksi |

### Manager Only

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/products` | List produk |
| POST | `/products` | Tambah produk dan resep |
| GET | `/products/{id}` | Detail produk |
| PUT/PATCH | `/products/{id}` | Update produk dan resep |
| DELETE | `/products/{id}` | Hapus produk |
| GET | `/dashboard/available-years` | Tahun transaksi yang tersedia |
| GET | `/dashboard/categories-list` | List kategori |
| GET | `/dashboard/kpi` | Data KPI dashboard |
| GET | `/dashboard/charts` | Data grafik sales/category |
| GET | `/dashboard/transactions` | Transaksi terbaru |
| GET | `/dashboard/transactions/{id}` | Detail transaksi |
| GET | `/dashboard/top-products` | Produk terlaris |
| GET | `/dashboard/inventory-alerts` | Alert stok rendah |
| GET | `/dashboard/donut-chart` | Komposisi kategori |
| GET | `/dashboard/advanced-analytics` | Daily revenue, peak hour, stacked trend, market basket |
| GET | `/dashboard/peak-hour-detail` | Drill-down jam ramai |
| GET | `/inventory/alerts` | Alert dan forecast inventory |
| GET | `/inventory/list` | List inventory |
| POST | `/inventory/update-stock` | Tambah stok bahan baku |
| POST | `/inventory/items` | Tambah master item inventory |
| GET | `/reports/export-pdf` | Export laporan PDF |

---

## Format CSV Import

File CSV harus menggunakan format itemized. Artinya, jika satu struk berisi beberapa menu, setiap menu ditulis sebagai baris terpisah dengan `receipt_no` yang sama.

Header yang dibutuhkan:

```csv
receipt_no,trx_date,product_name,qty,subtotal
```

Contoh:

```csv
receipt_no,trx_date,product_name,qty,subtotal
INV-001,2026-04-28 10:30,Aren Latte,2,40000
INV-001,2026-04-28 10:30,Mix Platter,1,35000
INV-002,2026-04-28 11:15,Lychee Tea,1,20000
```

Contoh file import tersedia di:

```txt
database/samples/import-transactions-2026-05-22.csv
```

Catatan:

- `subtotal` adalah total harga per item, yaitu harga satuan x qty.
- Jangan gunakan titik atau koma sebagai pemisah ribuan pada kolom nominal.

---

## Prasyarat

Pastikan sudah terinstall:

- PHP 8.3 atau lebih baru
- Composer
- MySQL / MariaDB
- Node.js dan npm jika ingin build asset Laravel

---

## Instalasi Lokal

Clone repository:

```bash
git clone https://github.com/fredyyfajarr/bi-dom-backend.git
cd bi-dom-backend
```

Install dependency PHP:

```bash
composer install
```

Buat file environment:

```bash
copy .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

Atur konfigurasi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dom_social_hub
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `dom_social_hub`, lalu jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

---

## Menjalankan Local Development

Jalankan service Laravel di terminal terpisah:

```bash
php artisan serve --host=127.0.0.1 --port=8000
php artisan queue:work
php artisan schedule:work
```

`php artisan serve` hanya server HTTP development. Queue dan scheduler tidak otomatis berjalan saat web dibuka, jadi keduanya harus dijalankan sebagai proses terpisah.

Flow local development:

1. User membuka frontend di `http://localhost:3000`.
2. Frontend memanggil API Laravel di `http://127.0.0.1:8000/api/v1`.
3. Laravel memproses request dan membaca/menulis data ke MySQL.
4. Proses berat sebaiknya dikirim sebagai job ke queue agar request tetap cepat.
5. `php artisan queue:work` mengambil dan menjalankan job dari antrean.
6. `php artisan schedule:work` menjalankan task terjadwal selama development.

Di VS Code, flow ini bisa dijalankan lewat task yang menyalakan frontend, Laravel serve, queue worker, dan scheduler sekaligus.

---

## Mode Cepat: Octane + FrankenPHP

Untuk local development di Windows, FrankenPHP dijalankan lewat Docker karena installer Octane tidak menyediakan binary FrankenPHP native untuk Windows.

Pastikan Docker Desktop sudah berjalan, lalu build dan jalankan backend Octane:

```bash
docker build -f Dockerfile.octane -t bi-dom-backend-octane .
docker run --rm --name bi-dom-backend-octane -p 8000:8000 -v "%cd%:/app" -e DB_HOST=host.docker.internal -e APP_ENV=local -e APP_DEBUG=true bi-dom-backend-octane --workers=2 --max-requests=500
```

Jika memakai VS Code dari root workspace BI-DOM, tekan `Ctrl+Shift+B` untuk menjalankan frontend, Octane backend, queue worker, dan scheduler sekaligus.

Untuk menghentikan container Octane:

```bash
docker rm -f bi-dom-backend-octane
```

---

## Akun Demo Seeder

| Role | Email | Password |
|---|---|---|
| Manager | `manager@dom.com` | `password123` |
| Kasir | `kasir@dom.com` | `password123` |

> Untuk production, ganti password default dan jangan gunakan credential demo.

---

## Command Berguna

Menjalankan development server Laravel:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Menjalankan queue worker:

```bash
php artisan queue:work
```

Menjalankan scheduler worker:

```bash
php artisan schedule:work
```

Menjalankan test:

```bash
php artisan test
```

Menjalankan Laravel Pint:

```bash
./vendor/bin/pint
```

---

## Integrasi Frontend

Frontend membaca API dari URL:

```txt
http://127.0.0.1:8000/api/v1
```

Pastikan repository frontend mengarah ke URL backend yang sama melalui environment:

```env
NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api/v1
```

---

## Deploy

Untuk production, jangan memakai `php artisan serve`. Gunakan web server atau platform PHP production, queue worker yang dikelola process manager, dan scheduler via cron.

Jika frontend memakai Vercel, backend tetap perlu host API terpisah seperti Railway, Render, Fly.io, VPS, atau Laravel Cloud.

Supabase bisa dipakai sebagai database jika project dimigrasikan ke PostgreSQL. Supabase bukan MySQL, jadi query MySQL-specific perlu dicek sebelum migrasi.

---

## Troubleshooting

### 401 Unauthorized

Kemungkinan token belum dikirim atau user belum login. Pastikan frontend menyimpan token dan mengirim header:

```txt
Authorization: Bearer <token>
```

### 403 Forbidden

User login tidak memiliki role `manager`. Beberapa endpoint seperti dashboard, product, inventory, dan report hanya untuk manager.

### Database error

Pastikan database `dom_social_hub` sudah dibuat dan `.env` sudah sesuai, lalu jalankan:

```bash
php artisan migrate --seed
```

### Data dashboard kosong

Jalankan seeder atau upload CSV transaksi terlebih dahulu.

---

## Related Repository

- Frontend: [`bi-dom-frontend`](https://github.com/fredyyfajarr/bi-dom-frontend)
