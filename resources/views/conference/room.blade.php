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
        // Define arrays in PHP block to avoid @json parse errors with multi-line arrays
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

        const peers = new Map();
        const members = new Map();
        let localStream = null;
        let pusher = null;
        let channel = null;

        function showBanner(message) {
            statusBanner.textContent = message;
            statusBanner.classList.remove('hidden');
        }

        function addSystemMessage(text) {
            const row = document.createElement('div');
            row.className = 'text-center text-xs text-slate-400';
            row.textContent = text;
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
            const existing = peers.get(peerId);
            if (existing) {
                existing.pc.close();
                peers.delete(peerId);
            }

            const tile = document.getElementById(`tile-${peerId}`);
            if (tile) {
                tile.remove();
            }
        }

        function signalPeer(payload) {
            if (!channel) {
                return;
            }

            channel.trigger('client-signal', {
                ...payload,
                from: actor.id,
            });
        }

        function ensurePeer(peerId) {
            if (peers.has(peerId)) {
                return peers.get(peerId);
            }

            const pc = new RTCPeerConnection({
                iceServers: [{ urls: 'stun:stun.l.google.com:19302' }],
            });

            if (localStream) {
                localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
            }

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    signalPeer({
                        to: peerId,
                        type: 'candidate',
                        candidate: event.candidate,
                    });
                }
            };

            pc.ontrack = (event) => {
                const label = members.get(peerId)?.name || peerId;
                const videoEl = document.getElementById(`video-${peerId}`) || createRemoteTile(peerId, label);
                if (event.streams[0]) {
                    videoEl.srcObject = event.streams[0];
                }
            };

            pc.onconnectionstatechange = () => {
                if (['failed', 'disconnected', 'closed'].includes(pc.connectionState)) {
                    removePeer(peerId);
                }
            };

            const data = { pc };
            peers.set(peerId, data);
            return data;
        }

        async function createOfferFor(peerId) {
            const { pc } = ensurePeer(peerId);

            if (pc.signalingState !== 'stable') {
                return;
            }

            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            signalPeer({
                to: peerId,
                type: 'offer',
                description: pc.localDescription,
            });
        }

        async function handleSignal(payload) {
            if (!payload || payload.from === actor.id) {
                return;
            }

            if (payload.to && payload.to !== actor.id) {
                return;
            }

            const peerId = payload.from;
            const { pc } = ensurePeer(peerId);

            if (payload.type === 'offer' && payload.description) {
                await pc.setRemoteDescription(new RTCSessionDescription(payload.description));
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                signalPeer({
                    to: peerId,
                    type: 'answer',
                    description: pc.localDescription,
                });
                return;
            }

            if (payload.type === 'answer' && payload.description) {
                await pc.setRemoteDescription(new RTCSessionDescription(payload.description));
                return;
            }

            if (payload.type === 'candidate' && payload.candidate) {
                try {
                    await pc.addIceCandidate(new RTCIceCandidate(payload.candidate));
                } catch (error) {
                    console.error('ICE candidate error:', error);
                }
            }
        }

        function shouldInitiateWith(peerId) {
            return actor.id.localeCompare(peerId) < 0;
        }

        async function setupLocalMedia() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showBanner('Your browser does not support camera/microphone access.');
                return;
            }

            try {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                localVideo.srcObject = localStream;
            } catch (error) {
                console.error(error);
                showBanner('Camera or microphone access is blocked. You can still use chat.');
            }
        }

        function bindRealtimeChannel() {
            if (!reverb.key) {
                showBanner('Missing Reverb key. Check REVERB_APP_KEY in your environment.');
                return;
            }

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
                    headers: {
                        'X-CSRF-TOKEN': meetingConfig.csrf,
                    },
                },
            });

            channel = pusher.subscribe(`presence-conference.${conference.id}`);

            channel.bind('pusher:subscription_succeeded', async (presenceMembers) => {
                members.clear();

                presenceMembers.each(async (member) => {
                    members.set(member.id, member.info);
                });

                renderParticipants();
                addSystemMessage('Connected to live room.');

                for (const peerId of members.keys()) {
                    if (peerId !== actor.id && shouldInitiateWith(peerId)) {
                        await createOfferFor(peerId);
                    }
                }
            });

            channel.bind('pusher:member_added', async (member) => {
                members.set(member.id, member.info);
                renderParticipants();
                addSystemMessage(`${member.info.name} joined the room.`);

                if (member.id !== actor.id && shouldInitiateWith(member.id)) {
                    await createOfferFor(member.id);
                }
            });

            channel.bind('pusher:member_removed', (member) => {
                members.delete(member.id);
                renderParticipants();
                removePeer(member.id);
                addSystemMessage(`${member.info.name} left the room.`);
            });

            channel.bind('client-signal', async (payload) => {
                try {
                    await handleSignal(payload);
                } catch (error) {
                    console.error('Signal error:', error);
                }
            });

            channel.bind('client-chat', (payload) => {
                if (payload.from === actor.id) {
                    return;
                }
                addChatMessage(payload, false);
            });

            channel.bind('client-meeting-ended', () => {
                if (actor.role === 'student') {
                    alert('The teacher ended this meeting.');
                    window.location.href = meetingConfig.backUrl;
                }
            });
        }

        chatForm.addEventListener('submit', (event) => {
            event.preventDefault();

            const message = chatInput.value.trim();
            if (!message || !channel) {
                return;
            }

            const payload = {
                from: actor.id,
                name: actor.name,
                role: actor.role,
                message,
                at: new Date().toISOString(),
            };

            channel.trigger('client-chat', payload);
            addChatMessage(payload, true);
            chatInput.value = '';
        });

        toggleAudioBtn.addEventListener('click', () => {
            if (!localStream) {
                showBanner('Microphone is not available.');
                return;
            }

            const audioTracks = localStream.getAudioTracks();
            if (!audioTracks.length) {
                showBanner('Microphone track not found.');
                return;
            }

            const enabled = !audioTracks[0].enabled;
            audioTracks.forEach(track => {
                track.enabled = enabled;
            });

            toggleAudioBtn.innerHTML = enabled
                ? '<i class="fa-solid fa-microphone mr-1"></i>Mic On'
                : '<i class="fa-solid fa-microphone-slash mr-1"></i>Mic Off';
            toggleAudioBtn.classList.toggle('bg-rose-700', !enabled);
        });

        toggleVideoBtn.addEventListener('click', () => {
            if (!localStream) {
                showBanner('Camera is not available.');
                return;
            }

            const videoTracks = localStream.getVideoTracks();
            if (!videoTracks.length) {
                showBanner('Camera track not found.');
                return;
            }

            const enabled = !videoTracks[0].enabled;
            videoTracks.forEach(track => {
                track.enabled = enabled;
            });

            toggleVideoBtn.innerHTML = enabled
                ? '<i class="fa-solid fa-video mr-1"></i>Cam On'
                : '<i class="fa-solid fa-video-slash mr-1"></i>Cam Off';
            toggleVideoBtn.classList.toggle('bg-rose-700', !enabled);
        });

        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(meetingConfig.joinLink);
                    addSystemMessage('Join link copied.');
                } catch (error) {
                    console.error(error);
                    showBanner('Unable to copy link. Copy it manually from browser URL.');
                }
            });
        }

        if (endMeetingBtn) {
            endMeetingBtn.addEventListener('click', async () => {
                const confirmed = confirm('End this meeting for all students?');
                if (!confirmed) {
                    return;
                }

                try {
                    if (channel) {
                        channel.trigger('client-meeting-ended', {
                            from: actor.id,
                            at: new Date().toISOString(),
                        });
                    }

                    await fetch(meetingConfig.endMeetingUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': meetingConfig.csrf,
                            'Accept': 'application/json',
                        },
                    });

                    window.location.href = meetingConfig.backUrl;
                } catch (error) {
                    console.error(error);
                    showBanner('Unable to end the meeting right now.');
                }
            });
        }

        window.addEventListener('beforeunload', () => {
            peers.forEach((entry) => entry.pc.close());
            peers.clear();

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            if (pusher) {
                pusher.disconnect();
            }
        });

        (async () => {
            if (!meetingConfig.isActive) {
                showBanner('This meeting has already ended.');
                return;
            }

            await setupLocalMedia();
            bindRealtimeChannel();
        })();
    </script>
</body>
</html>