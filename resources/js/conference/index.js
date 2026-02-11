import { ConferenceApp } from './app.js';

if (typeof window !== 'undefined') {
    window.ConferenceApp = ConferenceApp;
}

export { ConferenceApp };
export { SignalingClient } from './signaling.js';
export { MediaManager } from './media.js';
export { PeerManager } from './peers.js';
export { RecordingManager } from './recording.js';
export { ChatManager } from './chat.js';
export { NotificationManager } from './notifications.js';
