import { getDashboardData } from '../services/dashboardData.js';
import { store } from './state.js';
import { createDashboardThreeScene } from '../three/scene.js';
import * as Main from './main.js';

export async function initDashboard() {
    const payload = await getDashboardData();
    store.data = payload.controlRooms;
    store.util = payload.utilities;

    Main.registerGlobals();
    Main.initOptionalTabs();
    Main.initLogoFallback();
    Main.buildSidebarRooms();
    Main.updateAlarmSidebar();
    Main.updateUtilSidebar();

    const three = createDashboardThreeScene({
        canvas: document.getElementById('cv'),
        lyrEl: document.getElementById('lyr'),
        svgEl: document.getElementById('lyr-svg'),
        tipEl: document.getElementById('tip'),
        findCR: Main.findCR,
        findRoom: Main.findRoom,
        openPanel: Main.openPanel,
        openRoomPanel: Main.openRoomPanel,
    });
    three.start();

    Main.startLiveLoop();
    Main.initClock();
}
