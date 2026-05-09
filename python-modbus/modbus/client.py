import asyncio, struct
from pymodbus.client import AsyncModbusTcpClient
from modbus.register_map import REGISTER_MAP
from config import MODBUS_PORT, MODBUS_TIMEOUT

def parse_value(raw_registers: list, address: int) -> any:
    """Parse nilai register sesuai tipe data."""
    reg_info = REGISTER_MAP.get(address)
    if not reg_info:
        return None

    idx = address - 40001  # konversi ke 0-based index

    if reg_info["type"] == "bool":
        return bool(raw_registers[idx])

    elif reg_info["type"] == "uint16":
        return raw_registers[idx]

    elif reg_info["type"] == "float32":
        # Gabungkan 2 register menjadi float32 (big endian)
        if idx + 1 < len(raw_registers):
            raw = struct.pack(">HH", raw_registers[idx], raw_registers[idx + 1])
            return round(struct.unpack(">f", raw)[0], 3)
        return None

    return raw_registers[idx]

async def read_plc(room_id: int, plc_config: dict) -> dict:
    """Baca semua holding register dari 1 PLC."""
    client = AsyncModbusTcpClient(
        plc_config["ip"],
        port=MODBUS_PORT,
        timeout=MODBUS_TIMEOUT
    )

    result_data = {}
    status = "success"
    error_msg = None

    try:
        connected = await client.connect()
        if not connected:
            return {
                "room_id": room_id,
                "status": "offline",
                "data": {},
                "error": "Connection refused"
            }

        # Baca 12 register sekaligus (40001-40012, index 0-11)
        result = await client.read_holding_registers(address=0, count=12, slave=plc_config["unit_id"])

        if result.isError():
            status = "error"
            error_msg = str(result)
        else:
            regs = result.registers
            for address, info in REGISTER_MAP.items():
                idx = address - 40001
                if idx < len(regs):
                    parsed = parse_value(regs, address)
                    result_data[address] = {
                        "key":   info["key"],
                        "desc":  info["desc"],
                        "value": parsed,
                        "raw":   regs[idx],
                    }

    except Exception as e:
        status = "error"
        error_msg = str(e)
    finally:
        client.close()

    return {
        "room_id":    room_id,
        "plc_name":   plc_config["name"],
        "status":     status,
        "data":       result_data,
        "error":      error_msg,
    }