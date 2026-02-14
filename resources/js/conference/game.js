export class GameEngine {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.actorRole = config.actorRole;
        this.actorId = config.actorId;
        this.baseUrl = config.baseUrl || '';
        
        this.signaling = config.signaling || null;
        
        this.currentGame = null;
        this.gameState = null;
        this.playerData = {};
        this.listeners = new Map();
        
        this.games = {
            bingo: new BingoGame(this),
            wordcloud: new WordCloudGame(this),
            hangman: new HangmanGame(this),
            memory: new MemoryGame(this),
        };
        
        this.setupSignalingHandlers();
    }
    
    setupSignalingHandlers() {
        if (!this.signaling) return;
        
        this.signaling.on('game-started', (msg) => {
            this.handleGameStarted(msg);
        });
        
        this.signaling.on('game-state', (msg) => {
            this.handleGameState(msg);
        });
        
        this.signaling.on('game-action', (msg) => {
            this.handleGameAction(msg);
        });
        
        this.signaling.on('game-ended', (msg) => {
            this.handleGameEnded(msg);
        });
        
        this.signaling.on('game-scores', (msg) => {
            this.emit('scores', msg.scores);
        });
    }
    
    async startGame(gameType, config = {}) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/games`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    type: gameType,
                    config: config,
                }),
            });
            
            if (!res.ok) throw new Error('Failed to start game');
            const data = await res.json();
            
            this.currentGame = data.game;
            this.gameState = data.state;
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'game-started',
                    game: this.currentGame,
                    state: this.gameState,
                });
            }
            
            this.emit('started', { game: this.currentGame, state: this.gameState });
            return data;
        } catch (e) {
            console.error('[GameEngine] Start error:', e);
            return null;
        }
    }
    
    sendAction(action, data = {}) {
        if (this.signaling) {
            this.signaling.send({
                type: 'game-action',
                gameId: this.currentGame?.id,
                action: action,
                data: data,
            });
        }
    }
    
    async submitScore(score, data = {}) {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/games/${this.currentGame.id}/score`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    score: score,
                    data: data,
                }),
            });
            
            if (!res.ok) throw new Error('Failed to submit score');
            return await res.json();
        } catch (e) {
            console.error('[GameEngine] Submit score error:', e);
            return null;
        }
    }
    
    async endGame() {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/games/${this.currentGame.id}/end`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to end game');
            const data = await res.json();
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'game-ended',
                    gameId: this.currentGame.id,
                    results: data.results,
                });
            }
            
            this.emit('ended', { results: data.results });
            this.currentGame = null;
            this.gameState = null;
            return data;
        } catch (e) {
            console.error('[GameEngine] End error:', e);
            return null;
        }
    }
    
    handleGameStarted(msg) {
        this.currentGame = msg.game;
        this.gameState = msg.state;
        this.emit('started', { game: this.currentGame, state: this.gameState });
    }
    
    handleGameState(msg) {
        this.gameState = msg.state;
        this.emit('state', this.gameState);
    }
    
    handleGameAction(msg) {
        if (msg.from?.id === this.actorId) return;
        
        const game = this.games[msg.game?.type];
        if (game && game.handleRemoteAction) {
            game.handleRemoteAction(msg.action, msg.data, msg.from);
        }
        
        this.emit('action', { action: msg.action, data: msg.data, from: msg.from });
    }
    
    handleGameEnded(msg) {
        this.emit('ended', { results: msg.results });
        this.currentGame = null;
        this.gameState = null;
    }
    
    getGame(type) {
        return this.games[type];
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
                console.error(`[GameEngine] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        Object.values(this.games).forEach(game => {
            if (game.destroy) game.destroy();
        });
        this.listeners.clear();
    }
}

class BingoGame {
    constructor(engine) {
        this.engine = engine;
        this.card = [];
        this.markedCells = [];
        this.hasWon = false;
    }
    
    generateCard(words) {
        const shuffled = [...words].sort(() => Math.random() - 0.5);
        this.card = shuffled.slice(0, 25);
        this.markedCells = new Array(25).fill(false);
        this.hasWon = false;
        return this.card;
    }
    
    markCell(index) {
        if (index < 0 || index >= 25) return false;
        if (this.markedCells[index]) return false;
        
        this.markedCells[index] = true;
        this.engine.sendAction('mark', { index });
        
        if (this.checkWin()) {
            this.hasWon = true;
            this.engine.emit('bingo-win', { card: this.card, marked: this.markedCells });
        }
        
        return true;
    }
    
    checkWin() {
        const wins = [
            [0, 1, 2, 3, 4],
            [5, 6, 7, 8, 9],
            [10, 11, 12, 13, 14],
            [15, 16, 17, 18, 19],
            [20, 21, 22, 23, 24],
            [0, 5, 10, 15, 20],
            [1, 6, 11, 16, 21],
            [2, 7, 12, 17, 22],
            [3, 8, 13, 18, 23],
            [4, 9, 14, 19, 24],
            [0, 6, 12, 18, 24],
            [4, 8, 12, 16, 20],
        ];
        
        return wins.some(combo => combo.every(i => this.markedCells[i]));
    }
    
    handleRemoteAction(action, data, from) {
        if (action === 'bingo') {
            this.engine.emit('peer-bingo', { from, data });
        }
    }
}

