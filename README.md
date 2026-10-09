🚗 Zumi App - Real-Time Smart Parking System

Sistem Smart Parking berbasis AI yang mengintegrasikan deteksi kendaraan secara real-time menggunakan YOLOv8, pemrosesan aliran video FastAPI, backend manajemen Laravel 11, dan komunikasi WebSocket secara langsung melalui Laravel Reverb.

💻 Kebutuhan Sistem (System Requirements)

1. Perangkat Lunak Utama

PHP: >= 8.2

Composer: >= 2.5

Node.js & NPM: >= 18.x

Python: >= 3.10

MySQL / MariaDB Database

2. Sumber Kamera

Webcam bawaan laptop, kamera USB, IP Camera (RTSP), atau aplikasi IP Webcam HP (seperti DroidCam / IP Webcam).

🛠️ Instalasi & Full Setup

A. Setup Backend Laravel (zumi-app)

Buka terminal dan masuk ke direktori Laravel:

cd zumi-app


Install dependensi PHP dan Node.js:

composer install
npm install


Buat file konfig .env dari salinan contoh:

cp .env.example .env


Generasi App Key Laravel:

php artisan key:generate


Sesuaikan konfigurasi Database & Reverb di file .env:

APP_NAME="Zumi Parking"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zumi_parking
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=zumi-app-id
REVERB_APP_KEY=zumi-app-key
REVERB_APP_SECRET=zumi-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME="http"
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"


Jalankan migrasi database dan build asset frontend:

php artisan migrate
npm run build


B. Setup AI Engine FastAPI (fastapi-service)

Masuk ke direktori service FastAPI:

cd fastapi-service


Buat dan aktifkan Virtual Environment Python:

Windows:

python -m venv venv
.\venv\Scripts\Activate.ps1


Linux/MacOS:

python3 -m venv venv
source venv/bin/activate


Install seluruh paket requirements:

pip install -r requirements.txt


🎯 Panduan Penggunaan & Pemetaan ROI Parkir

Sebelum menjalankan pendeteksian AI, Anda wajib menentukan titik koordinat polygon area parkir (Region of Interest) sesuai sudut pandang kamera yang diam (static camera).

1. Menandai Slot Parkir (select_roi.py)

Buka file select_roi.py, lalu sesuaikan variabel CAMERA_SOURCE:

0 : Webcam Bawaan

"video.mp4" : File Video Testing

"http://192.168.1.X:8080/video" : Stream IP Webcam HP

Jalankan script penanda ROI:

python select_roi.py


Langkah Penandaan:

Klik 4 kali di sudut area parkir pada jendela video yang muncul.

Lihat terminal, masukkan kode slot (misal: A1), lalu tekan Enter.

Ulangi untuk slot berikutnya (A2, A3, dst.).

Tekan tombol q di keyboard pada jendela video untuk menyimpan hasil koordinat ke file slots.json.

🚀 Cara Menjalankan Aplikasi (Full Execution Flow)

Untuk menjalankan seluruh ekosistem aplikasi secara utuh, buka 4 Terminal terpisah:

Terminal 1 — Laravel HTTP Server

cd zumi-app
php artisan serve


(Akses Web Dashboard di: http://127.0.0.1:8000)

Terminal 2 — Laravel Reverb WebSocket Server

cd zumi-app
php artisan reverb:start


(Menangani penerimaan data instan dari backend ke browser via WebSocket)

Terminal 3 — Vite Assets Hot-Reload (Pengembangan UI)

cd zumi-app
npm run dev


Terminal 4 — FastAPI Service & Uvicorn Engine

cd fastapi-service

# Pastikan venv aktif
# Windows: .\venv\Scripts\Activate.ps1
# Linux/Mac: source venv/bin/activate

uvicorn main:app --reload --port 8001


📡 Mengontrol Deteksi AI dari Browser

Setelah keempat terminal di atas berjalan:

Mulai Deteksi Real-Time:
Buka browser dan kunjungi:
http://127.0.0.1:8001/start-detection

Buka Monitoring Dashboard:
Akses http://127.0.0.1:8000.
Tampilan Live Feed CCTV akan langsung muncul di box sebelah kiri, dan status slot parkir akan memperbarui warnanya (Merah = Terisi, Hijau = Kosong) secara otomatis tanpa refresh.

Menghentikan Deteksi AI:
Kunjungi URL berikut di browser untuk menghentikan proses background loop YOLO:
http://127.0.0.1:8001/stop-detection

Kelola Slot Parkir (Admin):
Akses http://127.0.0.1:8000/admin/slots untuk menambah, mengubah, atau menghapus slot dari database.