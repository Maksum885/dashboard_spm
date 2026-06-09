"""Quick check that the YOLO model loads. Run: cd camera && python test_yolo.py"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

from yolo_detector import model

print(f"YOLO model loaded: {model.model_name if hasattr(model, 'model_name') else 'ok'}")
