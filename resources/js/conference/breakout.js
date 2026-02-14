export class BreakoutRoomManager {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.actorRole = config.actorRole;
        this.actorId = config.actorId;
        this.baseUrl = config.baseUrl || '';
        
        this.signaling = config.signaling || null;
        this.rooms = new Map();
        this.participants = new Map();
        this.currentRoom = null;
        this.listeners = new Map();
        
        this.setupSignalingHandlers();
    }
    
    setupSignalingHandlers() {
        if (!this.signaling) return;
        
        this.signaling.on('breakout-created', (msg) => {
            this.handleRoomCreated(msg);
        });
        
        this.signaling.on('breakout-assigned', (msg) => {
            this.handleRoomAssigned(msg);
        });
        
        this.signaling.on('breakout-joined', (msg) => {
            this.handleRoomJoined(msg);
        });
        
        this.signaling.on('breakout-left', (msg) => {
            this.handleRoomLeft(msg);
        });
        
        this.signaling.on('breakout-ended', (msg) => {
            this.handleRoomEnded(msg);
        });
        
        this.signaling.on('breakout-broadcast', (msg) => {
            this.handleBroadcast(msg);
        });
        
        this.signaling.on('breakout-timer', (msg) => {
            this.emit('timer', msg);
        });
    }
    
    async createRooms(config) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/breakout-rooms`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(config),
            });
            
            if (!res.ok) throw new Error('Failed to create breakout rooms');
            const data = await res.json();
            
            data.rooms.forEach(room => {
                this.rooms.set(room.id, room);
            });
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'breakout-created',
                    rooms: data.rooms,
                });
            }
            
            this.emit('rooms-created', data.rooms);
            return data;
        } catch (e) {
            console.error('[BreakoutRoomManager] Create error:', e);
            return null;
        }
    }
    
    async autoAssign(roomCount, method = 'random') {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/breakout-rooms/auto-assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    room_count: roomCount,
                    method: method,
                }),
            });
            
            if (!res.ok) throw new Error('Failed to auto-assign');
            const data = await res.json();
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'breakout-assigned',
                    assignments: data.assignments,
                });
            }
            
            this.emit('assigned', data.assignments);
            return data;
        } catch (e) {
            console.error('[BreakoutRoomManager] Auto-assign error:', e);
            return null;
        }
    }
    
    async assignParticipant(roomId, participantId) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/breakout-rooms/${roomId}/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ participant_id: participantId }),
            });
            
            if (!res.ok) throw new Error('Failed to assign participant');
            const data = await res.json();
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'breakout-assigned',
                    participantId: participantId,
                    roomId: roomId,
                });
            }
            
            this.emit('participant-assigned', { roomId, participantId });
            return data;
        } catch (e) {
            console.error('[BreakoutRoomManager] Assign error:', e);
            return null;
        }
    }
    
    async joinRoom(roomId) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/breakout-rooms/${roomId}/join`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to join room');
            const data = await res.json();
            
            this.currentRoom = this.rooms.get(roomId);
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'breakout-joined',
                    roomId: roomId,
                });
            }
            
            this.emit('joined', { room: this.currentRoom });
            return data;
        } catch (e) {
            console.error('[BreakoutRoomManager] Join error:', e);
            return null;
        }
    }
    
    async leaveRoom(roomId) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/breakout-rooms/${roomId}/leave`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to leave room');
            
            const previousRoom = this.currentRoom;
            this.currentRoom = null;
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'breakout-left',
                    roomId: roomId,
                });
            }
            
            this.emit('left', { room: previousRoom });
            return true;
        } catch (e) {
            console.error('[BreakoutRoomManager] Leave error:', e);
            return false;
        }
    }
    
    async returnToMain() {
        if (this.currentRoom) {
            await this.leaveRoom(this.currentRoom.id);
        }
        this.emit('return-main');
    }
    
    async broadcastToAll(message) {
        if (!this.signaling) return;
        
        this.signaling.send({
            type: 'breakout-broadcast',
            message: message,
        });
        
        this.emit('broadcast-sent', { message });
    }
    
    async endAllRooms() {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/breakout-rooms/end-all`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to end rooms');
            
            if (this.signaling) {
                this.signaling.send({ type: 'breakout-ended' });
            }
            
            this.rooms.clear();
            this.currentRoom = null;
            this.emit('all-ended');
            return true;
        } catch (e) {
            console.error('[BreakoutRoomManager] End all error:', e);
            return false;
        }
    }
    
    setTimer(durationMinutes) {
        if (this.signaling) {
            this.signaling.send({
                type: 'breakout-timer',
                duration: durationMinutes,
            });
        }
        this.emit('timer-set', { duration: durationMinutes });
    }
    
    handleRoomCreated(msg) {
        (msg.rooms || []).forEach(room => {
            this.rooms.set(room.id, room);
        });
        this.emit('rooms-created', msg.rooms);
    }
    
    handleRoomAssigned(msg) {
        if (msg.assignments) {
            msg.assignments.forEach(a => {
                this.participants.set(a.participantId, a.roomId);
            });
        } else if (msg.participantId && msg.roomId) {
            this.participants.set(msg.participantId, msg.roomId);
        }
        this.emit('assigned', msg);
    }
    
    handleRoomJoined(msg) {
        this.emit('peer-joined', { roomId: msg.roomId, participant: msg.participant });
    }
    
    handleRoomLeft(msg) {
        this.emit('peer-left', { roomId: msg.roomId, participant: msg.participant });
    }
    
    handleRoomEnded(msg) {
        this.rooms.clear();
        this.currentRoom = null;
        this.emit('all-ended');
    }
    
    handleBroadcast(msg) {
        this.emit('broadcast', { message: msg.message, from: msg.from });
    }
    
    getRoomParticipants(roomId) {
        const participants = [];
        this.participants.forEach((room, participantId) => {
            if (room === roomId) {
                participants.push(participantId);
            }
        });
        return participants;
    }
    
    getParticipantRoom(participantId) {
        return this.participants.get(participantId);
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
                console.error(`[BreakoutRoomManager] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        this.rooms.clear();
        this.participants.clear();
        this.listeners.clear();
    }
}
