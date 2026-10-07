import os

import cv2
import json
import time
import requests
import serial
import numpy as np
from fastapi import FastAPI, BackgroundTasks
from ultralytics import YOLO

app = FastAPI(title="Zumi Parking AI Service")

# --- KONFIGURASI ---
# Kamera: 0 = Webcam Laptop, atau "http://192.168.1.X:8080/video" untuk IP Webcam HP
CAMERA_SOURCE = "video1.mp4"
LARAVEL_API_URL = "http://127.0.0.1:8000/api/v1/parking/update-slots"

# Hardware LCD (Opsional) - Isikan PORT Serial Arduino/ESP32 jika ada, misal 'COM3' atau '/dev/ttyUSB0'
SERIAL_PORT = None  # Contoh jika pakai: "COM3"
BAUD_RATE = 9600

# Load Model YOLO (yolov8n.pt ringan untuk laptop biasa)
model = YOLO("yolov8n.pt")


is_detection_running = False

@app.get("/start-detection")
def start_detection(background_tasks: BackgroundTasks):
    global is_detection_running
    if is_detection_running:
        return {"message": "Proses deteksi sudah berjalan!"}
    
    is_detection_running = True
    background_tasks.add_task(run_yolo_detection)
    return {"message": "Proses deteksi YOLO & Sync dimulai!"}

@app.get("/stop-detection")
def stop_detection():
    global is_detection_running
    is_detection_running = False
    return {"message": "Perintah menghentikan deteksi berhasil dikirim!"}

# Inisialisasi Serial Port LCD jika aktif
ser = None
if SERIAL_PORT:
    try:
        ser = serial.Serial(SERIAL_PORT, BAUD_RATE, timeout=1)
        print(f"LCD Hardware terhubung di port {SERIAL_PORT}")
    except Exception as e:
        print(f"Warning: LCD Serial tidak terhubung ({e})")

def send_to_lcd(text: str):
    """Kirim string ke Hardware LCD via Serial/USB"""
    if ser and ser.is_open:
        try:
            ser.write(f"{text}\n".encode('utf-8'))
        except Exception as e:
            print(f"Error sending to LCD: {e}")

def sync_to_laravel(payload: dict):
    """Kirim payload JSON ke API Laravel zumi-app"""
    try:
        response = requests.post(LARAVEL_API_URL, json=payload, timeout=2)
        print(f"Sync Laravel Response: {response.status_code}")
    except Exception as e:
        print(f"Gagal sync ke Laravel: {e}")

@app.get("/")
def index():
    return {"status": "FastAPI YOLO Service is running"}

@app.get("/start-detection")
def start_detection(background_tasks: BackgroundTasks):
    """Endpoint untuk memulai loop deteksi video"""
    background_tasks.add_task(run_yolo_detection)
    return {"message": "Proses deteksi YOLO & Sync dimulai!"}

def run_yolo_detection():
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    slots_path = os.path.join(BASE_DIR, "slots.json")
    
    # Load koordinat slot dari JSON
    try:
        with open(slots_path, "r") as f:
            parking_slots = json.load(f)
    except FileNotFoundError:
        print("Error: File 'slots.json' tidak ditemukan! Jalankan 'select_roi.py' dulu.")
        return

    cap = cv2.VideoCapture(CAMERA_SOURCE)
    
    last_sync_time = 0
    sync_interval = 2  # Kirim data ke Laravel setiap 2 detik sekali agar ringan

    while cap.isOpened():
        if not is_detection_running:
            print("Deteksi dihentikan oleh pengguna.")
            break
        
        ret, frame = cap.read()
        if not ret:
            if isinstance(CAMERA_SOURCE, str) and CAMERA_SOURCE.endswith(('.mp4', '.avi', '.mkv')):
                print("Video habis, mengulang dari awal (loop)...")
                cap.set(cv2.CAP_PROP_POS_FRAMES, 0)
                continue
            else:
                print("Stream kamera terputus...")
                break

        # Run Inference YOLO (Hanya deteksi kendaraan: car=2, bus=5, truck=7, motorcycle=3)
        results = model(frame, classes=[2, 3, 5, 7], conf=0.25, verbose=False)[0]

        # Ambil koordinat bounding box kendaraan
        vehicle_boxes = []
        if results.boxes is not None:
            for box in results.boxes.xyxy.cpu().numpy():
                vehicle_boxes.append(box) # [x1, y1, x2, y2]

        current_slot_statuses = []
        total_available = 0

        # Pengecekan setiap slot parkir
        for slot_code, pts in parking_slots.items():
            pts_np = np.array(pts, np.int32)
            is_occupied = False

            # Cek apakah ada Bounding Box kendaraan yang tumpang tindih dengan Polygon Slot
            for box in vehicle_boxes:
                x1, y1, x2, y2 = map(int, box)
                center_x = (x1 + x2) // 2
                center_y = (y1 + y2) // 2

                # Point polygon test: cek apakah titik tengah kendaraan berada di dalam area slot
                if cv2.pointPolygonTest(pts_np, (center_x, center_y), False) >= 0:
                    is_occupied = True
                    break

            if not is_occupied:
                total_available += 1

            current_slot_statuses.append({
                "slot_code": slot_code,
                "is_occupied": is_occupied
            })

            # Visualisasi di frame OpenCV
            color = (0, 0, 255) if is_occupied else (0, 255, 0) # Merah=Terisi, Hijau=Kosong
            cv2.polylines(frame, [pts_np], isClosed=True, color=color, thickness=2)
            cv2.putText(frame, f"{slot_code}: {'TERISI' if is_occupied else 'KOSONG'}", 
                        (pts[0][0], pts[0][1] - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 2)

        # Tampilkan informasi summary di layar OpenCV
        cv2.putText(frame, f"Slot Tersedia: {total_available}/{len(parking_slots)}", 
                    (20, 40), cv2.FONT_HERSHEY_SIMPLEX, 1, (255, 255, 0), 2)

        # Sync data ke Laravel & LCD setiap X detik
        current_time = time.time()
        if current_time - last_sync_time > sync_interval:
            payload = {"slots": current_slot_statuses}
            
            # Kirim ke Laravel
            sync_to_laravel(payload)
            
            # Kirim Teks ke LCD Hardware
            lcd_text = f"KOSONG:{total_available}"
            send_to_lcd(lcd_text)

            last_sync_time = current_time

        # Tampilkan Jendela Video Preview
        cv2.imshow("Zumi Parking AI - Webcam Preview", frame)

        # Tekan 'q' pada jendela video untuk berhenti
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()