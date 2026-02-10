<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $conference->title }} | Live Room</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #chat-log::-webkit-scrollbar, #participants-list::-webkit-scrollbar { width: 6px; }
        #chat-log::-webkit-scrollbar-thumb, #participants-list::-webkit-scrollbar-thumb { background: #475569; border-radius: 999px; }

        /* Emoji reaction float animation */
        @keyframes floatUp {
            0%   { opacity: 1; transform: translateY(0) scale(1); }
            80%  { opacity: 1; transform: translateY(-120px) scale(1.2); }
            100% { opacity: 0; transform: translateY(-160px) scale(0.8); }
        }
        .emoji-float {
            position: fixed;
            bottom: 100px;
            font-size: 2.5rem;
            animation: floatUp 2.2s ease-out forwards;
            pointer-events: none;
            z-index: 9999;
        }

        /* Raise hand pulse */
        @keyframes handPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.25); }
        }
        .hand-raised { animation: handPulse 1s infinite; }

        /* Screen share tile highlight */
        .screen-share-tile { border-color: #6366f1 !important; box-shadow: 0 0 20px rgba(99,102,241,0.3); }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-screen flex flex-col">
        {{-- ==================== HEADER ==================== --}}
        <header class="px-4 md:px-6 py-4 border-b border-slate-800 bg-slate-900/80 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-lg md:text-2xl font-black tracking-tight truncate">{{ $conference->title }}</h1>
                        <span class="text-[10px] px-2 py-1 rounded-full {{ $isMeetingActive ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }} uppercase tracking-widest font-black">
                            {{ $isMeetingActive ? 'Live' : 'Ended' }}
                        </span>
                        <span class="text-[10px] px-2 py-1 rounded-full bg-slate-700 text-slate-200 uppercase tracking-widest font-black">
                            {{ strtoupper($actorRole) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1 truncate">
                        Logged in as {{ $actorName }} | Participants:
                        <span id="participants-count" class="font-bold text-emerald-300">1</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($actorRole === 'teacher')
                        <button id="copy-link-btn" type="button" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors">
                            <i class="fa-solid fa-link mr-1"></i>Copy Join Link
                        </button>
                        @if($isMeetingActive)
                            <button id="end-meeting-btn" type="button" class="px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold uppercase tracking-wide transition-colors">
                                <i class="fa-solid fa-phone-slash mr-1"></i>End Meeting
                            </button>
                        @endif
                    @endif

                    <a href="{{ $backUrl }}" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold uppercase tracking-wide transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i>Leave Room
                    </a>
                </div>
            </div>
        </header>

        {{-- ==================== MAIN ==================== --}}
        <main class="flex-1 p-4 md:p-6">
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_22rem] gap-4 h-full">

                {{-- ========== VIDEO STAGE ========== --}}
                <section class="bg-slate-900 border border-slate-800 rounded-3xl flex flex-col min-h-[70vh]">
                    {{-- Toolbar --}}
                    <div class="p-4 border-b border-slate-800 flex items-center justify-between flex-wrap gap-2">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Video Stage</p>
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Mic --}}
                            <button id="toggle-audio-btn" type="button" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors" title="Toggle Microphone">
                                <i class="fa-solid fa-microphone mr-1"></i>Mic On
                            </button>
                            {{-- Cam --}}
                            <button id="toggle-video-btn" type="button" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors" title="Toggle Camera">
                                <i class="fa-solid fa-video mr-1"></i>Cam On
                            </button>
                            {{-- Screen Share --}}
                            <button id="share-screen-btn" type="button" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold uppercase tracking-wide transition-colors" title="Share Screen">
                                <i class="fa-solid fa-display mr-1"></i>Share Screen
                            </button>
                            {{-- Raise Hand --}}
                            <button id="raise-hand-btn" type="button" class="px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-xs font-bold uppercase tracking-wide transition-colors" title="Raise / Lower Hand">
                                <i class="fa-solid fa-hand mr-1"></i>Raise Hand
                            </button>
                            {{-- Emoji Picker --}}
                            <div class="relative">
                                <button id="emoji-btn" type="button" class="px-3 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-xs font-bold uppercase tracking-wide transition-colors" title="Send Emoji Reaction">
                                    <i class="fa-solid fa-face-smile mr-1"></i>React
                                </button>
                                <div id="emoji-picker" class="hidden absolute bottom-full mb-2 right-0 bg-slate-800 border border-slate-700 rounded-xl p-2 shadow-2xl z-30 min-w-[200px]">
                                    <div class="grid grid-cols-5 gap-1" id="emoji-grid"></div>
                                </div>
                            </div>
                            {{-- Reconnect --}}
                            <button id="reconnect-btn" type="button" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors" title="Reconnect Video">
                                <i class="fa-solid fa-sync mr-1"></i>Reconnect
                            </button>
                        </div>
                    </div>

                    <div id="status-banner" class="hidden mx-4 mt-4 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-200 text-sm px-4 py-3"></div>

                    {{-- Video Grid --}}
                    <div id="video-grid" class="p-4 grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4 flex-1 overflow-auto">
                        {{-- Local tile --}}
                        <div id="local-tile" class="relative rounded-2xl overflow-hidden border border-emerald-500/30 bg-slate-950 min-h-[220px]">
                            <video id="local-video" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover bg-black"></video>
                            <div class="absolute bottom-2 left-2 px-2 py-1 rounded-lg bg-black/60 text-xs font-bold flex items-center gap-1">
                                <span>You ({{ ucfirst($actorRole) }})</span>
                                <span id="local-hand-icon" class="hidden hand-raised text-amber-400 text-sm">✋</span>
                            </div>
                        </div>
                        {{-- Screen share tile (hidden by default) --}}
                        <div id="screen-share-tile" class="hidden relative rounded-2xl overflow-hidden border-2 border-indigo-500/50 bg-slate-950 min-h-[220px] col-span-full screen-share-tile">
                            <video id="screen-share-video" autoplay playsinline class="absolute inset-0 w-full h-full object-contain bg-black"></video>
                            <div class="absolute bottom-2 left-2 px-2 py-1 rounded-lg bg-indigo-600/80 text-xs font-bold">
                                <i class="fa-solid fa-display mr-1"></i><span id="screen-share-label">Screen Share</span>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ========== SIDEBAR ========== --}}
                <aside class="bg-slate-900 border border-slate-800 rounded-3xl flex flex-col min-h-[70vh]">
                    {{-- Participants --}}
                    <div class="p-4 border-b border-slate-800">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-300">Participants</h2>
                        <div id="participants-list" class="mt-3 space-y-2 max-h-52 overflow-auto"></div>
                    </div>

                    {{-- Chat --}}
                    <div class="p-4 border-b border-slate-800">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-300">Realtime Chat</h2>
                    </div>

                    <div id="chat-log" class="flex-1 p-4 space-y-3 overflow-auto"></div>

                    <form id="chat-form" class="p-4 border-t border-slate-800 flex gap-2">
                        <input id="chat-input" type="text" maxlength="300" required placeholder="Send a message..." class="flex-1 px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-sm text-slate-100 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none">
                        <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-black text-xs uppercase tracking-wide transition-colors">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </aside>
            </div>
        </main>
    </div>

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
        const conference = @json($conferenceData);
        const actor = @json($actorData);
        const signaling = @json($signalingConfig);
        const meetingConfig = @json($meetingData);

        // --- DOM Elements ---
        const localVideo = document.getElementById('local-video');
        const videoGrid = document.getElementById('video-grid');
        const participantsList = document.getElementById('participants-list');
        const participantsCount = document.getElementById('participants-count');
        const chatLog = document.getElementById('chat-log');
        const chatForm = document.getElementById('chat-form');
        const chatInput = document.getElementById('chat-input');
        const statusBanner = document.getElementById('status-banner');
        const toggleAudioBtn = document.getElementById('toggle-audio-btn');
        const toggleVideoBtn = document.getElementById('toggle-video-btn');
        const shareScreenBtn = document.getElementById('share-screen-btn');
        const raiseHandBtn = document.getElementById('raise-hand-btn');
        const emojiBtn = document.getElementById('emoji-btn');
        const emojiPicker = document.getElementById('emoji-picker');
        const emojiGrid = document.getElementById('emoji-grid');
        const reconnectBtn = document.getElementById('reconnect-btn');
        const copyLinkBtn = document.getElementById('copy-link-btn');
        const endMeetingBtn = document.getElementById('end-meeting-btn');
        const localHandIcon = document.getElementById('local-hand-icon');
        const screenShareTile = document.getElementById('screen-share-tile');
        const screenShareVideo = document.getElementById('screen-share-video');
        const screenShareLabel = document.getElementById('screen-share-label');

        // --- Available Emojis ---
        const emojis = ['👍', '👏', '❤️', '😂', '🎉', '🔥', '😮', '😢', '💯', '✅', '❌', '🤔', '👋', '⭐', '💪'];

        // Build emoji picker grid
        emojis.forEach(emoji => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'text-2xl hover:bg-slate-700 rounded-lg p-1 transition-colors cursor-pointer';
            btn.textContent = emoji;
            btn.onclick = () => {
                sendEmojiReaction(emoji);
                emojiPicker.classList.add('hidden');
            };
            emojiGrid.appendChild(btn);
        });

        // --- State ---
        const peers = new Map();
        const members = new Map();
        const raisedHands = new Set();
        let localStream = null;
        let screenStream = null;
        let screenSender = null;
        let isScreenSharing = false;
        let isHandRaised = false;
        let ws = null;
        let reconnectTimer = null;
        let reconnectAttempts = 0;
        let hasJoinedRoom = false;
        let isShuttingDown = false;

        // --- UI Helpers ---
        function showBanner(message) {
            statusBanner.textContent = message;
            statusBanner.classList.remove('hidden');
        }

        function hideBanner() {
            statusBanner.classList.add('hidden');
        }

        function addSystemMessage(text) {
            const row = document.createElement('div');
            row.className = 'text-center text-xs text-amber-400 font-mono my-1';
            row.textContent = `[SYS] ${text}`;
            chatLog.appendChild(row);
            chatLog.scrollTop = chatLog.scrollHeight;
        }

        function addChatMessage(payload, mine = false) {
            const wrapper = document.createElement('div');
            wrapper.className = mine ? 'flex justify-end' : 'flex justify-start';
            const card = document.createElement('div');
            card.className = mine
                ? 'max-w-[85%] bg-emerald-500 text-slate-900 rounded-2xl px-3 py-2'
                : 'max-w-[85%] bg-slate-800 text-slate-100 rounded-2xl px-3 py-2';
            const meta = document.createElement('p');
            meta.className = mine ? 'text-[10px] font-bold text-slate-800/70 mb-1' : 'text-[10px] font-bold text-slate-400 mb-1';
            meta.textContent = `${payload.name} (${payload.role})`;
            const body = document.createElement('p');
            body.className = 'text-sm';
            body.textContent = payload.message;
            card.appendChild(meta);
            card.appendChild(body);
            wrapper.appendChild(card);
            chatLog.appendChild(wrapper);
            chatLog.scrollTop = chatLog.scrollHeight;
        }

        function showFloatingEmoji(emoji, senderName) {
            const el = document.createElement('div');
            el.className = 'emoji-float';
            el.textContent = emoji;
            el.style.left = (Math.random() * 60 + 20) + '%';
            document.body.appendChild(el);

            // Also show in chat
            const row = document.createElement('div');
            row.className = 'text-center text-xs text-pink-400 font-mono my-1';
            row.textContent = `${senderName} reacted: ${emoji}`;
            chatLog.appendChild(row);
            chatLog.scrollTop = chatLog.scrollHeight;

            setTimeout(() => el.remove(), 2300);
        }

        function renderParticipants() {
            participantsList.innerHTML = '';
            members.forEach((info, id) => {
                const row = document.createElement('div');
                row.className = 'px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-between gap-2';

                // Left side: name + role + hand icon
                const left = document.createElement('div');
                left.className = 'min-w-0 flex-1';
                const nameRow = document.createElement('div');
                nameRow.className = 'flex items-center gap-1';
                const name = document.createElement('p');
                name.className = 'text-sm font-semibold text-slate-100 truncate';
                name.textContent = info.name || id;
                nameRow.appendChild(name);

                if (raisedHands.has(id)) {
                    const handBadge = document.createElement('span');
                    handBadge.className = 'hand-raised text-amber-400 text-sm';
                    handBadge.textContent = '✋';
                    handBadge.title = 'Hand Raised';
                    nameRow.appendChild(handBadge);
                }

                const role = document.createElement('p');
                role.className = 'text-[10px] uppercase tracking-wider text-slate-400';
                role.textContent = info.role || 'participant';
                left.appendChild(nameRow);
                left.appendChild(role);

                row.appendChild(left);

                // Right side: controls for teacher, badge for self
                const right = document.createElement('div');
                right.className = 'flex items-center gap-1 flex-shrink-0';

                // Teacher moderator controls (only for non-self participants)
                if (actor.role === 'teacher' && id !== actor.id) {
                    // Mute button
                    const muteBtn = document.createElement('button');
                    muteBtn.className = 'px-2 py-1 rounded-lg bg-rose-600/80 hover:bg-rose-600 text-[10px] font-bold text-white transition-colors';
                    muteBtn.innerHTML = '<i class="fa-solid fa-microphone-slash"></i>';
                    muteBtn.title = 'Mute this participant';
                    muteBtn.onclick = () => sendWsMessage({ type: 'mute-participant', targetId: id });
                    right.appendChild(muteBtn);

                    // Unmute button
                    const unmuteBtn = document.createElement('button');
                    unmuteBtn.className = 'px-2 py-1 rounded-lg bg-emerald-600/80 hover:bg-emerald-600 text-[10px] font-bold text-white transition-colors';
                    unmuteBtn.innerHTML = '<i class="fa-solid fa-microphone"></i>';
                    unmuteBtn.title = 'Request unmute';
                    unmuteBtn.onclick = () => sendWsMessage({ type: 'unmute-participant', targetId: id });
                    right.appendChild(unmuteBtn);

                    // Cam off button
                    const camOffBtn = document.createElement('button');
                    camOffBtn.className = 'px-2 py-1 rounded-lg bg-rose-600/80 hover:bg-rose-600 text-[10px] font-bold text-white transition-colors';
                    camOffBtn.innerHTML = '<i class="fa-solid fa-video-slash"></i>';
                    camOffBtn.title = 'Turn off camera';
                    camOffBtn.onclick = () => sendWsMessage({ type: 'disable-cam-participant', targetId: id });
                    right.appendChild(camOffBtn);

                    // Cam on button
                    const camOnBtn = document.createElement('button');
                    camOnBtn.className = 'px-2 py-1 rounded-lg bg-emerald-600/80 hover:bg-emerald-600 text-[10px] font-bold text-white transition-colors';
                    camOnBtn.innerHTML = '<i class="fa-solid fa-video"></i>';
                    camOnBtn.title = 'Request camera on';
                    camOnBtn.onclick = () => sendWsMessage({ type: 'enable-cam-participant', targetId: id });
                    right.appendChild(camOnBtn);
                }

                // Badge
                const badge = document.createElement('span');
                badge.className = `text-[10px] font-black px-2 py-1 rounded-full ${id === actor.id ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300'}`;
                badge.textContent = id === actor.id ? 'You' : 'Online';
                right.appendChild(badge);

                row.appendChild(right);
                participantsList.appendChild(row);
            });
            participantsCount.textContent = String(members.size || 1);
        }

        function createRemoteTile(peerId, label) {
            const tile = document.createElement('div');
            tile.id = `tile-${peerId}`;
            tile.className = 'relative rounded-2xl overflow-hidden border border-slate-700 bg-slate-950 min-h-[220px]';
            const video = document.createElement('video');
            video.id = `video-${peerId}`;
            video.autoplay = true;
            video.playsInline = true;
            video.className = 'absolute inset-0 w-full h-full object-cover bg-black';
            const caption = document.createElement('div');
            caption.className = 'absolute bottom-2 left-2 px-2 py-1 rounded-lg bg-black/60 text-xs font-bold flex items-center gap-1';
            const nameSpan = document.createElement('span');
            nameSpan.textContent = label;
            caption.appendChild(nameSpan);
            const handIcon = document.createElement('span');
            handIcon.id = `hand-${peerId}`;
            handIcon.className = 'hidden hand-raised text-amber-400 text-sm';
            handIcon.textContent = '✋';
            caption.appendChild(handIcon);
            tile.appendChild(video);
            tile.appendChild(caption);
            videoGrid.appendChild(tile);
            return video;
        }

        function removePeer(peerId) {
            const state = peers.get(peerId);
            if (state) {
                state.pc.close();
                peers.delete(peerId);
            }
            const tile = document.getElementById(`tile-${peerId}`);
            if (tile) tile.remove();
        }

        function sendWsMessage(payload) {
            if (!ws || ws.readyState !== WebSocket.OPEN) return false;
            ws.send(JSON.stringify(payload));
            return true;
        }

        function queueReconnect() {
            if (isShuttingDown || reconnectTimer) return;
            const delay = Math.min(6000, 1000 * (2 ** reconnectAttempts));
            reconnectAttempts += 1;
            reconnectTimer = setTimeout(() => {
                reconnectTimer = null;
                connectWebSocket();
            }, delay);
        }

        function serializeDescription(description) {
            if (!description) return null;
            if (typeof description.toJSON === 'function') return description.toJSON();
            return { type: description.type, sdp: description.sdp };
        }

        // --- WebRTC Core ---

        function ensurePeer(peerId) {
            if (peers.has(peerId)) return peers.get(peerId);

            addSystemMessage(`Setting up video with ${members.get(peerId)?.name || peerId}...`);

            const pc = new RTCPeerConnection({
                iceServers: (signaling.iceServers && signaling.iceServers.length)
                    ? signaling.iceServers
                    : [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' }
                    ]
            });

            if (localStream) {
                localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
            }

            // If we are already sharing screen, add the screen track too
            if (screenStream) {
                screenStream.getTracks().forEach(track => pc.addTrack(track, screenStream));
            }

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    sendWsMessage({
                        type: 'ice-candidate',
                        to: peerId,
                        payload: event.candidate.toJSON ? event.candidate.toJSON() : event.candidate,
                    });
                }
            };

            pc.ontrack = (event) => {
                const track = event.track;
                const stream = event.streams[0];

                // Check if this is a screen share track (typically the stream has a 'screen' label or is a second video stream)
                if (track.kind === 'video') {
                    // Get existing video element for this peer
                    const existingVideo = document.getElementById(`video-${peerId}`);
                    const hasExistingStream = existingVideo && existingVideo.srcObject;

                    // If peer already has a camera stream and this is a new video stream, treat as screen share
                    if (hasExistingStream && existingVideo.srcObject.id !== stream.id) {
                        addSystemMessage(`${members.get(peerId)?.name || peerId} is sharing their screen`);
                        screenShareVideo.srcObject = stream;
                        screenShareLabel.textContent = `${members.get(peerId)?.name || peerId}'s Screen`;
                        screenShareTile.classList.remove('hidden');
                        screenShareVideo.play().catch(e => console.error('Screen share play error:', e));
                        return;
                    }
                }

                addSystemMessage(`Received video stream from ${members.get(peerId)?.name}`);
                const label = members.get(peerId)?.name || peerId;
                const videoEl = document.getElementById(`video-${peerId}`) || createRemoteTile(peerId, label);
                if (stream) {
                    videoEl.srcObject = stream;
                    videoEl.play().catch(e => console.error('Autoplay error:', e));
                }
            };

            pc.onconnectionstatechange = () => {
                if (['failed', 'closed'].includes(pc.connectionState)) {
                    removePeer(peerId);
                }
            };

            const state = { pc, candidateBuffer: [], remoteDescriptionSet: false };
            peers.set(peerId, state);
            return state;
        }

        // --- Connection Initiation Logic ---
        function shouldInitiateWith(peerId) {
            if (actor.role === 'teacher') return true;
            const peerRole = members.get(peerId)?.role;
            if (peerRole === 'teacher') return false;
            return actor.id.localeCompare(peerId) < 0;
        }

        async function initiateConnection(peerId) {
            const { pc } = ensurePeer(peerId);
            if (pc.signalingState !== 'stable') return;

            addSystemMessage(`Calling ${members.get(peerId)?.name}...`);
            try {
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                sendWsMessage({
                    type: 'offer',
                    to: peerId,
                    payload: serializeDescription(pc.localDescription),
                });
            } catch (err) {
                console.error('Offer Error:', err);
            }
        }

        function forceReconnect() {
            members.forEach((info, id) => {
                if (id !== actor.id) {
                    addSystemMessage(`Forcing connection to ${info.name}...`);
                    if (peers.has(id)) removePeer(id);
                    initiateConnection(id);
                }
            });
        }

        async function handleSignal(type, message) {
            if (!message || !message.from || message.from.id === actor.id) return;

            const peerId = message.from.id;
            members.set(peerId, message.from);
            const peerState = ensurePeer(peerId);
            const { pc } = peerState;

            try {
                if (type === 'offer') {
                    const description = message.payload || null;
                    if (!description) return;

                    addSystemMessage(`Incoming call from ${members.get(peerId)?.name || peerId}`);
                    await pc.setRemoteDescription(new RTCSessionDescription(description));
                    peerState.remoteDescriptionSet = true;

                    for (const buffered of peerState.candidateBuffer) {
                        await pc.addIceCandidate(buffered);
                    }
                    peerState.candidateBuffer = [];

                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);

                    sendWsMessage({
                        type: 'answer',
                        to: peerId,
                        payload: serializeDescription(pc.localDescription),
                    });
                } else if (type === 'answer') {
                    const description = message.payload || null;
                    if (!description) return;

                    addSystemMessage(`Call accepted by ${members.get(peerId)?.name || peerId}`);
                    await pc.setRemoteDescription(new RTCSessionDescription(description));
                    peerState.remoteDescriptionSet = true;

                    for (const buffered of peerState.candidateBuffer) {
                        await pc.addIceCandidate(buffered);
                    }
                    peerState.candidateBuffer = [];
                } else if (type === 'ice-candidate') {
                    if (!message.payload) return;
                    const candidate = new RTCIceCandidate(message.payload);
                    if (peerState.remoteDescriptionSet || pc.remoteDescription) {
                        await pc.addIceCandidate(candidate);
                    } else {
                        peerState.candidateBuffer.push(candidate);
                    }
                }
            } catch (error) {
                console.error('Signal Error:', error);
            }
        }

        // =============================================
        //  SCREEN SHARING
        // =============================================

        async function startScreenShare() {
            try {
                screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true, audio: false });
                const screenTrack = screenStream.getVideoTracks()[0];

                // Show locally
                screenShareVideo.srcObject = screenStream;
                screenShareLabel.textContent = 'Your Screen';
                screenShareTile.classList.remove('hidden');
                screenShareVideo.play().catch(() => {});

                // Add track to all peers
                peers.forEach((peerState, peerId) => {
                    const sender = peerState.pc.addTrack(screenTrack, screenStream);
                    peerState.screenSender = sender;
                });

                // Renegotiate with all peers
                for (const [peerId, peerState] of peers) {
                    if (shouldInitiateWith(peerId) || actor.role === 'teacher') {
                        try {
                            const offer = await peerState.pc.createOffer();
                            await peerState.pc.setLocalDescription(offer);
                            sendWsMessage({
                                type: 'offer',
                                to: peerId,
                                payload: serializeDescription(peerState.pc.localDescription),
                            });
                        } catch (e) {
                            console.error('Screen share renegotiation error:', e);
                        }
                    }
                }

                isScreenSharing = true;
                shareScreenBtn.innerHTML = '<i class="fa-solid fa-display mr-1"></i>Stop Share';
                shareScreenBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                shareScreenBtn.classList.add('bg-rose-600', 'hover:bg-rose-700');

                sendWsMessage({ type: 'screen-share-started' });
                addSystemMessage('You started screen sharing.');

                // Handle when user stops sharing via browser UI
                screenTrack.onended = () => stopScreenShare();
            } catch (err) {
                if (err.name !== 'NotAllowedError') {
                    console.error('Screen share error:', err);
                    addSystemMessage('Failed to start screen sharing.');
                }
            }
        }

        function stopScreenShare() {
            if (!screenStream) return;

            screenStream.getTracks().forEach(t => t.stop());

            // Remove screen track from all peers
            peers.forEach((peerState) => {
                if (peerState.screenSender) {
                    try {
                        peerState.pc.removeTrack(peerState.screenSender);
                    } catch (e) { /* ignore */ }
                    peerState.screenSender = null;
                }
            });

            screenStream = null;
            isScreenSharing = false;
            screenShareTile.classList.add('hidden');
            screenShareVideo.srcObject = null;

            shareScreenBtn.innerHTML = '<i class="fa-solid fa-display mr-1"></i>Share Screen';
            shareScreenBtn.classList.remove('bg-rose-600', 'hover:bg-rose-700');
            shareScreenBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');

            sendWsMessage({ type: 'screen-share-stopped' });
            addSystemMessage('You stopped screen sharing.');
        }

        // =============================================
        //  RAISE HAND
        // =============================================

        function toggleRaiseHand() {
            isHandRaised = !isHandRaised;

            if (isHandRaised) {
                raisedHands.add(actor.id);
                raiseHandBtn.innerHTML = '<i class="fa-solid fa-hand mr-1"></i>Lower Hand';
                raiseHandBtn.classList.remove('bg-amber-600', 'hover:bg-amber-700');
                raiseHandBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                localHandIcon.classList.remove('hidden');
            } else {
                raisedHands.delete(actor.id);
                raiseHandBtn.innerHTML = '<i class="fa-solid fa-hand mr-1"></i>Raise Hand';
                raiseHandBtn.classList.remove('bg-slate-600', 'hover:bg-slate-700');
                raiseHandBtn.classList.add('bg-amber-600', 'hover:bg-amber-700');
                localHandIcon.classList.add('hidden');
            }

            sendWsMessage({ type: 'raise-hand', raised: isHandRaised });
            renderParticipants();
        }

        // =============================================
        //  EMOJI REACTIONS
        // =============================================

        function sendEmojiReaction(emoji) {
            sendWsMessage({ type: 'emoji-reaction', emoji });
            showFloatingEmoji(emoji, 'You');
        }

        // =============================================
        //  FORCE MUTE / CAM (received from teacher)
        // =============================================

        function handleForceMute() {
            if (localStream) {
                const track = localStream.getAudioTracks()[0];
                if (track) {
                    track.enabled = false;
                    toggleAudioBtn.innerHTML = '<i class="fa-solid fa-microphone-slash mr-1"></i>Mic Off';
                    toggleAudioBtn.classList.add('bg-rose-700');
                    addSystemMessage('The teacher muted your microphone.');
                    showBanner('Your microphone was muted by the teacher.');
                    setTimeout(hideBanner, 4000);
                }
            }
        }

        function handleForceUnmute() {
            if (localStream) {
                const track = localStream.getAudioTracks()[0];
                if (track) {
                    track.enabled = true;
                    toggleAudioBtn.innerHTML = '<i class="fa-solid fa-microphone mr-1"></i>Mic On';
                    toggleAudioBtn.classList.remove('bg-rose-700');
                    addSystemMessage('The teacher unmuted your microphone.');
                    showBanner('Your microphone was unmuted by the teacher.');
                    setTimeout(hideBanner, 4000);
                }
            }
        }

        function handleForceCamOff() {
            if (localStream) {
                const track = localStream.getVideoTracks()[0];
                if (track) {
                    track.enabled = false;
                    toggleVideoBtn.innerHTML = '<i class="fa-solid fa-video-slash mr-1"></i>Cam Off';
                    toggleVideoBtn.classList.add('bg-rose-700');
                    addSystemMessage('The teacher turned off your camera.');
                    showBanner('Your camera was turned off by the teacher.');
                    setTimeout(hideBanner, 4000);
                }
            }
        }

        function handleForceCamOn() {
            if (localStream) {
                const track = localStream.getVideoTracks()[0];
                if (track) {
                    track.enabled = true;
                    toggleVideoBtn.innerHTML = '<i class="fa-solid fa-video mr-1"></i>Cam On';
                    toggleVideoBtn.classList.remove('bg-rose-700');
                    addSystemMessage('The teacher turned on your camera.');
                    showBanner('Your camera was turned on by the teacher.');
                    setTimeout(hideBanner, 4000);
                }
            }
        }

        // --- Initialization ---

        async function setupLocalMedia() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                localVideo.srcObject = localStream;
                addSystemMessage('Camera & Microphone Ready.');
                return true;
            } catch (error) {
                showBanner('Camera access denied. Please allow permissions.');
                return false;
            }
        }

        function connectWebSocket() {
            if (!signaling.url) {
                showBanner('Missing signaling server URL.');
                return;
            }

            if (ws && (ws.readyState === WebSocket.OPEN || ws.readyState === WebSocket.CONNECTING)) {
                return;
            }

            ws = new WebSocket(signaling.url);

            ws.onopen = () => {
                reconnectAttempts = 0;
                hasJoinedRoom = false;
                addSystemMessage('Signaling connected.');

                sendWsMessage({
                    type: 'join',
                    roomId: signaling.roomId,
                    token: signaling.token,
                });
            };

            ws.onclose = () => {
                if (isShuttingDown) return;
                hasJoinedRoom = false;
                peers.forEach((peerState, peerId) => removePeer(peerId));
                members.clear();
                members.set(actor.id, { id: actor.id, name: actor.name, role: actor.role });
                renderParticipants();
                addSystemMessage('Signaling disconnected. Reconnecting...');
                queueReconnect();
            };

            ws.onerror = (error) => {
                console.error('WebSocket Error:', error);
            };

            ws.onmessage = async (event) => {
                let message = null;
                try {
                    message = JSON.parse(event.data);
                } catch {
                    return;
                }

                if (!message || !message.type) return;

                // --- Joined Room ---
                if (message.type === 'joined') {
                    hasJoinedRoom = true;
                    members.clear();

                    const participants = Array.isArray(message.participants) ? message.participants : [];
                    participants.forEach((participant) => {
                        if (!participant || !participant.id) return;
                        members.set(participant.id, participant);
                    });

                    if (!members.has(actor.id)) {
                        members.set(actor.id, { id: actor.id, name: actor.name, role: actor.role });
                    }

                    renderParticipants();
                    addSystemMessage('Room connected. Waiting for peers...');

                    setTimeout(() => {
                        members.forEach((_info, peerId) => {
                            if (peerId !== actor.id && shouldInitiateWith(peerId)) {
                                initiateConnection(peerId);
                            }
                        });
                    }, 500);

                    return;
                }

                // --- Peer Joined ---
                if (message.type === 'peer-joined') {
                    if (!message.participant?.id) return;

                    const participant = message.participant;
                    members.set(participant.id, participant);
                    renderParticipants();
                    addSystemMessage(`${participant.name || participant.id} joined.`);

                    if (participant.id !== actor.id && shouldInitiateWith(participant.id)) {
                        initiateConnection(participant.id);
                    }

                    return;
                }

                // --- Peer Left ---
                if (message.type === 'peer-left') {
                    if (!message.participant?.id) return;

                    const participant = message.participant;
                    members.delete(participant.id);
                    raisedHands.delete(participant.id);
                    removePeer(participant.id);
                    renderParticipants();
                    addSystemMessage(`${participant.name || participant.id} left.`);

                    return;
                }

                // --- WebRTC Signaling ---
                if (message.type === 'offer' || message.type === 'answer' || message.type === 'ice-candidate') {
                    await handleSignal(message.type, message);
                    return;
                }

                // --- Chat ---
                if (message.type === 'chat') {
                    if (message.from?.id !== actor.id) {
                        addChatMessage({
                            name: message.from?.name || 'Unknown',
                            role: message.from?.role || 'participant',
                            message: message.message || '',
                        }, false);
                    }
                    return;
                }

                // --- Meeting Ended ---
                if (message.type === 'meeting-ended') {
                    if (actor.role === 'student') {
                        window.location.href = meetingConfig.backUrl;
                    }
                    return;
                }

                // --- Raise Hand ---
                if (message.type === 'raise-hand') {
                    const fromId = message.from?.id;
                    const fromName = message.from?.name || fromId;
                    if (!fromId || fromId === actor.id) return;

                    if (message.raised) {
                        raisedHands.add(fromId);
                        addSystemMessage(`${fromName} raised their hand ✋`);
                    } else {
                        raisedHands.delete(fromId);
                        addSystemMessage(`${fromName} lowered their hand`);
                    }

                    // Update hand icon on video tile
                    const handEl = document.getElementById(`hand-${fromId}`);
                    if (handEl) {
                        handEl.classList.toggle('hidden', !message.raised);
                    }

                    renderParticipants();
                    return;
                }

                // --- Emoji Reaction ---
                if (message.type === 'emoji-reaction') {
                    const fromName = message.from?.name || 'Someone';
                    if (message.from?.id !== actor.id) {
                        showFloatingEmoji(message.emoji, fromName);
                    }
                    return;
                }

                // --- Force Mute/Unmute/Cam (from teacher) ---
                if (message.type === 'force-mute') {
                    handleForceMute();
                    return;
                }

                if (message.type === 'force-unmute') {
                    handleForceUnmute();
                    return;
                }

                if (message.type === 'force-cam-off') {
                    handleForceCamOff();
                    return;
                }

                if (message.type === 'force-cam-on') {
                    handleForceCamOn();
                    return;
                }

                // --- Participant Control (broadcasted to room for UI updates) ---
                if (message.type === 'participant-control') {
                    const targetName = members.get(message.targetId)?.name || message.targetId;
                    const actionLabels = {
                        'force-mute': 'muted',
                        'force-unmute': 'unmuted',
                        'force-cam-off': 'turned off the camera of',
                        'force-cam-on': 'turned on the camera of',
                    };
                    const label = actionLabels[message.action] || message.action;
                    if (message.from?.id !== actor.id && message.targetId !== actor.id) {
                        addSystemMessage(`Teacher ${label} ${targetName}`);
                    }
                    return;
                }

                // --- Screen Share Events ---
                if (message.type === 'screen-share-started') {
                    if (message.from?.id !== actor.id) {
                        addSystemMessage(`${message.from?.name || 'Someone'} started screen sharing`);
                    }
                    return;
                }

                if (message.type === 'screen-share-stopped') {
                    if (message.from?.id !== actor.id) {
                        addSystemMessage(`${message.from?.name || 'Someone'} stopped screen sharing`);
                        // Hide screen share tile if it was showing that person's screen
                        if (screenShareLabel.textContent.includes(message.from?.name)) {
                            screenShareTile.classList.add('hidden');
                            screenShareVideo.srcObject = null;
                        }
                    }
                    return;
                }

                // --- Error ---
                if (message.type === 'error') {
                    showBanner(message.message || 'Realtime signaling error.');
                    addSystemMessage(`Signaling error: ${message.message || message.code || 'unknown'}`);
                    return;
                }

                // --- Connection Replaced ---
                if (message.type === 'system' && message.event === 'connection-replaced') {
                    addSystemMessage('Another session connected with the same account.');
                }
            };
        }

        // --- Event Listeners ---

        // Chat
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;
            if (!hasJoinedRoom) return;

            sendWsMessage({
                type: 'chat',
                roomId: signaling.roomId,
                message,
            });

            addChatMessage({ name: actor.name, role: actor.role, message }, true);
            chatInput.value = '';
        });

        // Mic toggle
        toggleAudioBtn.addEventListener('click', () => {
            if (localStream) {
                const track = localStream.getAudioTracks()[0];
                if (track) {
                    track.enabled = !track.enabled;
                    toggleAudioBtn.innerHTML = track.enabled ? '<i class="fa-solid fa-microphone mr-1"></i>Mic On' : '<i class="fa-solid fa-microphone-slash mr-1"></i>Mic Off';
                    toggleAudioBtn.classList.toggle('bg-rose-700', !track.enabled);
                }
            }
        });

        // Cam toggle
        toggleVideoBtn.addEventListener('click', () => {
            if (localStream) {
                const track = localStream.getVideoTracks()[0];
                if (track) {
                    track.enabled = !track.enabled;
                    toggleVideoBtn.innerHTML = track.enabled ? '<i class="fa-solid fa-video mr-1"></i>Cam On' : '<i class="fa-solid fa-video-slash mr-1"></i>Cam Off';
                    toggleVideoBtn.classList.toggle('bg-rose-700', !track.enabled);
                }
            }
        });

        // Screen Share
        shareScreenBtn.addEventListener('click', () => {
            if (isScreenSharing) {
                stopScreenShare();
            } else {
                startScreenShare();
            }
        });

        // Raise Hand
        raiseHandBtn.addEventListener('click', () => toggleRaiseHand());

        // Emoji Picker toggle
        emojiBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            emojiPicker.classList.toggle('hidden');
        });

        // Close emoji picker on outside click
        document.addEventListener('click', (e) => {
            if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
                emojiPicker.classList.add('hidden');
            }
        });

        // Reconnect
        reconnectBtn.addEventListener('click', () => forceReconnect());

        // Copy Link
        if (copyLinkBtn) {
            copyLinkBtn.onclick = () => {
                navigator.clipboard.writeText(meetingConfig.joinLink);
                addSystemMessage('Link Copied!');
            };
        }

        // End Meeting
        if (endMeetingBtn) {
            endMeetingBtn.onclick = async () => {
                if (confirm('End Meeting?')) {
                    sendWsMessage({ type: 'meeting-ended' });
                    await fetch(meetingConfig.endMeetingUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': meetingConfig.csrf }
                    });
                    window.location.href = meetingConfig.backUrl;
                }
            };
        }

        // Cleanup on leave
        window.addEventListener('beforeunload', () => {
            isShuttingDown = true;

            if (reconnectTimer) {
                clearTimeout(reconnectTimer);
                reconnectTimer = null;
            }

            if (screenStream) {
                screenStream.getTracks().forEach(t => t.stop());
            }

            peers.forEach((state) => state.pc.close());
            peers.clear();

            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.close();
            }
        });

        // --- Boot ---
        (async () => {
            if (!meetingConfig.isActive) return showBanner('Meeting Ended');
            const hasMedia = await setupLocalMedia();
            if (!hasMedia) return;
            connectWebSocket();
        })();
    </script>
</body>
</html>