class WordCloudGame {
    constructor(engine) {
        this.engine = engine;
        this.words = [];
        this.maxWords = 50;
    }
    
    addWord(word) {
        if (!word || word.length > 30) return;
        
        const normalized = word.toLowerCase().trim();
        const existing = this.words.find(w => w.text === normalized);
        
        if (existing) {
            existing.count++;
        } else if (this.words.length < this.maxWords) {
            this.words.push({ text: normalized, count: 1 });
        }
        
        this.words.sort((a, b) => b.count - a.count);
        this.engine.sendAction('word', { word: normalized });
        this.engine.emit('wordcloud-updated', this.words);
    }
    
    clear() {
        this.words = [];
        this.engine.emit('wordcloud-updated', this.words);
    }
    
    getTopWords(limit = 20) {
        return this.words.slice(0, limit);
    }
    
    handleRemoteAction(action, data, from) {
        if (action === 'word' && data.word) {
            const existing = this.words.find(w => w.text === data.word);
            if (existing) {
                existing.count++;
            } else if (this.words.length < this.maxWords) {
                this.words.push({ text: data.word, count: 1 });
            }
            this.words.sort((a, b) => b.count - a.count);
            this.engine.emit('wordcloud-updated', this.words);
        }
    }
}

class HangmanGame {
    constructor(engine) {
        this.engine = engine;
        this.word = '';
        this.guessedLetters = [];
        this.wrongGuesses = 0;
        this.maxWrong = 6;
        this.isComplete = false;
    }
    
    start(word) {
        this.word = word.toLowerCase();
        this.guessedLetters = [];
        this.wrongGuesses = 0;
        this.isComplete = false;
        return this.getDisplayWord();
    }
    
    guess(letter) {
        if (this.isComplete) return null;
        
        const normalized = letter.toLowerCase();
        if (normalized.length !== 1 || !/[a-z]/.test(normalized)) return null;
        if (this.guessedLetters.includes(normalized)) return null;
        
        this.guessedLetters.push(normalized);
        this.engine.sendAction('guess', { letter: normalized });
        
        if (!this.word.includes(normalized)) {
            this.wrongGuesses++;
            if (this.wrongGuesses >= this.maxWrong) {
                this.isComplete = true;
                this.engine.emit('hangman-lost', { word: this.word });
            }
        } else if (this.isWon()) {
            this.isComplete = true;
            this.engine.emit('hangman-won', { word: this.word });
        }
        
        return {
            correct: this.word.includes(normalized),
            display: this.getDisplayWord(),
            wrongGuesses: this.wrongGuesses,
        };
    }
    
    getDisplayWord() {
        return this.word.split('').map(letter =>
            this.guessedLetters.includes(letter) ? letter : '_'
        ).join(' ');
    }
    
    isWon() {
        return this.word.split('').every(letter => this.guessedLetters.includes(letter));
    }
    
    handleRemoteAction(action, data, from) {
        if (action === 'guess' && data.letter) {
            const normalized = data.letter.toLowerCase();
            if (!this.guessedLetters.includes(normalized)) {
                this.guessedLetters.push(normalized);
                if (!this.word.includes(normalized)) {
                    this.wrongGuesses++;
                }
                this.engine.emit('hangman-updated', {
                    display: this.getDisplayWord(),
                    wrongGuesses: this.wrongGuesses,
                });
            }
        }
    }
}

class MemoryGame {
    constructor(engine) {
        this.engine = engine;
        this.cards = [];
        this.flipped = [];
        this.matched = [];
        this.moves = 0;
        this.isLocked = false;
    }
    
    start(pairs = 8) {
        const values = [];
        for (let i = 0; i < pairs; i++) {
            values.push(i, i);
        }
        
        this.cards = values.sort(() => Math.random() - 0.5).map((value, index) => ({
            id: index,
            value: value,
            isFlipped: false,
            isMatched: false,
        }));
        
        this.flipped = [];
        this.matched = [];
        this.moves = 0;
        this.isLocked = false;
        
        return this.cards;
    }
    
    flipCard(cardId) {
        if (this.isLocked) return null;
        
        const card = this.cards[cardId];
        if (!card || card.isFlipped || card.isMatched) return null;
        
        card.isFlipped = true;
        this.flipped.push(cardId);
        this.engine.sendAction('flip', { cardId });
        
        if (this.flipped.length === 2) {
            this.moves++;
            this.isLocked = true;
            
            const [first, second] = this.flipped;
            const firstCard = this.cards[first];
            const secondCard = this.cards[second];
            
            if (firstCard.value === secondCard.value) {
                firstCard.isMatched = true;
                secondCard.isMatched = true;
                this.matched.push(first, second);
                this.flipped = [];
                this.isLocked = false;
                
                if (this.matched.length === this.cards.length) {
                    this.engine.emit('memory-won', { moves: this.moves });
                }
            } else {
                setTimeout(() => {
                    firstCard.isFlipped = false;
                    secondCard.isFlipped = false;
                    this.flipped = [];
                    this.isLocked = false;
                }, 1000);
            }
        }
        
        return this.cards;
    }
    
    handleRemoteAction(action, data, from) {
        if (action === 'flip' && data.cardId !== undefined) {
            const card = this.cards[data.cardId];
            if (card && !card.isFlipped && !card.isMatched) {
                card.isFlipped = true;
                this.engine.emit('memory-updated', this.cards);
            }
        }
    }
}
