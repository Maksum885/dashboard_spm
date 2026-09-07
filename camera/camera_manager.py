from __future__ import annotations

import logging
import threading
import time
from concurrent.futures import ThreadPoolExecutor
from dataclasses import dataclass, field

import cv2

from config import (
    CAMERA_INPUT_MODE,
    FRAME_FPS,
    FRAME_HEIGHT,
    FRAME_WIDTH,
    JPEG_QUALITY,
    RECONNECT_DELAY_SEC,
    RTSP_TRANSPORT,
    YOLO_DETECT_EVERY_N_FRAMES,
    YOLO_ENABLED,
)
from plc_writer import plc_writer  # skema hybrid: tulis hasil CV ke PLC register 40009
from yolo_detector import detect_person

logger = logging.getLogger("cv.camera")


# ─── StreamState ──────────────────────────────────────────────────────────────

@dataclass
class StreamState:
    """
    Menyimpan semua status satu stream kamera.
    Setiap kamera aktif punya satu instance StreamState sendiri.
    """

    rtsp_url:   str   # URL sumber kamera (RTSP, csi://, usb://)
    name:       str   # nama kamera (dari Laravel)
    room_name:  str   # nama ruangan (dari Laravel)

    # Frame terbaru langsung dari kamera (numpy array BGR), tanpa anotasi YOLO.
    # Diupdate oleh _worker pada setiap cap.read() yang berhasil.
    raw_frame: object | None = None

    # Frame yang dikirim ke browser.
    # Saat YOLO tidak berjalan: sama dengan raw_frame.
    # Saat YOLO selesai: raw_frame + kotak hijau + label orang.
    display_frame: object | None = None

    # Hasil deteksi orang dari YOLO terakhir (True/False).
    # Dibaca oleh alarm_status() dan dikirim ke dashboard setiap 1 detik.
    person_detected: bool = False

    # Penghitung frame kumulatif sejak stream dimulai.
    # Dipakai untuk menentukan kapan YOLO dijalankan (setiap N frame).
    frame_counter: int = 0

    # ID frame yang selalu naik setiap display_frame diperbarui.
    # generate_mjpeg membandingkan frame_id terakhir yang sudah dikirim
    # dengan nilai ini → hanya kirim ke browser jika ada yang baru.
    # Tanpa ini, MJPEG bisa mengirim frame yang sama berkali-kali (duplikat).
    frame_id: int = 0

    # Condition variable — menggabungkan lock dan sinyal "ada frame baru".
    # _worker dan _run_yolo_async memanggil notify_all() setelah memperbarui display_frame.
    # generate_mjpeg memanggil wait_for() untuk tidur efisien sampai ada frame baru.
    # Lebih efisien dari sleep() tetap karena tidak busy-wait dan tidak mengirim duplikat.
    frame_cond: threading.Condition = field(default_factory=threading.Condition)

    # Event untuk menghentikan thread _worker secara bersih.
    # Di-set oleh _stop_stream() → _worker keluar dari while loop dan release cap.
    stop_event: threading.Event = field(default_factory=threading.Event)


# ─── CameraManager ────────────────────────────────────────────────────────────

