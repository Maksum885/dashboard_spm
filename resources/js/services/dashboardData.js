import axios from 'axios';
import { DUMMY_CONTROL_ROOMS, DUMMY_UTILITIES } from '../data/dummy.js';

const clone = (v) => structuredClone(v);

/**
 * Normalizes API JSON into the shape the dashboard UI expects.
 * Adjust field mapping here when your PostgreSQL/Laravel API schema is finalized.
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
 * Service entry: swap implementation to PostgreSQL-backed API by setting
 * VITE_DASHBOARD_API_URL in .env (e.g. https://app.test — no trailing slash required).
 */
export async function getDashboardData() {
    const base = import.meta.env.VITE_DASHBOARD_API_URL || '';

    if (!base) {
        return {
            controlRooms: clone(DUMMY_CONTROL_ROOMS),
            utilities: clone(DUMMY_UTILITIES),
        };
    }

    const url = `${String(base).replace(/\/$/, '')}/api/dashboard`;
    const res = await axios.get(url, {
        headers: { Accept: 'application/json' },
        validateStatus: () => true,
    });

    if (res.status < 200 || res.status >= 300) {
        throw new Error(`Dashboard API HTTP ${res.status}`);
    }

    const { data } = res;
    if (typeof data !== 'object' || data === null) {
        throw new Error('Dashboard API returned invalid JSON');
    }

    return normalizeDashboardPayload(data);
}
