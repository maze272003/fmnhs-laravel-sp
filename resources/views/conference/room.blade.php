<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $conference->title }} | Live Room</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        #chat-log::-webkit-scrollbar, #participants-list::-webkit-scrollbar { width: 6px; }
        #chat-log::-webkit-scrollbar-thumb, #participants-list::-webkit-scrollbar-thumb { background: #475569; border-radius: 999px; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-screen flex flex-col">
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
                        <button
                            id="copy-link-btn"
                            type="button"
                            class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors"
                        >
                            <i class="fa-solid fa-link mr-1"></i>Copy Join Link
                        </button>
                        @if($isMeetingActive)
                            <button
                                id="end-meeting-btn"
                                type="button"
                                class="px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-xs font-bold uppercase tracking-wide transition-colors"
                            >
                                <i class="fa-solid fa-phone-slash mr-1"></i>End Meeting
                            </button>
                        @endif
                    @endif

                    <a
                        href="{{ $backUrl }}"
                        class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-xs font-bold uppercase tracking-wide transition-colors"
                    >
                        <i class="fa-solid fa-arrow-left mr-1"></i>Leave Room
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-6">
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_22rem] gap-4 h-full">
                <section class="bg-slate-900 border border-slate-800 rounded-3xl flex flex-col min-h-[70vh]">
                    <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Video Stage</p>
                        <div class="flex items-center gap-2">
                            <button id="toggle-audio-btn" type="button" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors">
                                <i class="fa-solid fa-microphone mr-1"></i>Mic On
                            </button>
                            <button id="toggle-video-btn" type="button" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors">
                                <i class="fa-solid fa-video mr-1"></i>Cam On
                            </button>
                        </div>
                    </div>

                    <div id="status-banner" class="hidden mx-4 mt-4 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-200 text-sm px-4 py-3"></div>

                    <div id="video-grid" class="p-4 grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4 flex-1 overflow-auto">
                        <div id="local-tile" class="relative rounded-2xl overflow-hidden border border-emerald-500/30 bg-slate-950 min-h-[220px]">
                            <video id="local-video" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover bg-black"></video>
                            <div class="absolute bottom-2 left-2 px-2 py-1 rounded-lg bg-black/60 text-xs font-bold">
                                You ({{ ucfirst($actorRole) }})
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="bg-slate-900 border border-slate-800 rounded-3xl flex flex-col min-h-[70vh]">
                    <div class="p-4 border-b border-slate-800">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-300">Participants</h2>
                        <div id="participants-list" class="mt-3 space-y-2 max-h-40 overflow-auto"></div>
                    </div>

                    <div class="p-4 border-b border-slate-800">
                        <h2 class="text-sm font-black uppercase tracking-wider text-slate-300">Realtime Chat</h2>
                    </div>

                    <div id="chat-log" class="flex-1 p-4 space-y-3 overflow-auto"></div>

                    <form id="chat-form" class="p-4 border-t border-slate-800 flex gap-2">
                        <input
                            id="chat-input"
                            type="text"
                            maxlength="300"
                            required
                            placeholder="Send a message..."
                            class="flex-1 px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-sm text-slate-100 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none"
                        >
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
        const reverb = @json($reverbConfig);
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
        const copyLinkBtn = document.getElementById('copy-link-btn');
        const endMeetingBtn = document.getElementById('end-meeting-btn');

        // --- Manual Trigger Button ---
        const headerActions = document.querySelector('header .flex.flex-wrap');
        const reconnectBtn = document.createElement('button');
        reconnectBtn.className = 'px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-xs font-bold uppercase tracking-wide transition-colors ml-2';
        reconnectBtn.innerHTML = '<i class="fa-solid fa-sync mr-1"></i>Connect Video';
        reconnectBtn.onclick = () => forceReconnect();
        headerActions.appendChild(reconnectBtn);

        // --- State ---
        const peers = new Map();
        const members = new Map();
        let localStream = null;
        let pusher = null;
        let channel = null;
        let isSubscribed = false;

        // --- UI Helpers ---
        function showBanner(message) {
            statusBanner.textContent = message;
            statusBanner.classList.remove('hidden');
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

        function renderParticipants() {
            participantsList.innerHTML = '';
            members.forEach((info, id) => {
                const row = document.createElement('div');
                row.className = 'px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-between';
                const left = document.createElement('div');
                left.className = 'min-w-0';
                const name = document.createElement('p');
                name.className = 'text-sm font-semibold text-slate-100 truncate';
                name.textContent = info.name || id;
                const role = document.createElement('p');
                role.className = 'text-[10px] uppercase tracking-wider text-slate-400';
                role.textContent = info.role || 'participant';
                left.appendChild(name);
                left.appendChild(role);
                const badge = document.createElement('span');
                badge.className = `text-[10px] font-black px-2 py-1 rounded-full ${id === actor.id ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300'}`;
                badge.textContent = id === actor.id ? 'You' : 'Online';
                row.appendChild(left);
                row.appendChild(badge);
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
            caption.className = 'absolute bottom-2 left-2 px-2 py-1 rounded-lg bg-black/60 text-xs font-bold';
            caption.textContent = label;
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

        function triggerChannelEvent(eventName, payload) {
            if (!channel || !isSubscribed) return;
            try {
                channel.trigger(eventName, payload);
            } catch (error) {
                console.error(`Failed to trigger ${eventName}:`, error);
            }
        }

        // --- WebRTC Core ---

        function ensurePeer(peerId) {
            if (peers.has(peerId)) return peers.get(peerId);

            addSystemMessage(`Setting up video with ${members.get(peerId)?.name || peerId}...`);

            const pc = new RTCPeerConnection({
                iceServers: (reverb.iceServers && reverb.iceServers.length)
                    ? reverb.iceServers
                    : [
                        { urls: 'stun:stun.l.google.com:19302' },
                        { urls: 'stun:stun1.l.google.com:19302' }
                    ]
            });

            if (localStream) {
                localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
            }

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    triggerChannelEvent('client-signal', {
                        from: actor.id,
                        to: peerId,
                        candidate: event.candidate,
                    });
                }
            };

            pc.ontrack = (event) => {
                addSystemMessage(`Received video stream from ${members.get(peerId)?.name}`);
                const label = members.get(peerId)?.name || peerId;
                const videoEl = document.getElementById(`video-${peerId}`) || createRemoteTile(peerId, label);
                if (event.streams[0]) {
                    videoEl.srcObject = event.streams[0];
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

        // --- UPDATED LOGIC: Teacher Always Calls ---
        function shouldInitiateWith(peerId) {
            // 1. If I am the Teacher, I call EVERYONE.
            if (actor.role === 'teacher') return true;

            // 2. If I am a Student...
            const peerRole = members.get(peerId)?.role;
            
            // ... I NEVER call the Teacher (I wait for them to call me)
            if (peerRole === 'teacher') return false;

            // ... I only call other Students if my ID is "lower" (to prevent double-dialing)
            return actor.id.localeCompare(peerId) < 0;
        }

        async function initiateConnection(peerId) {
            const { pc } = ensurePeer(peerId);
            addSystemMessage(`Calling ${members.get(peerId)?.name}...`);
            try {
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                triggerChannelEvent('client-signal', {
                    from: actor.id,
                    to: peerId,
                    description: pc.localDescription,
                });
            } catch (err) {
                console.error('Offer Error:', err);
            }
        }

        function forceReconnect() {
             members.forEach((info, id) => {
                 if (id !== actor.id) {
                     addSystemMessage(`Forcing connection to ${info.name}...`);
                     if(peers.has(id)) removePeer(id);
                     initiateConnection(id);
                 }
             });
        }

        async function handleSignal(payload) {
            if (!payload || payload.from === actor.id) return;
            if (payload.to && payload.to !== actor.id) return;

            const peerId = payload.from;
            const peerState = ensurePeer(peerId);
            const { pc } = peerState;

            try {
                if (payload.description) {
                    const desc = new RTCSessionDescription(payload.description);
                    if (desc.type === 'offer') {
                        addSystemMessage(`Incoming call from ${members.get(peerId)?.name}`);
                        await pc.setRemoteDescription(desc);
                        peerState.remoteDescriptionSet = true;

                        // Flush any ICE candidates that arrived before the offer
                        for (const buffered of peerState.candidateBuffer) {
                            await pc.addIceCandidate(buffered);
                        }
                        peerState.candidateBuffer = [];

                        const answer = await pc.createAnswer();
                        await pc.setLocalDescription(answer);
                        triggerChannelEvent('client-signal', {
                            from: actor.id,
                            to: peerId,
                            description: pc.localDescription,
                        });
                    } else if (desc.type === 'answer') {
                        addSystemMessage(`Call accepted by ${members.get(peerId)?.name}`);
                        await pc.setRemoteDescription(desc);
                        peerState.remoteDescriptionSet = true;

                        // Flush any ICE candidates that arrived before the answer
                        for (const buffered of peerState.candidateBuffer) {
                            await pc.addIceCandidate(buffered);
                        }
                        peerState.candidateBuffer = [];
                    }
                } else if (payload.candidate) {
                    const candidate = new RTCIceCandidate(payload.candidate);
                    if (peerState.remoteDescriptionSet) {
                        await pc.addIceCandidate(candidate);
                    } else {
                        // Buffer candidate until remote description is set
                        peerState.candidateBuffer.push(candidate);
                    }
                }
            } catch (error) {
                console.error('Signal Error:', error);
            }
        }

        // --- Initialization ---

        async function setupLocalMedia() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                localVideo.srcObject = localStream;
                addSystemMessage('Camera & Microphone Ready.');
            } catch (error) {
                showBanner('Camera access denied. Please allow permissions.');
            }
        }

        function bindRealtimeChannel() {
            if (!reverb.key) return showBanner('Missing Reverb Key');

            pusher = new Pusher(reverb.key, {
                cluster: 'mt1',
                wsHost: reverb.host,
                wsPort: Number(reverb.port),
                wssPort: Number(reverb.port),
                forceTLS: reverb.scheme === 'https',
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
                channelAuthorization: {
                    endpoint: '/broadcasting/auth',
                    transport: 'ajax',
                    headers: { 'X-CSRF-TOKEN': meetingConfig.csrf }
                },
            });

            channel = pusher.subscribe(`presence-conference.${conference.id}`);

            channel.bind('pusher:subscription_succeeded', (presenceMembers) => {
                isSubscribed = true;
                members.clear();
                presenceMembers.each(member => {
                    const uid = (member.info && member.info.id) ? member.info.id : String(member.id);
                    members.set(uid, member.info);
                });
                
                if (!members.has(actor.id)) members.set(actor.id, { name: actor.name, role: actor.role });
                renderParticipants();
                addSystemMessage('Room Connected. Waiting for peers...');

                // Run initiation logic
                setTimeout(() => {
                    for (const peerId of members.keys()) {
                        if (peerId !== actor.id && shouldInitiateWith(peerId)) {
                            initiateConnection(peerId);
                        }
                    }
                }, 1000); // Small delay to ensure stability
            });

            channel.bind('pusher:member_added', (member) => {
                const uid = (member.info && member.info.id) ? member.info.id : String(member.id);
                members.set(uid, member.info);
                renderParticipants();
                addSystemMessage(`${member.info.name} joined.`);
                if (uid !== actor.id && shouldInitiateWith(uid)) {
                    initiateConnection(uid);
                }
            });

            channel.bind('pusher:member_removed', (member) => {
                const uid = (member.info && member.info.id) ? member.info.id : String(member.id);
                members.delete(uid);
                renderParticipants();
                removePeer(uid);
                addSystemMessage(`${member.info.name} left.`);
            });

            channel.bind('client-signal', handleSignal);
            channel.bind('client-chat', (payload) => {
                if (payload.from !== actor.id) addChatMessage(payload, false);
            });
            channel.bind('client-meeting-ended', () => {
                if (actor.role === 'student') window.location.href = meetingConfig.backUrl;
            });
        }

        // --- Event Listeners ---
        
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;
            const payload = { from: actor.id, name: actor.name, role: actor.role, message };
            triggerChannelEvent('client-chat', payload);
            addChatMessage(payload, true);
            chatInput.value = '';
        });

        toggleAudioBtn.addEventListener('click', () => {
            if (localStream) {
                const track = localStream.getAudioTracks()[0];
                track.enabled = !track.enabled;
                toggleAudioBtn.innerHTML = track.enabled ? '<i class="fa-solid fa-microphone mr-1"></i>Mic On' : '<i class="fa-solid fa-microphone-slash mr-1"></i>Mic Off';
                toggleAudioBtn.classList.toggle('bg-rose-700', !track.enabled);
            }
        });

        toggleVideoBtn.addEventListener('click', () => {
            if (localStream) {
                const track = localStream.getVideoTracks()[0];
                track.enabled = !track.enabled;
                toggleVideoBtn.innerHTML = track.enabled ? '<i class="fa-solid fa-video mr-1"></i>Cam On' : '<i class="fa-solid fa-video-slash mr-1"></i>Cam Off';
                toggleVideoBtn.classList.toggle('bg-rose-700', !track.enabled);
            }
        });

        if (copyLinkBtn) {
            copyLinkBtn.onclick = () => {
                navigator.clipboard.writeText(meetingConfig.joinLink);
                addSystemMessage('Link Copied!');
            };
        }

        if (endMeetingBtn) {
            endMeetingBtn.onclick = async () => {
                if(confirm('End Meeting?')) {
                    triggerChannelEvent('client-meeting-ended', { from: actor.id });
                    await fetch(meetingConfig.endMeetingUrl, { 
                        method: 'POST', 
                        headers: { 'X-CSRF-TOKEN': meetingConfig.csrf } 
                    });
                    window.location.href = meetingConfig.backUrl;
                }
            };
        }

        (async () => {
            if (!meetingConfig.isActive) return showBanner('Meeting Ended');
            await setupLocalMedia();
            bindRealtimeChannel();
        })();
    </script>
</body>
</html>
