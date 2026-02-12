/**
 * WebRTC Peer Connection Manager
 * Manages peer connections, tracks, ICE negotiation, and adaptive bitrate.
 */
export class PeerManager {
    constructor(config) {
        this.iceServers = config.iceServers || [
            { urls: ['stun:stun.l.google.com:19302', 'stun:stun1.l.google.com:19302'] },
        ];
        this.actorId = config.actorId;
        this.actorRole = config.actorRole;
        this.peers = new Map(); // peerId -> { pc, candidateBuffer, remoteDescriptionSet, screenSender, dataCh }
        this.onTrack = config.onTrack || null;
        this.onPeerClosed = config.onPeerClosed || null;
        this.onIceCandidate = config.onIceCandidate || null;
        this.onNegotiationNeeded = config.onNegotiationNeeded || null;

        this.localStream = null;
        this.screenStream = null;
        this.remoteStreams = new Map(); // peerId -> MediaStream

        // Adaptive bitrate
        this.statsIntervals = new Map();
        this.adaptiveBitrate = config.adaptiveBitrate !== false;
    }

    setLocalStream(stream) {
        this.localStream = stream;
    }

    setScreenStream(stream) {
        this.screenStream = stream;
    }

    /**
     * Determine if this actor should initiate the WebRTC connection.
     * Teacher always initiates. Between students, use ID comparison.
     */
    shouldInitiateWith(peerId, peerRole) {
        if (this.actorRole === 'teacher') return true;
        if (peerRole === 'teacher') return false;
        return this.actorId.localeCompare(peerId) < 0;
    }

    /**
     * Create or get an existing peer connection.
     */
    ensurePeer(peerId) {
        if (this.peers.has(peerId)) return this.peers.get(peerId);

        const pc = new RTCPeerConnection({ iceServers: this.iceServers });

        // Always negotiate audio/video m-lines so receive-only and late device attach keep working.
        const localAudioTrack = this.localStream?.getAudioTracks?.()[0] || null;
        const localVideoTrack = this.localStream?.getVideoTracks?.()[0] || null;

        if (localAudioTrack && this.localStream) {
            pc.addTrack(localAudioTrack, this.localStream);
        } else {
            pc.addTransceiver('audio', { direction: 'sendrecv' });
        }

        if (localVideoTrack && this.localStream) {
            pc.addTrack(localVideoTrack, this.localStream);
        } else {
            pc.addTransceiver('video', { direction: 'sendrecv' });
        }

        if (this.screenStream) {
            this.screenStream.getTracks().forEach(track => pc.addTrack(track, this.screenStream));
        }

        // ICE candidate
        pc.onicecandidate = (event) => {
            if (event.candidate && this.onIceCandidate) {
                this.onIceCandidate(peerId, event.candidate.toJSON ? event.candidate.toJSON() : event.candidate);
            }
        };

        // Track received
        pc.ontrack = (event) => {
            if (this.onTrack) {
                const stream = this.resolveRemoteStream(peerId, event);
                this.onTrack(peerId, event.track, stream);
            }
        };

        // Connection state
        pc.onconnectionstatechange = () => {
            if (['failed', 'closed'].includes(pc.connectionState)) {
                this.removePeer(peerId);
            }
        };

        // Negotiation needed (for renegotiation after adding/removing tracks)
        pc.onnegotiationneeded = () => {
            if (this.onNegotiationNeeded) {
                this.onNegotiationNeeded(peerId);
            }
        };

        const state = {
            pc,
            candidateBuffer: [],
            remoteDescriptionSet: false,
            screenSender: null,
        };

        this.peers.set(peerId, state);

        // Start adaptive bitrate monitoring
        if (this.adaptiveBitrate) {
            this.startBitrateMonitoring(peerId, pc);
        }

        return state;
    }

    /**
     * Create and send an offer.
     */
    async createOffer(peerId) {
        const { pc } = this.ensurePeer(peerId);
        if (pc.signalingState !== 'stable') return null;

        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        return this.serializeDescription(pc.localDescription);
    }

