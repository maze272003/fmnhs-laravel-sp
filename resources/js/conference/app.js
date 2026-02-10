/**
 * Conference Application
 * Main orchestrator wiring together signaling, media, peers, recording, chat, and notifications.
 */
import { SignalingClient } from './signaling.js';
import { MediaManager } from './media.js';
import { PeerManager } from './peers.js';
import { RecordingManager } from './recording.js';
import { ChatManager } from './chat.js';
import { NotificationManager } from './notifications.js';

export class ConferenceApp {
    constructor(config) {
        this.config = config;
        this.conference = config.conference;
        this.actor = config.actor;
        this.meetingConfig = config.meetingConfig;

        // Members & state
        this.members = new Map();
        this.raisedHands = new Set();
        this.isHandRaised = false;
        this.remoteScreenSharer = null;
        this.isDarkMode = true; // default dark
        this.isPiPActive = false;
        this.isTeacherSpotlight = false;
        this.isAnnotationMode = false;
        this.isLaserPointerActive = false;
        this.presentationMode = false;

        // UI callbacks
        this.ui = config.ui || {};

        // Initialize managers
        this.signaling = new SignalingClient({
            url: config.signalingConfig.url,
            roomId: config.signalingConfig.roomId,
            token: config.signalingConfig.token,
        });

        this.media = new MediaManager({
            defaultQuality: 'high',
            onAudioLevel: (level) => this.ui.onAudioLevel?.(level),
            onFreezeDetected: (peerId) => this.handleFreezeDetected(peerId),
        });

        this.peers = new PeerManager({
            iceServers: config.signalingConfig.iceServers,
            actorId: this.actor.id,
            actorRole: this.actor.role,
            onTrack: (peerId, track, stream) => this.handleTrack(peerId, track, stream),
            onPeerClosed: (peerId) => this.handlePeerClosed(peerId),
            onIceCandidate: (peerId, candidate) => {
                this.signaling.send({
                    type: 'ice-candidate', to: peerId,
                    payload: candidate,
                });
            },
        });

        this.recording = new RecordingManager({
            conferenceSlug: this.conference.slug,
            csrfToken: this.meetingConfig.csrf,
            uploadUrl: `/api/conference/${this.conference.slug}/recordings`,
            onRecordingStopped: (blob) => this.ui.onRecordingStopped?.(blob),
        });

        this.chat = new ChatManager({
            conferenceSlug: this.conference.slug,
            csrfToken: this.meetingConfig.csrf,
            baseUrl: '',
        });

        this.notifications = new NotificationManager({
            baseUrl: '',
            csrfToken: this.meetingConfig.csrf,
            actorRole: this.actor.role,
        });

        this.setupSignalingHandlers();
        this.setupKeyboardShortcuts();
    }

    /**
     * Boot the conference.
     */
    async start() {
        if (!this.meetingConfig.isActive) {
            this.ui.onBanner?.('This meeting has ended.');
            return;
        }

        // Setup local media
        try {
            const stream = await this.media.setupLocalMedia();
            this.peers.setLocalStream(stream);
            this.ui.onLocalStream?.(stream);
            this.ui.onSystemMessage?.('Camera & Microphone Ready.');
        } catch {
            this.ui.onBanner?.('Camera access denied. Audio-only mode.');
            try {
                const stream = await this.media.setupLocalMedia(true);
                this.peers.setLocalStream(stream);
                this.ui.onLocalStream?.(stream);
            } catch {
                this.ui.onBanner?.('No media access. Please allow camera/mic permissions.');
                return;
            }
        }

        // Load chat history
        const history = await this.chat.loadHistory();
        if (history.length > 0) {
            this.ui.onChatHistory?.(history);
        }

        // Record join
        this.recordJoinToServer();

        // Connect signaling
        this.signaling.connect();
    }

