from __future__ import annotations

import os
from pathlib import Path

from dotenv import load_dotenv

load_dotenv()

BASE_DIR = Path(__file__).resolve().parent

CV_HOST = os.getenv("CV_HOST", "0.0.0.0")
CV_PORT = int(os.getenv("CV_PORT", "5000"))

LARAVEL_BRIDGE_CAMERAS_URL = os.getenv("LARAVEL_BRIDGE_CAMERAS_URL", "").strip()
CV_MAP_REFRESH_SEC = float(os.getenv("CV_MAP_REFRESH_SEC", "15"))

YOLO_MODEL_PATH = os.getenv("YOLO_MODEL_PATH", str(BASE_DIR / "models" / "yolov8n.pt"))
YOLO_DETECT_EVERY_N_FRAMES = max(1, int(os.getenv("YOLO_DETECT_EVERY_N_FRAMES", "5")))
JPEG_QUALITY = max(10, min(100, int(os.getenv("JPEG_QUALITY", "70"))))

RTSP_TRANSPORT = os.getenv("RTSP_TRANSPORT", "tcp")
RECONNECT_DELAY_SEC = float(os.getenv("RECONNECT_DELAY_SEC", "2"))
