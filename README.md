# 🚗 Zumi App - Real-Time Smart Parking Monitoring System

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/FastAPI-0.100+-009688?style=for-the-badge&logo=fastapi&logoColor=white" alt="FastAPI">
  <img src="https://img.shields.io/badge/YOLOv8-AI_Detection-00FFFF?style=for-the-badge&logo=ultralytics&logoColor=black" alt="YOLOv8">
  <img src="https://img.shields.io/badge/Reverb-WebSocket-black?style=for-the-badge" alt="Laravel Reverb">
</div>

---

Sistem *Smart Parking* berbasis AI yang mengintegrasikan deteksi kendaraan secara *real-time* menggunakan **YOLOv8**, layanan pemrosesan video **FastAPI**, backend data & manajemen **Laravel 11**, serta komunikasi WebSocket instan menggunakan **Laravel Reverb**.

## 🛠️ System Requirements

Sebelum menjalankan sistem, pastikan perangkat kamu sudah terinstall:

* **PHP**: `>= 8.2`
* **Composer**: `>= 2.5`
* **Node.js & NPM**: `>= 18.x`
* **Python**: `>= 3.10`
* **Webcam / Camera Stream**: Kamera bawaan laptop, IP Camera, atau aplikasi IP Webcam HP (seperti DroidCam).

---

## 🚀 Setup & Instalasi

### 1. Setup Backend Laravel (`zumi-app`)

Masuk ke folder proyek Laravel dan install semua dependensi:
```bash
cd zumi-app
composer install
npm install
```

Salin file `.env.example` menjadi `.env` lalu sesuaikan konfigurasi Reverb & Database:
```env
APP_NAME="Zumi Parking"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zumi_parking
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi WebSocket Reverb
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=zumi-app-id
REVERB_APP_KEY=zumi-app-key
REVERB_APP_SECRET=zumi-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME="http"
```

Jalankan migrasi database:
```bash
php artisan migrate
```

### 2. Setup AI Detection Service (`fastapi-service`)

Masuk ke folder FastAPI dan buat *virtual environment* Python:
```bash
cd fastapi-service
python -m venv venv

# Aktivasi di Linux/MacOS:
source venv/bin/activate
# Aktivasi di Windows (PowerShell):
.\venv\Scripts\Activate.ps1
```

Install dependensi Python:
```bash
pip install -r requirements.txt
```

---

## 🎯 Panduan Penandaan Slot Parkir (ROI)

Setiap kali posisi kamera berubah, kamu **wajib** melakukan *mapping* koordinat area parkir:

1. Atur `CAMERA_SOURCE` di `fastapi-service/select_roi.py` (misal `0` untuk webcam laptop).
2. Jalankan script penanda ROI:
   ```bash
   python select_roi.py
   ```
3. Klik 4 titik sudut area parkir pertama di jendela video, lalu ketik kode slot (contoh: `A1`) di terminal dan tekan **Enter**.
4. Ulangi untuk slot lainnya. Jika sudah selesai, tekan tombol **`q`** pada jendela video untuk menyimpan file `slots.json`.

---

## ⚡ Menjalankan Layanan (Execution)

Untuk menjalankan sistem ini secara penuh di mode *development*, kamu perlu membuka **4 Terminal terpisah**:

<details open>
<summary><b>Terminal 1: Laravel Web Server</b></summary>
Berfungsi menjalankan backend aplikasi Zumi.

```bash
cd zumi-app
php artisan serve
```
</details>

<details open>
<summary><b>Terminal 2: Laravel Vite (Frontend Assets)</b></summary>
Berfungsi meng-compile CSS Tailwind dan file JS Laravel Echo agar dashboard berfungsi.

```bash
cd zumi-app
npm run dev
```
</details>

<details open>
<summary><b>Terminal 3: Laravel Reverb (WebSocket)</b></summary>
Berfungsi menangani komunikasi *real-time* antara FastAPI dan Dashboard.

```bash
cd zumi-app
php artisan reverb:start
```
</details>

<details open>
<summary><b>Terminal 4: FastAPI & YOLO Engine</b></summary>
Berfungsi menjalankan server pendeteksi kamera.

```bash
cd fastapi-service
uvicorn main:app --reload --port 8001
```
</details>

---

## 💻 Cara Penggunaan Dashboard

1. **Mulai Deteksi AI**:
   Buka browser dan akses endpoint berikut untuk menyalakan kamera dan sistem sinkronisasi:
   👉 `http://127.0.0.1:8001/start-detection`

2. **Buka Live Dashboard**:
   Buka halaman utama Laravel di:
   👉 `http://127.0.0.1:8000`
   *(Kamu akan melihat video CCTV di sebelah kiri dan status slot real-time di kanan).*

3. **Kelola Slot (Admin)**:
   Klik tombol **⚙️ Kelola Slot** di pojok kanan atas dashboard untuk menambah, mengedit, atau menghapus kode slot parkir dari database.

4. **Hentikan Deteksi AI**:
   Untuk mematikan kamera dengan aman:
   👉 `http://127.0.0.1:8001/stop-detection`