    /**
     * Setup signaling event handlers.
     */
    setupSignalingHandlers() {
        const s = this.signaling;

        s.on('connected', () => this.ui.onSystemMessage?.('Signaling connected.'));
        s.on('disconnected', () => {
            this.peers.destroy();
            this.members.clear();
            this.members.set(this.actor.id, { id: this.actor.id, name: this.actor.name, role: this.actor.role });
            this.ui.onParticipantsChanged?.(this.members, this.raisedHands);
            this.ui.onSystemMessage?.('Signaling disconnected. Reconnecting...');
        });
        s.on('reconnect-failed', () => this.ui.onBanner?.('Unable to reconnect. Please reload the page.'));

        s.on('joined', (msg) => {
            this.members.clear();
            const participants = Array.isArray(msg.participants) ? msg.participants : [];
            participants.forEach(p => { if (p?.id) this.members.set(p.id, p); });
            if (!this.members.has(this.actor.id)) {
                this.members.set(this.actor.id, { id: this.actor.id, name: this.actor.name, role: this.actor.role });
            }
            this.ui.onParticipantsChanged?.(this.members, this.raisedHands);
            this.ui.onSystemMessage?.('Room connected. Waiting for peers...');

            setTimeout(() => {
                this.members.forEach((info, peerId) => {
                    if (peerId !== this.actor.id && this.peers.shouldInitiateWith(peerId, info.role)) {
                        this.initiateConnection(peerId);
                    }
                });
            }, 500);
        });

        s.on('peer-joined', (msg) => {
            if (!msg.participant?.id) return;
            const p = msg.participant;
            this.members.set(p.id, p);
            this.ui.onParticipantsChanged?.(this.members, this.raisedHands);
            this.ui.onSystemMessage?.(`${p.name || p.id} joined.`);
            this.notifications.notifyJoin(p.name, p.role);

            if (p.id !== this.actor.id && this.peers.shouldInitiateWith(p.id, p.role)) {
                this.initiateConnection(p.id);
            }
        });

        s.on('peer-left', (msg) => {
            if (!msg.participant?.id) return;
            const p = msg.participant;
            this.members.delete(p.id);
            this.raisedHands.delete(p.id);
            this.peers.removePeer(p.id);
            this.ui.onPeerRemoved?.(p.id);
            this.ui.onParticipantsChanged?.(this.members, this.raisedHands);
            this.ui.onSystemMessage?.(`${p.name || p.id} left.`);
            this.notifications.notifyLeave(p.name);
        });

        // WebRTC signaling
        s.on('offer', async (msg) => {
            if (!msg.from || msg.from.id === this.actor.id) return;
            this.members.set(msg.from.id, msg.from);
            this.ui.onSystemMessage?.(`Incoming call from ${msg.from.name || msg.from.id}`);
            try {
                const answer = await this.peers.handleOffer(msg.from.id, msg.payload);
                s.send({ type: 'answer', to: msg.from.id, payload: answer });
            } catch (e) { console.error('[App] Offer handling error:', e); }
        });

        s.on('answer', async (msg) => {
            if (!msg.from || msg.from.id === this.actor.id) return;
            this.members.set(msg.from.id, msg.from);
            this.ui.onSystemMessage?.(`Call accepted by ${msg.from.name || msg.from.id}`);
            try {
                await this.peers.handleAnswer(msg.from.id, msg.payload);
            } catch (e) { console.error('[App] Answer handling error:', e); }
        });

        s.on('ice-candidate', async (msg) => {
            if (!msg.from || msg.from.id === this.actor.id || !msg.payload) return;
            this.members.set(msg.from.id, msg.from);
            try {
                await this.peers.handleIceCandidate(msg.from.id, msg.payload);
            } catch (e) { console.error('[App] ICE error:', e); }
        });

        // Chat
        s.on('chat', (msg) => {
            if (msg.from?.id !== this.actor.id) {
                this.ui.onChatMessage?.({
                    name: msg.from?.name || 'Unknown',
                    role: msg.from?.role || 'participant',
                    message: msg.message || '',
                }, false);
                this.notifications.notifyMessage(msg.from?.name, msg.message);
            }
        });

        // Meeting ended
        s.on('meeting-ended', () => {
            if (this.actor.role === 'student') {
                window.location.href = this.meetingConfig.backUrl;
            }
        });

        // Raise hand
        s.on('raise-hand', (msg) => {
            const fromId = msg.from?.id;
            if (!fromId || fromId === this.actor.id) return;
            if (msg.raised) {
                this.raisedHands.add(fromId);
                this.ui.onSystemMessage?.(`${msg.from.name} raised their hand ✋`);
                this.notifications.notifyHandRaise(msg.from.name);
            } else {
                this.raisedHands.delete(fromId);
                this.ui.onSystemMessage?.(`${msg.from.name} lowered their hand`);
            }
            this.ui.onHandRaised?.(fromId, msg.raised);
            this.ui.onParticipantsChanged?.(this.members, this.raisedHands);
        });

        // Emoji reaction
        s.on('emoji-reaction', (msg) => {
            if (msg.from?.id !== this.actor.id) {
                this.ui.onEmojiReaction?.(msg.emoji, msg.from?.name || 'Someone');
            }
        });

        // Force controls
        s.on('force-mute', () => {
            this.media.forceAudio(false);
            this.ui.onMediaStateChanged?.('audio', false, 'Teacher muted your microphone.');
        });
        s.on('force-unmute', () => {
            this.media.forceAudio(true);
            this.ui.onMediaStateChanged?.('audio', true, 'Teacher unmuted your microphone.');
        });
        s.on('force-cam-off', () => {
            this.media.forceVideo(false);
            this.ui.onMediaStateChanged?.('video', false, 'Teacher turned off your camera.');
        });
        s.on('force-cam-on', () => {
            this.media.forceVideo(true);
            this.ui.onMediaStateChanged?.('video', true, 'Teacher turned on your camera.');
        });
        s.on('force-mute-all', () => {
            this.media.forceAudio(false);
            this.ui.onMediaStateChanged?.('audio', false, 'Teacher muted everyone.');
        });

        // Participant control (broadcast)
        s.on('participant-control', (msg) => {
            const targetName = this.members.get(msg.targetId)?.name || msg.targetId;
            const labels = {
                'force-mute': 'muted', 'force-unmute': 'unmuted',
                'force-cam-off': 'turned off camera of', 'force-cam-on': 'turned on camera of',
            };
            if (msg.from?.id !== this.actor.id && msg.targetId !== this.actor.id) {
                this.ui.onSystemMessage?.(`Teacher ${labels[msg.action] || msg.action} ${targetName}`);
            }
        });

        // Screen share events
        s.on('screen-share-started', (msg) => {
            if (msg.from?.id !== this.actor.id) {
                this.remoteScreenSharer = msg.from;
                this.ui.onSystemMessage?.(`${msg.from?.name || 'Someone'} started screen sharing`);
                // Enable teacher spotlight mode
                if (msg.from?.role === 'teacher') {
                    this.isTeacherSpotlight = true;
                    this.ui.onTeacherSpotlight?.(true, msg.from);
                }
            }
        });
        s.on('screen-share-stopped', (msg) => {
            if (msg.from?.id !== this.actor.id) {
                this.ui.onSystemMessage?.(`${msg.from?.name || 'Someone'} stopped screen sharing`);
                this.ui.onRemoteScreenShareStopped?.(msg.from);
                if (this.isTeacherSpotlight && msg.from?.role === 'teacher') {
                    this.isTeacherSpotlight = false;
                    this.ui.onTeacherSpotlight?.(false, null);
                }
                this.remoteScreenSharer = null;
            }
        });

        // Recording events
        s.on('recording-started', (msg) => {
            this.ui.onSystemMessage?.(`${msg.from?.name || 'Teacher'} started recording.`);
            this.ui.onRecordingStateChanged?.(true);
        });
        s.on('recording-stopped', (msg) => {
            this.ui.onSystemMessage?.(`${msg.from?.name || 'Teacher'} stopped recording.`);
            this.ui.onRecordingStateChanged?.(false);
        });

        // Annotation
        s.on('annotation', (msg) => {
            this.ui.onAnnotation?.(msg.data, msg.from);
        });

        // Laser pointer
        s.on('laser-pointer', (msg) => {
            this.ui.onLaserPointer?.(msg.x, msg.y, msg.visible, msg.from);
        });

        // Presentation mode
        s.on('presentation-mode', (msg) => {
            this.presentationMode = msg.active;
            this.ui.onPresentationMode?.(msg.active, msg.slide);
        });

        // Remote control
        s.on('remote-control-request', (msg) => {
            this.ui.onRemoteControlRequest?.(msg.from);
        });
        s.on('remote-control-response', (msg) => {
            this.ui.onRemoteControlResponse?.(msg.approved, msg.from);
        });
        s.on('remote-control-granted', (msg) => {
            this.ui.onSystemMessage?.(`Remote control granted to ${this.members.get(msg.targetId)?.name || msg.targetId}`);
        });
        s.on('remote-control-stop', () => {
            this.ui.onRemoteControlStop?.();
        });

        // Kicked
        s.on('kicked', (msg) => {
            this.ui.onBanner?.('You have been removed from the meeting.');
            setTimeout(() => window.location.href = this.meetingConfig.backUrl, 2000);
        });

        // Video freeze alert (teacher sees this)
        s.on('video-freeze-alert', (msg) => {
            this.ui.onSystemMessage?.(`Video freeze detected for ${msg.from?.name}`);
        });

        // Network quality reports (teacher)
        s.on('network-quality-report', (msg) => {
            this.ui.onNetworkQualityReport?.(msg.from, msg.quality, msg.stats);
        });

        // Attention check
        s.on('attention-check', (msg) => {
            this.ui.onAttentionCheck?.(msg.message);
        });

        // File shared
        s.on('file-shared', (msg) => {
            this.ui.onFileShared?.(msg);
        });

        // Error
        s.on('error', (msg) => {
            this.ui.onBanner?.(msg.message || 'Realtime signaling error.');
            this.ui.onSystemMessage?.(`Signaling error: ${msg.message || msg.code || 'unknown'}`);
        });

        // Connection replaced
        s.on('system', (msg) => {
            if (msg.event === 'connection-replaced') {
                this.ui.onSystemMessage?.('Another session connected with the same account.');
            }
        });
    }

