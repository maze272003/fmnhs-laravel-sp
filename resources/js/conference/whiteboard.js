export class WhiteboardManager {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.canvas = config.canvas;
        this.ctx = this.canvas?.getContext('2d');
        
        this.signaling = config.signaling || null;
        this.baseUrl = config.baseUrl || '';
        
        this.isDrawing = false;
        this.currentTool = 'pen';
        this.currentColor = '#10b981';
        this.currentWidth = 3;
        this.strokes = [];
        this.undoStack = [];
        this.listeners = new Map();
        
        this.backgroundColor = '#1e293b';
        
        if (this.canvas) {
            this.setupCanvas();
            this.setupEventListeners();
        }
        
        this.setupSignalingHandlers();
    }
    
    setupCanvas() {
        this.resizeCanvas();
        window.addEventListener('resize', () => this.resizeCanvas());
        this.clearCanvas();
    }
    
    resizeCanvas() {
        if (!this.canvas) return;
        const parent = this.canvas.parentElement;
        if (!parent) return;
        
        const savedStrokes = [...this.strokes];
        
        this.canvas.width = parent.clientWidth;
        this.canvas.height = parent.clientHeight;
        
        this.redrawAll();
    }
    
    setupEventListeners() {
        if (!this.canvas) return;
        
        this.canvas.addEventListener('pointerdown', (e) => this.handlePointerDown(e));
        this.canvas.addEventListener('pointermove', (e) => this.handlePointerMove(e));
        this.canvas.addEventListener('pointerup', () => this.handlePointerUp());
        this.canvas.addEventListener('pointerleave', () => this.handlePointerUp());
    }
    
    setupSignalingHandlers() {
        if (!this.signaling) return;
        
        this.signaling.on('whiteboard-draw', (msg) => {
            if (msg.from?.id !== this.actorId) {
                this.drawStroke(msg.stroke);
                this.strokes.push(msg.stroke);
            }
        });
        
        this.signaling.on('whiteboard-clear', (msg) => {
            this.clearCanvas();
            this.strokes = [];
            this.undoStack = [];
            this.emit('cleared', { from: msg.from });
        });
        
        this.signaling.on('whiteboard-undo', (msg) => {
            if (this.strokes.length > 0) {
                this.strokes.pop();
                this.redrawAll();
            }
        });
        
        this.signaling.on('whiteboard-sync', (msg) => {
            this.strokes = msg.strokes || [];
            this.redrawAll();
        });
    }
    
    handlePointerDown(e) {
        if (!this.canvas) return;
        this.isDrawing = true;
        const pos = this.getCanvasPosition(e);
        this.lastPos = pos;
        
        if (this.currentTool === 'text') {
            this.handleTextTool(pos);
        }
    }
    
    handlePointerMove(e) {
        if (!this.isDrawing || !this.canvas) return;
        
        const pos = this.getCanvasPosition(e);
        
        const stroke = {
            type: this.currentTool,
            x1: this.lastPos.x,
            y1: this.lastPos.y,
            x2: pos.x,
            y2: pos.y,
            color: this.currentColor,
            width: this.currentWidth,
            opacity: this.currentTool === 'highlighter' ? 0.4 : 1,
        };
        
        this.drawStroke(stroke);
        this.strokes.push(stroke);
        this.broadcastStroke(stroke);
        
        this.lastPos = pos;
    }
    
    handlePointerUp() {
        this.isDrawing = false;
        this.lastPos = null;
    }
    
    getCanvasPosition(e) {
        if (!this.canvas) return { x: 0, y: 0 };
        const rect = this.canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top,
        };
    }
    
    drawStroke(stroke) {
        if (!this.ctx) return;
        
        if (stroke.type === 'clear') {
            this.clearCanvas();
            return;
        }
        
        this.ctx.save();
        this.ctx.globalAlpha = stroke.opacity || 1;
        this.ctx.strokeStyle = stroke.color || '#10b981';
        this.ctx.lineWidth = stroke.width || 3;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';
        
        if (stroke.type === 'line' || stroke.type === 'pen' || stroke.type === 'highlighter') {
            this.ctx.beginPath();
            this.ctx.moveTo(stroke.x1, stroke.y1);
            this.ctx.lineTo(stroke.x2, stroke.y2);
            this.ctx.stroke();
        } else if (stroke.type === 'rectangle') {
            this.ctx.strokeRect(
                Math.min(stroke.x1, stroke.x2),
                Math.min(stroke.y1, stroke.y2),
                Math.abs(stroke.x2 - stroke.x1),
                Math.abs(stroke.y2 - stroke.y1)
            );
        } else if (stroke.type === 'circle') {
            const radius = Math.sqrt(
                Math.pow(stroke.x2 - stroke.x1, 2) + Math.pow(stroke.y2 - stroke.y1, 2)
            );
            this.ctx.beginPath();
            this.ctx.arc(stroke.x1, stroke.y1, radius, 0, Math.PI * 2);
            this.ctx.stroke();
        } else if (stroke.type === 'arrow') {
            this.drawArrow(stroke);
        }
        
        this.ctx.restore();
    }
    
    drawArrow(stroke) {
        if (!this.ctx) return;
        
        const headlen = 15;
        const dx = stroke.x2 - stroke.x1;
        const dy = stroke.y2 - stroke.y1;
        const angle = Math.atan2(dy, dx);
        
        this.ctx.beginPath();
        this.ctx.moveTo(stroke.x1, stroke.y1);
        this.ctx.lineTo(stroke.x2, stroke.y2);
        this.ctx.stroke();
        
        this.ctx.beginPath();
        this.ctx.moveTo(stroke.x2, stroke.y2);
        this.ctx.lineTo(
            stroke.x2 - headlen * Math.cos(angle - Math.PI / 6),
            stroke.y2 - headlen * Math.sin(angle - Math.PI / 6)
        );
        this.ctx.moveTo(stroke.x2, stroke.y2);
        this.ctx.lineTo(
            stroke.x2 - headlen * Math.cos(angle + Math.PI / 6),
            stroke.y2 - headlen * Math.sin(angle + Math.PI / 6)
        );
        this.ctx.stroke();
    }
    
    handleTextTool(pos) {
        const text = prompt('Enter text:');
        if (text && this.ctx) {
            const textStroke = {
                type: 'text',
                x: pos.x,
                y: pos.y,
                text: text,
                color: this.currentColor,
                width: this.currentWidth * 5,
            };
            
            this.ctx.save();
            this.ctx.font = `${this.currentWidth * 5}px Inter, sans-serif`;
            this.ctx.fillStyle = this.currentColor;
            this.ctx.fillText(text, pos.x, pos.y);
            this.ctx.restore();
            
            this.strokes.push(textStroke);
            this.broadcastStroke(textStroke);
        }
        this.isDrawing = false;
    }
    
    redrawAll() {
        this.clearCanvas();
        this.strokes.forEach(stroke => this.drawStroke(stroke));
    }
    
    clearCanvas() {
        if (!this.ctx || !this.canvas) return;
        this.ctx.fillStyle = this.backgroundColor;
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
    }
    
    setTool(tool) {
        this.currentTool = tool;
        this.emit('tool-changed', tool);
    }
    
    setColor(color) {
        this.currentColor = color;
        this.emit('color-changed', color);
    }
    
    setWidth(width) {
        this.currentWidth = width;
        this.emit('width-changed', width);
    }
    
    undo() {
        if (this.strokes.length === 0) return;
        
        const stroke = this.strokes.pop();
        this.undoStack.push(stroke);
        this.redrawAll();
        
        if (this.signaling) {
            this.signaling.send({ type: 'whiteboard-undo' });
        }
        
        this.emit('undo', { strokesRemaining: this.strokes.length });
    }
    
    redo() {
        if (this.undoStack.length === 0) return;
        
        const stroke = this.undoStack.pop();
        this.strokes.push(stroke);
        this.drawStroke(stroke);
        
        this.broadcastStroke(stroke);
        this.emit('redo', { strokesCount: this.strokes.length });
    }
    
    clear() {
        this.clearCanvas();
        this.strokes = [];
        this.undoStack = [];
        
        if (this.signaling) {
            this.signaling.send({ type: 'whiteboard-clear' });
        }
        
        this.emit('cleared');
    }
    
    broadcastStroke(stroke) {
        if (this.signaling) {
            this.signaling.send({
                type: 'whiteboard-draw',
                stroke: stroke,
            });
        }
    }
    
    async saveToServer() {
        if (!this.canvas) return null;
        
        try {
            const dataUrl = this.canvas.toDataURL('image/png');
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/whiteboard`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    image: dataUrl,
                    strokes: this.strokes,
                }),
            });
            
            if (!res.ok) throw new Error('Failed to save whiteboard');
            return await res.json();
        } catch (e) {
            console.error('[WhiteboardManager] Save error:', e);
            return null;
        }
    }
    
    exportAsImage() {
        if (!this.canvas) return null;
        return this.canvas.toDataURL('image/png');
    }
    
    downloadImage(filename = 'whiteboard.png') {
        const dataUrl = this.exportAsImage();
        if (!dataUrl) return;
        
        const link = document.createElement('a');
        link.download = filename;
        link.href = dataUrl;
        link.click();
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
                console.error(`[WhiteboardManager] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        if (this.canvas) {
            this.canvas.removeEventListener('pointerdown', this.handlePointerDown);
            this.canvas.removeEventListener('pointermove', this.handlePointerMove);
            this.canvas.removeEventListener('pointerup', this.handlePointerUp);
            this.canvas.removeEventListener('pointerleave', this.handlePointerUp);
        }
        window.removeEventListener('resize', this.resizeCanvas);
        this.listeners.clear();
    }
}
