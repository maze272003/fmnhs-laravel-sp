export class MoodMeter {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.actorRole = config.actorRole;
        this.actorId = config.actorId;
        this.baseUrl = config.baseUrl || '';
        
        this.signaling = config.signaling || null;
        
        this.speedFeedback = 'just-right';
        this.understandingLevel = 3;
        this.confidenceLevel = 3;
        this.moodHistory = [];
        this.aggregateData = {
            tooFast: 0,
            justRight: 0,
            tooSlow: 0,
            avgUnderstanding: 0,
            avgConfidence: 0,
            responseCount: 0,
        };
        this.listeners = new Map();
        
        this.setupSignalingHandlers();
    }
    
    setupSignalingHandlers() {
        if (!this.signaling) return;
        
        this.signaling.on('mood-speed', (msg) => {
            this.handleSpeedFeedback(msg);
        });
        
        this.signaling.on('mood-understanding', (msg) => {
            this.handleUnderstandingFeedback(msg);
        });
        
        this.signaling.on('mood-confidence', (msg) => {
            this.handleConfidenceFeedback(msg);
        });
        
        this.signaling.on('mood-aggregate', (msg) => {
            this.aggregateData = msg.data;
            this.emit('aggregate-updated', this.aggregateData);
        });
    }
    
    setSpeed(speed) {
        const validSpeeds = ['too-fast', 'just-right', 'too-slow'];
        if (!validSpeeds.includes(speed)) return;
        
        this.speedFeedback = speed;
        
        if (this.signaling) {
            this.signaling.send({
                type: 'mood-speed',
                speed: speed,
            });
        }
        
        this.recordMood('speed', speed);
        this.emit('speed-changed', speed);
    }
    
    setUnderstanding(level) {
        const clamped = Math.max(1, Math.min(5, level));
        this.understandingLevel = clamped;
        
        if (this.signaling) {
            this.signaling.send({
                type: 'mood-understanding',
                level: clamped,
            });
        }
        
        this.recordMood('understanding', clamped);
        this.emit('understanding-changed', clamped);
    }
    
    setConfidence(level) {
        const clamped = Math.max(1, Math.min(5, level));
        this.confidenceLevel = clamped;
        
        if (this.signaling) {
            this.signaling.send({
                type: 'mood-confidence',
                level: clamped,
            });
        }
        
        this.recordMood('confidence', clamped);
        this.emit('confidence-changed', clamped);
    }
    
    quickFeedback(type) {
        switch (type) {
            case 'too-fast':
                this.setSpeed('too-fast');
                break;
            case 'too-slow':
                this.setSpeed('too-slow');
                break;
            case 'got-it':
                this.setUnderstanding(5);
                break;
            case 'confused':
                this.setUnderstanding(1);
                break;
            case 'need-help':
                this.setConfidence(1);
                this.emit('help-requested');
                break;
        }
    }
    
    recordMood(type, value) {
        this.moodHistory.push({
            type,
            value,
            timestamp: Date.now(),
        });
        
        if (this.moodHistory.length > 100) {
            this.moodHistory.shift();
        }
    }
    
    handleSpeedFeedback(msg) {
        if (msg.from?.id === this.actorId) return;
        
        const speed = msg.speed;
        if (speed === 'too-fast') this.aggregateData.tooFast++;
        else if (speed === 'just-right') this.aggregateData.justRight++;
        else if (speed === 'too-slow') this.aggregateData.tooSlow++;
        
        this.aggregateData.responseCount++;
        this.emit('peer-speed', { from: msg.from, speed: speed });
        this.emit('aggregate-updated', this.aggregateData);
    }
    
    handleUnderstandingFeedback(msg) {
        if (msg.from?.id === this.actorId) return;
        
        this.updateAverage('avgUnderstanding', msg.level);
        this.emit('peer-understanding', { from: msg.from, level: msg.level });
    }
    
    handleConfidenceFeedback(msg) {
        if (msg.from?.id === this.actorId) return;
        
        this.updateAverage('avgConfidence', msg.level);
        this.emit('peer-confidence', { from: msg.from, level: msg.level });
    }
    
    updateAverage(key, newValue) {
        const count = this.aggregateData.responseCount || 1;
        const current = this.aggregateData[key] || 0;
        this.aggregateData[key] = (current * (count - 1) + newValue) / count;
        this.aggregateData.responseCount = count + 1;
        this.emit('aggregate-updated', this.aggregateData);
    }
    
    async loadAggregate() {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/mood/aggregate`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load aggregate');
            const data = await res.json();
            
            this.aggregateData = data;
            this.emit('aggregate-updated', this.aggregateData);
            return data;
        } catch (e) {
            console.error('[MoodMeter] Load error:', e);
            return null;
        }
    }
    
    async saveMoodRating(type, value) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/mood`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    type: type,
                    value: value,
                }),
            });
            
            if (!res.ok) throw new Error('Failed to save mood');
            return await res.json();
        } catch (e) {
            console.error('[MoodMeter] Save error:', e);
            return null;
        }
    }
    
    getSpeedCounts() {
        return {
            tooFast: this.aggregateData.tooFast,
            justRight: this.aggregateData.justRight,
            tooSlow: this.aggregateData.tooSlow,
        };
    }
    
    getAverageUnderstanding() {
        return this.aggregateData.avgUnderstanding;
    }
    
    getAverageConfidence() {
        return this.aggregateData.avgConfidence;
    }
    
    getSpeedEmoji(speed) {
        switch (speed) {
            case 'too-fast': return '🐇';
            case 'just-right': return '👍';
            case 'too-slow': return '🐢';
            default: return '❓';
        }
    }
    
    getUnderstandingEmoji(level) {
        if (level >= 5) return '😊';
        if (level >= 4) return '🙂';
        if (level >= 3) return '😐';
        if (level >= 2) return '😕';
        return '😰';
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
                console.error(`[MoodMeter] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        this.listeners.clear();
    }
}
