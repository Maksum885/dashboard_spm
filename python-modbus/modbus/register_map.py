REGISTER_MAP = {
    40001: {"key": "tekanan_masuk",        "desc": "Tekanan Masuk Sebelum Testing",   "type": "uint16",  "change_type": "value_change"},
    40002: {"key": "alarm_alert",          "desc": "Alarm Alert",                     "type": "bool",    "change_type": "alarm_triggered"},
    40003: {"key": "mode_maintenance",     "desc": "Panel Sedang Di Mode Maintenance","type": "bool",    "change_type": "maintenance_on"},
    40004: {"key": "roof_tertutup",        "desc": "Roof Tertutup",                   "type": "bool",    "change_type": "status_change"},
    40005: {"key": "roof_terbuka",         "desc": "Roof Terbuka",                    "type": "bool",    "change_type": "status_change"},
    40006: {"key": "roof_bergerak_buka",   "desc": "Roof Bergerak Membuka",           "type": "bool",    "change_type": "roof_moving"},
    40007: {"key": "roof_bergerak_tutup",  "desc": "Roof Bergerak Menutup",           "type": "bool",    "change_type": "roof_moving"},
    40008: {"key": "pintu_terkunci",       "desc": "Pintu Akses Sedang Terkunci",     "type": "bool",    "change_type": "door_locked"},
    40009: {"key": "testing_dimulai",      "desc": "Testing Sedang Dimulai",          "type": "bool",    "change_type": "testing_started"},
    # 40010 skip
    40011: {"key": "pressure_1",           "desc": "Pressure 1",                      "type": "float32", "change_type": "value_change"},
    40012: {"key": "pressure_2",           "desc": "Pressure 2",                      "type": "float32", "change_type": "value_change"},
}