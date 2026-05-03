import cv2
from flask import Flask, Response

app = Flask(__name__)

# ==============================
# 🔐 ISI SENDIRI BAGIAN INI
# ==============================
username = "admin"
password = "Multimitraguna99"   # 🔥 isi sendiri
ip_camera = "192.168.1.64"

# 🔥 RTSP URL (substream lebih ringan)
rtsp_url = f"rtsp://{username}:{password}@{ip_camera}:554/Streaming/Channels/102"

# ==============================
# 🎥 CONNECT CAMERA
# ==============================
camera = cv2.VideoCapture(rtsp_url, cv2.CAP_FFMPEG)

if not camera.isOpened():
    print("❌ Kamera tidak terbuka (cek username/password/RTSP)")
else:
    print("✅ Kamera berhasil connect")

# ==============================
# 🎥 STREAM GENERATOR
# ==============================
def generate_frames():
    global camera

    while True:
        success, frame = camera.read()

        # 🔥 HANDLE ERROR (INI KUNCI FIX)
        if not success or frame is None:
            print("❌ Frame error, reconnect...")
            camera.release()
            camera = cv2.VideoCapture(rtsp_url, cv2.CAP_FFMPEG)
            continue

        # resize biar ringan
        frame = cv2.resize(frame, (640, 360))

        _, buffer = cv2.imencode('.jpg', frame)
        frame_bytes = buffer.tobytes()

        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

# ==============================
# 🌐 ROUTE
# ==============================
@app.route('/')
def home():
    return "Flask CCTV aktif"

@app.route('/camera1')
def camera1():
    return Response(generate_frames(),
        mimetype='multipart/x-mixed-replace; boundary=frame')

# ==============================
# 🚀 RUN SERVER
# ==============================
if __name__ == "__main__":
    print("🚀 Starting Flask...")
    app.run(host="0.0.0.0", port=5000, debug=True)