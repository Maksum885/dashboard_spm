import os
from datetime import datetime, timezone

from fastapi import APIRouter

router = APIRouter()


@router.get("/health")
async def health():
    return {
        "status": "ok",
        "service": "spm-python-modbus",
        "time": datetime.now(timezone.utc).isoformat(),
        "pid": os.getpid(),
    }
