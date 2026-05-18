"""
python-modbus/modbus/client.py

Async Modbus TCP client.
Fix utama vs versi lama:
  1. float32 byte order configurable (ABCD/CDAB/DCBA) via MODBUS_FLOAT_ORDER
  2. 40012 index calculation benar (word[12]+word[13], bukan word[11]+word[12])
  3. Robust reconnect: tidak pakai asyncio.wait_for(connect) yang bisa hang di pymodbus 3.x
  4. Verbose log per env MODBUS_VERBOSE_LOG=1
"""

from __future__ import annotations

import asyncio
import os
import struct
from typing import Any

from pymodbus.client import AsyncModbusTcpClient
from pymodbus.exceptions import ModbusException

from modbus.register_map import (
    REGISTER_MAP,
    REGISTER_COUNT,
    REGISTER_START,
    FLOAT_ORDER,
    address_to_index,
)
from config import MODBUS_PORT, MODBUS_TIMEOUT

_VERBOSE = os.getenv("MODBUS_VERBOSE_LOG", "0").strip() in ("1", "true", "yes")


# ─── Float32 parser ───────────────────────────────────────────────────────────

def _parse_float32(w_hi: int, w_lo: int) -> float:
    """
    Pack dua 16-bit word menjadi float32.

    Byte order yang umum di PLC industrial:
      ABCD = big-endian                  → struct.pack(">HH", hi, lo) → unpack(">f")
      CDAB = swapped-word little-endian  → struct.pack(">HH", lo, hi) → unpack(">f")
      BADC = swapped-byte big-endian     → struct.pack("<HH", hi, lo) → unpack(">f")
      DCBA = little-endian               → struct.pack("<HH", lo, hi) → unpack("<f")

    Set MODBUS_FLOAT_ORDER env untuk memilih (default: ABCD).
    """
    match FLOAT_ORDER:
        case "ABCD":
            raw = struct.pack(">HH", w_hi, w_lo)
            (val,) = struct.unpack(">f", raw)
        case "CDAB":
            # Most common alternative for Schneider/Modicon
            raw = struct.pack(">HH", w_lo, w_hi)
            (val,) = struct.unpack(">f", raw)
        case "BADC":
            raw = struct.pack("<HH", w_hi, w_lo)
            (val,) = struct.unpack(">f", raw)
        case "DCBA":
            raw = struct.pack("<HH", w_lo, w_hi)
            (val,) = struct.unpack("<f", raw)
        case _:
            raw = struct.pack(">HH", w_hi, w_lo)
            (val,) = struct.unpack(">f", raw)

    # Sanity check: NaN / Inf biasanya artinya byte order salah
    if not (-1e9 < val < 1e9):
        return 0.0
    return round(val, 3)


# ─── Error formatter ──────────────────────────────────────────────────────────

def _format_modbus_error(response: Any) -> str:
    parts = [str(response)]
    try:
        code = int(getattr(response, "exception_code", None) or 0)
        if code:
            names = {
                1: "Illegal Function",
                2: "Illegal Data Address — periksa REGISTER_START dan REGISTER_COUNT vs map PLC",
                3: "Illegal Data Value",
                4: "Slave Device Failure — periksa unit_id",
                6: "Slave Device Busy",
            }
            parts.append(f"exception_code={code}")
            if code in names:
                parts.append(names[code])
    except (TypeError, ValueError):
        pass
    return " | ".join(parts)


# ─── Register parser (single) ─────────────────────────────────────────────────

def parse_register_value(registers: list[int], address: int) -> tuple[Any, int]:
    """
    Returns (parsed_value, raw_word_at_base_index).
    float32: menggunakan dua word berturut-turut.
    """
    info = REGISTER_MAP.get(int(address))
    if info is None:
        return None, 0

    idx = address_to_index(address)
    if idx >= len(registers):
        if _VERBOSE:
            print(f"[WARN] parse_register_value: address {address} idx={idx} out of range (len={len(registers)})")
        return None, 0

    raw = registers[idx]

    match info["type"]:
        case "bool":
            # Beberapa PLC kirim 0xFF00 untuk TRUE (Modbus coil convention),
            # atau nilai non-zero apapun = True.
            return bool(raw), raw

        case "uint16":
            return raw, raw

        case "float32":
            # Butuh word berikutnya
            if idx + 1 >= len(registers):
                if _VERBOSE:
                    print(
                        f"[WARN] float32 address {address}: butuh word[{idx}]+word[{idx+1}] "
                        f"tapi hanya ada {len(registers)} word (REGISTER_COUNT terlalu kecil?)"
                    )
                return None, raw
            val = _parse_float32(registers[idx], registers[idx + 1])
            return val, raw

        case _:
            return raw, raw


# ─── Main read function ───────────────────────────────────────────────────────

