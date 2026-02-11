<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $conference->title }} | Live Room</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        :root {
            --brand-color: {{ $conference->branding_color ?? '#059669' }};
            --panel-border: rgba(71, 85, 105, 0.48);
            --spring-ease: cubic-bezier(0.22, 1, 0.36, 1);
        }

        html, body { height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background:
                radial-gradient(65rem 50rem at 12% -20%, rgba(16, 185, 129, 0.14), transparent 60%),
                radial-gradient(48rem 40rem at 100% 0%, rgba(59, 130, 246, 0.13), transparent 58%),
                #020617;
        }
        #conference-root { min-height: 100dvh; }
        .panel {
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.86));
            border: 1px solid var(--panel-border);
            box-shadow: 0 20px 45px rgba(2, 6, 23, 0.35), inset 0 1px 0 rgba(148, 163, 184, 0.07);
            backdrop-filter: blur(10px);
        }
        .physics-card {
            transform: translate3d(0, 0, 0);
            transition: transform 280ms var(--spring-ease), box-shadow 280ms var(--spring-ease);
            will-change: transform;
        }
        .physics-card:hover {
            box-shadow: 0 24px 55px rgba(2, 6, 23, 0.45), inset 0 1px 0 rgba(148, 163, 184, 0.11);
        }

        .meeting-grid { min-height: calc(100dvh - 9rem); }
        .meeting-sidebar { min-height: 0; }
        .header-shell { align-items: flex-start; }
        .header-actions { display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap; justify-content: flex-end; }

        /* Scrollbar */
        .scroll-thin::-webkit-scrollbar { width: 5px; }
        .scroll-thin::-webkit-scrollbar-thumb { background: #475569; border-radius: 999px; }
        .scroll-thin::-webkit-scrollbar-track { background: transparent; }

        /* Emoji float */
        @keyframes floatUp {
            0%   { opacity: 1; transform: translateY(0) scale(1); }
            80%  { opacity: 1; transform: translateY(-120px) scale(1.2); }
            100% { opacity: 0; transform: translateY(-160px) scale(0.8); }
        }
        .emoji-float { position: fixed; bottom: 100px; font-size: 2.5rem; animation: floatUp 2.2s ease-out forwards; pointer-events: none; z-index: 9999; }

        /* Hand pulse */
        @keyframes handPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.25); } }
        .hand-raised { animation: handPulse 1s infinite; }

        /* Screen share highlight */
        .screen-share-active { border-color: #6366f1 !important; box-shadow: 0 0 24px rgba(99,102,241,0.35); }

        /* Recording pulse */
        @keyframes recPulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        .rec-pulse { animation: recPulse 1.5s infinite; }

        /* Audio waveform bar */
        .audio-bar { transition: height 0.1s ease; }

        /* Laser pointer */
        .laser-dot { width: 12px; height: 12px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 12px #ef4444, 0 0 24px #ef4444; position: absolute; pointer-events: none; z-index: 100; transition: left 0.05s, top 0.05s; }

        /* Annotation canvas */
        .annotation-canvas { position: absolute; inset: 0; z-index: 50; cursor: crosshair; }

        /* Teacher Spotlight layout */
        .spotlight-layout { display: grid; grid-template-columns: 1fr 280px; gap: 12px; height: 100%; }
        .spotlight-main { min-height: 0; }
        .spotlight-sidebar-gallery { display: flex; flex-direction: column; gap: 8px; overflow-y: auto; }

        /* PiP hint */
        .pip-overlay { position: absolute; top: 8px; right: 8px; z-index: 20; }

        /* Toolbar responsive */
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-width: 2.35rem;
            min-height: 2.35rem;
            padding: 0.5rem 0.65rem;
            border-radius: 0.82rem;
            font-size: 0.67rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            line-height: 1;
            transition: transform 220ms var(--spring-ease), background-color 180ms ease, box-shadow 220ms ease;
            will-change: transform;
            white-space: nowrap;
        }
        .toolbar-btn i { width: 1rem; text-align: center; font-size: 0.9rem; }
        .toolbar-btn:hover { transform: translateY(-1px) scale(1.015); }
        .toolbar-btn:active { transform: translateY(0) scale(0.965); }
        .toolbar-btn:focus-visible { outline: 2px solid rgba(16, 185, 129, 0.7); outline-offset: 2px; }
        .toolbar-track {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
            min-width: 0;
        }

        /* Attention check modal */
        .attention-modal { position: fixed; inset: 0; z-index: 9998; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.7); }
        .attention-card { background: #1e293b; border: 2px solid #f59e0b; border-radius: 16px; padding: 32px; text-align: center; max-width: 400px; }

        /* Dark/Light mode */
        .light-mode { background: #f8fafc !important; color: #1e293b !important; }
        .light-mode .panel { background: #fff !important; border-color: #e2e8f0 !important; }
        .light-mode .chat-bubble-mine { background: #059669 !important; color: #fff !important; }
        .light-mode .chat-bubble-other { background: #f1f5f9 !important; color: #1e293b !important; }

        /* Video tile */
        .video-tile { position: relative; border-radius: 16px; overflow: hidden; min-height: 200px; background: #0f172a; }
        .video-tile video { position: absolute; inset: 0; width: 100%; height: 100%; object-cover; background: #000; }
        .video-tile .tile-label {
            position: absolute;
            bottom: 8px;
            left: 8px;
            max-width: calc(100% - 18px);
            padding: 4px 10px;
            border-radius: 8px;
            background: rgba(2, 6, 23, 0.72);
            backdrop-filter: blur(5px);
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .video-tile .tile-label > span:first-child {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .video-tile .tile-controls { position: absolute; top: 8px; right: 8px; display: flex; gap: 4px; opacity: 0; transition: opacity 0.2s; }
        .video-tile:hover .tile-controls { opacity: 1; }
        #video-grid { grid-auto-rows: minmax(190px, 1fr); }

        /* Notification bell badge */
        .notif-badge { position: absolute; top: -2px; right: -2px; width: 16px; height: 16px; border-radius: 50%; background: #ef4444; font-size: 9px; display: flex; align-items: center; justify-content: center; font-weight: 800; }

        @media (max-width: 1279px) {
            .meeting-grid { min-height: calc(100dvh - 9rem); height: auto; }
            .meeting-sidebar { max-height: 42dvh; }
            .header-shell { gap: 0.65rem; }
            .header-actions { justify-content: flex-start; width: 100%; }
        }
        @media (max-width: 1023px) {
            .toolbar-track {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 0.15rem;
                scrollbar-width: thin;
            }
            .toolbar-track .toolbar-btn,
            .toolbar-track > .relative {
                flex: 0 0 auto;
            }
        }
        @media (max-width: 767px) {
            .video-tile { min-height: 170px; }
            #video-grid { grid-auto-rows: minmax(170px, 1fr); }
        }
        @media (hover: none), (pointer: coarse) {
            .video-tile .tile-controls { opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            .physics-card,
            .toolbar-btn,
            .video-tile,
            .emoji-float,
            .hand-raised,
            .rec-pulse {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] } } }
        };
    </script>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <div id="conference-root" class="min-h-screen flex flex-col">
        {{-- ==================== HEADER ==================== --}}
        <header class="px-3 md:px-5 py-3 border-b border-slate-800 bg-slate-900/90 backdrop-blur-md sticky top-0 z-40">
            <div class="header-shell flex flex-col md:flex-row md:items-center justify-between gap-3">
                {{-- Left: Title & status --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($conference->branding_logo)
                            <img src="{{ $conference->branding_logo }}" alt="Logo" class="h-7 w-7 rounded-lg object-cover">
                        @endif
                        <h1 class="text-base md:text-xl font-black tracking-tight truncate">{{ $conference->title }}</h1>
                        @if($isMeetingActive)
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 uppercase tracking-widest font-black flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>LIVE
                            </span>
                        @else
                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 uppercase tracking-widest font-black">ENDED</span>
                        @endif
                        <span class="text-[9px] px-2 py-0.5 rounded-full bg-slate-700 text-slate-200 uppercase tracking-widest font-black">{{ strtoupper($actorRole) }}</span>
                        <span id="recording-badge" class="hidden text-[9px] px-2 py-0.5 rounded-full bg-red-600 text-white uppercase tracking-widest font-black rec-pulse">
                            <i class="fa-solid fa-circle text-[6px] mr-1"></i>REC
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                        {{ $actorName }} &bull; <span id="participants-count" class="font-bold text-emerald-300">1</span> participants
                        &bull; <span id="meeting-timer" class="font-mono text-slate-300">00:00:00</span>
                    </p>
                </div>

                {{-- Right: Actions --}}
                <div class="header-actions flex-shrink-0">
                    {{-- Notification bell --}}
                    <button id="notif-btn" type="button" class="relative toolbar-btn bg-slate-700 hover:bg-slate-600" title="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <span id="notif-badge" class="notif-badge hidden">0</span>
                    </button>

                    {{-- Dark/Light mode --}}
                    <button id="theme-toggle" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Toggle theme">
                        <i class="fa-solid fa-moon" id="theme-icon"></i>
                    </button>

                    {{-- Settings --}}
                    <button id="settings-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Settings">
                        <i class="fa-solid fa-gear"></i>
                    </button>

                    @if($actorRole === 'teacher')
                        <button id="copy-link-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600">
                            <i class="fa-solid fa-link mr-1"></i><span class="hidden sm:inline">Copy Link</span>
                        </button>
                        @if($isMeetingActive)
                            <button id="end-meeting-btn" type="button" class="toolbar-btn bg-rose-600 hover:bg-rose-700">
                                <i class="fa-solid fa-phone-slash mr-1"></i><span class="hidden sm:inline">End</span>
                            </button>
                        @endif
                    @endif

                    <a href="{{ $backUrl }}" id="leave-btn" class="toolbar-btn bg-slate-600 hover:bg-slate-500">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i><span class="hidden sm:inline">Leave</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- ==================== MAIN ==================== --}}
        <main class="flex-1 p-2 md:p-4 overflow-hidden">
            <div class="meeting-grid grid grid-cols-1 xl:grid-cols-[1fr_20rem] gap-3">

                {{-- ========== VIDEO STAGE ========== --}}
                <section id="stage-panel" class="panel physics-card rounded-2xl flex flex-col overflow-hidden">
                    {{-- Toolbar --}}
                    <div class="p-3 border-b border-slate-800 flex items-center justify-between flex-wrap gap-1.5">
                        <div class="flex items-center gap-2">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Stage</p>
                            {{-- Audio waveform indicator --}}
                            <div id="audio-waveform" class="flex items-end gap-px h-4">
                                <div class="audio-bar w-1 bg-emerald-400 rounded-full" style="height:20%"></div>
                                <div class="audio-bar w-1 bg-emerald-400 rounded-full" style="height:40%"></div>
                                <div class="audio-bar w-1 bg-emerald-400 rounded-full" style="height:60%"></div>
                                <div class="audio-bar w-1 bg-emerald-400 rounded-full" style="height:30%"></div>
                                <div class="audio-bar w-1 bg-emerald-400 rounded-full" style="height:50%"></div>
                            </div>
                        </div>
                        <div class="toolbar-track">
                            {{-- Mic --}}
                            <button id="toggle-audio-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Toggle Mic (M)">
                                <i class="fa-solid fa-microphone"></i><span class="hidden md:inline ml-1">Mic</span>
                            </button>
                            {{-- Cam --}}
                            <button id="toggle-video-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Toggle Camera (V)">
                                <i class="fa-solid fa-video"></i><span class="hidden md:inline ml-1">Cam</span>
                            </button>
                            {{-- Screen Share --}}
                            <button id="share-screen-btn" type="button" class="toolbar-btn bg-indigo-600 hover:bg-indigo-700" title="Share Screen (Ctrl+S)">
                                <i class="fa-solid fa-display"></i><span class="hidden md:inline ml-1">Screen</span>
                            </button>
                            {{-- Raise Hand --}}
                            <button id="raise-hand-btn" type="button" class="toolbar-btn bg-amber-600 hover:bg-amber-700" title="Raise Hand (H)">
                                <i class="fa-solid fa-hand"></i>
                            </button>
                            {{-- Emoji --}}
                            <div class="relative">
                                <button id="emoji-btn" type="button" class="toolbar-btn bg-pink-600 hover:bg-pink-700" title="React">
                                    <i class="fa-solid fa-face-smile"></i>
                                </button>
                                <div id="emoji-picker" class="hidden absolute top-full mt-2 right-0 bg-slate-800 border border-slate-700 rounded-xl p-2 shadow-2xl z-50 min-w-[200px]">
                                    <div class="grid grid-cols-5 gap-1" id="emoji-grid"></div>
                                </div>
                            </div>

                            @if($actorRole === 'teacher')
                                {{-- Recording --}}
                                <button id="record-btn" type="button" class="toolbar-btn bg-red-700 hover:bg-red-800" title="Record">
                                    <i class="fa-solid fa-circle text-[8px]"></i><span class="hidden md:inline ml-1">Rec</span>
                                </button>
                                {{-- Annotation --}}
                                <button id="annotate-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Annotate">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                {{-- Laser Pointer --}}
                                <button id="laser-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Laser Pointer">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </button>
                                {{-- Mute All --}}
                                <button id="mute-all-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Mute All">
                                    <i class="fa-solid fa-volume-xmark"></i>
                                </button>
                                {{-- Attention Check --}}
                                <button id="attention-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Attention Check">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            @endif

                            {{-- Quality --}}
                            <div class="relative">
                                <button id="quality-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Video Quality">
                                    <i class="fa-solid fa-sliders"></i>
                                </button>
                                <div id="quality-menu" class="hidden absolute top-full mt-2 right-0 bg-slate-800 border border-slate-700 rounded-xl p-2 shadow-2xl z-50 min-w-[140px]">
                                    <button data-quality="high" class="quality-opt w-full text-left px-3 py-1.5 rounded-lg hover:bg-slate-700 text-xs font-bold">1080p 60fps</button>
                                    <button data-quality="medium" class="quality-opt w-full text-left px-3 py-1.5 rounded-lg hover:bg-slate-700 text-xs font-bold">720p 30fps</button>
                                    <button data-quality="low" class="quality-opt w-full text-left px-3 py-1.5 rounded-lg hover:bg-slate-700 text-xs font-bold">480p 15fps</button>
                                    <button data-quality="minimal" class="quality-opt w-full text-left px-3 py-1.5 rounded-lg hover:bg-slate-700 text-xs font-bold">240p Low BW</button>
                                </div>
                            </div>

                            {{-- PTT toggle --}}
                            <button id="ptt-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Push-to-Talk">
                                <i class="fa-solid fa-walkie-talkie"></i>
                            </button>

                            {{-- PiP --}}
                            <button id="pip-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Picture-in-Picture">
                                <i class="fa-solid fa-clone"></i>
                            </button>

                            {{-- Reconnect --}}
                            <button id="reconnect-btn" type="button" class="toolbar-btn bg-slate-700 hover:bg-slate-600" title="Reconnect">
                                <i class="fa-solid fa-rotate"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Status banner --}}
                    <div id="status-banner" class="hidden mx-3 mt-3 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-200 text-xs px-4 py-2.5 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span id="status-banner-text"></span>
                        <button id="banner-dismiss" class="ml-auto text-amber-300 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    {{-- Video Grid --}}
                    <div id="video-stage" class="flex-1 p-2 sm:p-3 overflow-auto">
                        <div id="video-grid" class="grid grid-cols-1 sm:grid-cols-2 2xl:grid-cols-3 gap-3 min-h-full">
                            {{-- Local tile --}}
                            <div id="local-tile" class="video-tile physics-card border border-emerald-500/30">
                                <video id="local-video" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover bg-black"></video>
                                <div class="tile-label">
                                    <span>You ({{ ucfirst($actorRole) }})</span>
                                    <span id="local-hand-icon" class="hidden hand-raised text-amber-400">&#9995;</span>
                                </div>
                                <div class="tile-controls">
                                    <button id="local-pip-btn" class="px-1.5 py-0.5 rounded bg-black/60 hover:bg-black/80 text-[10px]" title="PiP">
                                        <i class="fa-solid fa-clone"></i>
                                    </button>
                                </div>
                            </div>
                            {{-- Screen share tile (hidden) --}}
                            <div id="screen-share-tile" class="hidden video-tile physics-card border-2 border-indigo-500/50 screen-share-active col-span-full relative">
                                <video id="screen-share-video" autoplay playsinline class="absolute inset-0 w-full h-full object-contain bg-black"></video>
                                <div class="tile-label bg-indigo-600/80">
                                    <i class="fa-solid fa-display mr-1"></i><span id="screen-share-label">Screen Share</span>
                                </div>
                                {{-- Annotation canvas overlay --}}
                                <canvas id="annotation-canvas" class="annotation-canvas hidden"></canvas>
                                {{-- Laser pointer dot --}}
                                <div id="laser-dot" class="laser-dot hidden"></div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ========== SIDEBAR ========== --}}
                <aside id="sidebar-panel" class="meeting-sidebar panel physics-card rounded-2xl flex flex-col overflow-hidden">
                    {{-- Tab switcher --}}
                    <div class="flex border-b border-slate-800">
                        <button class="sidebar-tab flex-1 py-2.5 text-[10px] font-black uppercase tracking-wider text-center transition-colors text-emerald-300 border-b-2 border-emerald-400" data-tab="chat">
                            <i class="fa-solid fa-comment mr-1"></i>Chat
                        </button>
                        <button class="sidebar-tab flex-1 py-2.5 text-[10px] font-black uppercase tracking-wider text-center transition-colors text-slate-400 border-b-2 border-transparent hover:text-slate-200" data-tab="participants">
                            <i class="fa-solid fa-users mr-1"></i>People
                        </button>
                        <button class="sidebar-tab flex-1 py-2.5 text-[10px] font-black uppercase tracking-wider text-center transition-colors text-slate-400 border-b-2 border-transparent hover:text-slate-200" data-tab="files">
                            <i class="fa-solid fa-paperclip mr-1"></i>Files
                        </button>
                    </div>

                    {{-- Chat Panel --}}
                    <div id="tab-chat" class="flex-1 flex flex-col min-h-0">
                        <div id="chat-log" class="flex-1 p-3 space-y-2 overflow-y-auto scroll-thin"></div>
                        <form id="chat-form" class="p-3 border-t border-slate-800 flex flex-wrap sm:flex-nowrap gap-2">
                            <label class="cursor-pointer px-2.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors">
                                <i class="fa-solid fa-paperclip"></i>
                                <input type="file" id="file-upload" class="hidden" accept="image/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip">
                            </label>
                            <input id="chat-input" type="text" maxlength="2000" required placeholder="Send a message..."
                                class="flex-1 min-w-0 px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-sm text-slate-100 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none placeholder-slate-500">
                            <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-black text-xs uppercase tracking-wide transition-colors">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                    {{-- Participants Panel --}}
                    <div id="tab-participants" class="hidden flex-1 p-3 overflow-y-auto scroll-thin">
                        <div id="participants-list" class="space-y-2"></div>
                    </div>

                    {{-- Files Panel --}}
                    <div id="tab-files" class="hidden flex-1 p-3 overflow-y-auto scroll-thin">
                        <div id="files-list" class="space-y-2">
                            <p class="no-files text-xs text-slate-500 text-center py-4">No files shared yet</p>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>

    {{-- ==================== ATTENTION CHECK MODAL ==================== --}}
    <div id="attention-modal" class="attention-modal hidden">
        <div class="attention-card">
            <div class="text-4xl mb-3">👀</div>
            <h2 class="text-xl font-black mb-2">Attention Check</h2>
            <p id="attention-message" class="text-slate-300 mb-4">Are you paying attention?</p>
            <button id="attention-confirm" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-black text-sm uppercase tracking-wide">I'm Here!</button>
        </div>
    </div>

    {{-- ==================== SETTINGS MODAL ==================== --}}
    <div id="settings-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-md mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-black">Settings</h2>
                <button id="close-settings" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-xs font-black uppercase text-slate-400 mb-1 block">Audio Input</label>
                    <select id="audio-input-select" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-sm"></select>
                </div>
                <div>
                    <label class="text-xs font-black uppercase text-slate-400 mb-1 block">Video Input</label>
                    <select id="video-input-select" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-sm"></select>
                </div>
                <div>
                    <label class="text-xs font-black uppercase text-slate-400 mb-1 block">Audio Output</label>
                    <select id="audio-output-select" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-sm"></select>
                </div>

                <hr class="border-slate-700">

                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold">Noise Suppression</span>
                    <button id="noise-toggle" class="w-10 h-5 rounded-full bg-emerald-500 relative transition-colors">
                        <span class="absolute top-0.5 left-5 w-4 h-4 rounded-full bg-white transition-transform"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold">Push-to-Talk</span>
                    <button id="ptt-settings-toggle" class="w-10 h-5 rounded-full bg-slate-600 relative transition-colors">
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-transform"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold">Notification Sounds</span>
                    <button id="sound-toggle" class="w-10 h-5 rounded-full bg-emerald-500 relative transition-colors">
                        <span class="absolute top-0.5 left-5 w-4 h-4 rounded-full bg-white transition-transform"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold">Silent Join Mode</span>
                    <button id="silent-join-toggle" class="w-10 h-5 rounded-full bg-slate-600 relative transition-colors">
                        <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-transform"></span>
                    </button>
                </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-700">
                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Keyboard Shortcuts</p>
                <div class="grid grid-cols-2 gap-1 mt-2 text-xs text-slate-400">
                    <span><kbd class="px-1.5 py-0.5 rounded bg-slate-700 text-slate-200 font-mono">M</kbd> Toggle Mic</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-slate-700 text-slate-200 font-mono">V</kbd> Toggle Camera</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-slate-700 text-slate-200 font-mono">H</kbd> Raise Hand</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-slate-700 text-slate-200 font-mono">Space</kbd> Push-to-Talk</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== SCRIPTS ==================== --}}
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

    {{-- Pass config data to window for the Vite-bundled module --}}
    <script>
        window.__CONFERENCE__ = @json($conferenceData);
        window.__ACTOR__ = @json($actorData);
        window.__SIGNALING__ = @json($signalingConfig);
        window.__MEETING__ = @json($meetingData);
    </script>

    @vite('resources/js/conference/index.js')

    <script type="module">
        const { ConferenceApp } = window;
        if (!ConferenceApp) {
            throw new Error('Conference bundle failed to load. Ensure Vite assets are built and deployed.');
        }

        // ========== CONFIG ==========
        const conference = window.__CONFERENCE__;
        const actor = window.__ACTOR__;
        const signalingConfig = window.__SIGNALING__;
        const meetingConfig = window.__MEETING__;

        // ========== DOM ==========
        const $ = (sel) => document.querySelector(sel);
        const $$ = (sel) => document.querySelectorAll(sel);

        const dom = {
            stagePanel: $('#stage-panel'),
            sidebarPanel: $('#sidebar-panel'),
            localVideo: $('#local-video'),
            videoGrid: $('#video-grid'),
            videoStage: $('#video-stage'),
            participantsList: $('#participants-list'),
            participantsCount: $('#participants-count'),
            chatLog: $('#chat-log'),
            chatForm: $('#chat-form'),
            chatInput: $('#chat-input'),
            fileUpload: $('#file-upload'),
            statusBanner: $('#status-banner'),
            statusBannerText: $('#status-banner-text'),
            bannerDismiss: $('#banner-dismiss'),
            toggleAudioBtn: $('#toggle-audio-btn'),
            toggleVideoBtn: $('#toggle-video-btn'),
            shareScreenBtn: $('#share-screen-btn'),
            raiseHandBtn: $('#raise-hand-btn'),
            emojiBtn: $('#emoji-btn'),
            emojiPicker: $('#emoji-picker'),
            emojiGrid: $('#emoji-grid'),
            reconnectBtn: $('#reconnect-btn'),
            copyLinkBtn: $('#copy-link-btn'),
            endMeetingBtn: $('#end-meeting-btn'),
            leaveBtn: $('#leave-btn'),
            localHandIcon: $('#local-hand-icon'),
            screenShareTile: $('#screen-share-tile'),
            screenShareVideo: $('#screen-share-video'),
            screenShareLabel: $('#screen-share-label'),
            meetingTimer: $('#meeting-timer'),
            recordingBadge: $('#recording-badge'),
            audioWaveform: $('#audio-waveform'),
            qualityBtn: $('#quality-btn'),
            qualityMenu: $('#quality-menu'),
            pttBtn: $('#ptt-btn'),
            pipBtn: $('#pip-btn'),
            themeToggle: $('#theme-toggle'),
            themeIcon: $('#theme-icon'),
            settingsBtn: $('#settings-btn'),
            settingsModal: $('#settings-modal'),
            closeSettings: $('#close-settings'),
            notifBtn: $('#notif-btn'),
            notifBadge: $('#notif-badge'),
            annotateBtn: $('#annotate-btn'),
            laserBtn: $('#laser-btn'),
            muteAllBtn: $('#mute-all-btn'),
            attentionBtn: $('#attention-btn'),
            attentionModal: $('#attention-modal'),
            attentionMessage: $('#attention-message'),
            attentionConfirm: $('#attention-confirm'),
            recordBtn: $('#record-btn'),
            annotationCanvas: $('#annotation-canvas'),
            laserDot: $('#laser-dot'),
            filesList: $('#files-list'),
            noiseToggle: $('#noise-toggle'),
            pttSettingsToggle: $('#ptt-settings-toggle'),
            soundToggle: $('#sound-toggle'),
            silentJoinToggle: $('#silent-join-toggle'),
            audioInputSelect: $('#audio-input-select'),
            videoInputSelect: $('#video-input-select'),
            audioOutputSelect: $('#audio-output-select'),
        };

        // ========== EMOJIS ==========
        const emojis = ['\u{1F44D}', '\u{1F44F}', '\u{2764}\u{FE0F}', '\u{1F602}', '\u{1F389}', '\u{1F525}', '\u{1F62E}', '\u{1F622}', '\u{1F4AF}', '\u{2705}', '\u{274C}', '\u{1F914}', '\u{1F44B}', '\u{2B50}', '\u{1F4AA}'];
        emojis.forEach(emoji => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'text-2xl hover:bg-slate-700 rounded-lg p-1 transition-colors cursor-pointer';
            btn.textContent = emoji;
            btn.onclick = () => { app.sendEmoji(emoji); dom.emojiPicker.classList.add('hidden'); };
            dom.emojiGrid.appendChild(btn);
        });

        // ========== UI HELPERS ==========
        const peerVideoStreams = new Map();

        function showBanner(msg) {
            dom.statusBannerText.textContent = msg;
            dom.statusBanner.classList.remove('hidden');
        }
        function hideBanner() { dom.statusBanner.classList.add('hidden'); }

        function addSystemMessage(text) {
            const row = document.createElement('div');
            row.className = 'text-center text-[10px] text-amber-400/80 font-mono my-0.5';
            row.textContent = `[SYS] ${text}`;
            dom.chatLog.appendChild(row);
            dom.chatLog.scrollTop = dom.chatLog.scrollHeight;
        }

        function addChatMessage(payload, mine = false) {
            const wrapper = document.createElement('div');
            wrapper.className = mine ? 'flex justify-end' : 'flex justify-start';
            const card = document.createElement('div');
            card.className = mine
                ? 'chat-bubble-mine max-w-[85%] bg-emerald-500 text-slate-900 rounded-2xl rounded-br-sm px-3 py-2'
                : 'chat-bubble-other max-w-[85%] bg-slate-800 text-slate-100 rounded-2xl rounded-bl-sm px-3 py-2';
            const meta = document.createElement('p');
            meta.className = mine ? 'text-[9px] font-bold text-slate-800/60 mb-0.5' : 'text-[9px] font-bold text-slate-400 mb-0.5';
            meta.textContent = `${payload.name} (${payload.role})`;
            const body = document.createElement('p');
            body.className = 'text-sm leading-snug';
            body.textContent = payload.message;
            card.appendChild(meta);
            card.appendChild(body);
            wrapper.appendChild(card);
            dom.chatLog.appendChild(wrapper);
            dom.chatLog.scrollTop = dom.chatLog.scrollHeight;
        }

        function addFileMessage(data) {
            const wrapper = document.createElement('div');
            wrapper.className = data.from?.id === actor.id ? 'flex justify-end' : 'flex justify-start';
            const card = document.createElement('div');
            card.className = 'max-w-[85%] bg-slate-700 text-slate-100 rounded-2xl px-3 py-2';
            const meta = document.createElement('p');
            meta.className = 'text-[9px] font-bold text-slate-400 mb-1';
            meta.textContent = `${data.from?.name || 'Unknown'} shared a file`;
            const link = document.createElement('a');
            link.href = data.fileUrl || '#';
            link.target = '_blank';
            link.className = 'flex items-center gap-2 text-sm text-emerald-300 hover:text-emerald-200';
            link.innerHTML = `<i class="fa-solid fa-file-arrow-down"></i> ${data.fileName || 'File'}`;
            const size = document.createElement('span');
            size.className = 'text-[10px] text-slate-500';
            size.textContent = formatFileSize(data.fileSize || 0);
            card.appendChild(meta);
            card.appendChild(link);
            card.appendChild(size);
            wrapper.appendChild(card);
            dom.chatLog.appendChild(wrapper);
            dom.chatLog.scrollTop = dom.chatLog.scrollHeight;

            // Also add to files list
            addToFilesList(data);
        }

        function addToFilesList(data) {
            if (dom.filesList.querySelector('.no-files')) dom.filesList.innerHTML = '';
            const item = document.createElement('div');
            item.className = 'flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-800 border border-slate-700';
            item.innerHTML = `
                <i class="fa-solid fa-file text-emerald-400"></i>
                <div class="flex-1 min-w-0">
                    <a href="${data.fileUrl || '#'}" target="_blank" class="text-xs font-bold text-slate-100 hover:text-emerald-300 truncate block">${data.fileName || 'File'}</a>
                    <p class="text-[10px] text-slate-500">${data.from?.name || ''} &bull; ${formatFileSize(data.fileSize || 0)}</p>
                </div>
            `;
            dom.filesList.appendChild(item);
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function showFloatingEmoji(emoji, name) {
            const el = document.createElement('div');
            el.className = 'emoji-float';
            el.textContent = emoji;
            el.style.left = (Math.random() * 60 + 20) + '%';
            document.body.appendChild(el);
            const row = document.createElement('div');
            row.className = 'text-center text-[10px] text-pink-400 font-mono my-0.5';
            row.textContent = `${name} reacted: ${emoji}`;
            dom.chatLog.appendChild(row);
            dom.chatLog.scrollTop = dom.chatLog.scrollHeight;
            setTimeout(() => el.remove(), 2300);
        }

        function createRemoteTile(peerId, label) {
            const tile = document.createElement('div');
            tile.id = `tile-${peerId}`;
            tile.className = 'video-tile physics-card border border-slate-700';
            const video = document.createElement('video');
            video.id = `video-${peerId}`;
            video.autoplay = true;
            video.playsInline = true;
            video.className = 'absolute inset-0 w-full h-full object-cover bg-black';
            const caption = document.createElement('div');
            caption.className = 'tile-label';
            caption.innerHTML = `<span>${label}</span><span id="hand-${peerId}" class="hidden hand-raised text-amber-400">&#9995;</span>`;
            const controls = document.createElement('div');
            controls.className = 'tile-controls';
            controls.innerHTML = `<button class="pip-tile-btn px-1.5 py-0.5 rounded bg-black/60 hover:bg-black/80 text-[10px]" data-peer="${peerId}" title="PiP"><i class="fa-solid fa-clone"></i></button>`;
            tile.appendChild(video);
            tile.appendChild(caption);
            tile.appendChild(controls);
            dom.videoGrid.appendChild(tile);
            registerPhysicsSurface(tile, 2.6);
            return video;
        }

        function renderParticipants(members, raisedHands) {
            dom.participantsList.innerHTML = '';
            members.forEach((info, id) => {
                const row = document.createElement('div');
                row.className = 'px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-between gap-2';

                const left = document.createElement('div');
                left.className = 'min-w-0 flex-1';
                const nameRow = document.createElement('div');
                nameRow.className = 'flex items-center gap-1';
                const name = document.createElement('p');
                name.className = 'text-xs font-semibold text-slate-100 truncate';
                name.textContent = info.name || id;
                nameRow.appendChild(name);
                if (raisedHands.has(id)) {
                    const hand = document.createElement('span');
                    hand.className = 'hand-raised text-amber-400 text-sm';
                    hand.textContent = '\u270B';
                    nameRow.appendChild(hand);
                }
                const role = document.createElement('p');
                role.className = 'text-[9px] uppercase tracking-wider text-slate-500';
                role.textContent = info.role || 'participant';
                left.appendChild(nameRow);
                left.appendChild(role);
                row.appendChild(left);

                const right = document.createElement('div');
                right.className = 'flex items-center gap-1 flex-shrink-0 flex-wrap justify-end';

                if (actor.role === 'teacher' && id !== actor.id) {
                    // Moderator controls
                    const controls = [
                        { icon: 'fa-microphone-slash', cls: 'bg-rose-600/60 hover:bg-rose-600', title: 'Mute', action: () => app.muteParticipant(id) },
                        { icon: 'fa-microphone', cls: 'bg-emerald-600/60 hover:bg-emerald-600', title: 'Unmute', action: () => app.unmuteParticipant(id) },
                        { icon: 'fa-video-slash', cls: 'bg-rose-600/60 hover:bg-rose-600', title: 'Cam Off', action: () => app.disableCamParticipant(id) },
                        { icon: 'fa-video', cls: 'bg-emerald-600/60 hover:bg-emerald-600', title: 'Cam On', action: () => app.enableCamParticipant(id) },
                        { icon: 'fa-user-slash', cls: 'bg-red-700/60 hover:bg-red-700', title: 'Kick', action: () => { if(confirm(`Kick ${info.name}?`)) app.kickParticipant(id); } },
                    ];
                    controls.forEach(c => {
                        const btn = document.createElement('button');
                        btn.className = `px-1.5 py-1 rounded-lg ${c.cls} text-[9px] font-bold text-white transition-colors`;
                        btn.innerHTML = `<i class="fa-solid ${c.icon}"></i>`;
                        btn.title = c.title;
                        btn.onclick = c.action;
                        right.appendChild(btn);
                    });
                }

                const badge = document.createElement('span');
                badge.className = `text-[9px] font-black px-2 py-0.5 rounded-full ${id === actor.id ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300'}`;
                badge.textContent = id === actor.id ? 'You' : 'Online';
                right.appendChild(badge);

                row.appendChild(right);
                dom.participantsList.appendChild(row);
            });
            dom.participantsCount.textContent = String(members.size || 1);
        }

        // ========== PHYSICS UI ==========
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const isCoarsePointer = window.matchMedia('(pointer: coarse)').matches;
        const physicsCleanups = [];

        function registerPhysicsSurface(el, strength = 4) {
            if (!el || prefersReducedMotion || isCoarsePointer || el.dataset.physicsBound === '1') return;
            el.dataset.physicsBound = '1';

            let targetX = 0;
            let targetY = 0;
            let x = 0;
            let y = 0;
            let vx = 0;
            let vy = 0;
            let raf = null;
            const stiffness = 0.13;
            const damping = 0.78;

            const animate = () => {
                const dx = targetX - x;
                const dy = targetY - y;
                vx = (vx + (dx * stiffness)) * damping;
                vy = (vy + (dy * stiffness)) * damping;
                x += vx;
                y += vy;
                el.style.transform = `translate3d(${x.toFixed(2)}px, ${y.toFixed(2)}px, 0)`;

                if (Math.abs(dx) < 0.06 && Math.abs(dy) < 0.06 && Math.abs(vx) < 0.06 && Math.abs(vy) < 0.06) {
                    raf = null;
                    if (targetX === 0 && targetY === 0) {
                        el.style.transform = 'translate3d(0, 0, 0)';
                    }
                    return;
                }
                raf = requestAnimationFrame(animate);
            };

            const wake = () => {
                if (!raf) raf = requestAnimationFrame(animate);
            };

            const onMove = (e) => {
                const rect = el.getBoundingClientRect();
                const px = (e.clientX - rect.left) / rect.width - 0.5;
                const py = (e.clientY - rect.top) / rect.height - 0.5;
                targetX = px * strength;
                targetY = py * strength;
                wake();
            };

            const onLeave = () => {
                targetX = 0;
                targetY = 0;
                wake();
            };

            el.addEventListener('mousemove', onMove);
            el.addEventListener('mouseleave', onLeave);

            physicsCleanups.push(() => {
                el.removeEventListener('mousemove', onMove);
                el.removeEventListener('mouseleave', onLeave);
                if (raf) cancelAnimationFrame(raf);
                el.style.transform = '';
            });
        }

        function initPhysicsUI() {
            registerPhysicsSurface(dom.stagePanel, 3.8);
            registerPhysicsSurface(dom.sidebarPanel, 2.9);
            registerPhysicsSurface(document.getElementById('local-tile'), 2.6);
            registerPhysicsSurface(dom.screenShareTile, 2.1);
        }

        // ========== MEETING TIMER ==========
        let timerInterval = null;
        let timerStart = Date.now();
        function startTimer() {
            timerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - timerStart) / 1000);
                const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
                const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
                const s = String(elapsed % 60).padStart(2, '0');
                dom.meetingTimer.textContent = `${h}:${m}:${s}`;
            }, 1000);
        }

        // ========== INIT APP ==========
        const app = new ConferenceApp({
            conference,
            actor,
            signalingConfig,
            meetingConfig,
            ui: {
                onLocalStream: (stream) => {
                    dom.localVideo.srcObject = stream;
                    startTimer();
                },
                onSystemMessage: addSystemMessage,
                onBanner: showBanner,
                onChatMessage: addChatMessage,
                onChatHistory: (msgs) => {
                    msgs.forEach(msg => {
                        if (msg.type === 'file') {
                            addFileMessage({ from: { name: msg.display_name, role: msg.role }, fileName: msg.file_name, fileUrl: msg.file_url, fileMime: msg.file_mime, fileSize: msg.file_size });
                        } else if (msg.type !== 'system') {
                            addChatMessage({ name: msg.display_name, role: msg.role, message: msg.content }, msg.actor_id === actor.id);
                        }
                    });
                },
                onParticipantsChanged: renderParticipants,
                onRemoteStream: (peerId, stream, name) => {
                    const videoEl = document.getElementById(`video-${peerId}`) || createRemoteTile(peerId, name);
                    videoEl.srcObject = stream;
                    videoEl.play().catch(() => {});
                    peerVideoStreams.set(peerId, stream);
                },
                getPeerStream: (peerId) => peerVideoStreams.get(peerId),
                onPeerRemoved: (peerId) => {
                    const tile = document.getElementById(`tile-${peerId}`);
                    if (tile) tile.remove();
                    peerVideoStreams.delete(peerId);
                },
                onRemoteScreenShare: (peerId, stream, name) => {
                    dom.screenShareVideo.srcObject = stream;
                    dom.screenShareLabel.textContent = `${name}'s Screen`;
                    dom.screenShareTile.classList.remove('hidden');
                    dom.screenShareVideo.play().catch(() => {});
                },
                onRemoteScreenShareStopped: (from) => {
                    if (dom.screenShareLabel.textContent.includes(from?.name)) {
                        dom.screenShareTile.classList.add('hidden');
                        dom.screenShareVideo.srcObject = null;
                    }
                },
                onLocalScreenShareStarted: (stream) => {
                    dom.screenShareVideo.srcObject = stream;
                    dom.screenShareLabel.textContent = 'Your Screen';
                    dom.screenShareTile.classList.remove('hidden');
                    dom.screenShareVideo.play().catch(() => {});
                    dom.shareScreenBtn.innerHTML = '<i class="fa-solid fa-display"></i><span class="hidden md:inline ml-1">Stop</span>';
                    dom.shareScreenBtn.classList.replace('bg-indigo-600', 'bg-rose-600');
                    dom.shareScreenBtn.classList.replace('hover:bg-indigo-700', 'hover:bg-rose-700');
                },
                onLocalScreenShareStopped: () => {
                    dom.screenShareTile.classList.add('hidden');
                    dom.screenShareVideo.srcObject = null;
                    dom.shareScreenBtn.innerHTML = '<i class="fa-solid fa-display"></i><span class="hidden md:inline ml-1">Screen</span>';
                    dom.shareScreenBtn.classList.replace('bg-rose-600', 'bg-indigo-600');
                    dom.shareScreenBtn.classList.replace('hover:bg-rose-700', 'hover:bg-indigo-700');
                },
                onTeacherSpotlight: (active, teacher) => {
                    if (active) {
                        dom.videoStage.classList.add('spotlight-layout');
                        dom.screenShareTile.classList.add('spotlight-main');
                    } else {
                        dom.videoStage.classList.remove('spotlight-layout');
                        dom.screenShareTile.classList.remove('spotlight-main');
                    }
                },
                onHandRaised: (peerId, raised) => {
                    if (peerId === actor.id) {
                        dom.localHandIcon.classList.toggle('hidden', !raised);
                        if (raised) {
                            dom.raiseHandBtn.classList.replace('bg-amber-600', 'bg-slate-600');
                            dom.raiseHandBtn.classList.replace('hover:bg-amber-700', 'hover:bg-slate-700');
                        } else {
                            dom.raiseHandBtn.classList.replace('bg-slate-600', 'bg-amber-600');
                            dom.raiseHandBtn.classList.replace('hover:bg-slate-700', 'hover:bg-amber-700');
                        }
                    } else {
                        const handEl = document.getElementById(`hand-${peerId}`);
                        if (handEl) handEl.classList.toggle('hidden', !raised);
                    }
                },
                onEmojiReaction: showFloatingEmoji,
                onMediaStateChanged: (type, enabled, message) => {
                    if (type === 'audio') {
                        dom.toggleAudioBtn.innerHTML = enabled
                            ? '<i class="fa-solid fa-microphone"></i><span class="hidden md:inline ml-1">Mic</span>'
                            : '<i class="fa-solid fa-microphone-slash"></i><span class="hidden md:inline ml-1">Muted</span>';
                        dom.toggleAudioBtn.classList.toggle('bg-rose-700', !enabled);
                        dom.toggleAudioBtn.classList.toggle('bg-slate-700', enabled);
                    } else {
                        dom.toggleVideoBtn.innerHTML = enabled
                            ? '<i class="fa-solid fa-video"></i><span class="hidden md:inline ml-1">Cam</span>'
                            : '<i class="fa-solid fa-video-slash"></i><span class="hidden md:inline ml-1">Off</span>';
                        dom.toggleVideoBtn.classList.toggle('bg-rose-700', !enabled);
                        dom.toggleVideoBtn.classList.toggle('bg-slate-700', enabled);
                    }
                    if (message) { showBanner(message); setTimeout(hideBanner, 4000); }
                },
                onRecordingStateChanged: (recording) => {
                    dom.recordingBadge.classList.toggle('hidden', !recording);
                    if (dom.recordBtn) {
                        dom.recordBtn.innerHTML = recording
                            ? '<i class="fa-solid fa-stop"></i><span class="hidden md:inline ml-1">Stop</span>'
                            : '<i class="fa-solid fa-circle text-[8px]"></i><span class="hidden md:inline ml-1">Rec</span>';
                    }
                },
                onRecordingStopped: (blob) => {
                    addSystemMessage('Recording saved.');
                },
                onAudioLevel: (level) => {
                    const bars = dom.audioWaveform.children;
                    for (let i = 0; i < bars.length; i++) {
                        const h = Math.max(15, Math.min(100, level * 100 * (0.5 + Math.random() * 0.5)));
                        bars[i].style.height = h + '%';
                        bars[i].style.background = level > 0.5 ? '#f59e0b' : '#34d399';
                    }
                },
                onAnnotation: (data, from) => {
                    // Draw annotation on canvas
                    drawAnnotation(data);
                },
                onLaserPointer: (x, y, visible, from) => {
                    dom.laserDot.classList.toggle('hidden', !visible);
                    if (visible) {
                        dom.laserDot.style.left = (x * 100) + '%';
                        dom.laserDot.style.top = (y * 100) + '%';
                    }
                },
                onPresentationMode: (active, slide) => {
                    addSystemMessage(active ? 'Presentation mode enabled.' : 'Presentation mode disabled.');
                },
                onAttentionCheck: (message) => {
                    dom.attentionMessage.textContent = message;
                    dom.attentionModal.classList.remove('hidden');
                },
                onRemoteControlRequest: (from) => {
                    if (confirm(`${from.name} requests remote control. Allow?`)) {
                        app.respondRemoteControl(from.id, true);
                    } else {
                        app.respondRemoteControl(from.id, false);
                    }
                },
                onRemoteControlResponse: (approved) => {
                    addSystemMessage(approved ? 'Remote control approved!' : 'Remote control denied.');
                },
                onRemoteControlStop: () => {
                    addSystemMessage('Remote control ended.');
                },
                onFileShared: addFileMessage,
                onNetworkQualityReport: (from, quality) => {
                    if (quality === 'poor') {
                        addSystemMessage(`Warning: ${from.name} has poor network quality`);
                    }
                },
            },
        });

        initPhysicsUI();

        function setToggleState(toggle, enabled) {
            if (!toggle) return;
            const knob = toggle.querySelector('span');
            toggle.dataset.enabled = enabled ? '1' : '0';
            toggle.classList.toggle('bg-emerald-500', enabled);
            toggle.classList.toggle('bg-slate-600', !enabled);
            if (knob) {
                knob.classList.toggle('left-5', enabled);
                knob.classList.toggle('left-0.5', !enabled);
            }
        }

        function setPttState(enabled, notify = false) {
            app.enablePushToTalk(enabled);
            dom.pttBtn.classList.toggle('bg-amber-600', enabled);
            dom.pttBtn.classList.toggle('bg-slate-700', !enabled);
            setToggleState(dom.pttSettingsToggle, enabled);
            if (notify) {
                addSystemMessage(enabled ? 'Push-to-talk enabled. Hold SPACE to talk.' : 'Push-to-talk disabled.');
            }
        }

        // ========== EVENT WIRING ==========
        dom.toggleAudioBtn.addEventListener('click', () => app.toggleAudio());
        dom.toggleVideoBtn.addEventListener('click', () => app.toggleVideo());
        dom.shareScreenBtn.addEventListener('click', () => app.toggleScreenShare());
        dom.raiseHandBtn.addEventListener('click', () => app.toggleRaiseHand());
        dom.reconnectBtn.addEventListener('click', () => app.forceReconnect());

        dom.emojiBtn.addEventListener('click', (e) => { e.stopPropagation(); dom.emojiPicker.classList.toggle('hidden'); });
        document.addEventListener('click', (e) => {
            if (!dom.emojiPicker.contains(e.target) && !e.target.closest('#emoji-btn')) dom.emojiPicker.classList.add('hidden');
            if (!dom.qualityMenu.contains(e.target) && !e.target.closest('#quality-btn')) dom.qualityMenu.classList.add('hidden');
        });

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
            addSystemMessage(`Uploading ${file.name}...`);
            const result = await app.uploadChatFile(file);
            if (result) addSystemMessage('File uploaded!');
            else addSystemMessage('File upload failed.');
            e.target.value = '';
        });

        if (dom.copyLinkBtn) {
            dom.copyLinkBtn.onclick = () => {
                navigator.clipboard.writeText(meetingConfig.joinLink);
                addSystemMessage('Join link copied!');
            };
        }

        if (dom.endMeetingBtn) {
            dom.endMeetingBtn.onclick = () => {
                if (confirm('End the meeting for everyone?')) app.endMeeting();
            };
        }

        dom.bannerDismiss?.addEventListener('click', hideBanner);

        // Quality menu
        dom.qualityBtn.addEventListener('click', (e) => { e.stopPropagation(); dom.qualityMenu.classList.toggle('hidden'); });
        document.querySelectorAll('.quality-opt').forEach(btn => {
            btn.addEventListener('click', () => {
                app.setQuality(btn.dataset.quality);
                dom.qualityMenu.classList.add('hidden');
                addSystemMessage(`Video quality: ${btn.textContent.trim()}`);
            });
        });

        // PTT
        dom.pttBtn.addEventListener('click', () => {
            setPttState(!app.media.isPushToTalk, true);
        });

        // PiP
        dom.pipBtn.addEventListener('click', () => app.togglePiP(dom.localVideo));

        // Theme toggle
        dom.themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            document.getElementById('conference-root').classList.toggle('light-mode');
            const isDark = document.documentElement.classList.contains('dark');
            dom.themeIcon.className = isDark ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
        });

        // Settings
        dom.settingsBtn.addEventListener('click', () => dom.settingsModal.classList.remove('hidden'));
        dom.closeSettings.addEventListener('click', () => dom.settingsModal.classList.add('hidden'));
        dom.settingsModal.addEventListener('click', (e) => { if (e.target === dom.settingsModal) dom.settingsModal.classList.add('hidden'); });
        setToggleState(dom.noiseToggle, true);
        setPttState(app.media.isPushToTalk, false);
        setToggleState(dom.soundToggle, app.notifications.soundEnabled);
        setToggleState(dom.silentJoinToggle, app.notifications.silentJoin);

        dom.noiseToggle?.addEventListener('click', async () => {
            const enabled = dom.noiseToggle.dataset.enabled !== '1';
            setToggleState(dom.noiseToggle, enabled);
            app.media.isNoiseSuppression = enabled;
            try {
                const track = app.media.localStream?.getAudioTracks?.()[0];
                if (track) {
                    await track.applyConstraints({ noiseSuppression: enabled });
                }
            } catch {}
            addSystemMessage(`Noise suppression ${enabled ? 'enabled' : 'disabled'}.`);
        });

        dom.pttSettingsToggle?.addEventListener('click', () => {
            setPttState(!app.media.isPushToTalk, true);
        });

        dom.soundToggle?.addEventListener('click', () => {
            const enabled = app.notifications.toggleSound();
            setToggleState(dom.soundToggle, enabled);
            addSystemMessage(`Notification sounds ${enabled ? 'enabled' : 'disabled'}.`);
        });

        dom.silentJoinToggle?.addEventListener('click', () => {
            const enabled = app.notifications.toggleSilentJoin();
            setToggleState(dom.silentJoinToggle, enabled);
            addSystemMessage(enabled ? 'Silent join mode enabled.' : 'Silent join mode disabled.');
        });

        // Sidebar tabs
        document.querySelectorAll('.sidebar-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.sidebar-tab').forEach(t => {
                    t.classList.remove('text-emerald-300', 'border-emerald-400');
                    t.classList.add('text-slate-400', 'border-transparent');
                });
                tab.classList.add('text-emerald-300', 'border-emerald-400');
                tab.classList.remove('text-slate-400', 'border-transparent');
                document.querySelectorAll('[id^="tab-"]').forEach(p => p.classList.add('hidden'));
                document.getElementById(`tab-${tab.dataset.tab}`).classList.remove('hidden');
                document.getElementById(`tab-${tab.dataset.tab}`).classList.add('flex', 'flex-col');
            });
        });

        // Attention check
        dom.attentionConfirm?.addEventListener('click', () => dom.attentionModal.classList.add('hidden'));

        // Teacher-only controls
        if (actor.role === 'teacher') {
            dom.recordBtn?.addEventListener('click', () => {
                if (app.recording.isRecording) {
                    app.stopRecording(true);
                } else {
                    app.startRecording('video');
                }
            });

            dom.muteAllBtn?.addEventListener('click', () => {
                if (confirm('Mute all participants?')) app.muteAll();
            });

            dom.attentionBtn?.addEventListener('click', () => {
                app.sendAttentionCheck('Please confirm you are paying attention.');
            });

            // Annotation mode
            let annotationActive = false;
            dom.annotateBtn?.addEventListener('click', () => {
                annotationActive = !annotationActive;
                dom.annotationCanvas?.classList.toggle('hidden', !annotationActive);
                dom.annotateBtn.classList.toggle('bg-amber-600', annotationActive);
                dom.annotateBtn.classList.toggle('bg-slate-700', !annotationActive);
            });

            // Laser pointer
            let laserActive = false;
            dom.laserBtn?.addEventListener('click', () => {
                laserActive = !laserActive;
                dom.laserBtn.classList.toggle('bg-red-600', laserActive);
                dom.laserBtn.classList.toggle('bg-slate-700', !laserActive);
            });

            dom.screenShareTile?.addEventListener('mousemove', (e) => {
                if (!laserActive) return;
                const rect = dom.screenShareTile.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width;
                const y = (e.clientY - rect.top) / rect.height;
                dom.laserDot.classList.remove('hidden');
                dom.laserDot.style.left = (x * 100) + '%';
                dom.laserDot.style.top = (y * 100) + '%';
                app.sendLaserPointer(x, y, true);
            });

            dom.screenShareTile?.addEventListener('mouseleave', () => {
                if (!laserActive) return;
                dom.laserDot.classList.add('hidden');
                app.sendLaserPointer(0, 0, false);
            });
        }

        // Annotation drawing
        function drawAnnotation(data) {
            const canvas = dom.annotationCanvas;
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;
            if (data.type === 'clear') {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                return;
            }
            if (data.type === 'line') {
                ctx.strokeStyle = data.color || '#ef4444';
                ctx.lineWidth = data.width || 3;
                ctx.lineCap = 'round';
                ctx.beginPath();
                ctx.moveTo(data.x1 * canvas.width, data.y1 * canvas.height);
                ctx.lineTo(data.x2 * canvas.width, data.y2 * canvas.height);
                ctx.stroke();
            }
        }

        // Populate device selects
        async function populateDevices() {
            if (!dom.audioInputSelect || !dom.videoInputSelect || !dom.audioOutputSelect) return;
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const audioInputs = devices.filter(d => d.kind === 'audioinput');
                const videoInputs = devices.filter(d => d.kind === 'videoinput');
                const audioOutputs = devices.filter(d => d.kind === 'audiooutput');

                dom.audioInputSelect.innerHTML = '';
                dom.videoInputSelect.innerHTML = '';
                dom.audioOutputSelect.innerHTML = '';

                audioInputs.forEach((d, i) => dom.audioInputSelect.add(new Option(d.label || `Mic ${i + 1}`, d.deviceId)));
                videoInputs.forEach((d, i) => dom.videoInputSelect.add(new Option(d.label || `Camera ${i + 1}`, d.deviceId)));
                audioOutputs.forEach((d, i) => dom.audioOutputSelect.add(new Option(d.label || `Speaker ${i + 1}`, d.deviceId)));
            } catch {}
        }

        async function switchInputDevice(kind, deviceId) {
            if (!deviceId) return;
            const constraints = kind === 'audioinput'
                ? { audio: { deviceId: { exact: deviceId } }, video: false }
                : { video: { deviceId: { exact: deviceId } }, audio: false };

            try {
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                const newTrack = kind === 'audioinput' ? stream.getAudioTracks()[0] : stream.getVideoTracks()[0];
                const localStream = app.media.localStream;
                if (!newTrack || !localStream) {
                    stream.getTracks().forEach(t => t.stop());
                    return;
                }

                const oldTrack = kind === 'audioinput'
                    ? localStream.getAudioTracks()[0]
                    : localStream.getVideoTracks()[0];

                if (oldTrack) {
                    localStream.removeTrack(oldTrack);
                    oldTrack.stop();
                }

                newTrack.enabled = kind === 'audioinput'
                    ? app.media.isAudioEnabled
                    : app.media.isVideoEnabled;
                localStream.addTrack(newTrack);

                app.peers.peers.forEach(({ pc }) => {
                    const sender = pc.getSenders().find(s => s.track && s.track.kind === newTrack.kind);
                    sender?.replaceTrack(newTrack).catch(() => {});
                });

                if (kind === 'videoinput') {
                    dom.localVideo.srcObject = localStream;
                    dom.localVideo.play().catch(() => {});
                }

                stream.getTracks().forEach(track => {
                    if (track !== newTrack) track.stop();
                });

                addSystemMessage(`${kind === 'audioinput' ? 'Microphone' : 'Camera'} switched.`);
            } catch (error) {
                addSystemMessage(`Unable to switch ${kind === 'audioinput' ? 'microphone' : 'camera'}.`);
            }
        }

        async function switchAudioOutput(deviceId) {
            if (!deviceId) return;
            const videos = [dom.localVideo, dom.screenShareVideo, ...document.querySelectorAll('#video-grid video')];
            let supported = false;

            for (const video of videos) {
                if (video && typeof video.setSinkId === 'function') {
                    supported = true;
                    try {
                        await video.setSinkId(deviceId);
                    } catch {}
                }
            }

            if (!supported) {
                addSystemMessage('Audio output switching is not supported by this browser.');
                return;
            }

            addSystemMessage('Audio output device switched.');
        }

        populateDevices();
        dom.audioInputSelect?.addEventListener('change', (e) => switchInputDevice('audioinput', e.target.value));
        dom.videoInputSelect?.addEventListener('change', (e) => switchInputDevice('videoinput', e.target.value));
        dom.audioOutputSelect?.addEventListener('change', (e) => switchAudioOutput(e.target.value));

        // PiP for remote tiles
        dom.videoGrid.addEventListener('click', (e) => {
            const btn = e.target.closest('.pip-tile-btn');
            if (!btn) return;
            const peerId = btn.dataset.peer;
            const video = document.getElementById(`video-${peerId}`);
            if (video) app.togglePiP(video);
        });

        // Cleanup
        window.addEventListener('beforeunload', () => {
            physicsCleanups.forEach(fn => fn());
            app.destroy();
        });

        // Network quality reporting (every 30s)
        setInterval(() => app.sendNetworkQuality(), 30000);

        // ========== BOOT ==========
        app.start();
    </script>
</body>
</html>
