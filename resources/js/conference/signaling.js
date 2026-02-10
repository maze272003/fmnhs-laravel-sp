/**
 * Conference Signaling Client
 * Handles WebSocket connection, authentication, and message routing.
 */
export class SignalingClient {
    constructor(config) {
        this.url = config.url;
        this.roomId = config.roomId;
        this.token = config.token;
        this.ws = null;
        this.reconnectTimer = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 15;
        this.isShuttingDown = false;
        this.hasJoined = false;
        this.handlers = new Map();
        this.messageQueue = [];
    }

    on(type, handler) {
        if (!this.handlers.has(type)) this.handlers.set(type, []);
        this.handlers.get(type).push(handler);
        return this;
    }

    off(type, handler) {
        const list = this.handlers.get(type);
        if (list) {
            const idx = list.indexOf(handler);
            if (idx !== -1) list.splice(idx, 1);
        }
        return this;
    }

    emit(type, data) {
        const list = this.handlers.get(type) || [];
        list.forEach(fn => {
            try { fn(data); } catch (e) { console.error(`[Signaling] Handler error for ${type}:`, e); }
        });
    }

    connect() {
        if (!this.url) {
            this.emit('error', { code: 'no-url', message: 'Missing signaling URL' });
            return;
        }
        if (this.ws && (this.ws.readyState === WebSocket.OPEN || this.ws.readyState === WebSocket.CONNECTING)) {
            return;
        }

        this.ws = new WebSocket(this.url);

        this.ws.onopen = () => {
            this.reconnectAttempts = 0;
            this.hasJoined = false;
            this.emit('connected', {});
            this.send({ type: 'join', roomId: this.roomId, token: this.token });
        };

        this.ws.onclose = (event) => {
            if (this.isShuttingDown) return;
            this.hasJoined = false;
            this.emit('disconnected', { code: event.code, reason: event.reason });
            this.scheduleReconnect();
        };

        this.ws.onerror = (error) => {
            this.emit('ws-error', error);
        };

        this.ws.onmessage = (event) => {
            let msg;
            try { msg = JSON.parse(event.data); } catch { return; }
            if (!msg || !msg.type) return;

            if (msg.type === 'joined') {
                this.hasJoined = true;
                this.flushQueue();
            }

            this.emit(msg.type, msg);
        };
    }

    send(payload) {
        if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
            if (payload.type !== 'join') this.messageQueue.push(payload);
            return false;
        }
        this.ws.send(JSON.stringify(payload));
        return true;
    }

    flushQueue() {
        while (this.messageQueue.length > 0) {
            const msg = this.messageQueue.shift();
            this.send(msg);
        }
    }

    scheduleReconnect() {
        if (this.isShuttingDown || this.reconnectTimer) return;
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            this.emit('reconnect-failed', {});
            return;
        }
        const delay = Math.min(8000, 1000 * (2 ** this.reconnectAttempts));
        this.reconnectAttempts++;
        this.reconnectTimer = setTimeout(() => {
            this.reconnectTimer = null;
            this.connect();
        }, delay);
    }

    disconnect() {
        this.isShuttingDown = true;
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.close();
        }
        this.ws = null;
    }

    get isConnected() {
        return this.ws && this.ws.readyState === WebSocket.OPEN && this.hasJoined;
    }
}