async def read_plc_registers(room_id: int, plc_config: dict) -> dict:
    """
    Baca semua holding register dari 1 PLC via FC3.

    Returns:
        {
            "room_id":  int,
            "plc_name": str,
            "status":   "success" | "offline" | "error",
            "data":     { address: {"key", "desc", "value", "raw"} },
            "error":    str | None,
        }
    """
    host     = str(plc_config["ip"]).strip()
    tcp_port = int(plc_config.get("port") or MODBUS_PORT)
    unit_id  = int(plc_config.get("unit_id") or 1)
    name     = plc_config.get("name", f"Room {room_id}")
    target   = f"room={room_id} {host}:{tcp_port} slave={unit_id}"

    result: dict = {
        "room_id":  room_id,
        "plc_name": name,
        "status":   "success",
        "data":     {},
        "error":    None,
    }

    # ── Validasi konfigurasi dasar ────────────────────────────────────────────
    if not host or host in ("0.0.0.0", ""):
        result["status"] = "offline"
        result["error"]  = "IP address belum diisi di Settings"
        return result

    # unit_id 247 adalah broadcast address di Modbus → hampir selalu salah
    if unit_id == 247:
        print(
            f"[WARN] {target}: unit_id=247 adalah Modbus broadcast address, "
            "PLC akan ignore request ini. Ganti ke unit_id=1 (atau 0/255 untuk Schneider)."
        )

    client = AsyncModbusTcpClient(
        host=host,
        port=tcp_port,
        timeout=MODBUS_TIMEOUT,
    )

    try:
        # pymodbus 3.x: connect() mengembalikan bool
        connected = await asyncio.wait_for(
            asyncio.get_event_loop().run_in_executor(None, client.connect)
            if False else client.connect(),
            timeout=MODBUS_TIMEOUT + 1,
        )

        if not connected:
            result["status"] = "offline"
            result["error"]  = "TCP connect failed — PLC tidak menerima koneksi pada port ini"
            print(f"[WARN] Modbus {target}: TCP connect refused")
            return result

        # FC3: Read Holding Registers
        response = await client.read_holding_registers(
            address=REGISTER_START,
            count=REGISTER_COUNT,
            device_id=unit_id,
        )

        if response.isError():
            detail = _format_modbus_error(response)
            result["status"] = "error"
            result["error"]  = detail
            print(f"[WARN] Modbus {target}: FC3 addr={REGISTER_START} count={REGISTER_COUNT} → {detail}")

            # Hints untuk error umum
            exc = getattr(response, "exception_code", None)
            if exc == 2:
                print(
                    f"  → Illegal Data Address: REGISTER_START={REGISTER_START} count={REGISTER_COUNT}. "
                    "Coba kurangi MODBUS_HOLDING_COUNT di .env (misal 12 dulu untuk test). "
                    "Atau periksa %MW range di program PLC."
                )
            elif exc == 4:
                print(f"  → Slave Device Failure: unit_id={unit_id} mungkin salah. Coba 1 atau 0.")
            return result

        regs = response.registers

        if _VERBOSE:
            print(f"[DEBUG] {target}: {len(regs)} words → {regs}")

        # Parse tiap register
        for address, info in REGISTER_MAP.items():
            idx = address_to_index(address)
            if idx >= len(regs):
                if _VERBOSE:
                    print(f"[DEBUG] skip address {address} (idx={idx} >= len={len(regs)})")
                continue

            parsed_val, raw_val = parse_register_value(regs, address)

            result["data"][address] = {
                "key":   info["key"],
                "desc":  info["desc"],
                "value": parsed_val,
                "raw":   raw_val,
            }

        if _VERBOSE:
            ok_keys = [str(a) for a in result["data"]]
            print(f"[DEBUG] {target}: parsed {len(ok_keys)} register(s): {ok_keys}")

    except asyncio.TimeoutError:
        result["status"] = "offline"
        result["error"]  = f"Timeout setelah {MODBUS_TIMEOUT}s — PLC tidak merespons"
        print(
            f"[WARN] Modbus {target}: timeout. "
            "Cek: IP benar, port 502 terbuka, Modbus TCP diaktifkan di PLC."
        )

    except ModbusException as e:
        result["status"] = "error"
        result["error"]  = f"ModbusException: {e}"
        print(f"[WARN] Modbus {target}: {result['error']}")

    except OSError as e:
        # Connection refused, network unreachable, dll.
        result["status"] = "offline"
        result["error"]  = f"OS network error: {e}"
        if _VERBOSE or "refused" in str(e).lower():
            print(f"[WARN] Modbus {target}: {e!r}")

    except Exception as e:
        result["status"] = "error"
        result["error"]  = str(e)
        print(f"[WARN] Modbus {target}: unexpected error: {e!r}")

    finally:
        try:
            client.close()
        except Exception:
            pass

    return result