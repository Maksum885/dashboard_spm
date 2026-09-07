# Panduan Computer Vision — Testing Bay (Jetson Nano)

Dokumen ini menjelaskan semua file Python di folder `camera/`, fungsi tiap sintaks penting, dan cara kerja integrasi ke **Jetson Nano P3450** (JetPack 4.6).

> **Catatan penting Jetson Nano:** sebelum menjalankan Python apapun, wajib set:
> ```bash
> echo "export OPENBLAS_CORETYPE=ARMV8" >> ~/.bashrc && source ~/.bashrc
> ```
> Tanpa ini, numpy crash saat import di lingkungan ARM64.

---

## Daftar File

| File              | Fungsi Utama                                                          |
| ----------------- | --------------------------------------------------------------------- |
| `config.py`       | Baca semua variabel `.env`, ekspor sebagai konstanta                  |
| `camera_loader.py`| Ambil daftar kamera aktif dari Laravel via HTTP                       |
| `yolo_detector.py`| Muat model YOLO, jalankan deteksi orang per frame (GPU Jetson)       |
| `camera_manager.py`| Kelola thread per kamera, pipeline GStreamer (`avdec_h264`)          |
| `camera_stream.py`| Flask server — endpoint stream video & status alarm (port 5001)      |
| `plc_writer.py`   | Tulis 0/1 ke Holding Register 10 PLC via Modbus TCP FC6               |
| `test_yolo.py`    | Skrip cepat untuk verifikasi model YOLO berhasil dimuat               |

Model YOLO (`yolov8n.pt`) **tidak disertakan dalam repo** — didownload otomatis oleh ultralytics saat pertama kali jalan, atau ekspor ke TensorRT `.engine` untuk performa optimal.

---

## `config.py` — Konfigurasi Terpusat

File ini dibaca **satu kali saat startup**. Semua file lain mengimpor konstanta dari sini.

```python
load_dotenv()   # membaca file camera/.env ke os.environ
```

### Variabel dan Fungsinya

#### Server

```python
CV_HOST = os.getenv("CV_HOST", "0.0.0.0")
CV_PORT = int(os.getenv("CV_PORT", "5001"))
```
Port 5001 (bukan 5000) — port 5000 sering dipakai proses lain di JetPack. Browser mengakses `http://192.168.1.200:5001/stream/...`.

#### Laravel Bridge

```python
LARAVEL_BRIDGE_CAMERAS_URL = os.getenv("LARAVEL_BRIDGE_CAMERAS_URL", "").strip()
CV_MAP_REFRESH_SEC = float(os.getenv("CV_MAP_REFRESH_SEC", "15"))
```
URL endpoint Laravel tempat Jetson mengambil daftar kamera. Gunakan IP server Laravel (bukan 127.0.0.1):
```
http://192.168.1.10:8000/api/cv/bridge-cameras?token=TOKEN
```

#### YOLO / Deteksi Orang

```python
YOLO_ENABLED = os.getenv("YOLO_ENABLED", "true").strip().lower() in ("1", "true", "yes")
```
`false` → model YOLO tidak dimuat; stream kamera RTSP tetap jalan.

```python
YOLO_MODEL_PATH = os.getenv("YOLO_MODEL_PATH", str(BASE_DIR / "models" / "yolov8n.pt"))
```
Bisa `.pt` (PyTorch) atau `.engine` (TensorRT, performa 2–3× lebih cepat di Jetson).

```python
YOLO_DETECT_EVERY_N_FRAMES = max(1, int(os.getenv("YOLO_DETECT_EVERY_N_FRAMES", "5")))
YOLO_DEVICE = os.getenv("YOLO_DEVICE", "cpu").strip()     # 'cuda:0' untuk Jetson GPU
YOLO_HALF = os.getenv("YOLO_HALF", "false")...            # FP16, aktifkan hanya jika cuda:0
```

#### Mode Input Kamera

```python
CAMERA_INPUT_MODE = os.getenv("CAMERA_INPUT_MODE", "ffmpeg").strip().lower()
```

