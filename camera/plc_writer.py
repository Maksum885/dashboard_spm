"""
PlcWriter — Tulis hasil deteksi orang dari Jetson ke PLC via Modbus TCP FC6.

Bagian dari skema HYBRID:
  Jalur cepat  : Jetson → /alarm_status → Browser (latensi ~1 detik, tetap berjalan)
  Jalur terpadu: Jetson → PLC reg %MW14 → python-modbus → Laravel → Pusher → Dashboard
                 (alarm CV masuk ke AlarmLog & PlcRegisterLog seperti alarm PLC lainnya)

Cara kerja PlcWriter:
  1. Setiap kali _run_yolo_async() selesai, ia memanggil plc_writer.update(room_id, slot, detected)
  2. PlcWriter melacak state per-slot secara terpisah
  3. Jika state AGREGAT ruangan berubah (ada orang ↔ tidak ada orang), baru menulis ke PLC
  4. Penulisan dilakukan via FC6 (Write Single Register) ke holding register %MW14
  5. Koneksi dibuka dan ditutup per penulisan — write terjadi hanya saat state berubah
     sehingga frekuensi koneksi sangat rendah

Konfigurasi di camera/.env:
  PLC_WRITE_ENABLED=true
  PLC_MODBUS_PORT=502
  PLC_MODBUS_UNIT_ID=1          ← Schneider M221 TM221CE24R default = 1
  PLC_MODBUS_TIMEOUT=3.0
  CV_PLC_WRITE_ADDRESS=14       ← PDU address 14 = %MW14 di Schneider M221
  PLC_04_IP=192.168.1.4         ← IP Schneider M221 TM221CE24R (sesuaikan nomor ruangan)

PENTING — PDU address vs logical address Modbus:
  %MW14 di Schneider M221 = PDU address 14 (0-based) = logical address 40015 (standar Modbus).
  Dalam register_map.py kita, register ini diberi label "40013" karena perhitungan
  address_to_index() — tapi CV_PLC_WRITE_ADDRESS=14 sudah benar dan menulis ke %MW14.

VERSI LIBRARY:
  Menggunakan pymodbus 2.5.3 (bukan 3.x) karena Jetson JetPack 4.6 berbasis Python 3.6/3.8.
  API pymodbus 2.x berbeda dari 3.x:
    Import : from pymodbus.client.sync import ModbusTcpClient  (bukan pymodbus.client)
    Param  : unit=UNIT_ID  (bukan slave=UNIT_ID)
    Host   : posisi pertama (bukan keyword host=)
"""

from __future__ import annotations

import logging
import os
import threading

logger = logging.getLogger("cv.plc_writer")


