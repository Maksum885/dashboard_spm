"""
Flask CV service: multi-room RTSP streams + YOLO person detection.

Streams:  GET /stream/<room_id>/<slot>
Alarms:   GET /alarm_status
Health:   GET /health
"""

from __future__ import annotations

import logging
import os
import threading
import time

from flask import Flask, Response, jsonify
from flask_cors import CORS

from camera_loader import load_cameras_sync
from camera_manager import CameraManager
from config import CV_HOST, CV_MAP_REFRESH_SEC, CV_PORT, RTSP_TRANSPORT

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("cv.app")

os.environ["OPENCV_FFMPEG_CAPTURE_OPTIONS"] = f"rtsp_transport;{RTSP_TRANSPORT}"

app = Flask(__name__)
CORS(app)

manager = CameraManager()


def _refresh_loop() -> None:
    while True:
        manager.refresh(load_cameras_sync())
        time.sleep(CV_MAP_REFRESH_SEC)


@app.route("/")
def home():
    return "SPM Testing Bay CV service running"


@app.route("/health")
def health():
    rooms = manager.alarm_status()
    stream_count = sum(len(slots) for slots in rooms.values())
    return jsonify({"ok": True, "streams": stream_count})


@app.route("/stream/<int:room_id>/<int:slot>")
def stream(room_id: int, slot: int):
    return Response(
        manager.generate_mjpeg(room_id, slot),
        mimetype="multipart/x-mixed-replace; boundary=frame",
    )


@app.route("/alarm_status")
def alarm_status():
    return jsonify({"ok": True, "rooms": manager.alarm_status()})


# Legacy route kept for older dashboard builds during transition.
@app.route("/camera1")
def camera1_legacy():
    return stream(1, 1)


if __name__ == "__main__":
    manager.refresh(load_cameras_sync())
    threading.Thread(target=_refresh_loop, name="cv-map-refresh", daemon=True).start()
    logger.info("Starting CV service on %s:%s", CV_HOST, CV_PORT)
    app.run(host=CV_HOST, port=CV_PORT, threaded=True, debug=False)
