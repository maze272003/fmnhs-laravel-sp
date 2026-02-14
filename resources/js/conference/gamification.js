export class GamificationManager {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.actorId = config.actorId;
        this.actorRole = config.actorRole;
        this.baseUrl = config.baseUrl || '';
        
        this.points = 0;
        this.badges = [];
        this.achievements = [];
        this.pointsHistory = [];
        this.listeners = new Map();
        
        this.loadState();
    }
    
    async loadState() {
        await Promise.all([
            this.loadSummary(),
            this.loadBadges(),
        ]);
    }
    
    async loadSummary() {
        try {
            const res = await fetch(`${this.baseUrl}/api/gamification/summary`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load summary');
            const data = await res.json();
            
            this.points = data.total_points || 0;
            this.achievements = data.achievements || [];
            this.emit('summary', data);
            return data;
        } catch (e) {
            console.error('[GamificationManager] Load summary error:', e);
            return null;
        }
    }
    
    async loadBadges() {
        try {
            const res = await fetch(`${this.baseUrl}/api/gamification/my-badges`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load badges');
            const data = await res.json();
            
            this.badges = data.badges || [];
            this.emit('badges', this.badges);
            return data;
        } catch (e) {
            console.error('[GamificationManager] Load badges error:', e);
            return null;
        }
    }
    
    async loadLeaderboard(period = 'weekly', limit = 10) {
        try {
            const res = await fetch(`${this.baseUrl}/api/gamification/leaderboard?period=${period}&limit=${limit}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load leaderboard');
            const data = await res.json();
            
            this.emit('leaderboard', data.leaderboard);
            return data.leaderboard;
        } catch (e) {
            console.error('[GamificationManager] Load leaderboard error:', e);
            return [];
        }
    }
    
    async loadPointsHistory(limit = 50) {
        try {
            const res = await fetch(`${this.baseUrl}/api/gamification/points-history?limit=${limit}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load points history');
            const data = await res.json();
            
            this.pointsHistory = data.history || [];
            this.emit('points-history', this.pointsHistory);
            return data;
        } catch (e) {
            console.error('[GamificationManager] Load points history error:', e);
            return null;
        }
    }
    
    addPoints(amount, reason, metadata = {}) {
        const previousPoints = this.points;
        this.points += amount;
        
        this.emit('points-added', {
            amount,
            reason,
            previousPoints,
            newPoints: this.points,
            metadata,
        });
        
        this.emit('points-changed', {
            total: this.points,
            delta: amount,
        });
        
        this.checkBadgeUnlocks();
    }
    
    checkBadgeUnlocks() {
        const badgeThresholds = [
            { id: 'first-points', name: 'First Steps', points: 1 },
            { id: 'points-10', name: 'Getting Started', points: 10 },
            { id: 'points-50', name: 'Rising Star', points: 50 },
            { id: 'points-100', name: 'Century Club', points: 100 },
            { id: 'points-500', name: 'Point Master', points: 500 },
            { id: 'points-1000', name: 'Point Legend', points: 1000 },
        ];
        
        badgeThresholds.forEach(threshold => {
            if (this.points >= threshold.points && !this.hasBadge(threshold.id)) {
                this.unlockBadge(threshold.id, threshold.name);
            }
        });
    }
    
    hasBadge(badgeId) {
        return this.badges.some(b => b.badge_id === badgeId || b.id === badgeId);
    }
    
    unlockBadge(badgeId, badgeName) {
        this.badges.push({
            id: badgeId,
            badge_id: badgeId,
            name: badgeName,
            unlocked_at: new Date().toISOString(),
        });
        
        this.emit('badge-unlocked', {
            id: badgeId,
            name: badgeName,
            totalBadges: this.badges.length,
        });
    }
    
    trackConferenceAction(action, metadata = {}) {
        const actionPoints = {
            'join': 5,
            'speak': 1,
            'chat': 1,
            'reaction': 1,
            'raise-hand': 2,
            'screen-share': 5,
            'quiz-correct': 10,
            'quiz-participate': 5,
            'attendance': 5,
        };
        
        const points = actionPoints[action] || 0;
        if (points > 0) {
            this.addPoints(points, `conference_action_${action}`, { action, ...metadata });
        }
    }
    
    formatPoints(points) {
        if (points >= 1000) {
            return (points / 1000).toFixed(1) + 'k';
        }
        return points.toString();
    }
    
    getRankInfo(points) {
        if (points >= 1000) return { rank: 'Legend', color: '#ffd700', icon: 'crown' };
        if (points >= 500) return { rank: 'Master', color: '#9b59b6', icon: 'star' };
        if (points >= 250) return { rank: 'Expert', color: '#3498db', icon: 'medal' };
        if (points >= 100) return { rank: 'Skilled', color: '#2ecc71', icon: 'award' };
        if (points >= 50) return { rank: 'Learner', color: '#e67e22', icon: 'book' };
        if (points >= 10) return { rank: 'Beginner', color: '#95a5a6', icon: 'seedling' };
        return { rank: 'Newcomer', color: '#7f8c8d', icon: 'user' };
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
                console.error(`[GamificationManager] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        this.listeners.clear();
    }
}
