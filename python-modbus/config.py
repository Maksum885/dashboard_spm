import os
from dotenv import load_dotenv
load_dotenv()

PLC_DEVICES = {
    1:  {"ip": os.getenv("PLC_01_IP", "192.168.1.1"), "name": "Test Pit 1",  "unit_id": 1},
    2:  {"ip": os.getenv("PLC_02_IP", "192.168.1.2"), "name": "Test Pit 2",  "unit_id": 1},
    3:  {"ip": os.getenv("PLC_03_IP", "192.168.1.3"), "name": "Test Pit 3",  "unit_id": 1},
    4:  {"ip": os.getenv("PLC_04_IP", "192.168.1.4"), "name": "Test Pit 4",  "unit_id": 1},
    5:  {"ip": os.getenv("PLC_05_IP", "192.168.1.5"), "name": "Test Pit 5",  "unit_id": 1},
    6:  {"ip": os.getenv("PLC_06_IP", "192.168.1.6"), "name": "Test Pit 6",   "unit_id": 1},
    7:  {"ip": os.getenv("PLC_07_IP", "192.168.1.11"), "name": "Test Cell 1",   "unit_id": 1},
    8:  {"ip": os.getenv("PLC_08_IP", "192.168.1.12"), "name": "Test Cell 2",   "unit_id": 1},
    9:  {"ip": os.getenv("PLC_09_IP", "192.168.1.13"), "name": "Test Cell 3",   "unit_id": 1},
    10: {"ip": os.getenv("PLC_10_IP", "192.168.1.14"), "name": "Test Cell 4",   "unit_id": 1},
    11: {"ip": os.getenv("PLC_11_IP", "192.168.1.15"), "name": "Test Cell 5",   "unit_id": 1},
}

MODBUS_PORT        = int(os.getenv("MODBUS_PORT", 502))
MODBUS_TIMEOUT     = float(os.getenv("MODBUS_TIMEOUT", 3.0))
POLL_INTERVAL_SEC  = float(os.getenv("POLL_INTERVAL_SEC", 5.0))
LARAVEL_WEBHOOK    = os.getenv("LARAVEL_WEBHOOK_URL", "http://127.0.0.1:8000/api/plc/webhook")
