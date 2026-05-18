"""
python-modbus/models/schemas.py
Pydantic schemas untuk validasi request/response FastAPI
"""

from pydantic import BaseModel, Field
from typing import Optional, Any
from datetime import datetime


# ─── Register Value ───────────────────────────────────────────────────────────

class RegisterValue(BaseModel):
    key:   str
    desc:  str
    value: Any        # bool | int | float | None
    raw:   int

    class Config:
        json_encoders = {float: lambda v: round(v, 3)}


# ─── PLC Read Result ──────────────────────────────────────────────────────────

class PlcReadResult(BaseModel):
    room_id:   int
    plc_name:  str
    status:    str        # "success" | "offline" | "error"
    data:      dict[int, RegisterValue] = Field(default_factory=dict)
    error:     Optional[str] = None


# ─── Register Change (dikirim ke Laravel) ────────────────────────────────────

class RegisterChange(BaseModel):
    register_address: int
    register_name:    str
    register_desc:    str
    old_value:        Any  # nilai sebelumnya, None jika pertama kali
    new_value:        Any  # nilai baru
    raw_value:        int


# ─── Webhook Payload (POST ke Laravel) ───────────────────────────────────────

class WebhookPayload(BaseModel):
    room_id:    int
    polled_at:  str        # ISO 8601
    status:     str        # "success" | "offline" | "error"
    snapshot:   dict[int, dict]   # {40001: {key, desc, value, raw}, ...}
    changes:    list[RegisterChange] = Field(default_factory=list)
    error:      Optional[str] = None


# ─── Health Response ──────────────────────────────────────────────────────────

class HealthResponse(BaseModel):
    status:    str          # "ok"
    plc_count: int
    uptime_s:  float


# ─── All Rooms Response ───────────────────────────────────────────────────────

class AllRoomsResponse(BaseModel):
    rooms: list[PlcReadResult]