"""
Webhook outbound ke Laravel + contoh payload polling.

Implementasi Modbus read sesuaikan dengan map register PLC Anda (alamat holding/input).
"""

import os
from datetime import datetime, timezone

import httpx
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

router = APIRouter()


class WebhookPayload(BaseModel):
    """Format diselaraskan dengan App\Http\Controllers\Api\PlcController::webhook"""

    room_id: int = Field(..., description="testing_rooms.id")
    changes: list = Field(default_factory=list)
    snapshot: dict = Field(default_factory=dict)
    status: str = Field("success", description="success | timeout | error")
    polled_at: str = Field(..., description="ISO8601")


def _iso_now() -> str:
    return datetime.now(timezone.utc).isoformat()


@router.post("/send-sample-webhook")
async def send_sample_webhook():
    """
    Kirim satu payload contoh ke Laravel (berguna untuk uji integrasi tanpa PLC fisik).
    """
    url = os.getenv("LARAVEL_WEBHOOK_URL", "http://127.0.0.1:8000/api/plc/webhook")
    room_id = int(os.getenv("ROOM_ID", "1"))
    webhook_secret = os.getenv("PLC_WEBHOOK_SECRET", "")

    payload = WebhookPayload(
        room_id=room_id,
        changes=[],
        snapshot={
            "40001": {"name": "line_pressure", "value": 6.35, "raw": 6350},
            "40002": {"name": "alarm_alert", "value": False, "raw": 0},
            "40011": {"name": "pressure_1", "value": 6.4, "raw": 6400},
        },
        status="success",
        polled_at=_iso_now(),
    )

    async with httpx.AsyncClient(timeout=15.0) as client:
        headers = {"X-PLC-Secret": webhook_secret} if webhook_secret else {}
        r = await client.post(url, json=payload.model_dump(), headers=headers)

    if r.status_code >= 400:
        raise HTTPException(status_code=502, detail=r.text)

    return {"forwarded": True, "laravel_status": r.status_code, "body": r.json() if r.content else None}


@router.get("/config")
async def read_config():
    """Tanpa menyimpan secret — hanya host/port yang dibutuhkan operator."""
    return {
        "plc_host": os.getenv("PLC_HOST"),
        "plc_port": int(os.getenv("PLC_PORT", "502")),
        "plc_unit_id": int(os.getenv("PLC_UNIT_ID", "1")),
        "room_id": int(os.getenv("ROOM_ID", "1")),
        "laravel_webhook_url": os.getenv("LARAVEL_WEBHOOK_URL"),
        "poll_interval_sec": int(os.getenv("POLL_INTERVAL_SEC", "5")),
    }
