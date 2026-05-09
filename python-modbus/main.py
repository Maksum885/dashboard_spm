"""
python-modbus/main.py
FastAPI entry point untuk SPM-SCADA Modbus Microservice.

Jalankan:
    uvicorn main:app --host 0.0.0.0 --port 8001 --reload
"""

import asyncio
from contextlib import asynccontextmanager
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from modbus.poller import start_polling
from routers import plc, health


# ─── Lifespan: start/stop background polling ─────────────────────────────────

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup: mulai polling background
    polling_task = asyncio.create_task(start_polling())
    print("[INFO] SPM-TestingBay Modbus Service started.")
    yield
    # Shutdown: hentikan polling
    polling_task.cancel()
    try:
        await polling_task
    except asyncio.CancelledError:
        pass
    print("[INFO] SPM-TestingBay Modbus Service stopped.")


# ─── App ──────────────────────────────────────────────────────────────────────

app = FastAPI(
    title="SPM-TestingBay Modbus Microservice",
    description="Modbus TCP polling service untuk 11 PLC. Mengirim data ke Laravel via webhook.",
    version="1.0.0",
    lifespan=lifespan,
)

# CORS: izinkan hanya dari Laravel (ubah origin sesuai domain production)
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000"],
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)

# ─── Routers ──────────────────────────────────────────────────────────────────

app.include_router(health.router)
app.include_router(plc.router)