    /**
     * Handle incoming track from a peer.
     */
    handleTrack(peerId, track, stream) {
        const member = this.members.get(peerId);
        const name = member?.name || peerId;

        if (track.kind === 'video') {
            // Check if this is a screen share (second video stream)
            const existingStream = this.ui.getPeerStream?.(peerId);
            if (existingStream && existingStream.id !== stream.id) {
                this.ui.onSystemMessage?.(`${name} is sharing their screen`);
                this.ui.onRemoteScreenShare?.(peerId, stream, name);

                // Teacher spotlight mode
                if (member?.role === 'teacher') {
                    this.isTeacherSpotlight = true;
                    this.ui.onTeacherSpotlight?.(true, member);
                }
                return;
            }
        }

        this.ui.onSystemMessage?.(`Received video stream from ${name}`);
        this.ui.onRemoteStream?.(peerId, stream, name);

        // Start freeze detection
        setTimeout(() => {
            const videoEl = document.getElementById(`video-${peerId}`);
            if (videoEl) this.media.startFreezeDetection(peerId, videoEl);
        }, 2000);
    }

    handlePeerClosed(peerId) {
        this.media.stopFreezeDetection(peerId);
        this.ui.onPeerRemoved?.(peerId);
    }

    handleFreezeDetected(peerId) {
        this.signaling.send({ type: 'video-freeze-detected', peerId });
        this.ui.onSystemMessage?.(`Video freeze detected for ${this.members.get(peerId)?.name || peerId}`);
    }

