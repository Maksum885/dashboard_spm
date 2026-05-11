import AuthService from './authService.js';
import { DUMMY_CONTROL_ROOMS, DUMMY_UTILITIES } from '../data/dummy.js';

const clone = (v) => structuredClone(v);

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
 * Ambil payload dashboard: prioritas token Sanctum → `/api/dashboard`,
 * fallback env `VITE_DASHBOARD_API_URL` + `/api/dashboard` (axios opsional),
 * lalu dummy jika tidak ada auth / URL.
 */
export async function getDashboardData() {
    if (import.meta.env.VITE_DASHBOARD_USE_DUMMY === 'true') {
        return {
            controlRooms: clone(DUMMY_CONTROL_ROOMS),
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

    return {
        controlRooms: clone(DUMMY_CONTROL_ROOMS),
        utilities: clone(DUMMY_UTILITIES),
    };
}

export const PlcAPI = {
    async getRoomData(roomId) {
        return AuthService.apiJson(`/api/plc/${roomId}/data`, { method: 'GET' });
    },
};
