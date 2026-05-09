import asyncio
from fastapi import FastAPI
from contextlib import asynccontextmanager
from modbus.poller import start_polling, poll_single
from config import PLC_DEVICES

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Jalankan polling background saat startup
    task = asyncio.create_task(start_polling())
    yield
    task.cancel()

app = FastAPI(title="SPM-TestingBay Modbus Service", lifespan=lifespan)

@app.get("/health")
async def health():
    return {"status": "ok", "plc_count": len(PLC_DEVICES)}

@app.get("/plc/{room_id}/data")
async def get_plc_data(room_id: int):
    if room_id not in PLC_DEVICES:
        return {"error": "Room tidak ditemukan"}
    return await poll_single(room_id)

@app.get("/plc/all/data")
async def get_all_plc_data():
    import asyncio
    tasks   = [poll_single(i) for i in PLC_DEVICES]
    results = await asyncio.gather(*tasks, return_exceptions=True)
    return {"rooms": results}