import AuthService from './authService.js';
import { DUMMY_CONTROL_ROOMS, DUMMY_UTILITIES } from '../data/dummy.js';

const clone = (v) => structuredClone(v);

function useDummyDashboard() {
    return import.meta.env.VITE_DASHBOARD_USE_DUMMY === 'true';
}

/** Tanpa dummy: tidak ada data palsu saat API gagal / belum login. */
function emptyDashboardPayload() {
    return {
        controlRooms: {},
        utilities: {},
    };
}

/**
 * Normalizes API JSON into the shape the dashboard UI expects.
 *
 * @param {unknown} raw
 * @returns {{ controlRooms: Record<string, object>, utilities: object }}
 */
export function normalizeDashboardPayload(raw) {
    if (raw && typeof raw === 'object') {
        if ('controlRooms' in raw && 'utilities' in raw) {
            return { controlRooms: raw.controlRooms, utilities: raw.utilities };
        }
        if ('data' in raw && raw.data && typeof raw.data === 'object') {
            const d = raw.data;
            return {
                controlRooms: d.controlRooms ?? d.cr ?? d,
                utilities: d.utilities ?? d.util ?? {},
            };
        }
    }
    return { controlRooms: raw ?? {}, utilities: {} };
}

/**
 * Ambil payload dashboard:
 * - Jika `VITE_DASHBOARD_USE_DUMMY=true` → data demo dari `dummy.js` (hanya untuk uji UI).
 * - Selain itu → `/api/dashboard` (Sanctum), lalu opsional `VITE_DASHBOARD_API_URL` + axios.
 * - Jika semua gagal → payload kosong (bukan dummy).
 */
export async function getDashboardData() {
    if (useDummyDashboard()) {
        const controlRooms = clone(DUMMY_CONTROL_ROOMS);
        Object.values(controlRooms).forEach((cr) => {
            (cr.rooms || []).forEach((room) => {
                if (!room.plc_link_status) room.plc_link_status = 'online';
            });
        });
        return {
            controlRooms,
            utilities: clone(DUMMY_UTILITIES),
        };
    }

    const token = AuthService.getToken();
    if (token) {
        try {
            const json = await AuthService.apiJson('/api/dashboard', { method: 'GET' });
            if (json) return normalizeDashboardPayload(json);
        } catch (e) {
            console.warn('[dashboard] API error:', e.message);
        }
    }

    const base = import.meta.env.VITE_DASHBOARD_API_URL || '';
    if (base) {
        try {
            const axios = (await import('axios')).default;
            const url = `${String(base).replace(/\/$/, '')}/api/dashboard`;
            const res = await axios.get(url, {
                headers: { Accept: 'application/json' },
                validateStatus: () => true,
            });
            if (res.status >= 200 && res.status < 300 && typeof res.data === 'object' && res.data) {
                return normalizeDashboardPayload(res.data);
            }
        } catch (e) {
            console.warn('[dashboard] legacy URL failed:', e.message);
        }
    }

    return emptyDashboardPayload();
}

export const PlcAPI = {
    async getRoomData(roomId) {
        return AuthService.apiJson(`/api/plc/${roomId}/data`, { method: 'GET' });
    },
};
