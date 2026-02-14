export class PresentationManager {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.actorRole = config.actorRole;
        this.actorId = config.actorId;
        this.baseUrl = config.baseUrl || '';
        
        this.signaling = config.signaling || null;
        
        this.presentation = null;
        this.slides = [];
        this.currentSlideIndex = 0;
        this.annotations = [];
        this.isAnnotationMode = false;
        this.listeners = new Map();
        
        this.slideViewers = new Map();
        
        this.setupSignalingHandlers();
    }
    
    setupSignalingHandlers() {
        if (!this.signaling) return;
        
        this.signaling.on('presentation-started', (msg) => {
            this.handlePresentationStarted(msg);
        });
        
        this.signaling.on('slide-changed', (msg) => {
            this.handleSlideChanged(msg);
        });
        
        this.signaling.on('slide-annotate', (msg) => {
            this.handleSlideAnnotate(msg);
        });
        
        this.signaling.on('presentation-ended', (msg) => {
            this.handlePresentationEnded(msg);
        });
        
        this.signaling.on('slide-progress', (msg) => {
            this.handleSlideProgress(msg);
        });
    }
    
    async uploadPresentation(file) {
        const formData = new FormData();
        formData.append('presentation', file);
        formData.append('conference_slug', this.conferenceSlug);
        
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/presentations`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: formData,
            });
            
            if (!res.ok) throw new Error('Failed to upload presentation');
            const data = await res.json();
            
            this.presentation = data.presentation;
            this.slides = data.slides || [];
            
            return data;
        } catch (e) {
            console.error('[PresentationManager] Upload error:', e);
            return null;
        }
    }
    
    async loadPresentation(presentationId) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/presentations/${presentationId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load presentation');
            const data = await res.json();
            
            this.presentation = data.presentation;
            this.slides = data.slides || [];
            
            return data;
        } catch (e) {
            console.error('[PresentationManager] Load error:', e);
            return null;
        }
    }
    
    startPresentation() {
        if (!this.presentation) return;
        
        this.currentSlideIndex = 0;
        
        if (this.signaling) {
            this.signaling.send({
                type: 'presentation-started',
                presentation: this.presentation,
                slides: this.slides,
                currentSlide: this.currentSlideIndex,
            });
        }
        
        this.emit('started', {
            presentation: this.presentation,
            slides: this.slides,
            currentSlide: this.currentSlideIndex,
        });
    }
    
    endPresentation() {
        if (this.signaling) {
            this.signaling.send({ type: 'presentation-ended' });
        }
        
        this.emit('ended');
        this.presentation = null;
        this.slides = [];
        this.currentSlideIndex = 0;
    }
    
    goToSlide(index) {
        if (index < 0 || index >= this.slides.length) return;
        
        this.currentSlideIndex = index;
        this.annotations = [];
        
        if (this.signaling) {
            this.signaling.send({
                type: 'slide-changed',
                slideIndex: index,
                slide: this.slides[index],
            });
        }
        
        this.emit('slide-changed', {
            index: index,
            slide: this.slides[index],
            total: this.slides.length,
        });
    }
    
    nextSlide() {
        if (this.currentSlideIndex < this.slides.length - 1) {
            this.goToSlide(this.currentSlideIndex + 1);
        }
    }
    
    previousSlide() {
        if (this.currentSlideIndex > 0) {
            this.goToSlide(this.currentSlideIndex - 1);
        }
    }
    
    addAnnotation(annotation) {
        this.annotations.push({
            ...annotation,
            slideIndex: this.currentSlideIndex,
            timestamp: Date.now(),
        });
        
        if (this.signaling) {
            this.signaling.send({
                type: 'slide-annotate',
                annotation: annotation,
                slideIndex: this.currentSlideIndex,
            });
        }
        
        this.emit('annotation', annotation);
    }
    
    clearAnnotations() {
        this.annotations = [];
        
        if (this.signaling) {
            this.signaling.send({
                type: 'slide-annotate',
                action: 'clear',
                slideIndex: this.currentSlideIndex,
            });
        }
        
        this.emit('annotations-cleared');
    }
    
    setAnnotationMode(enabled) {
        this.isAnnotationMode = enabled;
        this.emit('annotation-mode', enabled);
    }
    
    getCurrentSlide() {
        return this.slides[this.currentSlideIndex] || null;
    }
    
    getProgress() {
        return {
            current: this.currentSlideIndex + 1,
            total: this.slides.length,
            percent: this.slides.length > 0 
                ? Math.round((this.currentSlideIndex + 1) / this.slides.length * 100)
                : 0,
        };
    }
    
    reportProgress() {
        if (this.signaling) {
            this.signaling.send({
                type: 'slide-progress',
                slideIndex: this.currentSlideIndex,
            });
        }
    }
    
    handlePresentationStarted(msg) {
        this.presentation = msg.presentation;
        this.slides = msg.slides || [];
        this.currentSlideIndex = msg.currentSlide || 0;
        
        this.emit('started', {
            presentation: this.presentation,
            slides: this.slides,
            currentSlide: this.currentSlideIndex,
        });
    }
    
    handleSlideChanged(msg) {
        this.currentSlideIndex = msg.slideIndex;
        this.annotations = [];
        
        this.emit('slide-changed', {
            index: msg.slideIndex,
            slide: msg.slide,
            total: this.slides.length,
        });
    }
    
    handleSlideAnnotate(msg) {
        if (msg.action === 'clear') {
            this.annotations = [];
            this.emit('annotations-cleared');
        } else if (msg.annotation) {
            this.annotations.push(msg.annotation);
            this.emit('annotation', msg.annotation);
        }
    }
    
    handlePresentationEnded(msg) {
        this.emit('ended');
        this.presentation = null;
        this.slides = [];
        this.currentSlideIndex = 0;
    }
    
    handleSlideProgress(msg) {
        if (msg.from?.id !== this.actorId) {
            this.slideViewers.set(msg.from?.id, msg.slideIndex);
            this.emit('viewer-progress', {
                from: msg.from,
                slideIndex: msg.slideIndex,
            });
        }
    }
    
    getViewerProgress() {
        const progress = {};
        this.slideViewers.forEach((slideIndex, viewerId) => {
            progress[viewerId] = slideIndex;
        });
        return progress;
    }
    
    async getSlideAnalytics(slideIndex) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/presentations/${this.presentation?.id}/slides/${slideIndex}/analytics`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load slide analytics');
            return await res.json();
        } catch (e) {
            console.error('[PresentationManager] Analytics error:', e);
            return null;
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
                console.error(`[PresentationManager] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        this.slides = [];
        this.annotations = [];
        this.listeners.clear();
    }
}
