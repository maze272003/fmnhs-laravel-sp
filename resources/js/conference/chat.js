/**
 * Conference Chat Manager
 * Handles persistent chat, file uploads, and chat replay.
 */
export class ChatManager {
    constructor(options = {}) {
        this.conferenceSlug = options.conferenceSlug || '';
        this.csrfToken = options.csrfToken || '';
        this.baseUrl = options.baseUrl || '';
        this.messages = [];
        this.onMessage = options.onMessage || null;
    }

    /**
     * Persist a message to the server.
     */
    async saveMessage(content) {
        try {
            const response = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ content }),
            });
            if (response.ok) {
                const data = await response.json();
                this.messages.push(data);
                return data;
            }
        } catch (e) {
            console.warn('[Chat] Failed to persist message:', e);
        }
        return null;
    }

    /**
     * Upload a file via chat.
     */
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/files`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            if (response.ok) {
                return response.json();
            }
        } catch (e) {
            console.warn('[Chat] File upload failed:', e);
        }
        return null;
    }

    /**
     * Load chat history from server.
     */
    async loadHistory() {
        try {
            const response = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/messages`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });
            if (response.ok) {
                const data = await response.json();
                this.messages = data.messages || [];
                return this.messages;
            }
        } catch (e) {
            console.warn('[Chat] Failed to load history:', e);
        }
        return [];
    }

    /**
     * Format file size.
     */
    static formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    /**
     * Check if a mime type is an image.
     */
    static isImage(mime) {
        return mime && mime.startsWith('image/');
    }
}
