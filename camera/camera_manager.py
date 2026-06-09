from __future__ import annotations

import logging
import threading
import time
from dataclasses import dataclass, field

import cv2

from config import JPEG_QUALITY, RECONNECT_DELAY_SEC, YOLO_DETECT_EVERY_N_FRAMES
from yolo_detector import detect_person

logger = logging.getLogger("cv.camera")


@dataclass
class StreamState:
    rtsp_url: str
    name: str
    room_name: str
    latest_frame: object | None = None
    person_detected: bool = False
    frame_counter: int = 0
    lock: threading.Lock = field(default_factory=threading.Lock)
    stop_event: threading.Event = field(default_factory=threading.Event)


class CameraManager:
    def __init__(self) -> None:
        self._streams: dict[tuple[int, int], StreamState] = {}
        self._threads: dict[tuple[int, int], threading.Thread] = {}
        self._map_lock = threading.Lock()

    def refresh(self, camera_map: dict[int, dict[int, dict]]) -> None:
        desired: set[tuple[int, int]] = set()
        for room_id, slots in camera_map.items():
            for slot in slots:
                desired.add((int(room_id), int(slot)))

        with self._map_lock:
            current = set(self._streams.keys())
            for key in current - desired:
                self._stop_stream(key)

            for room_id, slots in camera_map.items():
                for slot, meta in slots.items():
                    key = (int(room_id), int(slot))
                    rtsp_url = str(meta["rtsp_url"])
                    if key in self._streams and self._streams[key].rtsp_url == rtsp_url:
                        self._streams[key].name = str(meta.get("name", self._streams[key].name))
                        self._streams[key].room_name = str(meta.get("room_name", self._streams[key].room_name))
                        continue
                    if key in self._streams:
                        self._stop_stream(key)
                    state = StreamState(
                        rtsp_url=rtsp_url,
                        name=str(meta.get("name", f"Camera {slot}")),
                        room_name=str(meta.get("room_name", f"Room {room_id}")),
                    )
                    self._streams[key] = state
                    thread = threading.Thread(
                        target=self._worker,
                        args=(key, state),
                        name=f"cv-{room_id}-{slot}",
                        daemon=True,
                    )
                    self._threads[key] = thread
                    thread.start()
                    logger.info("Started stream room=%s slot=%s (%s)", room_id, slot, state.name)

    def _stop_stream(self, key: tuple[int, int]) -> None:
        state = self._streams.pop(key, None)
        thread = self._threads.pop(key, None)
        if state:
            state.stop_event.set()
        if thread and thread.is_alive():
            thread.join(timeout=3)
        logger.info("Stopped stream room=%s slot=%s", key[0], key[1])

    def _open_capture(self, rtsp_url: str) -> cv2.VideoCapture | None:
        cap = cv2.VideoCapture(rtsp_url, cv2.CAP_FFMPEG)
        cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)
        time.sleep(1)
        if cap.isOpened():
            return cap
        cap.release()
        return None

    def _worker(self, key: tuple[int, int], state: StreamState) -> None:
        room_id, slot = key
        cap: cv2.VideoCapture | None = None

        while not state.stop_event.is_set():
            try:
                if cap is None or not cap.isOpened():
                    if cap is not None:
                        cap.release()
                    logger.info("Connecting RTSP room=%s slot=%s", room_id, slot)
                    cap = self._open_capture(state.rtsp_url)
                    if cap is None:
                        logger.warning("RTSP connect failed room=%s slot=%s — retry in %ss", room_id, slot, RECONNECT_DELAY_SEC)
                        time.sleep(RECONNECT_DELAY_SEC)
                        continue

                ok, frame = cap.read()
                if not ok or frame is None:
                    logger.warning("Frame read failed room=%s slot=%s — reconnecting", room_id, slot)
                    cap.release()
                    cap = None
                    time.sleep(RECONNECT_DELAY_SEC)
                    continue

                state.frame_counter += 1
                if state.frame_counter % YOLO_DETECT_EVERY_N_FRAMES == 0:
                    frame, detected = detect_person(frame)
                    state.person_detected = detected

                with state.lock:
                    state.latest_frame = frame.copy()

            except Exception as exc:
                logger.error("Worker error room=%s slot=%s: %s", room_id, slot, exc)
                if cap is not None:
                    try:
                        cap.release()
                    except Exception:
                        pass
                    cap = None
                time.sleep(RECONNECT_DELAY_SEC)

        if cap is not None:
            cap.release()

    def generate_mjpeg(self, room_id: int, slot: int):
        key = (room_id, slot)
        while True:
            state = self._streams.get(key)
            if state is None or state.stop_event.is_set():
                time.sleep(0.1)
                continue

            with state.lock:
                frame = None if state.latest_frame is None else state.latest_frame.copy()

            if frame is None:
                time.sleep(0.05)
                continue

            ok, buffer = cv2.imencode(".jpg", frame, [cv2.IMWRITE_JPEG_QUALITY, JPEG_QUALITY])
            if not ok:
                continue

            yield (
                b"--frame\r\n"
                b"Content-Type: image/jpeg\r\n\r\n"
                + buffer.tobytes()
                + b"\r\n"
            )
            time.sleep(0.03)

    def alarm_status(self) -> dict[str, dict[str, bool]]:
        rooms: dict[str, dict[str, bool]] = {}
        with self._map_lock:
            for (room_id, slot), state in self._streams.items():
                rid = str(room_id)
                rooms.setdefault(rid, {})[str(slot)] = bool(state.person_detected)
        return rooms
