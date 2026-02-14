export class QuizManager {
    constructor(config) {
        this.conferenceSlug = config.conferenceSlug;
        this.csrfToken = config.csrfToken;
        this.actorRole = config.actorRole;
        this.actorId = config.actorId;
        this.baseUrl = config.baseUrl || '';
        
        this.signaling = config.signaling || null;
        this.currentQuiz = null;
        this.currentQuestion = null;
        this.questionTimer = null;
        this.questionStartTime = null;
        this.responses = new Map();
        this.listeners = new Map();
        
        this.setupSignalingHandlers();
    }
    
    setupSignalingHandlers() {
        if (!this.signaling) return;
        
        this.signaling.on('quiz-started', (msg) => {
            this.handleQuizStarted(msg);
        });
        
        this.signaling.on('quiz-question', (msg) => {
            this.handleQuizQuestion(msg);
        });
        
        this.signaling.on('quiz-question-ended', (msg) => {
            this.handleQuestionEnded(msg);
        });
        
        this.signaling.on('quiz-response-received', (msg) => {
            this.handleResponseReceived(msg);
        });
        
        this.signaling.on('quiz-leaderboard', (msg) => {
            this.emit('leaderboard', msg.leaderboard);
        });
        
        this.signaling.on('quiz-ended', (msg) => {
            this.handleQuizEnded(msg);
        });
        
        this.signaling.on('quiz-results', (msg) => {
            this.emit('results', msg.results);
        });
    }
    
    async loadQuizzes() {
        try {
            const res = await fetch(`${this.baseUrl}/api/conference/${this.conferenceSlug}/quizzes`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to load quizzes');
            return await res.json();
        } catch (e) {
            console.error('[QuizManager] Load error:', e);
            return [];
        }
    }
    
    async createQuiz(quizData) {
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(quizData),
            });
            
            if (!res.ok) throw new Error('Failed to create quiz');
            return await res.json();
        } catch (e) {
            console.error('[QuizManager] Create error:', e);
            return null;
        }
    }
    
    async addQuestion(quizId, questionData) {
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify(questionData),
            });
            
            if (!res.ok) throw new Error('Failed to add question');
            return await res.json();
        } catch (e) {
            console.error('[QuizManager] Add question error:', e);
            return null;
        }
    }
    
    async startQuiz(quizId) {
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/start`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to start quiz');
            const data = await res.json();
            
            this.currentQuiz = data.quiz;
            this.responses.clear();
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'quiz-started',
                    quiz: this.currentQuiz,
                });
            }
            
            this.emit('started', this.currentQuiz);
            return data;
        } catch (e) {
            console.error('[QuizManager] Start error:', e);
            return null;
        }
    }
    
    async startQuestion(quizId, questionIndex) {
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/questions/${questionIndex}/start`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to start question');
            const data = await res.json();
            
            this.currentQuestion = data.question;
            this.questionStartTime = Date.now();
            this.responses.clear();
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'quiz-question',
                    question: this.currentQuestion,
                    questionIndex: questionIndex,
                    startTime: this.questionStartTime,
                });
            }
            
            if (this.currentQuestion.time_limit > 0) {
                this.startQuestionTimer(this.currentQuestion.time_limit, quizId, questionIndex);
            }
            
            this.emit('question', { question: this.currentQuestion, index: questionIndex });
            return data;
        } catch (e) {
            console.error('[QuizManager] Start question error:', e);
            return null;
        }
    }
    
    startQuestionTimer(seconds, quizId, questionIndex) {
        this.clearQuestionTimer();
        
        let remaining = seconds;
        this.emit('timer', remaining);
        
        this.questionTimer = setInterval(() => {
            remaining--;
            this.emit('timer', remaining);
            
            if (remaining <= 0) {
                this.clearQuestionTimer();
                this.endQuestion(quizId, questionIndex);
            }
        }, 1000);
    }
    
    clearQuestionTimer() {
        if (this.questionTimer) {
            clearInterval(this.questionTimer);
            this.questionTimer = null;
        }
    }
    
    async submitResponse(quizId, questionId, optionIndex) {
        const responseTime = this.questionStartTime ? (Date.now() - this.questionStartTime) / 1000 : 0;
        
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/questions/${questionId}/respond`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    option_index: optionIndex,
                    response_time: responseTime,
                }),
            });
            
            if (!res.ok) throw new Error('Failed to submit response');
            const data = await res.json();
            
            this.emit('response-submitted', { correct: data.correct, points: data.points });
            return data;
        } catch (e) {
            console.error('[QuizManager] Submit error:', e);
            return null;
        }
    }
    
    async endQuestion(quizId, questionIndex) {
        this.clearQuestionTimer();
        
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/questions/${questionIndex}/end`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to end question');
            const data = await res.json();
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'quiz-question-ended',
                    questionIndex: questionIndex,
                    results: data.results,
                });
            }
            
            this.emit('question-ended', { questionIndex, results: data.results });
            return data;
        } catch (e) {
            console.error('[QuizManager] End question error:', e);
            return null;
        }
    }
    
    async endQuiz(quizId) {
        this.clearQuestionTimer();
        
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/end`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to end quiz');
            const data = await res.json();
            
            if (this.signaling) {
                this.signaling.send({
                    type: 'quiz-ended',
                    quizId: quizId,
                    leaderboard: data.leaderboard,
                });
            }
            
            this.emit('ended', { leaderboard: data.leaderboard });
            this.currentQuiz = null;
            this.currentQuestion = null;
            return data;
        } catch (e) {
            console.error('[QuizManager] End quiz error:', e);
            return null;
        }
    }
    
    async getResults(quizId) {
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/results`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to get results');
            return await res.json();
        } catch (e) {
            console.error('[QuizManager] Get results error:', e);
            return null;
        }
    }
    
    async getLeaderboard(quizId) {
        try {
            const res = await fetch(`${this.baseUrl}/api/quizzes/${quizId}/leaderboard`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                credentials: 'same-origin',
            });
            
            if (!res.ok) throw new Error('Failed to get leaderboard');
            return await res.json();
        } catch (e) {
            console.error('[QuizManager] Get leaderboard error:', e);
            return [];
        }
    }
    
    handleQuizStarted(msg) {
        this.currentQuiz = msg.quiz;
        this.responses.clear();
        this.emit('started', this.currentQuiz);
    }
    
    handleQuizQuestion(msg) {
        this.currentQuestion = msg.question;
        this.questionStartTime = msg.startTime || Date.now();
        this.responses.clear();
        this.emit('question', { question: this.currentQuestion, index: msg.questionIndex });
        
        if (msg.question.time_limit > 0) {
            this.startQuestionTimer(msg.question.time_limit, this.currentQuiz.id, msg.questionIndex);
        }
    }
    
    handleQuestionEnded(msg) {
        this.clearQuestionTimer();
        this.emit('question-ended', { questionIndex: msg.questionIndex, results: msg.results });
    }
    
    handleResponseReceived(msg) {
        this.responses.set(msg.participantId, msg.response);
        this.emit('response', msg);
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
                console.error(`[QuizManager] Listener error for ${event}:`, e);
            }
        });
    }
    
    destroy() {
        this.clearQuestionTimer();
        this.listeners.clear();
        this.responses.clear();
    }
}
