/**
 * Conference Media Manager
 * Handles local media, audio processing, noise suppression, push-to-talk,
 * virtual backgrounds, adaptive bitrate, and video freeze detection.
 */
export class MediaManager {
    constructor(options = {}) {
        this.localStream = null;
        this.screenStream = null;
        this.audioContext = null;
        this.analyserNode = null;
        this.gainNode = null;

        // State
        this.isAudioEnabled = true;
        this.isVideoEnabled = true;
        this.isScreenSharing = false;
        this.isPushToTalk = false;
        this.isPTTActive = false;
        this.isAudioOnly = false;
        this.isLowBandwidth = false;
        this.isNoiseSuppression = true;

        // Video quality presets
        this.qualityPresets = {
            high: { width: 1920, height: 1080, frameRate: 60 },
            medium: { width: 1280, height: 720, frameRate: 30 },
            low: { width: 640, height: 480, frameRate: 15 },
            minimal: { width: 320, height: 240, frameRate: 10 },
        };
        this.currentQuality = options.defaultQuality || 'high';

        // Freeze detection
        this.freezeDetectionIntervals = new Map();
        this.onFreezeDetected = options.onFreezeDetected || null;

        // Audio level callback
        this.onAudioLevel = options.onAudioLevel || null;
        this.audioLevelInterval = null;
    }

    /**
     * Initialize local media (camera + mic).
     */
    async setupLocalMedia(audioOnly = false) {
        const mediaDevices = navigator?.mediaDevices;
        if (!mediaDevices?.getUserMedia) {
            const error = new Error('Media devices API is not available in this browser context.');
            error.name = 'NotSupportedError';
            throw error;
        }

        const preset = this.qualityPresets[this.currentQuality] || this.qualityPresets.high;
        const modes = audioOnly ? ['audio'] : ['audio-video', 'audio', 'video'];
        const failures = [];

        for (const mode of modes) {
            try {
                const constraints = this.buildMediaConstraints(mode, preset);
                const stream = await mediaDevices.getUserMedia(constraints);
                this.applyLocalStream(stream);
                return this.localStream;
            } catch (error) {
                failures.push({ mode, error });
            }
        }

        const finalError = failures[failures.length - 1]?.error || new Error('Unable to access media devices.');
        if (failures.length > 0) {
            const summary = failures
                .map(({ mode, error }) => `${mode}:${error?.name || 'Error'}`)
                .join(', ');
            console.warn(`[Media] getUserMedia failed (${summary})`);
        }
        throw finalError;
    }

    /**
     * Create constraints for the given acquisition mode.
     */
    buildMediaConstraints(mode, preset) {
        const audioConstraints = this.buildAudioConstraints();
        const videoConstraints = this.buildVideoConstraints(preset);

        if (mode === 'audio') {
            return { audio: audioConstraints, video: false };
        }

        if (mode === 'video') {
            return { audio: false, video: videoConstraints };
        }

        return { audio: audioConstraints, video: videoConstraints };
    }

    buildAudioConstraints(deviceId = null) {
        const constraints = {
            echoCancellation: true,
            noiseSuppression: this.isNoiseSuppression,
            autoGainControl: true,
        };

        if (deviceId) {
            constraints.deviceId = { exact: deviceId };
        }

        return constraints;
    }

    buildVideoConstraints(preset, deviceId = null) {
        const constraints = {
            width: { ideal: preset.width },
            height: { ideal: preset.height },
            frameRate: { ideal: preset.frameRate },
            facingMode: 'user',
        };

        if (deviceId) {
            constraints.deviceId = { exact: deviceId };
        }

        return constraints;
    }

    /**
     * Apply a newly acquired local stream and sync internal state.
     */
    applyLocalStream(stream) {
        if (this.localStream && this.localStream !== stream) {
            this.localStream.getTracks().forEach(track => track.stop());
        }

        this.localStream = stream;
        this.resetAudioAnalysis();
        this.syncStateFromLocalStream();

        this.setupAudioAnalysis();
    }

    syncStateFromLocalStream() {
        const audioTrack = this.localStream?.getAudioTracks?.()[0];
        const videoTrack = this.localStream?.getVideoTracks?.()[0];
        this.isAudioEnabled = Boolean(audioTrack?.enabled);
        this.isVideoEnabled = Boolean(videoTrack?.enabled);
        this.isAudioOnly = !videoTrack;
    }

    /**
     * Create and apply an empty stream (watch/listen-only join).
     */
    createEmptyLocalStream() {
        const stream = new MediaStream();
        this.applyLocalStream(stream);
        this.isAudioEnabled = false;
        this.isVideoEnabled = false;
        this.isAudioOnly = true;
        return stream;
    }

    /**
     * Ensure an audio track exists in the local stream (used for live recovery).
     */
    async ensureAudioTrack(deviceId = null) {
        return this.ensureTrack('audio', deviceId);
    }

    /**
     * Ensure a video track exists in the local stream (used for live recovery).
     */
    async ensureVideoTrack(deviceId = null) {
        return this.ensureTrack('video', deviceId);
    }

