"""
python-modbus/modbus/client.py
Async Modbus TCP client wrapper.
Handles connection, reading holding registers, dan parsing nilai.
"""

import struct
import asyncio
from pymodbus.client import AsyncModbusTcpClient
from pymodbus.exceptions import ModbusException

from modbus.register_map import REGISTER_MAP, REGISTER_COUNT, REGISTER_START, address_to_index
from config import MODBUS_PORT, MODBUS_TIMEOUT


def parse_register_value(registers: list[int], address: int) -> tuple[any, int]:
    """
    Parse nilai register sesuai tipe data.
    Returns: (parsed_value, raw_value)
    """
    info = REGISTER_MAP.get(address)
    if info is None:
        return None, 0

    idx = address_to_index(address)
    if idx >= len(registers):
        return None, 0

    raw = registers[idx]

    match info["type"]:
        case "bool":
            return bool(raw), raw

        case "uint16":
            return raw, raw

        case "float32":
            # Float 32-bit = 2 register × 16-bit, big-endian (IEEE 754)
            if idx + 1 < len(registers):
                packed = struct.pack(">HH", registers[idx], registers[idx + 1])
                value  = struct.unpack(">f", packed)[0]
                return round(value, 3), raw
            return None, raw

        case _:
            return raw, raw


async def read_plc_registers(room_id: int, plc_config: dict) -> dict:
    """
    Baca semua holding register dari 1 PLC.

    Returns dict:
    {
        "room_id":  1,
        "plc_name": "Test Cell 1",
        "status":   "success" | "offline" | "error",
        "data": {
            40001: {"key": "tekanan_masuk", "desc": "...", "value": 0, "raw": 0},
            ...
        },
        "error": None | "pesan error"
    }
    """
    result = {
        "room_id":  room_id,
        "plc_name": plc_config["name"],
        "status":   "success",
        "data":     {},
        "error":    None,
    }

    client = AsyncModbusTcpClient(
        host=plc_config["ip"],
        port=MODBUS_PORT,
        timeout=MODBUS_TIMEOUT,
    )

    try:
        connected = await asyncio.wait_for(client.connect(), timeout=MODBUS_TIMEOUT)
        if not connected:
            result["status"] = "offline"
            result["error"]  = "Connection refused"
            return result

        # Baca semua register sekaligus (lebih efisien dari 1-per-1)
        response = await client.read_holding_registers(
            address=REGISTER_START,
            count=REGISTER_COUNT,
            slave=plc_config["unit_id"],
        )

        if response.isError():
            result["status"] = "error"
            result["error"]  = str(response)
            return result

        regs = response.registers

        for address, info in REGISTER_MAP.items():
            idx = address_to_index(address)
            if idx < len(regs):
                parsed_val, raw_val = parse_register_value(regs, address)
                result["data"][address] = {
                    "key":   info["key"],
                    "desc":  info["desc"],
                    "value": parsed_val,
                    "raw":   raw_val,
                }

    except asyncio.TimeoutError:
        result["status"] = "offline"
        result["error"]  = "Connection timeout"

    except ModbusException as e:
        result["status"] = "error"
        result["error"]  = f"Modbus error: {e}"

    except Exception as e:
        result["status"] = "error"
        result["error"]  = str(e)

    finally:
        client.close()

    return result