| Nilai      | Cara buka kamera                      | Cocok untuk                         |
| ---------- | ------------------------------------- | ----------------------------------- |
| `ffmpeg`   | OpenCV default via FFmpeg             | PC biasa, development               |
| `gst_rtsp` | GStreamer + `avdec_h264` (SW decoder) | Jetson Nano + IP camera RTSP        |
| `csi`      | GStreamer `nvarguscamerasrc`           | CSI camera fisik di Jetson          |
| `usb`      | GStreamer `v4l2src`                   | USB webcam di Jetson                |

#### PLC Write (Skema Hybrid)

```python
PLC_WRITE_ENABLED = os.getenv("PLC_WRITE_ENABLED", "false")...
CV_PLC_WRITE_ADDRESS = int(os.getenv("CV_PLC_WRITE_ADDRESS", "9"))
# PDU 9 = "Holding Register 10" = %MW9 = address 40010 di Schneider M221 TM221CE24R
```

```python
JPEG_QUALITY = max(10, min(100, int(os.getenv("JPEG_QUALITY", "70"))))
RTSP_TRANSPORT = os.getenv("RTSP_TRANSPORT", "tcp")
RECONNECT_DELAY_SEC = float(os.getenv("RECONNECT_DELAY_SEC", "2"))
```

---

## `camera_loader.py` — Ambil Daftar Kamera dari Laravel

```python
def load_cameras_sync() -> dict[int, dict[int, dict]]:
```
Mengembalikan `{room_id: {slot: {rtsp_url, name, room_name}}}`.

```python
with httpx.Client(timeout=15.0) as client:
    resp = client.get(url)
    resp.raise_for_status()
```
Jika Laravel tidak bisa dijangkau, fungsi ini mengembalikan `{}` (tidak crash, hanya tidak ada kamera).

---

## `yolo_detector.py` — Deteksi Orang dengan YOLO

### Inisialisasi

```python
if YOLO_ENABLED:
    from ultralytics import YOLO
    _model = YOLO(str(_model_path))
```
`import ultralytics` dilakukan di dalam blok `if` — jika `YOLO_ENABLED=false`, library tidak perlu terinstall.

### Fungsi `detect_person(frame)`

```python
results = _model(
    frame,
    device=YOLO_DEVICE,   # 'cuda:0' di Jetson Nano
    half=YOLO_HALF,        # True = FP16 di GPU Jetson
    verbose=False,
)
```

```python
for result in results:
    for box in result.boxes:
        cls = int(box.cls[0])
        if cls != 0:
            continue   # hanya class 0 = "person" dalam COCO dataset
```

```python
x1, y1, x2, y2 = map(int, box.xyxy[0])
conf = float(box.conf[0])
cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
```
Menggambar kotak hijau dan label confidence score di atas frame.

---

## `camera_manager.py` — Manajer Thread Kamera

### `StreamState` (dataclass)

```python
@dataclass
class StreamState:
    rtsp_url: str
    name: str
    room_name: str
    latest_frame: object        # numpy array, dilindungi lock
    person_detected: bool
    frame_counter: int
    lock: threading.Lock
    stop_event: threading.Event
```

### `CameraManager.refresh(camera_map)`

Dipanggil setiap `CV_MAP_REFRESH_SEC` detik:
- Kamera **hilang** dari daftar → thread dihentikan.
- URL **berubah** → thread lama stop, thread baru start.
- URL **sama** → tidak disentuh.

### `_build_pipeline(rtsp_url)` — Pipeline GStreamer

#### Mode `gst_rtsp` — RTSP via Software Decoder H.264 (Jetson)

```python
pipeline = (
    f"rtspsrc location={rtsp_url} latency=0 drop-on-latency=true "
    f"protocols={RTSP_TRANSPORT} ! "
    "rtph264depay ! h264parse ! avdec_h264 ! "
    "videoconvert ! video/x-raw,format=BGR ! "
    "appsink max-buffers=1 drop=true sync=false"
)
return pipeline, cv2.CAP_GSTREAMER
```
`avdec_h264` = software H.264 decoder (bukan `nvv4l2decoder` hardware) — lebih kompatibel dengan stream Hikvision. Dipilih berdasarkan pengujian nyata pada Jetson Nano P3450 dengan kamera Hikvision.

#### Mode `csi` — CSI Camera Fisik di Jetson