    async initiateConnection(peerId) {
        this.ui.onSystemMessage?.(`Calling ${this.members.get(peerId)?.name || peerId}...`);
        try {
            const offer = await this.peers.createOffer(peerId);
            if (offer) {
                this.signaling.send({ type: 'offer', to: peerId, payload: offer });
            }
        } catch (e) { console.error('[App] Offer error:', e); }
    }

    // === User Actions ===

    toggleAudio() {
        const enabled = this.media.toggleAudio();
        this.ui.onMediaStateChanged?.('audio', enabled);
        return enabled;
    }

    toggleVideo() {
        const enabled = this.media.toggleVideo();
        this.ui.onMediaStateChanged?.('video', enabled);
        return enabled;
    }

    async toggleScreenShare() {
        if (this.media.isScreenSharing) {
            this.media.stopScreenShare();
            this.peers.removeScreenTrackFromAll();
            this.signaling.send({ type: 'screen-share-stopped' });
            this.ui.onLocalScreenShareStopped?.();
            this.ui.onSystemMessage?.('You stopped screen sharing.');
        } else {
            try {
                const stream = await this.media.startScreenShare(true);
                const offers = await this.peers.addScreenTrackToAll(stream);
                for (const { peerId, payload } of offers) {
                    this.signaling.send({ type: 'offer', to: peerId, payload });
                }
                this.signaling.send({ type: 'screen-share-started' });
                this.ui.onLocalScreenShareStarted?.(stream);
                this.ui.onSystemMessage?.('You started screen sharing.');
            } catch {
                return;
            }
        }
    }

