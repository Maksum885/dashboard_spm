import os
import cv2
import time
import threading

from flask import Flask, Response, jsonify
from flask_cors import CORS
from yolo_detector import detect_person

# =====================================
# FORCE RTSP TCP
# =====================================
os.environ["OPENCV_FFMPEG_CAPTURE_OPTIONS"] = "rtsp_transport;tcp"

app = Flask(__name__)

CORS(app)

# =====================================
# CAMERA CONFIG
# =====================================
USERNAME = "admin"
PASSWORD = "Multimitraguna99"
IP_CAMERA = "192.168.1.64"

RTSP_URL = (
    f"rtsp://{USERNAME}:{PASSWORD}"
    f"@{IP_CAMERA}:554/Streaming/Channels/102"
)

# =====================================
# GLOBALS
# =====================================
latest_frame = None
camera = None

frame_lock = threading.Lock()

person_detected = False
frame_counter = 0

# =====================================
# CONNECT CAMERA
# =====================================
def create_camera():

    print("🔄 Connecting camera...")

    cap = cv2.VideoCapture(
        RTSP_URL,
        cv2.CAP_FFMPEG
    )

    cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)

    time.sleep(1)

    if cap.isOpened():
        print("✅ Kamera berhasil connect")
    else:
        print("❌ Kamera gagal connect")

    return cap


# =====================================
# CAMERA THREAD
# =====================================
def camera_worker():

    global camera
    global latest_frame
    global person_detected
    global frame_counter

    camera = create_camera()

    while True:

        try:

            if camera is None or not camera.isOpened():

                try:
                    camera.release()
                except:
                    pass

                camera = None

                time.sleep(2)

                camera = create_camera()
                continue

            success, frame = camera.read()

            if not success or frame is None:

                print("⚠️ Frame gagal, reconnect...")

                try:
                    camera.release()
                except:
                    pass

                camera = None

                time.sleep(2)

                camera = create_camera()
                continue

            # =========================
            # YOLO DETECTION
            # =========================
            frame_counter += 1

            if frame_counter % 5 == 0:

                frame, detected = detect_person(frame)

                person_detected = detected

            # =========================
            # SAVE FRAME
            # =========================
            with frame_lock:
                latest_frame = frame.copy()

        except Exception as e:

            print("❌ Camera Error:", e)

            try:
                camera.release()
            except:
                pass

            camera = None

            time.sleep(2)

            camera = create_camera()


# =====================================
# MJPEG STREAM
# =====================================
def generate_frames():

    global latest_frame

    while True:

        try:

            if latest_frame is None:
                time.sleep(0.05)
                continue

            with frame_lock:
                frame = latest_frame.copy()

            success, buffer = cv2.imencode(
                ".jpg",
                frame,
                [cv2.IMWRITE_JPEG_QUALITY, 70]
            )

            if not success:
                continue

            yield (
                b"--frame\r\n"
                b"Content-Type: image/jpeg\r\n\r\n"
                + buffer.tobytes()
                + b"\r\n"
            )

            time.sleep(0.03)

        except GeneratorExit:
            break

        except Exception as e:

            print("❌ Stream Error:", e)
            time.sleep(1)


# =====================================
# ROUTES
# =====================================
@app.route("/")
def home():

    return "Flask CCTV + YOLO Active"


@app.route("/camera1")
def camera1():

    return Response(
        generate_frames(),
        mimetype="multipart/x-mixed-replace; boundary=frame"
    )


@app.route("/alarm_status")
def alarm_status():

    return jsonify({
        "person_detected": person_detected
    })


# =====================================
# START
# =====================================
if __name__ == "__main__":

    threading.Thread(
        target=camera_worker,
        daemon=True
    ).start()

    print("🚀 Starting Flask YOLO...")

    app.run(
        host="192.168.1.100",
        port=5000,
        threaded=True,
        debug=False
    )