    /**
     * Handle an incoming offer, create answer.
     */
    async handleOffer(peerId, description) {
        const peerState = this.ensurePeer(peerId);
        const { pc } = peerState;

        await pc.setRemoteDescription(new RTCSessionDescription(description));
        peerState.remoteDescriptionSet = true;

        // Flush buffered ICE candidates
        for (const buffered of peerState.candidateBuffer) {
            await pc.addIceCandidate(buffered);
        }
        peerState.candidateBuffer = [];

        const answer = await pc.createAnswer();
        await pc.setLocalDescription(answer);
        return this.serializeDescription(pc.localDescription);
    }

    /**
     * Handle an incoming answer.
     */
    async handleAnswer(peerId, description) {
        const peerState = this.peers.get(peerId);
        if (!peerState) return;

        await peerState.pc.setRemoteDescription(new RTCSessionDescription(description));
        peerState.remoteDescriptionSet = true;

        for (const buffered of peerState.candidateBuffer) {
            await peerState.pc.addIceCandidate(buffered);
        }
        peerState.candidateBuffer = [];
    }

    /**
     * Handle an incoming ICE candidate.
     */
    async handleIceCandidate(peerId, candidateData) {
        const peerState = this.peers.get(peerId);
        if (!peerState) return;

        const candidate = new RTCIceCandidate(candidateData);
        if (peerState.remoteDescriptionSet || peerState.pc.remoteDescription) {
            await peerState.pc.addIceCandidate(candidate);
        } else {
            peerState.candidateBuffer.push(candidate);
        }
    }

    /**
     * Add screen share track to all peers and renegotiate.
     */
    async addScreenTrackToAll(screenStream) {
        this.screenStream = screenStream;
        const screenTrack = screenStream.getVideoTracks()[0];
        const offers = [];

        for (const [peerId, peerState] of this.peers) {
            const sender = peerState.pc.addTrack(screenTrack, screenStream);
            peerState.screenSender = sender;

            if (this.shouldInitiateWith(peerId, null) || this.actorRole === 'teacher') {
                try {
                    const offer = await peerState.pc.createOffer();
                    await peerState.pc.setLocalDescription(offer);
                    offers.push({
                        peerId,
                        payload: this.serializeDescription(peerState.pc.localDescription),
                    });
                } catch (e) {
                    console.error('[Peers] Screen share renegotiation error:', e);
                }
            }
        }
        return offers;
    }

    /**
     * Remove screen share track from all peers.
     */
    removeScreenTrackFromAll() {
        for (const [, peerState] of this.peers) {
            if (peerState.screenSender) {
                try { peerState.pc.removeTrack(peerState.screenSender); } catch {}
                peerState.screenSender = null;
            }
        }
        this.screenStream = null;
    }

    /**
     * Replace or attach an outgoing media track for all active peers.
     * Returns offers for peers that require renegotiation.
     */
    async replaceOutgoingTrackForAll(track) {
        if (!track) return [];

        const offers = [];
        for (const [peerId, peerState] of this.peers) {
            const sender = this.findSenderByKind(peerState.pc, track.kind);

            if (sender) {
                try {
                    await sender.replaceTrack(track);
                } catch (e) {
                    console.error(`[Peers] replaceTrack failed for ${peerId} (${track.kind}):`, e);
                }
                continue;
            }

            if (this.localStream) {
                peerState.pc.addTrack(track, this.localStream);
                if (peerState.pc.signalingState === 'stable') {
                    try {
                        const offer = await peerState.pc.createOffer();
                        await peerState.pc.setLocalDescription(offer);
                        offers.push({
                            peerId,
                            payload: this.serializeDescription(peerState.pc.localDescription),
                        });
                    } catch (e) {
                        console.error(`[Peers] Renegotiation failed for ${peerId} (${track.kind}):`, e);
                    }
                }
            }
        }

        return offers;
    }

    /**
     * Remove a peer and close its connection.
     */
    removePeer(peerId) {
        const peerState = this.peers.get(peerId);
        if (peerState) {
            peerState.pc.close();
            this.peers.delete(peerId);
        }
        this.remoteStreams.delete(peerId);
        this.stopBitrateMonitoring(peerId);
        if (this.onPeerClosed) this.onPeerClosed(peerId);
    }

