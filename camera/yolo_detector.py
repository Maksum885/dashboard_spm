from __future__ import annotations

from pathlib import Path

import cv2
from ultralytics import YOLO

from config import YOLO_MODEL_PATH

_model_path = Path(YOLO_MODEL_PATH)
if not _model_path.is_file():
    _model_path = Path(__file__).resolve().parent / "models" / "yolov8n.pt"

model = YOLO(str(_model_path))


def detect_person(frame):
    results = model(frame, verbose=False)
    person_found = False

    for result in results:
        for box in result.boxes:
            cls = int(box.cls[0])
            if cls != 0:
                continue

            person_found = True
            x1, y1, x2, y2 = map(int, box.xyxy[0])
            conf = float(box.conf[0])

            cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
            cv2.putText(
                frame,
                f"PERSON {conf:.2f}",
                (x1, y1 - 10),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.6,
                (0, 255, 0),
                2,
            )

    return frame, person_found