class CameraManager:
    def __init__(self) -> None:
        # Menyimpan semua StreamState yang sedang aktif.
        # Key: (room_id, slot) — tuple unik per kamera.
        self._streams: dict[tuple[int, int], StreamState] = {}

        # Menyimpan thread _worker untuk setiap kamera.
        # Key: sama dengan _streams.
        self._threads: dict[tuple[int, int], threading.Thread] = {}

        # Lock untuk melindungi akses ke _streams dan _threads dari banyak thread
        # (Flask request thread vs refresh thread bisa terjadi bersamaan).
        self._map_lock = threading.Lock()

    # ── refresh ───────────────────────────────────────────────────────────────

    def refresh(self, camera_map: dict[int, dict[int, dict]]) -> None:
        """
        Sinkronkan stream aktif dengan daftar kamera terbaru dari Laravel.

        Dipanggil saat startup dan setiap CV_MAP_REFRESH_SEC detik.
        - Kamera yang hilang dari daftar → thread-nya dihentikan.
        - Kamera baru atau URL berubah  → thread baru dibuat.
        - Kamera yang sama              → tidak disentuh (thread tetap jalan).
        """
        # Buat set semua kamera yang seharusnya aktif sekarang.
        desired: set[tuple[int, int]] = {
            (int(room_id), int(slot))
            for room_id, slots in camera_map.items()
            for slot in slots
        }

        with self._map_lock:
            current = set(self._streams.keys())

            # Hentikan kamera yang tidak ada di daftar terbaru.
            for key in current - desired:
                self._stop_stream(key)

            # Mulai atau perbarui kamera dari daftar terbaru.
            for room_id, slots in camera_map.items():
                for slot, meta in slots.items():
                    key = (int(room_id), int(slot))
                    rtsp_url = str(meta["rtsp_url"])

                    if key in self._streams:
                        if self._streams[key].rtsp_url == rtsp_url:
                            # URL tidak berubah — update nama saja, thread tetap jalan.
                            self._streams[key].name = str(meta.get("name", self._streams[key].name))
                            self._streams[key].room_name = str(meta.get("room_name", self._streams[key].room_name))
                            continue
                        # URL berubah — hentikan thread lama agar bisa membuat yang baru.
                        self._stop_stream(key)

                    # Buat StreamState baru untuk kamera ini.
                    state = StreamState(
                        rtsp_url=rtsp_url,
                        name=str(meta.get("name", f"Camera {slot}")),
                        room_name=str(meta.get("room_name", f"Room {room_id}")),
                    )
                    self._streams[key] = state

                    # Satu daemon thread per kamera.
                    # daemon=True → thread ikut mati saat proses Python berhenti.
                    thread = threading.Thread(
                        target=self._worker,
                        args=(key, state),
                        name=f"cv-{room_id}-{slot}",
                        daemon=True,
                    )
                    self._threads[key] = thread
                    thread.start()
                    logger.info("Stream dimulai room=%s slot=%s (%s)", room_id, slot, state.name)

    def _stop_stream(self, key: tuple[int, int]) -> None:
        """Hentikan stream satu kamera secara bersih."""
        state  = self._streams.pop(key, None)
        thread = self._threads.pop(key, None)

        if state:
            # Set stop_event → _worker akan keluar dari while loop pada iterasi berikutnya.
            state.stop_event.set()
            # Bangunkan generate_mjpeg yang sedang wait_for() agar bisa cek stop_event.
            with state.frame_cond:
                state.frame_cond.notify_all()

        if thread and thread.is_alive():
            # Tunggu maksimal 3 detik agar thread selesai dan melepas cap.
            thread.join(timeout=3)

        # Bersihkan state CV di PlcWriter agar saat stream dimulai lagi,
        # state tidak dianggap sudah ditulis dan write pertama tidak di-skip.
        plc_writer.reset_room(key[0])

        logger.info("Stream dihentikan room=%s slot=%s", key[0], key[1])

    # ── Pipeline builder (Jetson Nano integration) ────────────────────────────

    def _build_pipeline(self, rtsp_url: str) -> tuple[str | int, int | None]:
        """
        Tentukan cara cv2.VideoCapture membuka kamera berdasarkan CAMERA_INPUT_MODE.

        Mengembalikan (source, backend):
          source  → string pipeline GStreamer ATAU string URL RTSP
          backend → cv2.CAP_GSTREAMER atau cv2.CAP_FFMPEG

        cv2.VideoCapture(source, backend) akan menggunakan nilai ini.
        """
        mode = CAMERA_INPUT_MODE

        # ── Mode CSI — Kamera fisik di slot CSI Jetson Nano ──────────────────
        # Kamera yang terhubung langsung ke port kamera Jetson (bukan USB, bukan jaringan).
        # Contoh: Raspberry Pi Camera v2 (IMX219), Arducam.
        # URL di database diisi: 'csi://0' (kamera pertama) atau 'csi://1' (kedua).
        if mode == "csi" or rtsp_url.startswith("csi://"):
            sensor_id = 0
            if rtsp_url.startswith("csi://"):
                try:
                    # Ekstrak nomor sensor dari URL, contoh 'csi://1' → sensor_id=1
                    sensor_id = int(rtsp_url.replace("csi://", "").strip())
                except ValueError:
                    pass

            # Pipeline GStreamer untuk CSI Jetson:
            # nvarguscamerasrc → driver NVIDIA untuk sensor CSI (menggunakan ISP Jetson)
            # memory:NVMM      → frame langsung di memori GPU, tidak naik ke RAM CPU dulu
            # nvvidconv        → konversi format (NV12 → BGRx) dilakukan di GPU
            # appsink          → endpoint untuk OpenCV; drop=true buang frame lama
            pipeline = (
                f"nvarguscamerasrc sensor-id={sensor_id} ! "
                f"video/x-raw(memory:NVMM),width={FRAME_WIDTH},height={FRAME_HEIGHT},"
                f"framerate={FRAME_FPS}/1,format=NV12 ! "
                "nvvidconv flip-method=0 ! "
                "video/x-raw,format=BGRx ! videoconvert ! "
                "video/x-raw,format=BGR ! appsink max-buffers=1 drop=true sync=false"
            )
            logger.info("[CSI] Pipeline sensor=%s: %s", sensor_id, pipeline)
            return pipeline, cv2.CAP_GSTREAMER

        # ── Mode USB — USB Webcam via GStreamer ───────────────────────────────
        # USB webcam yang terhubung ke Jetson atau PC Linux.
        # URL di database: 'usb:///dev/video0' atau 'usb:///dev/video1'
        if mode == "usb" or rtsp_url.startswith("usb://"):
            device = "/dev/video0"
            if rtsp_url.startswith("usb://"):
                # Ekstrak path device, contoh 'usb:///dev/video1' → '/dev/video1'
                device = rtsp_url.replace("usb://", "").strip() or device

            # v4l2src → Video4Linux2, driver standar Linux untuk USB webcam
            # videoconvert → konversi format ke BGR (format yang dipakai OpenCV)
            # appsink drop=true → buang frame lama agar tidak menumpuk di buffer
            pipeline = (
                f"v4l2src device={device} ! "
                f"video/x-raw,width={FRAME_WIDTH},height={FRAME_HEIGHT},"
                f"framerate={FRAME_FPS}/1 ! "
                "videoconvert ! video/x-raw,format=BGR ! "
                "appsink max-buffers=1 drop=true sync=false"
            )
            logger.info("[USB] Pipeline device=%s: %s", device, pipeline)
            return pipeline, cv2.CAP_GSTREAMER

        # ── Mode GStreamer RTSP — IP Camera via Software Decoder ─────────────
        # Untuk IP camera RTSP di jaringan menggunakan avdec_h264 (software decoder).
        # Dikonfirmasi bekerja dengan Hikvision RTSP di Jetson Nano P3450.
        #
        # Catatan decoder:
        #   avdec_h264  → software H.264 (GStreamer libav). Lebih kompatibel,
        #                  bekerja dengan semua kamera RTSP termasuk Hikvision.
        #                  Digunakan di sini karena dikonfirmasi jalan di setup nyata.
        #   nvv4l2decoder → hardware H.264 Jetson (lebih cepat tapi membutuhkan
        #                   kamera yang mengirim bitstream dalam format yang tepat;
        #                   terkadang tidak kompatibel dengan sub-stream Hikvision).
        #
        # Hikvision sub-stream URL: rtsp://admin:pass@192.168.1.64:554/Streaming/Channels/102
        #   Channel 101 = main stream (1080p), Channel 102 = sub-stream (640×360, lebih ringan)
        if mode == "gst_rtsp":
            # rtspsrc           → sumber stream RTSP
            # latency=0         → tidak ada buffer delay (tampilan real-time)
            # protocols=tcp     → RTSP over TCP (lebih stabil dari UDP di LAN)
            # rtph264depay      → ekstrak payload H.264 dari paket RTP
            # h264parse         → parse bitstream H.264
            # avdec_h264        → decode H.264 ke raw video (software, CPU)
            # videoconvert      → konversi colorspace ke BGR (format OpenCV)
            # appsink drop=1    → selalu ambil frame terbaru, buang yang lama
            pipeline = (
                f"rtspsrc location={rtsp_url} latency=0 drop-on-latency=true "
                f"protocols={RTSP_TRANSPORT} ! "
                "rtph264depay ! h264parse ! avdec_h264 ! "
                "videoconvert ! video/x-raw,format=BGR ! "
                "appsink max-buffers=1 drop=true sync=false"
            )
            logger.info("[GStreamer RTSP] Pipeline: %s", pipeline)
            return pipeline, cv2.CAP_GSTREAMER

        # ── Mode Default — RTSP via FFmpeg (PC biasa) ─────────────────────────
        # OpenCV membuka URL RTSP langsung menggunakan FFmpeg.
        # Decoding H.264 dilakukan oleh CPU. Lebih mudah setup tapi lebih berat.
        # Tidak membutuhkan GStreamer terinstall.
        logger.info("[FFmpeg] URL: %s", rtsp_url)
        return rtsp_url, cv2.CAP_FFMPEG

    def _open_capture(self, rtsp_url: str) -> cv2.VideoCapture | None:
        """
        Buka cv2.VideoCapture menggunakan pipeline yang sesuai CAMERA_INPUT_MODE.
        Kembalikan None jika gagal membuka sumber kamera.
        """
        source, backend = self._build_pipeline(rtsp_url)
        cap = cv2.VideoCapture(source, backend)

        if backend == cv2.CAP_FFMPEG:
            # Untuk FFmpeg mode: set buffer internal OpenCV ke 1 frame.
            # Tanpa ini, OpenCV menyimpan banyak frame lama → video tertunda.
            cap.set(cv2.CAP_PROP_BUFFERSIZE, 1)

        # Beri waktu 1 detik agar pipeline GStreamer / koneksi RTSP stabil.
        time.sleep(1)

        if cap.isOpened():
            return cap

        cap.release()
        logger.warning("Gagal membuka kamera: %s (mode=%s)", rtsp_url, CAMERA_INPUT_MODE)
        return None

    # ── Worker thread (pembaca kamera) ────────────────────────────────────────

    def _worker(self, key: tuple[int, int], state: StreamState) -> None:
        """
        Thread utama per kamera. HANYA bertugas membaca frame secepat mungkin.
        Tidak melakukan inferensi YOLO — itu tugas _run_yolo_async().

        Jika koneksi kamera terputus, thread ini otomatis mencoba reconnect.
        Thread berhenti saat state.stop_event di-set oleh _stop_stream().
        """
        room_id, slot = key
        cap: cv2.VideoCapture | None = None

        # ThreadPoolExecutor dengan max 1 worker agar hanya ada 1 job YOLO
        # yang berjalan pada satu waktu per kamera.
        # Jika YOLO masih berjalan saat frame berikutnya datang, frame itu dilewati.
        yolo_pool = ThreadPoolExecutor(
            max_workers=1,
            thread_name_prefix=f"yolo-{room_id}-{slot}",
        )
        yolo_future = None  # Future dari submit YOLO terakhir

        try:
            while not state.stop_event.is_set():
                try:
                    # Jika cap belum ada atau sudah terputus, coba buka ulang.
                    if cap is None or not cap.isOpened():
                        if cap is not None:
                            cap.release()
                        logger.info("Menghubungkan kamera room=%s slot=%s ...", room_id, slot)
                        cap = self._open_capture(state.rtsp_url)
                        if cap is None:
                            # Kamera tidak bisa dibuka — tunggu lalu coba lagi.
                            logger.warning(
                                "Koneksi gagal room=%s slot=%s — retry dalam %ss",
                                room_id, slot, RECONNECT_DELAY_SEC,
                            )
                            time.sleep(RECONNECT_DELAY_SEC)
                            continue

                    # ── Baca satu frame dari kamera ──────────────────────────
                    # cap.read() memblokir sampai frame tersedia dari kamera.
                    # ok=False berarti stream terputus atau end-of-file.
                    ok, frame = cap.read()
                    if not ok or frame is None:
                        logger.warning("Gagal baca frame room=%s slot=%s — reconnect", room_id, slot)
                        cap.release()
                        cap = None
                        time.sleep(RECONNECT_DELAY_SEC)
                        continue

                    state.frame_counter += 1

                    # ── Submit YOLO secara asinkron ───────────────────────────
                    # Kondisi submit YOLO:
                    #   1. YOLO diaktifkan di .env
                    #   2. Sudah waktunya (setiap N frame)
                    #   3. Tidak ada job YOLO yang sedang berjalan (done() == True)
                    # Dengan ThreadPoolExecutor max_workers=1: jika YOLO masih jalan
                    # saat frame ke-N berikutnya datang, frame itu dilewati (tidak
                    # antri). Ini mencegah tumpukan job yang menyebabkan delay.
                    if (
                        YOLO_ENABLED
                        and state.frame_counter % YOLO_DETECT_EVERY_N_FRAMES == 0
                        and (yolo_future is None or yolo_future.done())
                    ):
                        # Kirim SALINAN frame ke YOLO (bukan referensi yang sama).
                        # PENTING: frame.copy() agar thread YOLO dan thread ini
                        # tidak bersamaan mengakses objek array yang sama.
                        # room_id dan slot diteruskan agar _run_yolo_async bisa
                        # memanggil plc_writer.update(room_id, slot, detected).
                        yolo_future = yolo_pool.submit(
                            self._run_yolo_async, state, frame.copy(), room_id, slot
                        )

                    # ── Perbarui frame yang ditampilkan ke browser ────────────
                    # Simpan raw frame (tanpa kotak YOLO) sebagai display_frame sementara.
                    # Jika YOLO selesai, _run_yolo_async() akan mengganti display_frame
                    # dengan versi yang sudah ada kotak hijaunya.
                    with state.frame_cond:
                        state.display_frame = frame
                        state.frame_id += 1
                        # Beritahu semua generate_mjpeg() yang sedang wait_for()
                        # bahwa ada frame baru → mereka langsung encode dan kirim ke browser.
                        state.frame_cond.notify_all()

                except Exception as exc:
                    logger.error("Error worker room=%s slot=%s: %s", room_id, slot, exc)
                    if cap is not None:
                        try:
                            cap.release()
                        except Exception:
                            pass
                    cap = None
                    time.sleep(RECONNECT_DELAY_SEC)

        finally:
            # Cleanup saat thread berhenti (karena stop_event atau exception fatal).
            yolo_pool.shutdown(wait=False)  # jangan tunggu YOLO yang sedang jalan
            if cap is not None:
                cap.release()

    def _run_yolo_async(self, state: StreamState, frame, room_id: int, slot: int) -> None:
        """
        Dijalankan di dalam ThreadPoolExecutor — thread TERPISAH dari _worker.

        Tidak pernah memblokir pembacaan frame kamera.
        Setelah YOLO selesai:
          1. Hasilnya disimpan ke display_frame → browser menerima frame beranotasi
          2. plc_writer.update() dipanggil → jika state berubah, tulis ke PLC register 40013
             (skema hybrid: alarm CV masuk ke pipeline PLC seperti alarm lainnya)
        """
        try:
            # detect_person() menjalankan inferensi YOLO dan menggambar kotak hijau.
            # Berjalan di GPU jika YOLO_DEVICE=cuda:0 (Jetson Nano).
            annotated, detected = detect_person(frame)

            # Simpan hasil deteksi di state (dibaca oleh /alarm_status → browser).
            state.person_detected = detected

            # ── Jalur terpadu: tulis ke PLC register 40013 ───────────────────
            # PlcWriter hanya benar-benar menulis ke PLC jika state AGREGAT ruangan
            # berubah (ada orang ↔ tidak ada orang), sehingga traffic Modbus minimal.
            # Jika PLC_WRITE_ENABLED=false, fungsi ini langsung return tanpa efek apapun.
            plc_writer.update(room_id, slot, detected)

            # Perbarui display_frame dengan versi yang sudah ada anotasi kotak.
            with state.frame_cond:
                state.display_frame = annotated
                state.frame_id += 1
                # Beritahu MJPEG clients bahwa ada versi frame baru (dengan kotak).
                state.frame_cond.notify_all()

        except Exception as exc:
            logger.error("Error YOLO async room=%s slot=%s: %s", room_id, slot, exc)

    # ── MJPEG generator ───────────────────────────────────────────────────────

    def generate_mjpeg(self, room_id: int, slot: int):
        """
        Generator Python yang menghasilkan frame MJPEG untuk satu koneksi browser.

        Dipanggil oleh Flask setiap kali ada request ke /stream/<room_id>/<slot>.
        Setiap browser tab yang membuka stream mendapat generator sendiri,
        berjalan di thread Flask tersendiri (karena app.run(threaded=True)).

        Perbaikan dari versi lama:
        - Lama: time.sleep(0.03) tetap → browser menerima frame duplikat
          (frame yang sama dikirim berkali-kali jika kamera lambat) +
          kalau kamera cepat (60fps), stream dibatasi 33fps oleh sleep.
        - Baru: frame_cond.wait_for() → generator tidur sampai ada frame BARU.
          Tidak ada duplikat. Jika kamera 30fps → browser terima tepat 30fps.
          Jika kamera 60fps → browser terima 60fps (tidak dibatasi sleep).
        """
        key = (room_id, slot)
        last_id = -1  # frame_id terakhir yang sudah dikirim ke browser ini

        while True:
            # Ambil state stream kamera.
            state = self._streams.get(key)
            if state is None or state.stop_event.is_set():
                # Kamera belum ada atau sudah dihentikan — tunggu sebentar lalu cek lagi.
                time.sleep(0.1)
                continue

            # Tunggu sampai ada frame BARU (frame_id berubah).
            # wait_for() melepas lock sementara menunggu, kemudian mengambil
            # lock lagi saat kondisi terpenuhi — sangat efisien, tidak busy-wait.
            # timeout=1.0 → cek stop_event setiap 1 detik jika tidak ada frame baru.
            with state.frame_cond:
                state.frame_cond.wait_for(
                    lambda: state.frame_id != last_id or state.stop_event.is_set(),
                    timeout=1.0,
                )

                # Jika tidak ada frame baru (timeout) atau tidak ada frame sama sekali, lanjut.
                if state.display_frame is None or state.frame_id == last_id:
                    continue

                # Update last_id dan ambil SALINAN frame selagi lock masih dipegang.
                # PENTING: copy() di sini agar lock bisa segera dilepas.
                # Encoding JPEG di bawah terjadi di luar lock → tidak memblokir _worker.
                last_id = state.frame_id
                frame = state.display_frame.copy()

            # Encode frame ke JPEG dan bungkus dalam format MJPEG multipart.
            # cv2.imencode lebih cepat dari PIL dan tidak butuh library tambahan.
            ok, buffer = cv2.imencode(
                ".jpg", frame,
                [cv2.IMWRITE_JPEG_QUALITY, JPEG_QUALITY],
            )
            if not ok:
                continue

            # Format MJPEG multipart — browser memahami format ini sebagai frame video.
            # Setiap frame diawali "--frame\r\n..." dan diakhiri "\r\n".
            yield (
                b"--frame\r\n"
                b"Content-Type: image/jpeg\r\n\r\n"
                + buffer.tobytes()
                + b"\r\n"
            )

    # ── Alarm status ──────────────────────────────────────────────────────────

    def alarm_status(self) -> dict[str, dict[str, bool]]:
        """
        Kembalikan status deteksi orang untuk semua kamera aktif.

        Contoh hasil:
          {"1": {"1": false, "2": true}, "2": {"1": false}}
          Artinya: di room 1 slot 2 ada orang terdeteksi.

        Dipanggil oleh endpoint /alarm_status setiap 1 detik dari dashboard.
        """
        rooms: dict[str, dict[str, bool]] = {}
        with self._map_lock:
            for (room_id, slot), state in self._streams.items():
                rid = str(room_id)
                rooms.setdefault(rid, {})[str(slot)] = bool(state.person_detected)
        return rooms
