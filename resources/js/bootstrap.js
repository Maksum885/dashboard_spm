import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;
if (pusherKey) {
    const scheme = import.meta.env.VITE_PUSHER_SCHEME || 'https';
    const useTls = scheme === 'https';
    const host = import.meta.env.VITE_PUSHER_HOST;
    const port = import.meta.env.VITE_PUSHER_PORT
        ? Number(import.meta.env.VITE_PUSHER_PORT)
        : useTls
          ? 443
          : 80;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
        wsHost: host || undefined,
        wsPort: host ? port : undefined,
        wssPort: host ? port : undefined,
        forceTLS: useTls,
        encrypted: useTls,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
    });
}