    toggleRaiseHand() {
        this.isHandRaised = !this.isHandRaised;
        if (this.isHandRaised) this.raisedHands.add(this.actor.id);
        else this.raisedHands.delete(this.actor.id);
        this.signaling.send({ type: 'raise-hand', raised: this.isHandRaised });
        this.ui.onHandRaised?.(this.actor.id, this.isHandRaised);
        this.ui.onParticipantsChanged?.(this.members, this.raisedHands);
        return this.isHandRaised;
    }

    sendEmoji(emoji) {
        this.signaling.send({ type: 'emoji-reaction', emoji });
        this.ui.onEmojiReaction?.(emoji, 'You');
    }

    sendChat(message) {
        if (!message.trim()) return;
        const trimmed = message.trim();
        this.signaling.send({ type: 'chat', roomId: this.signaling.roomId, message: trimmed });
        this.ui.onChatMessage?.({ name: this.actor.name, role: this.actor.role, message: trimmed }, true);
        // Persist to server
        this.chat.saveMessage(trimmed);
    }

    async uploadChatFile(file) {
        const result = await this.chat.uploadFile(file);
        if (result) {
            this.signaling.send({
                type: 'file-shared',
                fileName: result.file_name,
                fileUrl: result.file_url,
                fileMime: result.file_mime,
                fileSize: result.file_size,
            });
            this.ui.onFileShared?.({
                from: { id: this.actor.id, name: this.actor.name, role: this.actor.role },
                fileName: result.file_name,
                fileUrl: result.file_url,
                fileMime: result.file_mime,
                fileSize: result.file_size,
            });
        }
        return result;
    }

    forceReconnect() {
        const offers = this.peers.forceReconnectAll(this.members);
        this.ui.onSystemMessage?.('Reconnecting to all peers...');
    }

    // Teacher controls
    muteParticipant(targetId) { this.signaling.send({ type: 'mute-participant', targetId }); }
    unmuteParticipant(targetId) { this.signaling.send({ type: 'unmute-participant', targetId }); }
    disableCamParticipant(targetId) { this.signaling.send({ type: 'disable-cam-participant', targetId }); }
    enableCamParticipant(targetId) { this.signaling.send({ type: 'enable-cam-participant', targetId }); }
    kickParticipant(targetId, reason = '') { this.signaling.send({ type: 'kick-participant', targetId, reason }); }
    muteAll() { this.signaling.send({ type: 'mute-all' }); }

    // Annotation & laser pointer
    sendAnnotation(data) { this.signaling.send({ type: 'annotation', data }); }
    sendLaserPointer(x, y, visible) { this.signaling.send({ type: 'laser-pointer', x, y, visible }); }

    // Remote control
    requestRemoteControl() { this.signaling.send({ type: 'remote-control-request' }); }
    respondRemoteControl(targetId, approved) { this.signaling.send({ type: 'remote-control-response', targetId, approved }); }
    stopRemoteControl() { this.signaling.send({ type: 'remote-control-stop' }); }

    // Presentation mode
    setPresentationMode(active, slide = 0) { this.signaling.send({ type: 'presentation-mode', active, slide }); }

    // Recording
    async startRecording(type = 'video') {
        const streams = [];
        if (this.media.localStream) streams.push(this.media.localStream);
        if (this.media.screenStream) streams.push(this.media.screenStream);

        const combined = RecordingManager.createCombinedStream(
            this.media.screenStream || this.media.localStream,
            this.media.localStream,
        );

        const started = this.recording.startRecording(combined, type);
        if (started) {
            this.signaling.send({ type: 'recording-started' });
            this.ui.onRecordingStateChanged?.(true);
            this.ui.onSystemMessage?.('Recording started.');
        }
        return started;
    }