```python
pipeline = (
    f"nvarguscamerasrc sensor-id={sensor_id} ! "
    f"video/x-raw(memory:NVMM),width={FRAME_WIDTH},height={FRAME_HEIGHT},"
    f"framerate={FRAME_FPS}/1,format=NV12 ! "
    "nvvidconv flip-method=0 ! "
    "video/x-raw,format=BGRx ! videoconvert ! "
    "video/x-raw,format=BGR ! appsink max-buffers=1 drop=true sync=false"
)
```
URL di database: `csi://0` (sensor pertama) atau `csi://1`.

#### Mode `usb` — USB Webcam

```python
pipeline = (
    f"v4l2src device={device} ! "
    f"video/x-raw,width={FRAME_WIDTH},height={FRAME_HEIGHT},"
    f"framerate={FRAME_FPS}/1 ! "
    "videoconvert ! video/x-raw,format=BGR ! "
    "appsink max-buffers=1 drop=true sync=false"
)
```
URL di database: `usb:///dev/video0`.

#### Mode `ffmpeg` — Default untuk Development (PC)

```python
return rtsp_url, cv2.CAP_FFMPEG
```

### `_worker(key, state)` — Thread Per Kamera

```python
while not state.stop_event.is_set():
    cap = self._open_capture(state.rtsp_url)
    ok, frame = cap.read()

    state.frame_counter += 1
    if state.frame_counter % YOLO_DETECT_EVERY_N_FRAMES == 0:
        frame, detected = detect_person(frame)
        state.person_detected = detected
        if PLC_WRITE_ENABLED:
            write_cv_to_plc(room_id, int(detected))   # plc_writer.py

    with state.lock:
        state.latest_frame = frame.copy()
```

Jika kamera terputus, thread menunggu `RECONNECT_DELAY_SEC` lalu coba lagi — otomatis reconnect.

### `generate_mjpeg(room_id, slot)` — Generator Stream

```python
ok, buffer = cv2.imencode(".jpg", frame, [cv2.IMWRITE_JPEG_QUALITY, JPEG_QUALITY])
yield (
    b"--frame\r\n"
    b"Content-Type: image/jpeg\r\n\r\n"
    + buffer.tobytes()
    + b"\r\n"
)
time.sleep(0.03)   # ~33 FPS maksimal
```
Setiap klien browser mendapat generator sendiri di thread Flask terpisah.

---

## `plc_writer.py` — Tulis Hasil CV ke PLC via Modbus TCP

```python
from pymodbus.client.sync import ModbusTcpClient   # pymodbus 2.5.3 (API 2.x)
```

> **PENTING:** pymodbus 2.5.3 wajib digunakan (bukan >=3.0). API berbeda total — 3.x menggunakan `ModbusTcpClient` dari `pymodbus.client` dengan argumen `slave=` sedangkan 2.x dari `pymodbus.client.sync` dengan `unit=`.

```python
client = ModbusTcpClient(ip, port=PLC_MODBUS_PORT, timeout=PLC_MODBUS_TIMEOUT)
client.connect()
result = client.write_register(
    address=CV_PLC_WRITE_ADDRESS,   # PDU 9 = Holding Register 10 = %MW9
    value=value,                     # 1 (orang terdeteksi) atau 0 (tidak ada)
    unit=PLC_MODBUS_UNIT_ID          # 1 untuk Schneider M221 TM221CE24R
)
```

Mapping register:
- `CV_PLC_WRITE_ADDRESS = 9` (PDU 9)
- PDU 9 = "Holding Register 10" = address 40010 = `%MW9` di Schneider M221

Fungsi ini hanya dipanggil jika `PLC_WRITE_ENABLED=true` di `.env`.

---

## `camera_stream.py` — Flask Server

Entry point. Jalankan: `python camera_stream.py`.

### Startup

```python
manager = CameraManager()

if __name__ == "__main__":
    manager.refresh(load_cameras_sync())
    threading.Thread(target=_refresh_loop, daemon=True).start()
    app.run(host=CV_HOST, port=CV_PORT, threaded=True, debug=False)
```
`threaded=True` agar banyak browser bisa stream bersamaan.

### Endpoint HTTP

