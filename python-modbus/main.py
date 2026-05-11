"""
Microservice: health + Modbus TCP polling → Laravel webhook (realtime DB + cache).
"""

import asyncio
import os
from contextlib import asynccontextmanager
from pathlib import Path

from dotenv import load_dotenv

load_dotenv(Path(__file__).resolve().parent / ".env")

from fastapi import FastAPI

from modbus.device_loader import load_plc_devices_sync
from modbus.poller import set_plc_devices, start_polling
from routers import health, plc


@asynccontextmanager
async def lifespan(_app: FastAPI):
    poll_task = None
    if os.getenv("ENABLE_POLLING", "1").strip() in ("1", "true", "yes"):
        set_plc_devices(load_plc_devices_sync())
        poll_task = asyncio.create_task(start_polling())
    yield
    if poll_task:
        poll_task.cancel()
        try:
            await poll_task
        except asyncio.CancelledError:
            pass


app = FastAPI(title="SPM Modbus Bridge", version="0.1.0", lifespan=lifespan)

app.include_router(health.router, tags=["health"])
app.include_router(plc.router, prefix="/plc", tags=["plc"])
