from __future__ import annotations
import os
from dotenv import load_dotenv

load_dotenv()

# ─── Modbus TCP ───────────────────────────────────────────────────────────────

MODBUS_PORT    = int(os.getenv("MODBUS_PORT",    "502"))
MODBUS_TIMEOUT = float(os.getenv("MODBUS_TIMEOUT", "3.0"))

# ─── Polling ──────────────────────────────────────────────────────────────────

POLL_INTERVAL_SEC = float(os.getenv("POLL_INTERVAL_SEC", "5.0"))

# ─── Laravel integration ──────────────────────────────────────────────────────

LARAVEL_WEBHOOK         = os.getenv("LARAVEL_WEBHOOK_URL",  "http://127.0.0.1:8000/api/plc/webhook")
LARAVEL_WEBHOOK_TIMEOUT = float(os.getenv("LARAVEL_WEBHOOK_TIMEOUT", "30.0"))
WEBHOOK_MAX_CONCURRENT  = max(1, int(os.getenv("WEBHOOK_MAX_CONCURRENT", "2")))

# Re-fetch daftar enabled device dari Laravel Settings (0 = hanya saat startup)
PLC_MAP_REFRESH_SEC = float(os.getenv("PLC_MAP_REFRESH_SEC", "15"))


def _dev(room_id: int, default_ip: str, name: str) -> dict:
    """Build satu entry PLC device dengan override per-room via env."""
    env_key = f"PLC_{room_id:02d}_IP"
    return {
        "ip":      os.getenv(env_key, default_ip),
        "name":    name,
        "unit_id": int(os.getenv("PLC_DEFAULT_UNIT_ID", "1")),
        "port":    MODBUS_PORT,
    }


# ─── PLC Devices (SINKRON dengan DatabaseSeeder) ─────────────────────────────
#
# SEBELUM deploy ke lapangan:
# 1. Jalankan `php artisan db:seed` → cek id di tabel testing_rooms
# 2. Cocokkan id di sini dengan id database
# 3. Isi IP PLC yang sebenarnya via Settings UI atau env PLC_xx_IP
#
PLC_DEVICES: dict[int, dict] = {
    1:  _dev(1,  "192.168.1.1", "Test Pit 1"),
    2:  _dev(2,  "192.168.1.2", "Test Pit 2"),
    3:  _dev(3,  "192.168.1.3", "Test Pit 3"),
    4:  _dev(4,  "192.168.1.4", "Test Pit 4"),
    5:  _dev(5,  "192.168.1.5", "Test Pit 5"),
    6:  _dev(6,  "192.168.1.6", "Test Pit 6"),
    7:  _dev(7,  "192.168.1.11", "Test Cell 1"),
    8:  _dev(8,  "192.168.1.12", "Test Cell 2"),
    9:  _dev(9,  "192.168.1.13", "Test Cell 3"),
    10: _dev(10, "192.168.1.14", "Test Cell 4"),
    11: _dev(11, "192.168.1.15", "Test Cell 5"),
}