class PlcWriter:
    """
    Menulis status deteksi orang ke holding register PLC via Modbus TCP FC6.

    Thread-safe: dipanggil dari beberapa thread YOLO pool secara bersamaan.
    Menggunakan per-ruangan lock agar penulisan ke PLC yang berbeda tidak saling blokir.
    """

    def __init__(self) -> None:
        # State deteksi terakhir per slot: {(room_id, slot): bool}
        # Digunakan untuk menghitung agregat per ruangan.
        self._slot_state: dict[tuple[int, int], bool] = {}

        # State agregat terakhir yang sudah ditulis ke PLC per ruangan: {room_id: bool}
        # Digunakan untuk menghindari penulisan ulang jika tidak ada perubahan.
        self._last_written: dict[int, bool] = {}

        # Lock per ruangan agar tidak ada dua thread yang menulis ke PLC yang sama sekaligus.
        # defaultdict-like: dibuat saat pertama kali diakses.
        self._room_locks: dict[int, threading.Lock] = {}
        self._meta_lock = threading.Lock()  # melindungi akses ke _room_locks itu sendiri

    def _get_room_lock(self, room_id: int) -> threading.Lock:
        """Ambil atau buat lock untuk ruangan tertentu."""
        with self._meta_lock:
            if room_id not in self._room_locks:
                self._room_locks[room_id] = threading.Lock()
            return self._room_locks[room_id]

    def update(self, room_id: int, slot: int, detected: bool) -> None:
        """
        Perbarui state deteksi untuk satu slot kamera.

        Hanya menulis ke PLC jika state AGREGAT ruangan berubah:
          - false → true  : ada orang baru terdeteksi di ruangan ini
          - true → false  : semua kamera di ruangan ini sudah tidak mendeteksi orang

        Parameter:
          room_id  : ID ruangan (sama dengan room_id di database Laravel)
          slot     : nomor kamera dalam ruangan (1, 2, dst)
          detected : True jika YOLO mendeteksi orang di frame terakhir
        """
        from config import PLC_WRITE_ENABLED
        if not PLC_WRITE_ENABLED:
            return

        # Simpan state slot ini
        self._slot_state[(room_id, slot)] = detected

        # Hitung agregat: ruangan ini punya orang jika SALAH SATU slot mendeteksi orang
        room_detected = any(
            v for (r, _s), v in self._slot_state.items() if r == room_id
        )

        lock = self._get_room_lock(room_id)
        with lock:
            # Bandingkan dengan nilai terakhir yang ditulis ke PLC.
            # Jika sama, tidak perlu menulis lagi (hemat koneksi Modbus).
            last = self._last_written.get(room_id)
            if last == room_detected:
                return  # tidak ada perubahan, skip

            # State berubah — tulis ke PLC
            self._last_written[room_id] = room_detected
            self._write_to_plc(room_id, room_detected)

    def _write_to_plc(self, room_id: int, detected: bool) -> None:
        """
        Tulis 1 word ke holding register PLC via FC6 (Write Single Register).

        Koneksi dibuka → tulis → tutup dalam satu operasi.
        Ini aman karena penulisan hanya terjadi saat state berubah (jarang).

        Parameter:
          room_id  : digunakan untuk lookup IP PLC dari env PLC_xx_IP
          detected : True → tulis 1, False → tulis 0
        """
        from config import (
            CV_PLC_WRITE_ADDRESS,
            PLC_MODBUS_PORT,
            PLC_MODBUS_TIMEOUT,
            PLC_MODBUS_UNIT_ID,
        )

        # Lookup IP PLC untuk ruangan ini dari environment variable.
        # Pola: PLC_01_IP, PLC_02_IP, ... PLC_11_IP (sama seperti python-modbus/config.py)
        env_key = f"PLC_{room_id:02d}_IP"
        ip = os.getenv(env_key, "").strip()

        if not ip:
            # Tidak ada IP dikonfigurasi untuk ruangan ini — skip tanpa error.
            # (Jetson mungkin tidak punya akses ke semua PLC, atau belum dikonfigurasi)
            logger.debug("PLC_WRITE skip room=%s: %s tidak di-set", room_id, env_key)
            return

        value = 1 if detected else 0
        client = None  # inisialisasi di sini agar finally bisa cek apakah client sempat dibuat

        try:
            # pymodbus 2.5.3 (Jetson JetPack 4.6, Python 3.6/3.8).
            # Import path berbeda dari pymodbus 3.x:
            #   pymodbus 3.x : from pymodbus.client import ModbusTcpClient  ← SALAH untuk 2.x
            #   pymodbus 2.x : from pymodbus.client.sync import ModbusTcpClient  ← BENAR
            from pymodbus.client.sync import ModbusTcpClient

            logger.info(
                "FC6 WRITE room=%s ip=%s PDU=%s (%s) value=%s",
                room_id, ip, CV_PLC_WRITE_ADDRESS, "%MW" + str(CV_PLC_WRITE_ADDRESS),
                value,
            )

            # Buat client Modbus TCP.
            # pymodbus 2.x: host sebagai argumen posisi pertama (bukan keyword 'host=')
            client = ModbusTcpClient(
                ip,
                port=PLC_MODBUS_PORT,
                timeout=PLC_MODBUS_TIMEOUT,
            )

            # Coba konek ke PLC.
            connected = client.connect()
            if not connected:
                logger.warning(
                    "FC6 WRITE gagal room=%s ip=%s: TCP connect ditolak — "
                    "periksa IP PLC dan pastikan Modbus TCP aktif di PLC",
                    room_id, ip,
                )
                return

            # FC6: Write Single Register
            # address : PDU address (0-based). 14 = %MW14 di Schneider M221.
            # value   : 1 (ada orang) atau 0 (tidak ada orang)
            # unit    : Unit ID PLC. pymodbus 2.x pakai 'unit=', BUKAN 'slave=' (pymodbus 3.x)
            result = client.write_register(
                address=CV_PLC_WRITE_ADDRESS,
                value=value,
                unit=PLC_MODBUS_UNIT_ID,
            )

            if result.isError():
                logger.warning(
                    "FC6 WRITE error room=%s ip=%s PDU=%s: %s",
                    room_id, ip, CV_PLC_WRITE_ADDRESS, result,
                )
            else:
                logger.info(
                    "FC6 WRITE OK room=%s ip=%s %s → detected=%s",
                    room_id, ip, "%MW" + str(CV_PLC_WRITE_ADDRESS), bool(detected),
                )

        except ImportError:
            logger.error(
                "PLC_WRITE_ENABLED=true tapi pymodbus tidak terinstall. "
                "Jetson: pip install pymodbus==2.5.3"
            )

        except Exception as exc:
            logger.warning("FC6 WRITE exception room=%s ip=%s: %s", room_id, ip, exc)

        finally:
            # Tutup koneksi hanya jika client sempat dibuat.
            if client is not None:
                try:
                    client.close()
                except Exception:
                    pass

    def reset_room(self, room_id: int) -> None:
        """
        Reset state semua slot di ruangan ini.
        Dipanggil saat stream dihentikan (_stop_stream) agar state bersih
        dan penulisan berikutnya tidak skip karena dianggap belum berubah.
        """
        # Hapus semua slot milik ruangan ini
        keys_to_delete = [k for k in self._slot_state if k[0] == room_id]
        for k in keys_to_delete:
            del self._slot_state[k]

        # Reset nilai terakhir yang ditulis ke PLC untuk ruangan ini
        self._last_written.pop(room_id, None)


# Instance tunggal yang dipakai seluruh aplikasi.
# Diimpor langsung oleh camera_manager.py.
plc_writer = PlcWriter()