    async stopRecording(uploadToCloud = true) {
        this.recording.stopRecording();
        this.signaling.send({ type: 'recording-stopped' });
        this.ui.onRecordingStateChanged?.(false);
        this.ui.onSystemMessage?.('Recording stopped.');

        if (uploadToCloud) {
            try {
                const result = await this.recording.uploadToServer();
                this.ui.onSystemMessage?.('Recording uploaded successfully.');
                return result;
            } catch (e) {
                this.ui.onSystemMessage?.('Failed to upload recording. Downloading locally...');
                this.recording.downloadLocal();
            }
        } else {
            this.recording.downloadLocal();
        }
        return null;
    }

    // Quality controls
    async setQuality(quality) { await this.media.setQuality(quality); }
    async toggleLowBandwidth() { return this.media.toggleLowBandwidth(); }

    // Push-to-talk
    enablePushToTalk(enabled) { this.media.enablePushToTalk(enabled); }
    pttDown() { this.media.pttDown(); this.signaling.send({ type: 'push-to-talk', active: true }); }
    pttUp() { this.media.pttUp(); this.signaling.send({ type: 'push-to-talk', active: false }); }

    // PiP
    async togglePiP(videoElement) {
        if (document.pictureInPictureElement) {
            await document.exitPictureInPicture();
            this.isPiPActive = false;
        } else if (videoElement && videoElement.requestPictureInPicture) {
            await videoElement.requestPictureInPicture();
            this.isPiPActive = true;
        }
        return this.isPiPActive;
    }

    // Network quality sending
    async sendNetworkQuality() {
        for (const [peerId] of this.peers.peers) {
            const stats = await this.peers.getStats(peerId);
            if (stats) {
                this.signaling.send({
                    type: 'network-quality',
                    quality: stats.rtt < 0.1 ? 'good' : stats.rtt < 0.3 ? 'fair' : 'poor',
                    stats,
                });
                break; // Send based on first peer
            }
        }
    }

    // Attention check (teacher)
    sendAttentionCheck(message = 'Are you paying attention?') {
        this.signaling.send({ type: 'attention-check', message });
    }

    // End meeting
    async endMeeting() {
        this.signaling.send({ type: 'meeting-ended' });
        // Record leave
        this.recordLeaveToServer();
        try {
            await fetch(this.meetingConfig.endMeetingUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': this.meetingConfig.csrf },
            });
        } catch {}
        window.location.href = this.meetingConfig.backUrl;
    }

    // Keyboard shortcuts
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Don't trigger shortcuts when typing in inputs
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                // PTT works even in inputs
                if (e.code === 'Space' && this.media.isPushToTalk) {
                    e.preventDefault();
                    this.pttDown();
                }
                return;
            }

            switch (e.code) {
                case 'KeyM': this.toggleAudio(); break;
                case 'KeyV': this.toggleVideo(); break;
                case 'KeyS': if (e.ctrlKey || e.metaKey) { e.preventDefault(); this.toggleScreenShare(); } break;
                case 'KeyH': this.toggleRaiseHand(); break;
                case 'Space':
                    if (this.media.isPushToTalk) { e.preventDefault(); this.pttDown(); }
                    break;
                case 'KeyP':
                    if (e.ctrlKey || e.metaKey) { e.preventDefault(); /* toggle PiP */ }
                    break;
            }
        });

        document.addEventListener('keyup', (e) => {
            if (e.code === 'Space' && this.media.isPushToTalk) {
                e.preventDefault();
                this.pttUp();
            }
        });
    }

    // Server persistence
    async recordJoinToServer() {
        try {
            await fetch(`/api/conference/${this.conference.slug}/join`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.meetingConfig.csrf,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    device_info: {
                        userAgent: navigator.userAgent,
                        screen: `${screen.width}x${screen.height}`,
                    },
                }),
            });
        } catch {}
    }

    async recordLeaveToServer() {
        try {
            await fetch(`/api/conference/${this.conference.slug}/leave`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.meetingConfig.csrf,
                    'Accept': 'application/json',
                },
            });
        } catch {}
    }

    /**
     * Clean up everything.
     */
    destroy() {
        this.recordLeaveToServer();
        this.recording.destroy();
        this.peers.destroy();
        this.media.destroy();
        this.signaling.disconnect();
    }
}