    /**
     * Ensure a specific track exists, requesting new device access if needed.
     */
    async ensureTrack(kind, deviceId = null) {
        const mediaDevices = navigator?.mediaDevices;
        if (!mediaDevices?.getUserMedia) return false;

        const isAudio = kind === 'audio';
        const existing = isAudio
            ? this.localStream?.getAudioTracks?.()[0]
            : this.localStream?.getVideoTracks?.()[0];

        if (existing) {
            existing.enabled = true;
            if (isAudio) this.isAudioEnabled = true;
            else this.isVideoEnabled = true;
            return true;
        }

        const preset = this.qualityPresets[this.currentQuality] || this.qualityPresets.high;
        const constraints = isAudio
            ? { audio: this.buildAudioConstraints(deviceId), video: false }
            : { audio: false, video: this.buildVideoConstraints(preset, deviceId) };

        try {
            const capture = await mediaDevices.getUserMedia(constraints);
            const track = isAudio ? capture.getAudioTracks()[0] : capture.getVideoTracks()[0];
            if (!track) {
                capture.getTracks().forEach(t => t.stop());
                return false;
            }

            if (!this.localStream) {
                this.createEmptyLocalStream();
            }

            track.enabled = true;
            this.localStream.addTrack(track);
            this.syncStateFromLocalStream();

            if (isAudio) {
                this.resetAudioAnalysis();
                this.setupAudioAnalysis();
            }

            capture.getTracks().forEach((t) => {
                if (t !== track) t.stop();
            });

            return true;
        } catch (error) {
            console.warn(`[Media] Failed to recover ${kind} track:`, error?.name || error);
            return false;
        }
    }

    /**
     * Clear current audio analysis resources.
     */
    resetAudioAnalysis() {
        if (this.audioLevelInterval) {
            clearInterval(this.audioLevelInterval);
            this.audioLevelInterval = null;
        }

        this.analyserNode = null;
        this.gainNode = null;

        if (this.audioContext) {
            this.audioContext.close().catch(() => {});
            this.audioContext = null;
        }
    }

