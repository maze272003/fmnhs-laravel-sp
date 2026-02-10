/**
 * Conference Recording Manager
 * Handles client-side MediaRecorder for local and cloud recording.
 */
export class RecordingManager {
    constructor(options = {}) {
        this.mediaRecorder = null;
        this.recordedChunks = [];
        this.isRecording = false;
        this.recordingStartTime = null;
        this.recordingType = 'video'; // video or audio
        this.mimeType = this.getSupportedMimeType();
        this.onDataAvailable = options.onDataAvailable || null;
        this.onRecordingStopped = options.onRecordingStopped || null;

        // Conference API
        this.conferenceSlug = options.conferenceSlug || '';
        this.csrfToken = options.csrfToken || '';
        this.uploadUrl = options.uploadUrl || '';
    }

    getSupportedMimeType() {
        const types = [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
            'video/mp4',
        ];
        for (const type of types) {
            if (MediaRecorder.isTypeSupported(type)) return type;
        }
        return 'video/webm';
    }

    /**
     * Start recording a MediaStream.
     */
    startRecording(stream, type = 'video') {
        if (this.isRecording) return false;
        this.recordingType = type;
        this.recordedChunks = [];

        const mimeType = type === 'audio' ? 'audio/webm' : this.mimeType;

        try {
            this.mediaRecorder = new MediaRecorder(stream, {
                mimeType,
                videoBitsPerSecond: type === 'video' ? 2500000 : undefined,
                audioBitsPerSecond: 128000,
            });
        } catch (e) {
            this.mediaRecorder = new MediaRecorder(stream);
        }

        this.mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                this.recordedChunks.push(event.data);
                if (this.onDataAvailable) this.onDataAvailable(event.data);
            }
        };

        this.mediaRecorder.onstop = () => {
            this.isRecording = false;
            if (this.onRecordingStopped) {
                this.onRecordingStopped(this.getBlob());
            }
        };

        this.mediaRecorder.start(1000); // Collect data every second
        this.isRecording = true;
        this.recordingStartTime = Date.now();
        return true;
    }

    /**
     * Stop recording.
     */
    stopRecording() {
        if (!this.isRecording || !this.mediaRecorder) return null;
        this.mediaRecorder.stop();
        return this.getDuration();
    }

    /**
     * Get recorded blob.
     */
    getBlob() {
        const mimeType = this.recordingType === 'audio' ? 'audio/webm' : this.mimeType;
        return new Blob(this.recordedChunks, { type: mimeType });
    }

    /**
     * Get recording duration in seconds.
     */
    getDuration() {
        if (!this.recordingStartTime) return 0;
        return Math.round((Date.now() - this.recordingStartTime) / 1000);
    }

    /**
     * Download recording locally.
     */
    downloadLocal(fileName = null) {
        const blob = this.getBlob();
        const ext = this.recordingType === 'audio' ? 'webm' : 'webm';
        const name = fileName || `recording-${Date.now()}.${ext}`;
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = name;
        a.click();
        URL.revokeObjectURL(url);
    }

    /**
     * Upload recording to server (cloud recording).
     */
    async uploadToServer(title = null) {
        const blob = this.getBlob();
        if (!blob.size) throw new Error('No recording data');

        const formData = new FormData();
        const ext = this.recordingType === 'audio' ? 'webm' : 'webm';
        formData.append('recording', blob, `recording.${ext}`);
        formData.append('type', this.recordingType);
        if (title) formData.append('title', title);

        const response = await fetch(this.uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (!response.ok) {
            throw new Error(`Upload failed: ${response.status}`);
        }

        return response.json();
    }

    /**
     * Create a combined stream from video + audio for recording.
     */
    static createCombinedStream(videoStream, audioStream) {
        const tracks = [];
        if (videoStream) {
            videoStream.getVideoTracks().forEach(t => tracks.push(t));
        }
        if (audioStream) {
            audioStream.getAudioTracks().forEach(t => tracks.push(t));
        }
        return new MediaStream(tracks);
    }

    /**
     * Clean up.
     */
    destroy() {
        if (this.isRecording && this.mediaRecorder) {
            try { this.mediaRecorder.stop(); } catch {}
        }
        this.recordedChunks = [];
        this.mediaRecorder = null;
    }
}
