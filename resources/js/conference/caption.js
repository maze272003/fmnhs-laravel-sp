export class CaptionOverlay {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.baseUrl = config.baseUrl || '';
        
        this.signaling = config.signaling || null;
        
        this.isEnabled = false;
        this.language = config.language || 'en';
        this.fontSize = config.fontSize || 18;
        this.position = config.position || 'bottom';
        this.captions = [];
        this.maxCaptions = 5;
        this.listeners = new Map();
        
        this.settings = {
            showSpeaker: true,
            maxLineLength: 60,
            displayDuration: 8000,
            backgroundColor: 'rgba(0, 0, 0, 0.7)',
            textColor: '#ffffff',
        };
        
        this.setupSignalingHandlers();
    }
    
    setupSignalingHandlers() {
        if (!this.signaling) return;
        
        this.signaling.on('caption', (msg) => {
            this.handleCaption(msg);
        });
        
        this.signaling.on('caption-clear', (msg) => {
            this.clear();
        });
    }
    
    enable() {
        this.isEnabled = true;
        this.emit('enabled');
    }
    
    disable() {
        this.isEnabled = false;
        this.emit('disabled');
    }
    
    toggle() {
        this.isEnabled = !this.isEnabled;
        this.emit(this.isEnabled ? 'enabled' : 'disabled');
        return this.isEnabled;
    }
    
    setLanguage(lang) {
        this.language = lang;
        this.emit('language-changed', lang);
    }
    
    setFontSize(size) {
        this.fontSize = Math.max(12, Math.min(32, size));
        this.emit('font-size-changed', this.fontSize);
    }
    
    setPosition(position) {
        const validPositions = ['top', 'bottom', 'top-left', 'top-right', 'bottom-left', 'bottom-right'];
        if (validPositions.includes(position)) {
            this.position = position;
            this.emit('position-changed', this.position);
        }
    }
    
    addCaption(text, speaker = null, language = null) {
        if (!this.isEnabled) return;
        
        const caption = {
            id: Date.now(),
            text: this.formatText(text),
            speaker: speaker,
            language: language || this.language,
            timestamp: Date.now(),
        };
        
        this.captions.push(caption);
        
        if (this.captions.length > this.maxCaptions) {
            this.captions.shift();
        }
        
        if (this.signaling) {
            this.signaling.send({
                type: 'caption',
                caption: caption,
            });
        }
        
        this.emit('caption', caption);
        
        setTimeout(() => {
            this.removeCaption(caption.id);
        }, this.settings.displayDuration);
        
        return caption;
    }
    
    removeCaption(id) {
        const index = this.captions.findIndex(c => c.id === id);
        if (index > -1) {
            this.captions.splice(index, 1);
            this.emit('caption-removed', id);
        }
    }
    
    handleCaption(msg) {
        if (!this.isEnabled) return;
        
        const caption = msg.caption;
        this.captions.push(caption);
        
        if (this.captions.length > this.maxCaptions) {
            this.captions.shift();
        }
        
        this.emit('caption', caption);
        
        setTimeout(() => {
            this.removeCaption(caption.id);
        }, this.settings.displayDuration);
    }
    
    formatText(text) {
        let formatted = text.trim();
        
        if (formatted.length > this.settings.maxLineLength) {
            const words = formatted.split(' ');
            const lines = [];
            let currentLine = '';
            
            words.forEach(word => {
                if ((currentLine + ' ' + word).length <= this.settings.maxLineLength) {
                    currentLine = currentLine ? currentLine + ' ' + word : word;
                } else {
                    if (currentLine) lines.push(currentLine);
                    currentLine = word;
                }
            });
            
            if (currentLine) lines.push(currentLine);
            formatted = lines.join('\n');
        }
        
        return formatted;
    }
    
    clear() {
        this.captions = [];
        this.emit('cleared');
    }
    
    render() {
        return {
            captions: this.captions,
            settings: {
                fontSize: this.fontSize,
                position: this.position,
                ...this.settings,
            },
        };
    }
    
    async getHistory(limit = 100) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/captions?limit=${limit}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load caption history');
            return await res.json();
        } catch (e) {
            console.error('[CaptionOverlay] Load history error:', e);
            return [];
        }
    }
    
    async searchCaptions(query) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/captions/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to search captions');
            return await res.json();
        } catch (e) {
            console.error('[CaptionOverlay] Search error:', e);
            return [];
        }
    }
    
    on(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }
    
    off(event, callback) {
        if (!this.listeners.has(event)) return;
        const callbacks = this.listeners.get(event);
        const index = callbacks.indexOf(callback);
        if (index > -1) callbacks.splice(index, 1);
    }
    
    emit(event, data) {
        if (!this.listeners.has(event)) return;
        this.listeners.get(event).forEach(callback => {
            try {
                callback(data);
            } catch (e) {
                console.error(`[CaptionOverlay] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        this.captions = [];
        this.listeners.clear();
    }
}
