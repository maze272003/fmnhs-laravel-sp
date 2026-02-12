<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#020617">
    <title>{{ $conference->title }} | Live Room</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        :root {
            --brand: {{ $conference->branding_color ?? '#10b981' }};
            --brand-glow: {{ $conference->branding_color ?? '#10b981' }}33;
            --surface-0: #020617;
            --surface-1: #0f172a;
            --surface-2: #1e293b;
            --surface-3: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border: rgba(51, 65, 85, 0.5);
            --spring: cubic-bezier(0.22, 1, 0.36, 1);
            --smooth: cubic-bezier(0.4, 0, 0.2, 1);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --safe-top: env(safe-area-inset-top, 0px);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; touch-action: manipulation; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--surface-0);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ========== LAYOUT SHELL ========== */
        .conf-shell {
            display: grid;
            grid-template-rows: auto 1fr auto;
            height: 100dvh;
            height: calc(var(--vh, 1vh) * 100);
            overflow: hidden;
        }

        /* ========== TOP BAR ========== */
        .conf-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            padding-top: calc(0.5rem + var(--safe-top));
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px) saturate(1.2);
            -webkit-backdrop-filter: blur(20px) saturate(1.2);
            border-bottom: 1px solid var(--border);
            z-index: 50;
            min-height: 3rem;
        }
        .conf-topbar__info { display: flex; align-items: center; gap: 0.5rem; min-width: 0; flex: 1; }
        .conf-topbar__title { font-size: 0.8125rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .conf-topbar__actions { display: flex; align-items: center; gap: 0.25rem; flex-shrink: 0; }

        .badge {
            display: inline-flex; align-items: center; gap: 0.25rem;
            padding: 0.125rem 0.5rem; border-radius: 999px;
            font-size: 0.5625rem; font-weight: 800; letter-spacing: 0.08em;
            text-transform: uppercase; line-height: 1.4;
        }
        .badge--live { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; }
        .badge--live::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #34d399; animation: pulse-dot 1.5s infinite; }
        .badge--ended { background: rgba(244, 63, 94, 0.15); color: #fda4af; }
        .badge--role { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; }
        .badge--rec { background: #dc2626; color: #fff; animation: rec-flash 1.4s infinite; }

        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        @keyframes rec-flash { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

        /* ========== MAIN STAGE ========== */
        .conf-stage {
            position: relative;
            display: flex;
            overflow: hidden;
            background: var(--surface-0);
        }
        .conf-stage__video-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            position: relative;
        }

        /* ---- Video Grid ---- */
        .video-grid {
            flex: 1;
            display: grid;
            gap: 0.375rem;
            padding: 0.375rem;
            overflow: hidden;
            transition: all 350ms var(--spring);
        }
        /* Adaptive grid based on participant count */
        .video-grid[data-count="1"] { grid-template-columns: 1fr; }
        .video-grid[data-count="2"] { grid-template-columns: 1fr 1fr; }
        .video-grid[data-count="3"],
        .video-grid[data-count="4"] { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
        .video-grid[data-count="5"],
        .video-grid[data-count="6"] { grid-template-columns: repeat(3, 1fr); grid-template-rows: 1fr 1fr; }
        .video-grid[data-count="7"],
        .video-grid[data-count="8"],
        .video-grid[data-count="9"] { grid-template-columns: repeat(3, 1fr); }

        /* Spotlight mode: screen share dominant */
        .video-grid--spotlight {
            grid-template-columns: 1fr 220px !important;
            grid-template-rows: 1fr !important;
        }
        .video-grid--spotlight .video-tile--screen {
            grid-row: 1 / -1;
            grid-column: 1;
        }
        .video-grid--spotlight .video-grid__sidebar {
            grid-column: 2;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* ---- Video Tile ---- */
        .video-tile {
            position: relative;
            border-radius: 0.75rem;
            overflow: hidden;
            background: var(--surface-1);
            border: 1px solid var(--border);
            min-height: 0;
            transition: border-color 200ms ease, box-shadow 200ms ease, transform 200ms var(--spring);
        }
        .video-tile:hover { border-color: rgba(99, 102, 241, 0.3); }
        .video-tile--speaking { border-color: #10b981 !important; box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25); }
        .video-tile--screen {
            border: 2px solid rgba(99, 102, 241, 0.5);
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.15);
            cursor: pointer;
        }
        .video-tile--local { border-color: rgba(16, 185, 129, 0.3); }
        .video-tile video {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            background: #000;
        }
        .video-tile--screen video { object-fit: contain; }

        .video-tile__label {
            position: absolute; bottom: 0.375rem; left: 0.375rem;
            display: flex; align-items: center; gap: 0.25rem;
            padding: 0.1875rem 0.5rem;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 0.375rem;
            font-size: 0.6875rem; font-weight: 600;
            max-width: calc(100% - 0.75rem);
            pointer-events: none;
        }
        .video-tile__label span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .video-tile__label .hand-icon { color: #fbbf24; animation: hand-wave 1s infinite; }

        .video-tile__status {
            position: absolute; top: 0.375rem; right: 0.375rem;
            display: flex; gap: 0.25rem;
        }
        .video-tile__status-icon {
            width: 1.5rem; height: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            border-radius: 0.375rem;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(6px);
            font-size: 0.625rem; color: #f87171;
        }
        .video-tile__controls {
            position: absolute; top: 0.375rem; left: 0.375rem;
            display: flex; gap: 0.25rem;
            opacity: 0; transition: opacity 200ms ease;
        }
        .video-tile:hover .video-tile__controls { opacity: 1; }

        .video-tile__avatar {
            position: absolute; inset: 0;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--surface-1), var(--surface-2));
        }
        .video-tile__avatar-circle {
            width: 4rem; height: 4rem;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), #6366f1);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 800; color: #fff;
            text-transform: uppercase;
        }

        @keyframes hand-wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-15deg); }
        }

        /* ---- Screen Share Fullscreen Overlay ---- */
        .fullscreen-overlay {
            position: fixed; inset: 0;
            z-index: 9999;
            background: #000;
            display: flex; flex-direction: column;
            opacity: 0; pointer-events: none;
            transition: opacity 250ms var(--smooth);
        }
        .fullscreen-overlay--active { opacity: 1; pointer-events: all; }
        .fullscreen-overlay video {
            flex: 1;
            width: 100%; height: 100%;
            object-fit: contain;
            background: #000;
        }
        .fullscreen-overlay__topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.75rem 1rem;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(12px);
        }
        .fullscreen-overlay__hint {
            position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
            padding: 0.5rem 1.25rem;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            border-radius: 999px;
            font-size: 0.75rem; color: var(--text-secondary);
            animation: hint-fade 3s 2s forwards;
            pointer-events: none;
        }
        @keyframes hint-fade { to { opacity: 0; } }

        /* Annotation canvas on screen share */
        .annotation-layer {
            position: absolute; inset: 0;
            z-index: 10;
            pointer-events: none;
        }
        .annotation-layer--active { pointer-events: all; cursor: crosshair; }

        /* Laser dot */
        .laser-dot {
            position: absolute;
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 12px #ef4444, 0 0 30px rgba(239, 68, 68, 0.5);
            pointer-events: none;
            z-index: 15;
            transition: left 50ms linear, top 50ms linear;
        }

        /* ========== SIDEBAR DRAWER ========== */
        .conf-sidebar {
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 320px;
            max-width: 85vw;
            background: var(--surface-1);
            border-left: 1px solid var(--border);
            display: flex; flex-direction: column;
            transform: translateX(100%);
            transition: transform 300ms var(--spring);
            z-index: 40;
        }
        .conf-sidebar--open { transform: translateX(0); }
        .conf-sidebar__backdrop {
            position: absolute; inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 35;
            opacity: 0; pointer-events: none;
            transition: opacity 250ms ease;
        }
        .conf-sidebar__backdrop--visible { opacity: 1; pointer-events: all; }

        .conf-sidebar__tabs {
            display: flex; border-bottom: 1px solid var(--border);
        }
        .conf-sidebar__tab {
            flex: 1; padding: 0.625rem;
            text-align: center; font-size: 0.6875rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em;
            color: var(--text-muted); border: none; background: none;
            border-bottom: 2px solid transparent;
            transition: color 200ms, border-color 200ms;
            cursor: pointer;
        }
        .conf-sidebar__tab--active { color: var(--brand); border-bottom-color: var(--brand); }
        .conf-sidebar__content { flex: 1; overflow-y: auto; overflow-x: hidden; }

        /* ========== TEACHER COMMAND CENTER ========== */
        .command-center {
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 280px;
            max-width: 80vw;
            background: var(--surface-1);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            transform: translateX(-100%);
            transition: transform 300ms var(--spring);
            z-index: 40;
        }
        .command-center--open { transform: translateX(0); }
        .command-center__header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .command-center__title { font-size: 0.8125rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; }
        .command-center__body { flex: 1; overflow-y: auto; padding: 0.75rem; }
        .command-center__section {
            margin-bottom: 1rem;
        }
        .command-center__section-title {
            font-size: 0.625rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.1em;
            color: var(--text-muted); margin-bottom: 0.5rem;
            display: flex; align-items: center; gap: 0.375rem;
        }

        .cmd-btn {
            display: flex; align-items: center; gap: 0.5rem;
            width: 100%; padding: 0.5rem 0.625rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            background: var(--surface-2);
            color: var(--text-primary);
            font-size: 0.75rem; font-weight: 600;
            cursor: pointer;
            transition: background 150ms, border-color 150ms, transform 150ms var(--spring);
            margin-bottom: 0.375rem;
        }
        .cmd-btn:hover { background: var(--surface-3); border-color: rgba(99, 102, 241, 0.3); }
        .cmd-btn:active { transform: scale(0.97); }
        .cmd-btn i { width: 1rem; text-align: center; font-size: 0.8rem; }
        .cmd-btn--danger { border-color: rgba(239, 68, 68, 0.3); color: #fca5a5; }
        .cmd-btn--danger:hover { background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.5); }
        .cmd-btn--active { background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; }

        /* Annotation toolbar floating */
        .annotation-toolbar {
            position: absolute;
            bottom: 5rem; left: 50%;
            transform: translateX(-50%);
            display: flex; align-items: center; gap: 0.25rem;
            padding: 0.375rem 0.5rem;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            z-index: 50;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }
        .annotation-toolbar button {
            width: 2rem; height: 2rem;
            display: flex; align-items: center; justify-content: center;
            border: none; border-radius: 0.375rem;
            background: transparent; color: var(--text-secondary);
            font-size: 0.75rem; cursor: pointer;
            transition: background 150ms, color 150ms;
        }
        .annotation-toolbar button:hover { background: var(--surface-3); color: var(--text-primary); }
        .annotation-toolbar button.active { background: var(--brand); color: #fff; }
        .annotation-toolbar .color-dot {
            width: 1.25rem; height: 1.25rem;
            border-radius: 50%; border: 2px solid transparent;
            cursor: pointer; transition: transform 150ms, border-color 150ms;
        }
        .annotation-toolbar .color-dot:hover { transform: scale(1.2); }
        .annotation-toolbar .color-dot.active { border-color: #fff; }
        .annotation-toolbar .separator {
            width: 1px; height: 1.5rem;
            background: var(--border); margin: 0 0.25rem;
        }

        /* ========== BOTTOM TOOLBAR ========== */
        .conf-toolbar {
            display: flex; align-items: center; justify-content: center;
            gap: 0.25rem;
            padding: 0.5rem 0.5rem;
            padding-bottom: calc(0.5rem + var(--safe-bottom));
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px) saturate(1.2);
            -webkit-backdrop-filter: blur(20px) saturate(1.2);
            border-top: 1px solid var(--border);
            z-index: 50;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        .conf-toolbar::-webkit-scrollbar { display: none; }

        .tb-btn {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 0.125rem;
            min-width: 3rem; height: 3rem;
            padding: 0.25rem 0.375rem;
            border: none; border-radius: 0.75rem;
            background: var(--surface-2);
            color: var(--text-secondary);
            font-size: 0.5625rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.04em;
            cursor: pointer;
            transition: background 150ms, color 150ms, transform 100ms var(--spring);
            flex-shrink: 0;
            position: relative;
        }
        .tb-btn i { font-size: 1rem; line-height: 1; }
        .tb-btn:hover { background: var(--surface-3); color: var(--text-primary); }
        .tb-btn:active { transform: scale(0.92); }
        .tb-btn--active { background: var(--brand); color: #fff; }
        .tb-btn--danger { background: #dc2626; color: #fff; }
        .tb-btn--danger:hover { background: #b91c1c; }
        .tb-btn--muted { background: #dc2626; color: #fff; }
        .tb-btn--screen { background: #4f46e5; color: #fff; }
        .tb-btn--screen:hover { background: #4338ca; }
        .tb-divider { width: 1px; height: 2rem; background: var(--border); flex-shrink: 0; margin: 0 0.125rem; }
        .tb-btn .tb-badge {
            position: absolute; top: 0.125rem; right: 0.125rem;
            min-width: 0.875rem; height: 0.875rem;
            display: flex; align-items: center; justify-content: center;
            border-radius: 999px;
            background: #dc2626; color: #fff;
            font-size: 0.5rem; font-weight: 900;
        }

        /* ========== STATUS TOAST ========== */
        .toast-container {
            position: fixed; top: 4rem; left: 50%; transform: translateX(-50%);
            display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
            z-index: 9000; pointer-events: none;
        }
        .toast {
            padding: 0.5rem 1rem;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 0.625rem;
            font-size: 0.75rem; font-weight: 600;
            color: var(--text-primary);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            animation: toast-in 250ms var(--spring), toast-out 250ms 3s var(--smooth) forwards;
            pointer-events: auto;
        }
        .toast--warning { border-color: rgba(245, 158, 11, 0.4); color: #fcd34d; }
        .toast--success { border-color: rgba(16, 185, 129, 0.4); color: #6ee7b7; }
        .toast--error { border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; }

        @keyframes toast-in { from { opacity: 0; transform: translateY(-1rem); } to { opacity: 1; transform: translateY(0); } }
        @keyframes toast-out { to { opacity: 0; transform: translateY(-0.5rem); } }

        /* ========== EMOJI FLOAT ========== */
        .emoji-float {
            position: fixed; bottom: 6rem;
            font-size: 2.5rem;
            animation: float-up 2.2s ease-out forwards;
            pointer-events: none; z-index: 8000;
        }
        @keyframes float-up {
            0% { opacity: 1; transform: translateY(0) scale(1); }
            80% { opacity: 1; transform: translateY(-140px) scale(1.2); }
            100% { opacity: 0; transform: translateY(-180px) scale(0.7); }
        }

        /* ========== ATTENTION MODAL ========== */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 9990;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-card {
            background: var(--surface-1);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2rem;
            max-width: 400px; width: 90%;
            text-align: center;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.4);
        }

        /* ========== SETTINGS MODAL ========== */
        .settings-overlay {
            position: fixed; inset: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9980;
            display: flex; align-items: center; justify-content: center;
        }
        .settings-card {
            background: var(--surface-1);
            border: 1px solid var(--border);
            border-radius: 1rem;
            max-width: 440px; width: 92%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.4);
        }
        .settings-card__header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0;
            background: var(--surface-1);
            z-index: 2;
        }

        /* Toggle Switch */
        .toggle {
            position: relative; width: 2.25rem; height: 1.25rem;
            border-radius: 999px; background: var(--surface-3);
            cursor: pointer; transition: background 200ms;
            flex-shrink: 0;
        }
        .toggle--on { background: var(--brand); }
        .toggle::after {
            content: ''; position: absolute;
            top: 2px; left: 2px;
            width: 1rem; height: 1rem;
            border-radius: 50%; background: #fff;
            transition: transform 200ms var(--spring);
        }
        .toggle--on::after { transform: translateX(1rem); }

        /* Scrollbar */
        .scroll-y { overflow-y: auto; }
        .scroll-y::-webkit-scrollbar { width: 4px; }
        .scroll-y::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 999px; }
        .scroll-y::-webkit-scrollbar-track { background: transparent; }

        /* Chat */
        .chat-bubble {
            max-width: 85%;
            padding: 0.375rem 0.625rem;
            border-radius: 0.75rem;
            font-size: 0.8125rem; line-height: 1.4;
        }
        .chat-bubble--mine { background: var(--brand); color: #fff; border-bottom-right-radius: 0.25rem; }
        .chat-bubble--other { background: var(--surface-2); color: var(--text-primary); border-bottom-left-radius: 0.25rem; }
        .chat-meta { font-size: 0.5625rem; font-weight: 700; color: var(--text-muted); margin-bottom: 0.125rem; }
        .chat-system { text-align: center; font-size: 0.625rem; color: var(--text-muted); font-weight: 500; padding: 0.125rem 0; }

        /* Participant row */
        .participant-row {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 0.625rem;
            border-radius: 0.5rem;
            transition: background 150ms;
        }
        .participant-row:hover { background: var(--surface-2); }
        .participant-row__avatar {
            width: 2rem; height: 2rem; border-radius: 50%;
            background: linear-gradient(135deg, var(--brand), #6366f1);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.6875rem; font-weight: 800; color: #fff;
            text-transform: uppercase; flex-shrink: 0;
        }
        .participant-row__info { flex: 1; min-width: 0; }
        .participant-row__name { font-size: 0.75rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .participant-row__role { font-size: 0.5625rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em; }
        .participant-row__actions { display: flex; gap: 0.25rem; flex-shrink: 0; }
        .participant-action {
            width: 1.5rem; height: 1.5rem;
            display: flex; align-items: center; justify-content: center;
            border: none; border-radius: 0.375rem;
            background: var(--surface-3); color: var(--text-secondary);
            font-size: 0.625rem; cursor: pointer;
            transition: background 150ms, color 150ms;
        }
        .participant-action:hover { background: var(--brand); color: #fff; }
        .participant-action--danger:hover { background: #dc2626; }

        /* ========== MOBILE RESPONSIVE ========== */
        @media (max-width: 639px) {
            .conf-topbar__title { max-width: 120px; font-size: 0.75rem; }
            .video-grid { gap: 0.25rem; padding: 0.25rem; }
            .video-grid[data-count="2"] { grid-template-columns: 1fr; grid-template-rows: 1fr 1fr; }
            .video-grid[data-count="3"],
            .video-grid[data-count="4"] { grid-template-columns: 1fr 1fr; }
            .video-grid--spotlight { grid-template-columns: 1fr !important; grid-template-rows: 1fr auto !important; }
            .video-grid--spotlight .video-grid__sidebar { flex-direction: row; overflow-x: auto; }
            .video-grid--spotlight .video-grid__sidebar .video-tile { min-width: 120px; min-height: 90px; }
            .video-tile { border-radius: 0.5rem; }
            .video-tile__avatar-circle { width: 3rem; height: 3rem; font-size: 1.25rem; }
            .tb-btn { min-width: 2.75rem; height: 2.75rem; font-size: 0; gap: 0; }
            .tb-btn i { font-size: 1.125rem; }
            .conf-sidebar { width: 100%; max-width: 100%; }
            .command-center { width: 100%; max-width: 100%; }
            .annotation-toolbar { bottom: 4.5rem; }
        }

        @media (min-width: 640px) and (max-width: 1023px) {
            .video-grid--spotlight { grid-template-columns: 1fr 180px !important; }
        }

        @media (min-width: 1024px) {
            .conf-sidebar { position: relative; transform: translateX(0); width: 0; overflow: hidden; transition: width 300ms var(--spring); border-left: none; }
            .conf-sidebar--open { width: 320px; border-left: 1px solid var(--border); }
            .conf-sidebar__backdrop { display: none !important; }
            .command-center { position: relative; transform: translateX(0); width: 0; overflow: hidden; transition: width 300ms var(--spring); border-right: none; }
            .command-center--open { width: 280px; border-right: 1px solid var(--border); }
        }

        @media (hover: none), (pointer: coarse) {
            .video-tile__controls { opacity: 1; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }

        /* Light mode */
        .light-mode { --surface-0: #f8fafc; --surface-1: #ffffff; --surface-2: #f1f5f9; --surface-3: #e2e8f0; --text-primary: #0f172a; --text-secondary: #475569; --text-muted: #94a3b8; --border: rgba(148, 163, 184, 0.4); }
        .light-mode body { background: var(--surface-0); color: var(--text-primary); }
        .light-mode .chat-bubble--mine { color: #fff; }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'] } } }
        };
        /* Fix 100vh on mobile browsers */
        function setVH() { document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`); }
        setVH(); window.addEventListener('resize', setVH);
    </script>
</head>
<body>

<div id="app" class="conf-shell">

    {{-- ==================== TOP BAR ==================== --}}
    <header class="conf-topbar">
        <div class="conf-topbar__info">
            @if($actorRole === 'teacher')
                <button id="cmd-toggle" class="tb-btn" style="width:2rem;height:2rem;min-width:2rem;" title="Command Center">
                    <i class="fa-solid fa-bars" style="font-size:0.875rem;"></i>
                </button>
            @endif
            @if($conference->branding_logo)
                <img src="{{ $conference->branding_logo }}" alt="" class="w-6 h-6 rounded-md object-cover flex-shrink-0">
            @endif
            <span class="conf-topbar__title">{{ $conference->title }}</span>
            @if($isMeetingActive)
                <span class="badge badge--live">LIVE</span>
            @else
                <span class="badge badge--ended">ENDED</span>
            @endif
            <span id="recording-indicator" class="badge badge--rec hidden"><i class="fa-solid fa-circle" style="font-size:5px;margin-right:2px;"></i>REC</span>
        </div>

        <div class="conf-topbar__actions">
            <span id="meeting-timer" class="text-xs font-mono text-slate-400 mr-1">00:00:00</span>
            <span class="badge badge--role">{{ strtoupper($actorRole) }}</span>
            <button id="sidebar-toggle" class="tb-btn" style="width:2rem;height:2rem;min-width:2rem;" title="Chat & People">
                <i class="fa-solid fa-message" style="font-size:0.875rem;"></i>
                <span id="chat-unread-badge" class="tb-badge hidden">0</span>
            </button>
        </div>
    </header>

    {{-- ==================== MAIN STAGE ==================== --}}
    <main class="conf-stage">

        {{-- Teacher Command Center Drawer --}}
        @if($actorRole === 'teacher')
        <aside id="command-center" class="command-center">
            <div class="command-center__header">
                <span class="command-center__title"><i class="fa-solid fa-shield-halved mr-1 text-indigo-400"></i> Command Center</span>
                <button id="cmd-close" class="participant-action"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="command-center__body scroll-y">
                {{-- Session Controls --}}
                <div class="command-center__section">
                    <div class="command-center__section-title"><i class="fa-solid fa-bolt text-amber-400"></i> Session</div>
                    <button id="cmd-mute-all" class="cmd-btn"><i class="fa-solid fa-volume-xmark text-rose-400"></i> Mute All Participants</button>
                    <button id="cmd-unmute-all" class="cmd-btn"><i class="fa-solid fa-volume-high text-emerald-400"></i> Unmute All Participants</button>
                    <button id="cmd-cam-off-all" class="cmd-btn"><i class="fa-solid fa-video-slash text-rose-400"></i> Cameras Off (All)</button>
                    <button id="cmd-attention" class="cmd-btn"><i class="fa-solid fa-bell text-amber-400"></i> Attention Check</button>
                    <button id="cmd-lock-room" class="cmd-btn"><i class="fa-solid fa-lock text-sky-400"></i> Lock Room</button>
                </div>

                {{-- Presentation Tools --}}
                <div class="command-center__section">
                    <div class="command-center__section-title"><i class="fa-solid fa-chalkboard text-sky-400"></i> Presentation</div>
                    <button id="cmd-annotate" class="cmd-btn"><i class="fa-solid fa-pen-ruler text-violet-400"></i> Annotation Mode</button>
                    <button id="cmd-laser" class="cmd-btn"><i class="fa-solid fa-wand-magic-sparkles text-red-400"></i> Laser Pointer</button>
                    <button id="cmd-whiteboard" class="cmd-btn"><i class="fa-solid fa-chalkboard text-sky-400"></i> Whiteboard</button>
                    <button id="cmd-presentation" class="cmd-btn"><i class="fa-solid fa-tv text-indigo-400"></i> Presentation Mode</button>
                </div>

                {{-- Recording --}}
                <div class="command-center__section">
                    <div class="command-center__section-title"><i class="fa-solid fa-circle text-red-400" style="font-size:0.5rem;"></i> Recording</div>
                    <button id="cmd-record" class="cmd-btn"><i class="fa-solid fa-circle text-red-500" style="font-size:0.625rem;"></i> Start Recording</button>
                </div>

                {{-- Room Management --}}
                <div class="command-center__section">
                    <div class="command-center__section-title"><i class="fa-solid fa-users-gear text-emerald-400"></i> Permissions</div>
                    <button id="cmd-disable-chat" class="cmd-btn"><i class="fa-solid fa-comment-slash text-slate-400"></i> Disable Student Chat</button>
                    <button id="cmd-disable-screenshare" class="cmd-btn"><i class="fa-solid fa-display text-slate-400"></i> Disable Student Screen Share</button>
                    <button id="cmd-raise-hand-only" class="cmd-btn"><i class="fa-solid fa-hand text-amber-400"></i> Raise Hand Required to Speak</button>
                </div>

                {{-- Session Actions --}}
                <div class="command-center__section">
                    <div class="command-center__section-title"><i class="fa-solid fa-door-open text-rose-400"></i> Session</div>
                    <button id="cmd-copy-link" class="cmd-btn"><i class="fa-solid fa-link text-sky-400"></i> Copy Invite Link</button>
                    @if($isMeetingActive)
                        <button id="cmd-end-meeting" class="cmd-btn cmd-btn--danger"><i class="fa-solid fa-phone-slash"></i> End Meeting for All</button>
                    @endif
                </div>
            </div>
        </aside>
        @endif

        {{-- Video Area --}}
        <div class="conf-stage__video-area">
            <div id="video-grid" class="video-grid" data-count="1">
                {{-- Local Video Tile --}}
                <div id="local-tile" class="video-tile video-tile--local">
                    <video id="local-video" autoplay playsinline muted></video>
                    <div id="local-avatar" class="video-tile__avatar hidden">
                        <div class="video-tile__avatar-circle">{{ strtoupper(substr($actorName, 0, 1)) }}</div>
                    </div>
                    <div class="video-tile__label">
                        <span>You</span>
                        <span id="local-hand-icon" class="hand-icon hidden">&#9995;</span>
                    </div>
                    <div id="local-muted-icon" class="video-tile__status hidden">
                        <span class="video-tile__status-icon"><i class="fa-solid fa-microphone-slash"></i></span>
                    </div>
                </div>

                {{-- Screen Share Tile (injected dynamically, hidden by default) --}}
                <div id="screen-tile" class="video-tile video-tile--screen hidden" title="Double-click for full screen">
                    <video id="screen-video" autoplay playsinline></video>
                    <canvas id="annotation-canvas" class="annotation-layer" width="1920" height="1080"></canvas>
                    <div id="laser-dot" class="laser-dot hidden"></div>
                    <div class="video-tile__label" style="background:rgba(79,70,229,0.8);">
                        <i class="fa-solid fa-display" style="font-size:0.625rem;margin-right:0.25rem;"></i>
                        <span id="screen-label">Screen Share</span>
                    </div>
                </div>
            </div>

            {{-- Annotation Toolbar (shown when annotation mode active) --}}
            <div id="annotation-toolbar" class="annotation-toolbar hidden">
                <button data-tool="pen" class="active" title="Pen"><i class="fa-solid fa-pen"></i></button>
                <button data-tool="highlighter" title="Highlighter"><i class="fa-solid fa-highlighter"></i></button>
                <button data-tool="arrow" title="Arrow"><i class="fa-solid fa-arrow-pointer"></i></button>
                <button data-tool="rectangle" title="Rectangle"><i class="fa-regular fa-square"></i></button>
                <button data-tool="circle" title="Circle"><i class="fa-regular fa-circle"></i></button>
                <button data-tool="text" title="Text"><i class="fa-solid fa-font"></i></button>
                <div class="separator"></div>
                <div class="color-dot active" data-color="#ef4444" style="background:#ef4444;" title="Red"></div>
                <div class="color-dot" data-color="#3b82f6" style="background:#3b82f6;" title="Blue"></div>
                <div class="color-dot" data-color="#10b981" style="background:#10b981;" title="Green"></div>
                <div class="color-dot" data-color="#f59e0b" style="background:#f59e0b;" title="Yellow"></div>
                <div class="color-dot" data-color="#ffffff" style="background:#ffffff;" title="White"></div>
                <div class="separator"></div>
                <button data-tool="undo" title="Undo"><i class="fa-solid fa-rotate-left"></i></button>
                <button data-tool="clear" title="Clear All"><i class="fa-solid fa-trash-can"></i></button>
            </div>
        </div>

        {{-- Sidebar Drawer --}}
        <div id="sidebar-backdrop" class="conf-sidebar__backdrop"></div>
        <aside id="sidebar" class="conf-sidebar">
            <div class="conf-sidebar__tabs">
                <button class="conf-sidebar__tab conf-sidebar__tab--active" data-tab="chat">
                    <i class="fa-solid fa-comment mr-1"></i>Chat
                </button>
                <button class="conf-sidebar__tab" data-tab="people">
                    <i class="fa-solid fa-users mr-1"></i>People <span id="people-count" class="ml-0.5 text-xs opacity-60">1</span>
                </button>
                <button class="conf-sidebar__tab" data-tab="files">
                    <i class="fa-solid fa-paperclip mr-1"></i>Files
                </button>
            </div>

            {{-- Chat Tab --}}
            <div id="pane-chat" class="conf-sidebar__content flex flex-col">
                <div id="chat-log" class="flex-1 p-3 space-y-1.5 scroll-y" style="min-height:0;"></div>
                <form id="chat-form" class="p-2 border-t" style="border-color:var(--border);">
                    <div class="flex gap-1.5">
                        <label class="tb-btn" style="min-width:2.25rem;height:2.25rem;cursor:pointer;">
                            <i class="fa-solid fa-paperclip" style="font-size:0.75rem;"></i>
                            <input type="file" id="file-upload" class="hidden" accept="image/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip">
                        </label>
                        <input id="chat-input" type="text" maxlength="2000" required placeholder="Message..."
                            class="flex-1 min-w-0 px-3 py-1.5 rounded-lg text-sm outline-none"
                            style="background:var(--surface-2);border:1px solid var(--border);color:var(--text-primary);">
                        <button type="submit" class="tb-btn" style="min-width:2.25rem;height:2.25rem;background:var(--brand);color:#fff;">
                            <i class="fa-solid fa-paper-plane" style="font-size:0.75rem;"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- People Tab --}}
            <div id="pane-people" class="conf-sidebar__content hidden scroll-y p-2 space-y-0.5"></div>

            {{-- Files Tab --}}
            <div id="pane-files" class="conf-sidebar__content hidden scroll-y p-3">
                <p id="no-files-msg" class="text-center text-xs py-6" style="color:var(--text-muted);">No files shared yet</p>
                <div id="files-list" class="space-y-2"></div>
            </div>
        </aside>
    </main>

    {{-- ==================== BOTTOM TOOLBAR ==================== --}}
    <footer class="conf-toolbar">
        <button id="btn-mic" class="tb-btn" title="Toggle Mic (M)">
            <i class="fa-solid fa-microphone"></i>
            <span>Mic</span>
        </button>
        <button id="btn-cam" class="tb-btn" title="Toggle Camera (V)">
            <i class="fa-solid fa-video"></i>
            <span>Cam</span>
        </button>
        <button id="btn-screen" class="tb-btn tb-btn--screen" title="Share Screen">
            <i class="fa-solid fa-display"></i>
            <span>Screen</span>
        </button>
        <div class="tb-divider"></div>
        <button id="btn-hand" class="tb-btn" title="Raise Hand (H)">
            <i class="fa-solid fa-hand"></i>
            <span>Hand</span>
        </button>
        <button id="btn-emoji" class="tb-btn" title="React">
            <i class="fa-solid fa-face-smile"></i>
            <span>React</span>
        </button>

        @if($actorRole === 'teacher')
            <div class="tb-divider"></div>
            <button id="btn-record" class="tb-btn" title="Record">
                <i class="fa-solid fa-circle" style="font-size:0.625rem;color:#ef4444;"></i>
                <span>Rec</span>
            </button>
        @endif

        <div class="tb-divider"></div>
        <button id="btn-settings" class="tb-btn" title="Settings">
            <i class="fa-solid fa-gear"></i>
            <span>More</span>
        </button>
        <button id="btn-pip" class="tb-btn" title="Picture-in-Picture">
            <i class="fa-solid fa-clone"></i>
            <span>PiP</span>
        </button>

        @if($actorRole === 'teacher' && $isMeetingActive)
            <div class="tb-divider"></div>
            <button id="btn-end" class="tb-btn tb-btn--danger" title="End Meeting">
                <i class="fa-solid fa-phone-slash"></i>
                <span>End</span>
            </button>
        @endif

        <a href="{{ $backUrl }}" id="btn-leave" class="tb-btn" title="Leave" style="text-decoration:none;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Leave</span>
        </a>
    </footer>
</div>

{{-- ==================== SCREEN SHARE FULLSCREEN OVERLAY ==================== --}}
<div id="fullscreen-overlay" class="fullscreen-overlay">
    <div class="fullscreen-overlay__topbar">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-expand text-indigo-400"></i>
            <span id="fs-label" class="text-sm font-semibold">Screen Share</span>
        </div>
        <div class="flex items-center gap-2">
            <button id="fs-pip" class="tb-btn" style="width:2rem;height:2rem;min-width:2rem;"><i class="fa-solid fa-clone" style="font-size:0.75rem;"></i></button>
            <button id="fs-close" class="tb-btn" style="width:2rem;height:2rem;min-width:2rem;background:#dc2626;color:#fff;"><i class="fa-solid fa-xmark" style="font-size:0.875rem;"></i></button>
        </div>
    </div>
    <video id="fs-video" autoplay playsinline></video>
    <canvas id="fs-annotation-canvas" class="annotation-layer" width="1920" height="1080"></canvas>
    <div id="fs-laser-dot" class="laser-dot hidden"></div>
    <div class="fullscreen-overlay__hint">
        <i class="fa-solid fa-compress mr-1"></i> Press <kbd>ESC</kbd> or double-click to exit full screen
    </div>
</div>

{{-- ==================== EMOJI PICKER POPUP ==================== --}}
<div id="emoji-popup" class="hidden" style="position:fixed;bottom:5rem;left:50%;transform:translateX(-50%);
    background:var(--surface-1);border:1px solid var(--border);border-radius:0.75rem;padding:0.5rem;
    box-shadow:0 12px 40px rgba(0,0,0,0.4);z-index:9000;">
    <div id="emoji-grid" class="grid grid-cols-5 gap-0.5"></div>
</div>

{{-- ==================== ATTENTION CHECK MODAL ==================== --}}
<div id="attention-modal" class="modal-backdrop hidden">
    <div class="modal-card">
        <div class="text-4xl mb-3">👀</div>
        <h2 class="text-lg font-extrabold mb-1.5">Attention Check</h2>
        <p id="attention-text" class="text-sm mb-4" style="color:var(--text-secondary);">Are you paying attention?</p>
        <button id="attention-ok" class="px-6 py-2 rounded-lg text-sm font-bold" style="background:var(--brand);color:#fff;">I'm Here!</button>
    </div>
</div>

{{-- ==================== SETTINGS MODAL ==================== --}}
<div id="settings-modal" class="settings-overlay hidden">
    <div class="settings-card">
        <div class="settings-card__header">
            <h2 class="text-sm font-extrabold">Settings</h2>
            <button id="settings-close" class="participant-action"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="p-4 space-y-4">
            <div>
                <label class="text-xs font-bold uppercase tracking-wider block mb-1" style="color:var(--text-muted);">Audio Input</label>
                <select id="sel-audio-in" class="w-full px-3 py-1.5 rounded-lg text-sm" style="background:var(--surface-2);border:1px solid var(--border);color:var(--text-primary);"></select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider block mb-1" style="color:var(--text-muted);">Camera</label>
                <select id="sel-video-in" class="w-full px-3 py-1.5 rounded-lg text-sm" style="background:var(--surface-2);border:1px solid var(--border);color:var(--text-primary);"></select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-wider block mb-1" style="color:var(--text-muted);">Speaker</label>
                <select id="sel-audio-out" class="w-full px-3 py-1.5 rounded-lg text-sm" style="background:var(--surface-2);border:1px solid var(--border);color:var(--text-primary);"></select>
            </div>

            <div style="border-top:1px solid var(--border);padding-top:0.75rem;">
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-xs font-semibold">Video Quality</span>
                    <select id="sel-quality" class="px-2 py-1 rounded text-xs" style="background:var(--surface-2);border:1px solid var(--border);color:var(--text-primary);">
                        <option value="high">1080p 60fps</option>
                        <option value="medium" selected>720p 30fps</option>
                        <option value="low">480p 15fps</option>
                        <option value="minimal">240p Low BW</option>
                    </select>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-xs font-semibold">Push-to-Talk</span>
                    <div id="toggle-ptt" class="toggle"></div>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-xs font-semibold">Noise Suppression</span>
                    <div id="toggle-noise" class="toggle toggle--on"></div>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-xs font-semibold">Notification Sounds</span>
                    <div id="toggle-sounds" class="toggle toggle--on"></div>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <span class="text-xs font-semibold">Dark Mode</span>
                    <div id="toggle-theme" class="toggle toggle--on"></div>
                </div>
            </div>

            <div style="border-top:1px solid var(--border);padding-top:0.75rem;">
                <p class="text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--text-muted);">Keyboard Shortcuts</p>
                <div class="grid grid-cols-2 gap-y-1 text-xs" style="color:var(--text-secondary);">
                    <span><kbd class="px-1 py-0.5 rounded text-xs font-mono" style="background:var(--surface-3);color:var(--text-primary);">M</kbd> Mic</span>
                    <span><kbd class="px-1 py-0.5 rounded text-xs font-mono" style="background:var(--surface-3);color:var(--text-primary);">V</kbd> Camera</span>
                    <span><kbd class="px-1 py-0.5 rounded text-xs font-mono" style="background:var(--surface-3);color:var(--text-primary);">H</kbd> Hand</span>
                    <span><kbd class="px-1 py-0.5 rounded text-xs font-mono" style="background:var(--surface-3);color:var(--text-primary);">Esc</kbd> Exit FS</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==================== TOAST CONTAINER ==================== --}}
<div id="toast-container" class="toast-container"></div>

{{-- ==================== DATA & SCRIPTS ==================== --}}
@php
    $conferenceData = [
        'id' => $conference->id,
        'slug' => $conference->slug,
        'title' => $conference->title,
    ];
    $actorData = [
        'id' => $actorId,
        'name' => $actorName,
        'role' => $actorRole,
    ];
    $meetingData = [
        'isActive' => $isMeetingActive,
        'joinLink' => $joinLink,
        'endMeetingUrl' => $endMeetingUrl,
        'backUrl' => $backUrl,
        'csrf' => csrf_token(),
    ];
@endphp

<script>
    window.__CONFERENCE__ = @json($conferenceData);
    window.__ACTOR__ = @json($actorData);
    window.__SIGNALING__ = @json($signalingConfig);
    window.__MEETING__ = @json($meetingData);
</script>

@vite('resources/js/conference/index.js')

<script type="module">
const { ConferenceApp } = window;
if (!ConferenceApp) throw new Error('Conference bundle not loaded.');

// ═══════════════════════════════════════════
// CONFIG
// ═══════════════════════════════════════════
const conference = window.__CONFERENCE__;
const actor = window.__ACTOR__;
const signalingConfig = window.__SIGNALING__;
const meetingConfig = window.__MEETING__;

const $ = (s) => document.querySelector(s);
const $$ = (s) => document.querySelectorAll(s);

// ═══════════════════════════════════════════
// DOM REFERENCES
// ═══════════════════════════════════════════
const dom = {
    // Video
    videoGrid: $('#video-grid'),
    localVideo: $('#local-video'),
    localAvatar: $('#local-avatar'),
    localTile: $('#local-tile'),
    localHandIcon: $('#local-hand-icon'),
    localMutedIcon: $('#local-muted-icon'),
    screenTile: $('#screen-tile'),
    screenVideo: $('#screen-video'),
    screenLabel: $('#screen-label'),
    // Fullscreen overlay
    fsOverlay: $('#fullscreen-overlay'),
    fsVideo: $('#fs-video'),
    fsLabel: $('#fs-label'),
    fsClose: $('#fs-close'),
    fsPip: $('#fs-pip'),
    fsAnnotCanvas: $('#fs-annotation-canvas'),
    fsLaserDot: $('#fs-laser-dot'),
    // Annotation
    annotCanvas: $('#annotation-canvas'),
    annotToolbar: $('#annotation-toolbar'),
    laserDot: $('#laser-dot'),
    // Toolbar buttons
    btnMic: $('#btn-mic'),
    btnCam: $('#btn-cam'),
    btnScreen: $('#btn-screen'),
    btnHand: $('#btn-hand'),
    btnEmoji: $('#btn-emoji'),
    btnRecord: $('#btn-record'),
    btnSettings: $('#btn-settings'),
    btnPip: $('#btn-pip'),
    btnEnd: $('#btn-end'),
    btnLeave: $('#btn-leave'),
    // Sidebar
    sidebar: $('#sidebar'),
    sidebarBackdrop: $('#sidebar-backdrop'),
    sidebarToggle: $('#sidebar-toggle'),
    chatUnreadBadge: $('#chat-unread-badge'),
    chatLog: $('#chat-log'),
    chatForm: $('#chat-form'),
    chatInput: $('#chat-input'),
    fileUpload: $('#file-upload'),
    paneChat: $('#pane-chat'),
    panePeople: $('#pane-people'),
    paneFiles: $('#pane-files'),
    noFilesMsg: $('#no-files-msg'),
    filesList: $('#files-list'),
    peopleCount: $('#people-count'),
    // Emoji popup
    emojiPopup: $('#emoji-popup'),
    emojiGrid: $('#emoji-grid'),
    // Command center (teacher)
    cmdToggle: $('#cmd-toggle'),
    cmdCenter: $('#command-center'),
    cmdClose: $('#cmd-close'),
    // Top bar
    meetingTimer: $('#meeting-timer'),
    recordingIndicator: $('#recording-indicator'),
    // Modals
    attentionModal: $('#attention-modal'),
    attentionText: $('#attention-text'),
    attentionOk: $('#attention-ok'),
    settingsModal: $('#settings-modal'),
    settingsClose: $('#settings-close'),
    // Settings
    selAudioIn: $('#sel-audio-in'),
    selVideoIn: $('#sel-video-in'),
    selAudioOut: $('#sel-audio-out'),
    selQuality: $('#sel-quality'),
    togglePtt: $('#toggle-ptt'),
    toggleNoise: $('#toggle-noise'),
    toggleSounds: $('#toggle-sounds'),
    toggleTheme: $('#toggle-theme'),
    // Toast
    toastContainer: $('#toast-container'),
};

// ═══════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════
const peerStreams = new Map();
let sidebarOpen = false;
let cmdOpen = false;
let chatUnread = 0;
let isScreenFull = false;
let screenShareSrc = null; // 'local' | 'remote' | null
let annotationTool = 'pen';
let annotationColor = '#ef4444';
let annotationWidth = 3;
let annotationActive = false;
let laserActive = false;
let annotationStrokes = []; // undo stack
let isDrawing = false;
let lastDrawPt = null;
let roomLocked = false;
let chatDisabled = false;
let studentScreenShareDisabled = false;
let raiseHandRequired = false;
let isWhiteboardActive = false;

// ═══════════════════════════════════════════
// TOASTS
// ═══════════════════════════════════════════
function toast(msg, type = '') {
    const el = document.createElement('div');
    el.className = `toast ${type ? 'toast--' + type : ''}`;
    el.textContent = msg;
    dom.toastContainer.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

// ═══════════════════════════════════════════
// SIDEBAR
// ═══════════════════════════════════════════
function toggleSidebar(force) {
    sidebarOpen = force !== undefined ? force : !sidebarOpen;
    dom.sidebar.classList.toggle('conf-sidebar--open', sidebarOpen);
    dom.sidebarBackdrop.classList.toggle('conf-sidebar__backdrop--visible', sidebarOpen && window.innerWidth < 1024);
    if (sidebarOpen) { chatUnread = 0; dom.chatUnreadBadge.classList.add('hidden'); }
}
dom.sidebarToggle.addEventListener('click', () => toggleSidebar());
dom.sidebarBackdrop.addEventListener('click', () => toggleSidebar(false));

// Sidebar tabs
$$('.conf-sidebar__tab').forEach(tab => {
    tab.addEventListener('click', () => {
        $$('.conf-sidebar__tab').forEach(t => t.classList.remove('conf-sidebar__tab--active'));
        tab.classList.add('conf-sidebar__tab--active');
        [dom.paneChat, dom.panePeople, dom.paneFiles].forEach(p => p.classList.add('hidden'));
        const target = tab.dataset.tab;
        if (target === 'chat') dom.paneChat.classList.remove('hidden');
        else if (target === 'people') dom.panePeople.classList.remove('hidden');
        else if (target === 'files') dom.paneFiles.classList.remove('hidden');
    });
});

// ═══════════════════════════════════════════
// COMMAND CENTER (Teacher)
// ═══════════════════════════════════════════
if (dom.cmdToggle) {
    dom.cmdToggle.addEventListener('click', () => {
        cmdOpen = !cmdOpen;
        dom.cmdCenter.classList.toggle('command-center--open', cmdOpen);
    });
    dom.cmdClose?.addEventListener('click', () => {
        cmdOpen = false;
        dom.cmdCenter.classList.remove('command-center--open');
    });
}

// ═══════════════════════════════════════════
// VIDEO GRID LAYOUT
// ═══════════════════════════════════════════
function updateGridLayout() {
    const tiles = dom.videoGrid.querySelectorAll('.video-tile:not(.hidden)');
    dom.videoGrid.dataset.count = String(Math.min(tiles.length, 9));
}

function createRemoteTile(peerId, name) {
    const tile = document.createElement('div');
    tile.id = `tile-${peerId}`;
    tile.className = 'video-tile';

    const video = document.createElement('video');
    video.id = `video-${peerId}`;
    video.autoplay = true;
    video.playsInline = true;

    const avatar = document.createElement('div');
    avatar.id = `avatar-${peerId}`;
    avatar.className = 'video-tile__avatar hidden';
    avatar.innerHTML = `<div class="video-tile__avatar-circle">${(name || '?').charAt(0).toUpperCase()}</div>`;

    const label = document.createElement('div');
    label.className = 'video-tile__label';
    label.innerHTML = `<span>${name || peerId}</span><span id="hand-${peerId}" class="hand-icon hidden">&#9995;</span>`;

    const status = document.createElement('div');
    status.id = `status-${peerId}`;
    status.className = 'video-tile__status hidden';
    status.innerHTML = '<span class="video-tile__status-icon"><i class="fa-solid fa-microphone-slash"></i></span>';

    tile.appendChild(video);
    tile.appendChild(avatar);
    tile.appendChild(label);
    tile.appendChild(status);
    dom.videoGrid.appendChild(tile);
    updateGridLayout();
    return video;
}

// ═══════════════════════════════════════════
// SCREEN SHARE - DOUBLE-CLICK FULLSCREEN
// ═══════════════════════════════════════════
dom.screenTile.addEventListener('dblclick', () => enterFullScreen());
dom.fsClose.addEventListener('click', () => exitFullScreen());
dom.fsOverlay.addEventListener('dblclick', (e) => {
    if (e.target === dom.fsVideo || e.target === dom.fsOverlay) exitFullScreen();
});
dom.fsPip?.addEventListener('click', () => {
    if (dom.fsVideo.requestPictureInPicture) dom.fsVideo.requestPictureInPicture().catch(() => {});
});

function enterFullScreen() {
    const srcVideo = dom.screenVideo;
    if (!srcVideo.srcObject) return;
    dom.fsVideo.srcObject = srcVideo.srcObject;
    dom.fsLabel.textContent = dom.screenLabel.textContent;
    dom.fsOverlay.classList.add('fullscreen-overlay--active');
    isScreenFull = true;
    dom.fsVideo.play().catch(() => {});
    // Sync annotation canvas
    syncAnnotCanvasSize(dom.fsAnnotCanvas);
    // Try native fullscreen on mobile
    if (dom.fsOverlay.requestFullscreen) dom.fsOverlay.requestFullscreen().catch(() => {});
    else if (dom.fsOverlay.webkitRequestFullscreen) dom.fsOverlay.webkitRequestFullscreen();
}

function exitFullScreen() {
    dom.fsOverlay.classList.remove('fullscreen-overlay--active');
    isScreenFull = false;
    dom.fsVideo.srcObject = null;
    if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
    else if (document.webkitFullscreenElement) document.webkitExitFullscreen();
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isScreenFull) exitFullScreen();
});
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement && isScreenFull) exitFullScreen();
});

// ═══════════════════════════════════════════
// ANNOTATION SYSTEM
// ═══════════════════════════════════════════
function syncAnnotCanvasSize(canvas) {
    if (!canvas) return;
    const parent = canvas.parentElement;
    if (!parent) return;
    canvas.width = parent.clientWidth;
    canvas.height = parent.clientHeight;
}

// Annotation toolbar tool selection
dom.annotToolbar.querySelectorAll('button[data-tool]').forEach(btn => {
    btn.addEventListener('click', () => {
        const tool = btn.dataset.tool;
        if (tool === 'undo') {
            annotationStrokes.pop();
            redrawAnnotations();
            app.sendAnnotation({ type: 'clear' });
            annotationStrokes.forEach(s => app.sendAnnotation(s));
            return;
        }
        if (tool === 'clear') {
            annotationStrokes = [];
            redrawAnnotations();
            app.sendAnnotation({ type: 'clear' });
            return;
        }
        annotationTool = tool;
        dom.annotToolbar.querySelectorAll('button[data-tool]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    });
});

dom.annotToolbar.querySelectorAll('.color-dot').forEach(dot => {
    dot.addEventListener('click', () => {
        annotationColor = dot.dataset.color;
        dom.annotToolbar.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
        dot.classList.add('active');
    });
});

// Canvas drawing
function setupAnnotDrawing(canvas) {
    if (!canvas) return;
    canvas.addEventListener('pointerdown', (e) => {
        if (!annotationActive) return;
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        lastDrawPt = { x: (e.clientX - rect.left) / rect.width, y: (e.clientY - rect.top) / rect.height };
    });
    canvas.addEventListener('pointermove', (e) => {
        if (!isDrawing || !annotationActive) return;
        const rect = canvas.getBoundingClientRect();
        const pt = { x: (e.clientX - rect.left) / rect.width, y: (e.clientY - rect.top) / rect.height };
        const stroke = {
            type: 'line', x1: lastDrawPt.x, y1: lastDrawPt.y, x2: pt.x, y2: pt.y,
            color: annotationColor, width: annotationTool === 'highlighter' ? 12 : annotationWidth,
            opacity: annotationTool === 'highlighter' ? 0.4 : 1,
        };
        drawStroke(canvas, stroke);
        app.sendAnnotation(stroke);
        annotationStrokes.push(stroke);
        lastDrawPt = pt;
    });
    canvas.addEventListener('pointerup', () => { isDrawing = false; lastDrawPt = null; });
    canvas.addEventListener('pointerleave', () => { isDrawing = false; lastDrawPt = null; });
}

function drawStroke(canvas, s) {
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    if (s.type === 'clear') { ctx.clearRect(0, 0, canvas.width, canvas.height); return; }
    if (s.type !== 'line') return;
    ctx.save();
    ctx.globalAlpha = s.opacity || 1;
    ctx.strokeStyle = s.color || '#ef4444';
    ctx.lineWidth = s.width || 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.beginPath();
    ctx.moveTo(s.x1 * canvas.width, s.y1 * canvas.height);
    ctx.lineTo(s.x2 * canvas.width, s.y2 * canvas.height);
    ctx.stroke();
    ctx.restore();
}

function redrawAnnotations() {
    [dom.annotCanvas, dom.fsAnnotCanvas].forEach(c => {
        if (!c) return;
        const ctx = c.getContext('2d');
        if (!ctx) return;
        ctx.clearRect(0, 0, c.width, c.height);
        annotationStrokes.forEach(s => drawStroke(c, s));
    });
}

setupAnnotDrawing(dom.annotCanvas);
setupAnnotDrawing(dom.fsAnnotCanvas);

// ═══════════════════════════════════════════
// EMOJIS
// ═══════════════════════════════════════════
const emojis = ['👍','👏','❤️','😂','🎉','🔥','😮','😢','💯','✅','❌','🤔','👋','⭐','💪'];
emojis.forEach(e => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'text-xl p-1 rounded-lg cursor-pointer transition-colors';
    btn.style.cssText = 'background:transparent;border:none;color:inherit;';
    btn.textContent = e;
    btn.onmouseenter = () => btn.style.background = 'var(--surface-3)';
    btn.onmouseleave = () => btn.style.background = 'transparent';
    btn.onclick = () => { app.sendEmoji(e); dom.emojiPopup.classList.add('hidden'); };
    dom.emojiGrid.appendChild(btn);
});

dom.btnEmoji.addEventListener('click', (e) => { e.stopPropagation(); dom.emojiPopup.classList.toggle('hidden'); });
document.addEventListener('click', (e) => {
    if (!dom.emojiPopup.contains(e.target) && e.target !== dom.btnEmoji) dom.emojiPopup.classList.add('hidden');
});

function showFloatingEmoji(emoji, name) {
    const el = document.createElement('div');
    el.className = 'emoji-float';
    el.textContent = emoji;
    el.style.left = (20 + Math.random() * 60) + '%';
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 2500);
    addSystemChat(`${name} reacted ${emoji}`);
}

// ═══════════════════════════════════════════
// CHAT HELPERS
// ═══════════════════════════════════════════
function addChatBubble(payload, mine = false) {
    const wrap = document.createElement('div');
    wrap.className = mine ? 'flex justify-end' : 'flex justify-start';
    const bubble = document.createElement('div');
    bubble.className = `chat-bubble ${mine ? 'chat-bubble--mine' : 'chat-bubble--other'}`;
    const meta = document.createElement('div');
    meta.className = 'chat-meta';
    meta.textContent = `${payload.name}`;
    const body = document.createElement('p');
    body.textContent = payload.message;
    bubble.appendChild(meta);
    bubble.appendChild(body);
    wrap.appendChild(bubble);
    dom.chatLog.appendChild(wrap);
    dom.chatLog.scrollTop = dom.chatLog.scrollHeight;
    if (!mine && !sidebarOpen) {
        chatUnread++;
        dom.chatUnreadBadge.textContent = String(chatUnread);
        dom.chatUnreadBadge.classList.remove('hidden');
    }
}

function addSystemChat(text) {
    const el = document.createElement('div');
    el.className = 'chat-system';
    el.textContent = text;
    dom.chatLog.appendChild(el);
    dom.chatLog.scrollTop = dom.chatLog.scrollHeight;
}

function addFileChat(data) {
    const wrap = document.createElement('div');
    wrap.className = data.from?.id === actor.id ? 'flex justify-end' : 'flex justify-start';
    const card = document.createElement('div');
    card.className = 'chat-bubble chat-bubble--other';
    card.innerHTML = `
        <div class="chat-meta">${data.from?.name || 'Unknown'} shared a file</div>
        <a href="${data.fileUrl || '#'}" target="_blank" class="flex items-center gap-1.5 text-sm font-semibold" style="color:var(--brand);">
            <i class="fa-solid fa-file-arrow-down"></i> ${data.fileName || 'File'}
        </a>
        <span class="text-xs" style="color:var(--text-muted);">${formatBytes(data.fileSize || 0)}</span>
    `;
    wrap.appendChild(card);
    dom.chatLog.appendChild(wrap);
    dom.chatLog.scrollTop = dom.chatLog.scrollHeight;
    addToFiles(data);
}

function addToFiles(data) {
    dom.noFilesMsg?.classList.add('hidden');
    const item = document.createElement('div');
    item.className = 'flex items-center gap-2 p-2 rounded-lg';
    item.style.cssText = 'background:var(--surface-2);border:1px solid var(--border);';
    item.innerHTML = `
        <i class="fa-solid fa-file" style="color:var(--brand);"></i>
        <div class="flex-1 min-w-0">
            <a href="${data.fileUrl || '#'}" target="_blank" class="text-xs font-semibold block truncate" style="color:var(--text-primary);">${data.fileName || 'File'}</a>
            <p class="text-xs" style="color:var(--text-muted);">${data.from?.name || ''} · ${formatBytes(data.fileSize || 0)}</p>
        </div>
    `;
    dom.filesList.appendChild(item);
}

function formatBytes(b) {
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
    return (b / 1048576).toFixed(1) + ' MB';
}

// ═══════════════════════════════════════════
// PARTICIPANTS LIST
// ═══════════════════════════════════════════
function renderParticipants(members, raisedHands) {
    dom.panePeople.innerHTML = '';
    members.forEach((info, id) => {
        const row = document.createElement('div');
        row.className = 'participant-row';

        const av = document.createElement('div');
        av.className = 'participant-row__avatar';
        av.textContent = (info.name || '?').charAt(0).toUpperCase();

        const inf = document.createElement('div');
        inf.className = 'participant-row__info';
        const nameHTML = `<div class="participant-row__name">${info.name || id}${raisedHands.has(id) ? ' ✋' : ''}${id === actor.id ? ' (You)' : ''}</div>`;
        const roleHTML = `<div class="participant-row__role">${info.role || 'participant'}</div>`;
        inf.innerHTML = nameHTML + roleHTML;

        row.appendChild(av);
        row.appendChild(inf);

        if (actor.role === 'teacher' && id !== actor.id) {
            const acts = document.createElement('div');
            acts.className = 'participant-row__actions';
            [
                { icon: 'fa-microphone-slash', title: 'Mute', cls: '', action: () => app.muteParticipant(id) },
                { icon: 'fa-microphone', title: 'Unmute', cls: '', action: () => app.unmuteParticipant(id) },
                { icon: 'fa-video-slash', title: 'Cam Off', cls: '', action: () => app.disableCamParticipant(id) },
                { icon: 'fa-user-slash', title: 'Kick', cls: 'participant-action--danger', action: () => { if (confirm(`Remove ${info.name}?`)) app.kickParticipant(id); } },
            ].forEach(c => {
                const btn = document.createElement('button');
                btn.className = `participant-action ${c.cls}`;
                btn.innerHTML = `<i class="fa-solid ${c.icon}"></i>`;
                btn.title = c.title;
                btn.onclick = c.action;
                acts.appendChild(btn);
            });
            row.appendChild(acts);
        }

        dom.panePeople.appendChild(row);
    });
    dom.peopleCount.textContent = String(members.size);
}

// ═══════════════════════════════════════════
// MEETING TIMER
// ═══════════════════════════════════════════
let timerStart = Date.now();
let timerInterval = null;
function startMeetingTimer() {
    timerInterval = setInterval(() => {
        const s = Math.floor((Date.now() - timerStart) / 1000);
        const h = String(Math.floor(s / 3600)).padStart(2, '0');
        const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
        const sec = String(s % 60).padStart(2, '0');
        dom.meetingTimer.textContent = `${h}:${m}:${sec}`;
    }, 1000);
}

// ═══════════════════════════════════════════
// TOGGLE HELPERS
// ═══════════════════════════════════════════
function setToggle(el, on) {
    if (!el) return;
    el.classList.toggle('toggle--on', on);
    el.dataset.on = on ? '1' : '0';
}

// ═══════════════════════════════════════════
// CONFERENCE APP INIT
// ═══════════════════════════════════════════
const app = new ConferenceApp({
    conference, actor, signalingConfig, meetingConfig,
    ui: {
        onLocalStream: (stream) => {
            dom.localVideo.srcObject = stream;
            startMeetingTimer();
        },
        onSystemMessage: (text) => addSystemChat(text),
        onBanner: (msg) => toast(msg, 'warning'),

        onChatMessage: (payload, mine) => addChatBubble(payload, mine),
        onChatHistory: (msgs) => {
            msgs.forEach(msg => {
                if (msg.type === 'file') {
                    addFileChat({ from: { name: msg.display_name, role: msg.role }, fileName: msg.file_name, fileUrl: msg.file_url, fileMime: msg.file_mime, fileSize: msg.file_size });
                } else if (msg.type !== 'system') {
                    addChatBubble({ name: msg.display_name, role: msg.role, message: msg.content }, msg.actor_id === actor.id);
                }
            });
        },

        onParticipantsChanged: renderParticipants,

        onRemoteStream: (peerId, stream, name) => {
            const el = document.getElementById(`video-${peerId}`) || createRemoteTile(peerId, name);
            el.srcObject = stream;
            el.play().catch(() => {});
            peerStreams.set(peerId, stream);
        },
        getPeerStream: (peerId) => peerStreams.get(peerId),
        onPeerRemoved: (peerId) => {
            document.getElementById(`tile-${peerId}`)?.remove();
            peerStreams.delete(peerId);
            updateGridLayout();
        },

        onRemoteScreenShare: (peerId, stream, name) => {
            dom.screenVideo.srcObject = stream;
            dom.screenLabel.textContent = `${name}'s Screen`;
            dom.screenTile.classList.remove('hidden');
            dom.screenVideo.play().catch(() => {});
            screenShareSrc = 'remote';
            updateGridLayout();
            syncAnnotCanvasSize(dom.annotCanvas);
            toast(`${name} is sharing their screen. Double-click to view full screen.`, 'success');
        },
        onRemoteScreenShareStopped: () => {
            dom.screenTile.classList.add('hidden');
            dom.screenVideo.srcObject = null;
            screenShareSrc = null;
            updateGridLayout();
            if (isScreenFull) exitFullScreen();
        },
        onLocalScreenShareStarted: (stream) => {
            dom.screenVideo.srcObject = stream;
            dom.screenLabel.textContent = 'Your Screen';
            dom.screenTile.classList.remove('hidden');
            dom.screenVideo.play().catch(() => {});
            screenShareSrc = 'local';
            updateGridLayout();
            syncAnnotCanvasSize(dom.annotCanvas);
            dom.btnScreen.classList.add('tb-btn--danger');
            dom.btnScreen.classList.remove('tb-btn--screen');
            dom.btnScreen.querySelector('span').textContent = 'Stop';
        },
        onLocalScreenShareStopped: () => {
            dom.screenTile.classList.add('hidden');
            dom.screenVideo.srcObject = null;
            screenShareSrc = null;
            updateGridLayout();
            if (isScreenFull) exitFullScreen();
            dom.btnScreen.classList.remove('tb-btn--danger');
            dom.btnScreen.classList.add('tb-btn--screen');
            dom.btnScreen.querySelector('span').textContent = 'Screen';
        },

        onTeacherSpotlight: (active) => {
            dom.videoGrid.classList.toggle('video-grid--spotlight', active);
        },
        onHandRaised: (peerId, raised) => {
            if (peerId === actor.id) {
                dom.localHandIcon.classList.toggle('hidden', !raised);
                dom.btnHand.classList.toggle('tb-btn--active', raised);
            } else {
                const h = document.getElementById(`hand-${peerId}`);
                if (h) h.classList.toggle('hidden', !raised);
            }
        },
        onEmojiReaction: showFloatingEmoji,
        onMediaStateChanged: (type, enabled, message) => {
            if (type === 'audio') {
                const icon = dom.btnMic.querySelector('i');
                icon.className = enabled ? 'fa-solid fa-microphone' : 'fa-solid fa-microphone-slash';
                dom.btnMic.classList.toggle('tb-btn--muted', !enabled);
                dom.localMutedIcon.classList.toggle('hidden', enabled);
            } else {
                const icon = dom.btnCam.querySelector('i');
                icon.className = enabled ? 'fa-solid fa-video' : 'fa-solid fa-video-slash';
                dom.btnCam.classList.toggle('tb-btn--muted', !enabled);
                dom.localAvatar.classList.toggle('hidden', enabled);
            }
            if (message) toast(message);
        },
        onRecordingStateChanged: (recording) => {
            dom.recordingIndicator.classList.toggle('hidden', !recording);
            if (dom.btnRecord) {
                const icon = dom.btnRecord.querySelector('i');
                if (recording) {
                    icon.className = 'fa-solid fa-stop';
                    dom.btnRecord.querySelector('span').textContent = 'Stop';
                    dom.btnRecord.classList.add('tb-btn--danger');
                } else {
                    icon.className = 'fa-solid fa-circle';
                    icon.style.fontSize = '0.625rem';
                    icon.style.color = '#ef4444';
                    dom.btnRecord.querySelector('span').textContent = 'Rec';
                    dom.btnRecord.classList.remove('tb-btn--danger');
                }
            }
            // Update command center record button too
            const cmdRec = $('#cmd-record');
            if (cmdRec) {
                cmdRec.innerHTML = recording
                    ? '<i class="fa-solid fa-stop text-red-500"></i> Stop Recording'
                    : '<i class="fa-solid fa-circle text-red-500" style="font-size:0.625rem;"></i> Start Recording';
                cmdRec.classList.toggle('cmd-btn--active', recording);
            }
        },
        onRecordingStopped: () => toast('Recording saved.', 'success'),

        onAnnotation: (data) => {
            drawStroke(dom.annotCanvas, data);
            if (isScreenFull) drawStroke(dom.fsAnnotCanvas, data);
        },
        onLaserPointer: (x, y, visible) => {
            [dom.laserDot, dom.fsLaserDot].forEach(dot => {
                if (!dot) return;
                dot.classList.toggle('hidden', !visible);
                if (visible) { dot.style.left = (x * 100) + '%'; dot.style.top = (y * 100) + '%'; }
            });
        },
        onPresentationMode: (active) => toast(active ? 'Presentation mode on' : 'Presentation mode off'),
        onAttentionCheck: (msg) => {
            dom.attentionText.textContent = msg;
            dom.attentionModal.classList.remove('hidden');
        },
        onRemoteControlRequest: (from) => {
            if (confirm(`${from.name} requests remote control. Allow?`)) app.respondRemoteControl(from.id, true);
            else app.respondRemoteControl(from.id, false);
        },
        onRemoteControlResponse: (approved) => toast(approved ? 'Remote control approved!' : 'Remote control denied.'),
        onRemoteControlStop: () => toast('Remote control ended.'),
        onFileShared: addFileChat,
        onNetworkQualityReport: (from, quality) => {
            if (quality === 'poor') toast(`${from.name} has poor network quality`, 'warning');
        },
        onAudioLevel: () => {},
    },
});

// ═══════════════════════════════════════════
// TOOLBAR EVENT WIRING
// ═══════════════════════════════════════════
dom.btnMic.addEventListener('click', () => app.toggleAudio());
dom.btnCam.addEventListener('click', () => app.toggleVideo());
dom.btnScreen.addEventListener('click', () => app.toggleScreenShare());
dom.btnHand.addEventListener('click', () => app.toggleRaiseHand());
dom.btnPip.addEventListener('click', () => app.togglePiP(dom.localVideo));

// Chat form
dom.chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const msg = dom.chatInput.value.trim();
    if (!msg) return;
    app.sendChat(msg);
    dom.chatInput.value = '';
});

dom.fileUpload?.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    addSystemChat(`Uploading ${file.name}...`);
    const result = await app.uploadChatFile(file);
    addSystemChat(result ? 'File uploaded!' : 'Upload failed.');
    e.target.value = '';
});

// Settings
dom.btnSettings.addEventListener('click', () => dom.settingsModal.classList.remove('hidden'));
dom.settingsClose.addEventListener('click', () => dom.settingsModal.classList.add('hidden'));
dom.settingsModal.addEventListener('click', (e) => { if (e.target === dom.settingsModal) dom.settingsModal.classList.add('hidden'); });

dom.selQuality?.addEventListener('change', (e) => { app.setQuality(e.target.value); toast(`Quality: ${e.target.selectedOptions[0].text}`); });

dom.togglePtt?.addEventListener('click', () => {
    const on = dom.togglePtt.dataset.on !== '1';
    setToggle(dom.togglePtt, on);
    app.enablePushToTalk(on);
    toast(on ? 'Push-to-talk enabled. Hold SPACE to talk.' : 'Push-to-talk disabled.');
});

dom.toggleNoise?.addEventListener('click', () => {
    const on = dom.toggleNoise.dataset.on !== '1';
    setToggle(dom.toggleNoise, on);
    const track = app.media.localStream?.getAudioTracks?.()?.[0];
    if (track) track.applyConstraints({ noiseSuppression: on }).catch(() => {});
    toast(`Noise suppression ${on ? 'on' : 'off'}`);
});

dom.toggleSounds?.addEventListener('click', () => {
    const on = dom.toggleSounds.dataset.on !== '1';
    setToggle(dom.toggleSounds, on);
    app.notifications?.toggleSound?.();
});

dom.toggleTheme?.addEventListener('click', () => {
    const on = dom.toggleTheme.dataset.on !== '1';
    setToggle(dom.toggleTheme, on);
    document.getElementById('app').classList.toggle('light-mode', !on);
});

// Attention
dom.attentionOk?.addEventListener('click', () => dom.attentionModal.classList.add('hidden'));

// End meeting & leave
dom.btnEnd?.addEventListener('click', () => { if (confirm('End meeting for everyone?')) app.endMeeting(); });

// ═══════════════════════════════════════════
// TEACHER COMMAND CENTER WIRING
// ═══════════════════════════════════════════
if (actor.role === 'teacher') {
    $('#cmd-mute-all')?.addEventListener('click', () => { app.muteAll(); toast('All participants muted', 'success'); });
    $('#cmd-unmute-all')?.addEventListener('click', () => {
        // Unmute all via signaling — send per each member
        app.members?.forEach((_, id) => { if (id !== actor.id) app.unmuteParticipant(id); });
        toast('All participants unmuted', 'success');
    });
    $('#cmd-cam-off-all')?.addEventListener('click', () => {
        app.members?.forEach((_, id) => { if (id !== actor.id) app.disableCamParticipant(id); });
        toast('All cameras turned off', 'success');
    });
    $('#cmd-attention')?.addEventListener('click', () => {
        app.sendAttentionCheck('Please confirm you are paying attention.');
        toast('Attention check sent', 'success');
    });

    // Lock room toggle
    $('#cmd-lock-room')?.addEventListener('click', function() {
        roomLocked = !roomLocked;
        this.classList.toggle('cmd-btn--active', roomLocked);
        this.innerHTML = roomLocked
            ? '<i class="fa-solid fa-lock-open text-emerald-400"></i> Unlock Room'
            : '<i class="fa-solid fa-lock text-sky-400"></i> Lock Room';
        toast(roomLocked ? 'Room locked — no new participants' : 'Room unlocked');
    });

    // Annotation mode
    $('#cmd-annotate')?.addEventListener('click', function() {
        annotationActive = !annotationActive;
        this.classList.toggle('cmd-btn--active', annotationActive);
        dom.annotCanvas.classList.toggle('annotation-layer--active', annotationActive);
        dom.annotToolbar.classList.toggle('hidden', !annotationActive);
        if (annotationActive) syncAnnotCanvasSize(dom.annotCanvas);
        toast(annotationActive ? 'Annotation mode ON' : 'Annotation mode OFF');
    });

    // Laser pointer
    $('#cmd-laser')?.addEventListener('click', function() {
        laserActive = !laserActive;
        this.classList.toggle('cmd-btn--active', laserActive);
        toast(laserActive ? 'Laser pointer ON' : 'Laser pointer OFF');
    });

    dom.screenTile.addEventListener('mousemove', (e) => {
        if (!laserActive) return;
        const rect = dom.screenTile.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;
        dom.laserDot.classList.remove('hidden');
        dom.laserDot.style.left = (x * 100) + '%';
        dom.laserDot.style.top = (y * 100) + '%';
        app.sendLaserPointer(x, y, true);
    });
    dom.screenTile.addEventListener('mouseleave', () => {
        if (!laserActive) return;
        dom.laserDot.classList.add('hidden');
        app.sendLaserPointer(0, 0, false);
    });

    // Whiteboard toggle
    $('#cmd-whiteboard')?.addEventListener('click', function() {
        isWhiteboardActive = !isWhiteboardActive;
        this.classList.toggle('cmd-btn--active', isWhiteboardActive);
        if (isWhiteboardActive) {
            // Show screen tile as whiteboard
            dom.screenTile.classList.remove('hidden');
            dom.screenLabel.textContent = 'Whiteboard';
            dom.annotCanvas.classList.add('annotation-layer--active');
            annotationActive = true;
            dom.annotToolbar.classList.remove('hidden');
            const ctx = dom.annotCanvas.getContext('2d');
            syncAnnotCanvasSize(dom.annotCanvas);
            if (ctx) { ctx.fillStyle = '#1e293b'; ctx.fillRect(0, 0, dom.annotCanvas.width, dom.annotCanvas.height); }
            updateGridLayout();
        } else {
            if (!screenShareSrc) {
                dom.screenTile.classList.add('hidden');
                updateGridLayout();
            }
            annotationActive = false;
            dom.annotCanvas.classList.remove('annotation-layer--active');
            dom.annotToolbar.classList.add('hidden');
        }
        toast(isWhiteboardActive ? 'Whiteboard active' : 'Whiteboard closed');
    });

    // Presentation mode
    $('#cmd-presentation')?.addEventListener('click', function() {
        const active = !app.presentationMode;
        app.setPresentationMode(active, 0);
        this.classList.toggle('cmd-btn--active', active);
    });

    // Recording (both toolbar + command center)
    const handleRecordToggle = () => {
        if (app.recording.isRecording) app.stopRecording(true);
        else app.startRecording('video');
    };
    dom.btnRecord?.addEventListener('click', handleRecordToggle);
    $('#cmd-record')?.addEventListener('click', handleRecordToggle);

    // Copy link
    $('#cmd-copy-link')?.addEventListener('click', () => {
        navigator.clipboard.writeText(meetingConfig.joinLink);
        toast('Invite link copied!', 'success');
    });

    // End meeting (command center)
    $('#cmd-end-meeting')?.addEventListener('click', () => {
        if (confirm('End meeting for everyone?')) app.endMeeting();
    });

    // Disable chat toggle
    $('#cmd-disable-chat')?.addEventListener('click', function() {
        chatDisabled = !chatDisabled;
        this.classList.toggle('cmd-btn--active', chatDisabled);
        this.innerHTML = chatDisabled
            ? '<i class="fa-solid fa-comment text-emerald-400"></i> Enable Student Chat'
            : '<i class="fa-solid fa-comment-slash text-slate-400"></i> Disable Student Chat';
        toast(chatDisabled ? 'Student chat disabled' : 'Student chat enabled');
    });

    // Disable student screen share
    $('#cmd-disable-screenshare')?.addEventListener('click', function() {
        studentScreenShareDisabled = !studentScreenShareDisabled;
        this.classList.toggle('cmd-btn--active', studentScreenShareDisabled);
        this.innerHTML = studentScreenShareDisabled
            ? '<i class="fa-solid fa-display text-emerald-400"></i> Enable Student Screen Share'
            : '<i class="fa-solid fa-display text-slate-400"></i> Disable Student Screen Share';
        toast(studentScreenShareDisabled ? 'Student screen sharing disabled' : 'Student screen sharing enabled');
    });

    // Raise hand required
    $('#cmd-raise-hand-only')?.addEventListener('click', function() {
        raiseHandRequired = !raiseHandRequired;
        this.classList.toggle('cmd-btn--active', raiseHandRequired);
        this.innerHTML = raiseHandRequired
            ? '<i class="fa-solid fa-hand text-emerald-400"></i> Free Speaking Allowed'
            : '<i class="fa-solid fa-hand text-amber-400"></i> Raise Hand Required to Speak';
        toast(raiseHandRequired ? 'Students must raise hand to speak' : 'Free speaking allowed');
    });
}

// ═══════════════════════════════════════════
// DEVICE SELECTORS
// ═══════════════════════════════════════════
async function populateDevices() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const fill = (sel, kind) => {
            if (!sel) return;
            sel.innerHTML = '';
            devices.filter(d => d.kind === kind).forEach((d, i) => {
                sel.add(new Option(d.label || `Device ${i + 1}`, d.deviceId));
            });
        };
        fill(dom.selAudioIn, 'audioinput');
        fill(dom.selVideoIn, 'videoinput');
        fill(dom.selAudioOut, 'audiooutput');
    } catch {}
}
populateDevices();

async function switchDevice(kind, deviceId) {
    if (!deviceId) return;
    const isAudio = kind === 'audioinput';
    const constraints = isAudio
        ? { audio: { deviceId: { exact: deviceId } }, video: false }
        : { video: { deviceId: { exact: deviceId } }, audio: false };
    try {
        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        const newTrack = isAudio ? stream.getAudioTracks()[0] : stream.getVideoTracks()[0];
        const local = app.media.localStream;
        if (!newTrack || !local) { stream.getTracks().forEach(t => t.stop()); return; }
        const oldTrack = isAudio ? local.getAudioTracks()[0] : local.getVideoTracks()[0];
        if (oldTrack) { local.removeTrack(oldTrack); oldTrack.stop(); }
        newTrack.enabled = isAudio ? app.media.isAudioEnabled : app.media.isVideoEnabled;
        local.addTrack(newTrack);
        app.peers.peers.forEach(({ pc }) => {
            const sender = pc.getSenders().find(s => s.track?.kind === newTrack.kind)
                || pc.getTransceivers().find(t => t.receiver?.track?.kind === newTrack.kind)?.sender;
            sender?.replaceTrack(newTrack).catch(() => {});
        });
        if (!isAudio) { dom.localVideo.srcObject = local; dom.localVideo.play().catch(() => {}); }
        stream.getTracks().forEach(t => { if (t !== newTrack) t.stop(); });
        toast(`${isAudio ? 'Microphone' : 'Camera'} switched`);
    } catch { toast(`Can't switch ${isAudio ? 'mic' : 'camera'}`, 'error'); }
}

dom.selAudioIn?.addEventListener('change', (e) => switchDevice('audioinput', e.target.value));
dom.selVideoIn?.addEventListener('change', (e) => switchDevice('videoinput', e.target.value));
dom.selAudioOut?.addEventListener('change', async (e) => {
    const id = e.target.value;
    document.querySelectorAll('video').forEach(v => { if (v.setSinkId) v.setSinkId(id).catch(() => {}); });
    toast('Speaker switched');
});

// ═══════════════════════════════════════════
// KEYBOARD SHORTCUTS
// ═══════════════════════════════════════════
// (ConferenceApp handles M, V, H, Ctrl+S, Space already)

// ═══════════════════════════════════════════
// NETWORK QUALITY REPORTING
// ═══════════════════════════════════════════
setInterval(() => app.sendNetworkQuality(), 30000);

// ═══════════════════════════════════════════
// CLEANUP
// ═══════════════════════════════════════════
window.addEventListener('beforeunload', () => app.destroy());

// ═══════════════════════════════════════════
// BOOT
// ═══════════════════════════════════════════
app.start();
</script>
</body>
</html>
