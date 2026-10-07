import cv2
import json

# Sumber video: 0 untuk Webcam Laptop, atau URL IP Webcam HP (misal: "http://192.168.1.5:8080/video")
CAMERA_SOURCE = "video1.mp4"

points = []
slots = {}

def click_event(event, x, y, flags, params):
    global points, slots
    if event == cv2.EVENT_LBUTTONDOWN:
        points.append((x, y))
        print(f"Point selected: ({x}, {y})")
        
        # Jika sudah klik 4 titik (membentuk 1 area slot persegi/polygon)
        if len(points) == 4:
            slot_name = input("Masukkan Kode Slot (contoh: A1, A2): ").strip().upper()
            slots[slot_name] = points.copy()
            print(f"Slot {slot_name} berhasil disimpan!")
            points.clear()

cap = cv2.VideoCapture(CAMERA_SOURCE)
ret, frame = cap.read()

if not ret:
    print("Gagal membuka kamera!")
    exit()

print("\n--- CARA PENGGUNAAN ---")
print("1. Klik 4 kali pada sudut area slot parkir di jendela gambar.")
print("2. Ketik nama slot di terminal (misal: A1) lalu tekan Enter.")
print("3. Ulangi untuk slot lain.")
print("4. Tekan tombol 'q' di keyboard untuk Selesai & Simpan ke file 'slots.json'.\n")

cv2.namedWindow("Select Parking Slots")
cv2.setMouseCallback("Select Parking Slots", click_event)

while True:
    temp_frame = frame.copy()
    
    # Gambar polygon slot yang sudah tersimpan
    for slot_code, pts in slots.items():
        for i in range(4):
            cv2.line(temp_frame, pts[i], pts[(i+1)%4], (0, 255, 0), 2)
        cv2.putText(temp_frame, slot_code, pts[0], cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)

    # Gambar titik sementara yang sedang diklik
    for pt in points:
        cv2.circle(temp_frame, pt, 5, (0, 0, 255), -1)

    cv2.imshow("Select Parking Slots", temp_frame)
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()

# Simpan ke file JSON
with open("slots.json", "w") as f:
    json.dump(slots, f, indent=4)

print("Koordinat slot berhasil disimpan di 'slots.json'!")