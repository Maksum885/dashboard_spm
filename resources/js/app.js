import { initDashboard } from './dashboard/init.js';

document.addEventListener('DOMContentLoaded', () => {
    initDashboard().catch((err) => {
        console.error('Dashboard init failed:', err);
    });
});
