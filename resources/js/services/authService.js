/**
 * Token Sanctum + fetch API terautentikasi untuk dashboard SPA.
 */

const TOKEN_KEY = 'spm_auth_token';

function apiOrigin() {
    if (import.meta.env.VITE_APP_URL) {
        return String(import.meta.env.VITE_APP_URL).replace(/\/$/, '');
    }
    return '';
}

export default {
    TOKEN_KEY,

    getToken() {
        try {
            return sessionStorage.getItem(TOKEN_KEY);
        } catch {
            return null;
        }
    },

    setToken(token) {
        try {
            if (token) sessionStorage.setItem(TOKEN_KEY, token);
            else sessionStorage.removeItem(TOKEN_KEY);
        } catch {
            /* ignore */
        }
    },

    clearToken() {
        this.setToken(null);
    },

    /**
     * Fetch dengan Bearer token. Pada 401 hapus token dan redirect ke /login.
     * @param {string} path — mis. `/api/dashboard` atau URL absolut
     * @param {RequestInit} options
     * @returns {Promise<Response|null>} null jika 401 (redirect login)
     */
    async apiFetch(path, options = {}) {
        const token = this.getToken();
        const headers = new Headers(options.headers || {});
        if (!headers.has('Accept')) headers.set('Accept', 'application/json');
        if (token) headers.set('Authorization', `Bearer ${token}`);

        const url =
            path.startsWith('http') || path.startsWith('//')
                ? path
                : `${apiOrigin() || ''}${path.startsWith('/') ? '' : '/'}${path}`;

        const res = await fetch(url, {
            ...options,
            headers,
            credentials: 'same-origin',
        });

        if (res.status === 401) {
            this.clearToken();
            if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
                window.location.href = '/login';
            }
            return null;
        }

        return res;
    },

    /**
     * Parse JSON dari response OK; lempar Error jika gagal.
     * @param {Response} res
     */
    async parseJson(res) {
        const text = await res.text();
        if (!text) return null;
        try {
            return JSON.parse(text);
        } catch {
            throw new Error('Response bukan JSON');
        }
    },

    async apiJson(path, options = {}) {
        const res = await this.apiFetch(path, options);
        if (!res) return null;
        if (!res.ok) {
            let msg = `HTTP ${res.status}`;
            try {
                const err = await res.json();
                if (err.message) msg = err.message;
            } catch {
                /* ignore */
            }
            throw new Error(msg);
        }
        return this.parseJson(res);
    },
};