    /**
     * Force reconnect with all peers.
     */
    async forceReconnectAll(members) {
        const offers = [];
        for (const [peerId] of members) {
            if (peerId === this.actorId) continue;
            if (this.peers.has(peerId)) this.removePeer(peerId);
            try {
                const offer = await this.createOffer(peerId);
                if (offer) offers.push({ peerId, payload: offer });
            } catch (e) {
                console.error(`[Peers] Reconnect error for ${peerId}:`, e);
            }
        }
        return offers;
    }

    /**
     * Adaptive bitrate monitoring.
     */
    startBitrateMonitoring(peerId, pc) {
        let lastBytesReceived = 0;
        let lowBitrateCount = 0;

        const interval = setInterval(async () => {
            try {
                const stats = await pc.getStats();
                let currentBytesReceived = 0;

                stats.forEach(report => {
                    if (report.type === 'inbound-rtp' && report.kind === 'video') {
                        currentBytesReceived += report.bytesReceived || 0;
                    }
                });

                const delta = currentBytesReceived - lastBytesReceived;
                lastBytesReceived = currentBytesReceived;

                // If receiving very low data, might need to lower quality
                if (delta < 500 && currentBytesReceived > 0) {
                    lowBitrateCount++;
                } else {
                    lowBitrateCount = Math.max(0, lowBitrateCount - 1);
                }
            } catch {}
        }, 5000);

        this.statsIntervals.set(peerId, interval);
    }

    stopBitrateMonitoring(peerId) {
        const interval = this.statsIntervals.get(peerId);
        if (interval) {
            clearInterval(interval);
            this.statsIntervals.delete(peerId);
        }
    }

    /**
     * Get network stats for a specific peer.
     */
    async getStats(peerId) {
        const peerState = this.peers.get(peerId);
        if (!peerState) return null;

        const stats = await peerState.pc.getStats();
        let result = { bytesReceived: 0, bytesSent: 0, packetsLost: 0, rtt: 0 };

        stats.forEach(report => {
            if (report.type === 'inbound-rtp' && report.kind === 'video') {
                result.bytesReceived += report.bytesReceived || 0;
                result.packetsLost += report.packetsLost || 0;
            }
            if (report.type === 'outbound-rtp' && report.kind === 'video') {
                result.bytesSent += report.bytesSent || 0;
            }
            if (report.type === 'candidate-pair' && report.state === 'succeeded') {
                result.rtt = report.currentRoundTripTime || 0;
            }
        });

        return result;
    }

    serializeDescription(description) {
        if (!description) return null;
        if (typeof description.toJSON === 'function') return description.toJSON();
        return { type: description.type, sdp: description.sdp };
    }

    /**
     * Resolve a stable remote stream for incoming tracks.
     * Some browsers can emit ontrack without event.streams[0] when transceivers
     * start without a bound sender track; we synthesize a peer stream in that case.
     */
    resolveRemoteStream(peerId, event) {
        const eventStream = event.streams?.[0];
        if (eventStream) {
            this.remoteStreams.set(peerId, eventStream);
            return eventStream;
        }

        let stream = this.remoteStreams.get(peerId);
        if (!stream) {
            stream = new MediaStream();
            this.remoteStreams.set(peerId, stream);
        }

        const incomingTrack = event.track;
        if (incomingTrack && !stream.getTracks().some((t) => t.id === incomingTrack.id)) {
            stream.addTrack(incomingTrack);
        }

        return stream;
    }

    findSenderByKind(pc, kind) {
        return pc.getSenders().find((s) => s.track?.kind === kind)
            || pc.getTransceivers().find((t) => t.receiver?.track?.kind === kind)?.sender
            || null;
    }

    /**
     * Clean up all peers.
     */
    destroy() {
        this.statsIntervals.forEach(interval => clearInterval(interval));
        this.statsIntervals.clear();
        this.peers.forEach(({ pc }) => pc.close());
        this.peers.clear();
        this.remoteStreams.clear();
    }
}