    /**
     * Set up audio analysis for waveform/level indicator.
     */
    setupAudioAnalysis() {
        if (!this.localStream) return;
        const audioTrack = this.localStream.getAudioTracks()[0];
        if (!audioTrack) return;

        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const source = this.audioContext.createMediaStreamSource(this.localStream);
            this.analyserNode = this.audioContext.createAnalyser();
            this.analyserNode.fftSize = 256;
            this.analyserNode.smoothingTimeConstant = 0.8;
            source.connect(this.analyserNode);

            // Start monitoring audio levels
            if (this.onAudioLevel) {
                this.audioLevelInterval = setInterval(() => {
                    const level = this.getAudioLevel();
                    this.onAudioLevel(level);
                }, 100);
            }
        } catch (e) {
            console.warn('[Media] AudioContext setup failed:', e);
        }
    }

    /**
     * Get current audio level (0-1).
     */
    getAudioLevel() {
        if (!this.analyserNode) return 0;
        const data = new Uint8Array(this.analyserNode.frequencyBinCount);
        this.analyserNode.getByteFrequencyData(data);
        let sum = 0;
        for (let i = 0; i < data.length; i++) sum += data[i];
        return Math.min(1, (sum / data.length) / 128);
    }

    /**
     * Toggle microphone.
     */
    toggleAudio() {
        if (!this.localStream) return false;
        const track = this.localStream.getAudioTracks()[0];
        if (!track) return false;
        track.enabled = !track.enabled;
        this.isAudioEnabled = track.enabled;
        return this.isAudioEnabled;
    }

    /**
     * Toggle camera.
     */
    toggleVideo() {
        if (!this.localStream) return false;
        const track = this.localStream.getVideoTracks()[0];
        if (!track) return false;
        track.enabled = !track.enabled;
        this.isVideoEnabled = track.enabled;
        return this.isVideoEnabled;
    }

    /**
     * Force mute/unmute (from teacher command).
     */
    forceAudio(enabled) {
        if (!this.localStream) return;
        const track = this.localStream.getAudioTracks()[0];
        if (track) {
            track.enabled = enabled;
            this.isAudioEnabled = enabled;
        }
    }

    /**
     * Force cam on/off (from teacher command).
     */
    forceVideo(enabled) {
        if (!this.localStream) return;
        const track = this.localStream.getVideoTracks()[0];
        if (track) {
            track.enabled = enabled;
            this.isVideoEnabled = enabled;
        }
    }

    /**
     * Enable push-to-talk mode.
     */
    enablePushToTalk(enabled = true) {
        this.isPushToTalk = enabled;
        if (enabled && this.localStream) {
            const track = this.localStream.getAudioTracks()[0];
            if (track) {
                track.enabled = false;
                this.isAudioEnabled = false;
            }
        }
    }

    /**
     * Push-to-talk: key down = unmute.
     */
    pttDown() {
        if (!this.isPushToTalk || this.isPTTActive) return;
        this.isPTTActive = true;
        const track = this.localStream?.getAudioTracks()[0];
        if (track) {
            track.enabled = true;
            this.isAudioEnabled = true;
        }
    }

    /**
     * Push-to-talk: key up = mute.
     */
    pttUp() {
        if (!this.isPushToTalk || !this.isPTTActive) return;
        this.isPTTActive = false;
        const track = this.localStream?.getAudioTracks()[0];
        if (track) {
            track.enabled = false;
            this.isAudioEnabled = false;
        }
    }

    /**
     * Switch video quality.
     */
    async setQuality(quality) {
        if (!this.qualityPresets[quality]) return;
        this.currentQuality = quality;
        const preset = this.qualityPresets[quality];
        const videoTrack = this.localStream?.getVideoTracks()[0];
        if (!videoTrack) return;
        try {
            await videoTrack.applyConstraints({
                width: { ideal: preset.width },
                height: { ideal: preset.height },
                frameRate: { ideal: preset.frameRate },
            });
        } catch (e) {
            console.warn('[Media] Failed to apply quality constraints:', e);
        }
    }

    /**
     * Toggle low bandwidth mode.
     */
    async toggleLowBandwidth() {
        this.isLowBandwidth = !this.isLowBandwidth;
        await this.setQuality(this.isLowBandwidth ? 'minimal' : 'high');
        return this.isLowBandwidth;
    }

    /**
     * Start screen sharing.
     */
    async startScreenShare(captureAudio = true) {
        try {
            this.screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: { cursor: 'always' },
                audio: captureAudio,
            });
            this.isScreenSharing = true;

            const screenTrack = this.screenStream.getVideoTracks()[0];
            screenTrack.onended = () => this.stopScreenShare();

            return this.screenStream;
        } catch (error) {
            if (error.name !== 'NotAllowedError') {
                console.error('[Media] Screen share error:', error);
            }
            throw error;
        }
    }

    /**
     * Stop screen sharing.
     */
    stopScreenShare() {
        if (this.screenStream) {
            this.screenStream.getTracks().forEach(t => t.stop());
            this.screenStream = null;
        }
        this.isScreenSharing = false;
    }

    /**
     * Start monitoring a video element for freeze detection.
     */
    startFreezeDetection(peerId, videoElement) {
        if (this.freezeDetectionIntervals.has(peerId)) return;

        let lastTime = 0;
        let freezeCount = 0;

        const interval = setInterval(() => {
            if (!videoElement || videoElement.paused) return;
            const currentTime = videoElement.currentTime;
            if (currentTime === lastTime && !videoElement.paused && videoElement.readyState >= 2) {
                freezeCount++;
                if (freezeCount >= 3 && this.onFreezeDetected) {
                    this.onFreezeDetected(peerId);
                    freezeCount = 0;
                }
            } else {
                freezeCount = 0;
            }
            lastTime = currentTime;
        }, 2000);

        this.freezeDetectionIntervals.set(peerId, interval);
    }

    /**
     * Stop freeze detection for a peer.
     */
    stopFreezeDetection(peerId) {
        const interval = this.freezeDetectionIntervals.get(peerId);
        if (interval) {
            clearInterval(interval);
            this.freezeDetectionIntervals.delete(peerId);
        }
    }

    /**
     * Get network quality stats from a peer connection.
     */
    async getNetworkStats(peerConnection) {
        if (!peerConnection) return null;
        try {
            const stats = await peerConnection.getStats();
            let bytesReceived = 0, bytesSent = 0, packetsLost = 0, jitter = 0, rtt = 0;

            stats.forEach(report => {
                if (report.type === 'inbound-rtp' && report.kind === 'video') {
                    bytesReceived += report.bytesReceived || 0;
                    packetsLost += report.packetsLost || 0;
                    jitter = report.jitter || 0;
                }
                if (report.type === 'outbound-rtp' && report.kind === 'video') {
                    bytesSent += report.bytesSent || 0;
                }
                if (report.type === 'candidate-pair' && report.state === 'succeeded') {
                    rtt = report.currentRoundTripTime || 0;
                }
            });

            const quality = rtt < 0.1 && packetsLost < 5 ? 'good'
                : rtt < 0.3 && packetsLost < 20 ? 'fair' : 'poor';

            return { bytesReceived, bytesSent, packetsLost, jitter, rtt, quality };
        } catch {
            return null;
        }
    }

    /**
     * Clean up all media resources.
     */
    destroy() {
        this.resetAudioAnalysis();
        this.freezeDetectionIntervals.forEach(interval => clearInterval(interval));
        this.freezeDetectionIntervals.clear();

        if (this.localStream) {
            this.localStream.getTracks().forEach(t => t.stop());
            this.localStream = null;
        }
        if (this.screenStream) {
            this.screenStream.getTracks().forEach(t => t.stop());
            this.screenStream = null;
        }
    }
}