```python
@app.route("/stream/<int:room_id>/<int:slot>")
def stream(room_id, slot):
    return Response(
        manager.generate_mjpeg(room_id, slot),
        mimetype="multipart/x-mixed-replace; boundary=frame",
    )
```
Browser menggunakan `<img src="http://192.168.1.200:5001/stream/1/1">`.

```python
@app.route("/alarm_status")
def alarm_status():
    return jsonify({"ok": True, "rooms": manager.alarm_status()})
```
Dashboard polling ini setiap 1 detik. Contoh respons:
```json
{"ok": true, "rooms": {"1": {"1": false, "2": true}, "2": {"1": false}}}
```

```python
@app.route("/health")
def health():
    stream_count = sum(len(slots) for slots in rooms.values())
    return jsonify({"ok": True, "streams": stream_count})
```
Cek cepat: `curl http://192.168.1.200:5001/health`

---

## `test_yolo.py` — Verifikasi Model

```bash
cd camera
python test_yolo.py
```
Memastikan model YOLO berhasil dimuat sebelum server penuh dijalankan.

---

## Setup Jetson Nano — Panduan Lengkap

### Prasyarat sistem

```bash
# Wajib sebelum Python apapun
echo "export OPENBLAS_CORETYPE=ARMV8" >> ~/.bashrc
source ~/.bashrc

# Python 3.8 (JetPack 4.6 default Python 3.6, tapi kode butuh 3.7+)
sudo apt update
sudo apt install python3.8 python3.8-venv python3.8-dev
```

### Install dependensi

```bash
cd camera
python3.8 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt   # termasuk pymodbus==2.5.3
```

### File `.env` untuk Jetson

```env
CV_HOST=0.0.0.0
CV_PORT=5001

LARAVEL_BRIDGE_CAMERAS_URL=http://192.168.1.10:8000/api/cv/bridge-cameras?token=TOKEN

YOLO_ENABLED=true
YOLO_DEVICE=cuda:0
YOLO_HALF=true
YOLO_DETECT_EVERY_N_FRAMES=1

CAMERA_INPUT_MODE=gst_rtsp

PLC_WRITE_ENABLED=true
PLC_MODBUS_UNIT_ID=1
CV_PLC_WRITE_ADDRESS=9
PLC_04_IP=192.168.1.4    # IP PLC Schneider M221 untuk room_id 4
```

### Model YOLO

Model didownload otomatis saat pertama kali jalan. Untuk performa maksimal gunakan TensorRT:

```python
from ultralytics import YOLO
YOLO('models/yolov8n.pt').export(format='engine', device='cuda:0', half=True, imgsz=640)
# Menghasilkan: models/yolov8n.engine
```

Lalu update `.env`:
```env
YOLO_MODEL_PATH=models/yolov8n.engine
```

### Format URL kamera Hikvision (Settings → Camera)

| Jenis Kamera          | URL di Dashboard                                                       |
| --------------------- | ---------------------------------------------------------------------- |
| IP Camera RTSP (main) | `rtsp://admin:pass@192.168.1.64:554/Streaming/Channels/101`            |
| IP Camera RTSP (sub)  | `rtsp://admin:pass@192.168.1.64:554/Streaming/Channels/102` (640×360, lebih ringan) |
| CSI Camera slot 1     | `csi://0`                                                              |
| CSI Camera slot 2     | `csi://1`                                                              |
| USB Webcam pertama    | `usb:///dev/video0`                                                    |

Gunakan `Channels/102` (sub-stream) untuk hemat bandwidth dan CPU.

### Jalankan service

```bash
cd camera
source .venv/bin/activate
python camera_stream.py
```

Cek: `curl http://192.168.1.200:5001/health`

### Perbandingan performa

| Konfigurasi                              | FPS Deteksi YOLO | Catatan                      |
| ---------------------------------------- | ---------------- | ---------------------------- |
| PC, `ffmpeg`, `cpu`                      | ~5–10 FPS        | Untuk development/testing    |
| Jetson, `gst_rtsp`, `.pt`, `cuda:0`      | ~15–20 FPS       | Performa dasar Jetson GPU    |
| Jetson, `gst_rtsp`, `.engine`, `cuda:0`  | ~30 FPS          | Optimal dengan TensorRT      |
