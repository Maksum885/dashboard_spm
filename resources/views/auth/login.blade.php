<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SPM SCADA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Rajdhani:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0a0c0f;
            --surface:   #0f1318;
            --panel:     #131820;
            --border:    #1e2a38;
            --accent:    #f0a500;
            --accent2:   #e05c00;
            --text:      #c8d6e0;
            --muted:     #4a6070;
            --danger:    #e03030;
            --success:   #1aaa6e;
            --online:    #00d48a;
        }

        html, body {
            height: 100%;
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            color: var(--text);
            overflow: hidden;
        }

        /* ── Grid background ── */
        .grid-bg {
            position: fixed; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(240,165,0,.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(240,165,0,.03) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* ── Corner decorations ── */
        .corner {
            position: fixed;
            width: 120px; height: 120px;
            z-index: 1;
        }
        .corner-tl { top: 0; left: 0; border-top: 1px solid var(--accent); border-left: 1px solid var(--accent); opacity: .25; }
        .corner-tr { top: 0; right: 0; border-top: 1px solid var(--accent); border-right: 1px solid var(--accent); opacity: .25; }
        .corner-bl { bottom: 0; left: 0; border-bottom: 1px solid var(--accent); border-left: 1px solid var(--accent); opacity: .25; }
        .corner-br { bottom: 0; right: 0; border-bottom: 1px solid var(--accent); border-right: 1px solid var(--accent); opacity: .25; }

        /* ── Status bar atas ── */
        .statusbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 10;
            height: 32px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center;
            padding: 0 24px;
            gap: 24px;
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: var(--muted);
        }
        .statusbar-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--online);
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .3; }
        }
        .statusbar-item { display: flex; align-items: center; gap: 6px; }
        .statusbar-right { margin-left: auto; }

        /* ── Main layout ── */
        .layout {
            position: relative; z-index: 5;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 480px 1fr;
            grid-template-rows: 1fr;
            align-items: center;
            padding-top: 32px;
        }

        /* ── Info panel kiri ── */
        .info-panel {
            padding: 48px;
            animation: fadein .8s ease both;
        }
        @keyframes fadein {
            from { opacity: 0; transform: translateX(-20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .sys-label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: var(--accent);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 32px;
        }
        .sys-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 48px;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            margin-bottom: 8px;
        }
        .sys-sub {
            font-family: 'Rajdhani', sans-serif;
            font-size: 20px;
            font-weight: 400;
            color: var(--muted);
            margin-bottom: 40px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            max-width: 320px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            padding: 16px;
        }
        .stat-label {
            font-family: 'Share Tech Mono', monospace;
            font-size: 9px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .stat-value {
            font-family: 'Rajdhani', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
        }
        .stat-unit {
            font-size: 12px;
            color: var(--muted);
            margin-left: 4px;
        }

        /* ── Login card tengah ── */
        .login-card {
            background: var(--panel);
            border: 1px solid var(--border);
            padding: 40px;
            position: relative;
            animation: slidein .6s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes slidein {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Corner accents pada card */
        .login-card::before, .login-card::after {
            content: '';
            position: absolute;
            width: 20px; height: 20px;
        }
        .login-card::before {
            top: -1px; left: -1px;
            border-top: 2px solid var(--accent);
            border-left: 2px solid var(--accent);
        }
        .login-card::after {
            bottom: -1px; right: -1px;
            border-bottom: 2px solid var(--accent);
            border-right: 2px solid var(--accent);
        }

        .card-header {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }
        .card-eyebrow {
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: var(--accent);
            letter-spacing: 3px;
            margin-bottom: 8px;
        }
        .card-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #fff;
        }
        .card-subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* ── Form ── */
        .field { margin-bottom: 20px; }
        .field-label {
            display: block;
            font-family: 'Share Tech Mono', monospace;
            font-size: 10px;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .field-wrap {
            position: relative;
        }
        .field-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
            pointer-events: none;
        }
        .field input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            padding: 12px 14px 12px 40px;
            outline: none;
            transition: border-color .2s, background .2s;
        }
        .field input::placeholder { color: var(--muted); }
        .field input:focus {
            border-color: var(--accent);
            background: #111820;
        }
        .field input.error-input {
            border-color: var(--danger);
        }

        .toggle-pass {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--muted); cursor: pointer;
            font-size: 14px; padding: 0;
        }
        .toggle-pass:hover { color: var(--text); }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .remember-row input[type="checkbox"] {
            width: 14px; height: 14px;
            accent-color: var(--accent);
            cursor: pointer;
        }
        .remember-row label {
            font-size: 12px;
            color: var(--muted);
            cursor: pointer;
        }

        /* Error dari server */
        .error-box {
            background: rgba(224,48,48,.08);
            border: 1px solid rgba(224,48,48,.3);
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .error-box-icon { color: var(--danger); font-size: 16px; }
        .error-box-text { font-size: 13px; color: #ff7070; }

        /* Submit button */
        .btn-login {
            width: 100%;
            background: var(--accent);
            border: none;
            color: #0a0c0f;
            font-family: 'Rajdhani', sans-serif;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 14px;
            cursor: pointer;
            transition: background .2s, opacity .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { background: #ffc107; }
        .btn-login:active { opacity: .85; }
        .btn-login:disabled { opacity: .5; cursor: not-allowed; }
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(0,0,0,.2);
            border-top-color: #000;
            border-radius: 50%;
            animation: spin .6s linear infinite;
            display: none;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Field error inline */
        .field-error {
            font-size: 11px;
            color: var(--danger);
            margin-top: 5px;
            font-family: 'Share Tech Mono', monospace;
        }

        /* ── Info panel kanan ── */
        .right-panel {
            padding: 48px;
            animation: fadein2 .8s ease .2s both;
        }
        @keyframes fadein2 {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .role-list { list-style: none; }
        .role-item {
            padding: 16px;
            border: 1px solid var(--border);
            margin-bottom: 10px;
            background: var(--surface);
        }
        .role-name {
            font-family: 'Rajdhani', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 4px;
        }
        .role-desc {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.5;
        }
        .role-badge {
            display: inline-block;
            font-family: 'Share Tech Mono', monospace;
            font-size: 9px;
            padding: 2px 8px;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }
        .badge-admin    { background: rgba(240,165,0,.15); color: var(--accent); border: 1px solid rgba(240,165,0,.3); }
        .badge-operator { background: rgba(26,170,110,.1); color: var(--online); border: 1px solid rgba(26,170,110,.3); }
        .badge-viewer   { background: rgba(74,96,112,.15); color: var(--muted); border: 1px solid var(--border); }

        .version-tag {
            font-family: 'Share Tech Mono', monospace;
            font-size: 9px;
            color: var(--muted);
            letter-spacing: 1px;
            margin-top: 24px;
        }

        /* Responsive */
        @media (max-width: 1100px) {
            .layout { grid-template-columns: 1fr; justify-items: center; padding: 60px 20px 40px; overflow-y: auto; }
            html, body { overflow: auto; }
            .info-panel, .right-panel { display: none; }
            .login-card { width: 100%; max-width: 440px; }
        }
    </style>
</head>
<body>

<div class="grid-bg"></div>
<div class="corner corner-tl"></div>
<div class="corner corner-tr"></div>
<div class="corner corner-bl"></div>
<div class="corner corner-br"></div>

<!-- Status Bar -->
<div class="statusbar">
    <div class="statusbar-item">
        <span class="statusbar-dot"></span>
        <span>SYSTEM ONLINE</span>
    </div>
    <div class="statusbar-item">SPM-SCADA v2.0</div>
    <div class="statusbar-item">MODBUS TCP</div>
    <div class="statusbar-right" id="clock">--:--:--</div>
</div>

<div class="layout">

    <!-- Panel Kiri -->
    <div class="info-panel">
        <div class="sys-label">// Sistem Monitoring</div>
        <div class="sys-title">SPM</div>
        <div class="sys-title" style="color:var(--accent)">SCADA</div>
        <div class="sys-sub">Supervisory Control & Data Acquisition</div>
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Total Rooms</div>
                <div class="stat-value">11<span class="stat-unit">unit</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Control Rooms</div>
                <div class="stat-value">3<span class="stat-unit">CR</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">PLC Devices</div>
                <div class="stat-value">11<span class="stat-unit">PLC</span></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Protocol</div>
                <div class="stat-value" style="font-size:16px;padding-top:5px">Modbus<span class="stat-unit">TCP</span></div>
            </div>
        </div>
    </div>

    <!-- Form Login -->
    <div class="login-card">
        <div class="card-header">
            <div class="card-eyebrow">// Akses Sistem</div>
            <div class="card-title">Masuk ke Dashboard</div>
            <div class="card-subtitle">Masukkan kredensial Anda untuk melanjutkan</div>
        </div>

        @if ($errors->any())
        <div class="error-box">
            <div class="error-box-icon">⚠</div>
            <div class="error-box-text">{{ $errors->first() }}</div>
        </div>
        @endif

        <form id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate>
            @csrf

            <div class="field">
                <label class="field-label" for="email">Email Address</label>
                <div class="field-wrap">
                    <span class="field-icon">✉</span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@spm-scada.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        class="{{ $errors->has('email') ? 'error-input' : '' }}"
                        required
                    >
                </div>
                @error('email')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label class="field-label" for="password">Password</label>
                <div class="field-wrap">
                    <span class="field-icon">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'error-input' : '' }}"
                        required
                    >
                    <button type="button" class="toggle-pass" id="togglePass" aria-label="Toggle password">👁</button>
                </div>
                @error('password')<div class="field-error">{{ $message }}</div>@enderror
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya di perangkat ini</label>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <div class="spinner" id="spinner"></div>
                <span id="btnText">MASUK KE SISTEM</span>
            </button>
        </form>
    </div>

    <!-- Panel Kanan -->
    <div class="right-panel">
        <div class="sys-label" style="margin-bottom:20px">// Hak Akses</div>
        <ul class="role-list">
            <li class="role-item">
                <div class="role-badge badge-admin">ADMIN</div>
                <div class="role-name">Administrator</div>
                <div class="role-desc">Akses penuh ke semua Control Room, manajemen user, konfigurasi PLC, dan export report.</div>
            </li>
            <li class="role-item">
                <div class="role-badge badge-operator">OPERATOR</div>
                <div class="role-name">Operator Room</div>
                <div class="role-desc">Akses terbatas ke Control Room yang di-assign. Dapat melihat status dan alarm room-nya.</div>
            </li>
            <li class="role-item">
                <div class="role-badge badge-viewer">VIEWER</div>
                <div class="role-name">Viewer</div>
                <div class="role-desc">Hanya dapat melihat dashboard tanpa aksi. Akses read-only ke Control Room terkait.</div>
            </li>
        </ul>
        <div class="version-tag">SPM-SCADA // BUILD 2025.01 // MODBUS TCP // PYMODBUS</div>
    </div>

</div>

<script>
// Clock
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toTimeString().slice(0, 8);
}
updateClock();
setInterval(updateClock, 1000);

// Toggle password visibility
document.getElementById('togglePass').addEventListener('click', function() {
    const inp = document.getElementById('password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    this.textContent = inp.type === 'password' ? '👁' : '🙈';
});

// Loading state saat submit
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnLogin');
    const spinner = document.getElementById('spinner');
    const txt = document.getElementById('btnText');
    btn.disabled = true;
    spinner.style.display = 'block';
    txt.textContent = 'MEMPROSES...';
});
</script>

</body>
</html>