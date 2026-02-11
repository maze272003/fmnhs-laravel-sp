/**
 * Conference Notification Manager
 * Handles smart alerts, join notifications, and notification preferences.
 */
export class NotificationManager {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || '';
        this.csrfToken = options.csrfToken || '';
        this.actorRole = options.actorRole || 'participant';
        this.soundEnabled = true;
        this.browserNotificationsEnabled = false;
        this.silentJoin = false;

        // Sounds
        this.sounds = {
            join: null,
            leave: null,
            message: null,
            hand: null,
            alert: null,
        };

        this.initSounds();
        this.requestBrowserPermission();
    }

    initSounds() {
        // Create minimal beep sounds using AudioContext
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            this.audioCtx = ctx;
        } catch {}
    }

    playBeep(frequency = 440, duration = 150, volume = 0.3) {
        if (!this.soundEnabled || !this.audioCtx) return;
        try {
            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            osc.connect(gain);
            gain.connect(this.audioCtx.destination);
            osc.frequency.value = frequency;
            gain.gain.value = volume;
            osc.start();
            osc.stop(this.audioCtx.currentTime + duration / 1000);
        } catch {}
    }

    async requestBrowserPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            try {
                const result = await Notification.requestPermission();
                this.browserNotificationsEnabled = result === 'granted';
            } catch {}
        } else if ('Notification' in window) {
            this.browserNotificationsEnabled = Notification.permission === 'granted';
        }
    }

    /**
     * Show a browser notification (when tab is not focused).
     */
    showBrowserNotification(title, body, icon = null) {
        if (!this.browserNotificationsEnabled || document.hasFocus()) return;
        try {
            new Notification(title, { body, icon, silent: false });
        } catch {}
    }

    /**
     * Notify on participant join.
     */
    notifyJoin(name, role) {
        if (this.silentJoin) return;
        this.playBeep(600, 100, 0.2);
        if (this.actorRole === 'teacher') {
            this.showBrowserNotification('Participant Joined', `${name} (${role}) joined the meeting`);
        }
    }

    /**
     * Notify on participant leave.
     */
    notifyLeave(name) {
        this.playBeep(300, 150, 0.15);
    }

    /**
     * Notify on new chat message.
     */
    notifyMessage(name, message) {
        this.playBeep(800, 80, 0.15);
        this.showBrowserNotification('New Message', `${name}: ${message}`);
    }

    /**
     * Notify on hand raise.
     */
    notifyHandRaise(name) {
        this.playBeep(500, 200, 0.25);
        if (this.actorRole === 'teacher') {
            this.showBrowserNotification('Hand Raised', `${name} raised their hand`);
        }
    }

    /**
     * Network warning alert.
     */
    notifyNetworkWarning(message) {
        this.playBeep(200, 300, 0.3);
    }

    /**
     * Speaker attention alert (teacher only).
     */
    notifySpeakerAttention(name) {
        if (this.actorRole !== 'teacher') return;
        this.playBeep(400, 250, 0.25);
        this.showBrowserNotification('Attention Alert', `${name} may not be paying attention`);
    }

    /**
     * Toggle sound.
     */
    toggleSound() {
        this.soundEnabled = !this.soundEnabled;
        return this.soundEnabled;
    }

    /**
     * Toggle silent join mode.
     */
    toggleSilentJoin() {
        this.silentJoin = !this.silentJoin;
        return this.silentJoin;
    }

    /**
     * Fetch unread server notifications.
     */
    async fetchUnread() {
        try {
            const response = await fetch(`${this.baseUrl}/conference/notifications`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });
            if (response.ok) return response.json();
        } catch {}
        return { notifications: [] };
    }

    /**
     * Mark notifications as read.
     */
    async markRead(ids) {
        try {
            await fetch(`${this.baseUrl}/conference/notifications/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids }),
            });
        } catch {}
    }
}
