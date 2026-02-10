<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recording Playback | {{ $conference->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .scroll-thin::-webkit-scrollbar { width: 5px; }
        .scroll-thin::-webkit-scrollbar-thumb { background: #475569; border-radius: 999px; }
        .scroll-thin::-webkit-scrollbar-track { background: transparent; }
        .chapter-marker { cursor: pointer; position: absolute; top: 0; width: 3px; height: 100%; background: #f59e0b; border-radius: 2px; z-index: 2; }
        .chapter-marker:hover::after {
            content: attr(data-label);
            position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
            background: #1e293b; color: #f8fafc; font-size: 10px; padding: 2px 8px;
            border-radius: 4px; white-space: nowrap; pointer-events: none;
        }
        .chat-replay-highlight { background: rgba(5, 150, 105, 0.15); border-left: 3px solid #059669; }
    </style>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans','sans-serif'] } } } };
    </script>
</head>
<body class="bg-slate-950 text-slate-100 antialiased min-h-screen">
    <header class="px-4 md:px-6 py-4 border-b border-slate-800 bg-slate-900/90 sticky top-0 z-40 backdrop-blur-md">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-lg md:text-xl font-black tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-play-circle text-emerald-400"></i>
                    Recording Playback
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $conference->title }} &bull; {{ $recording->created_at->format('M j, Y g:ia') }}
                    &bull; Duration: {{ gmdate('H:i:s', $recording->duration_seconds ?? 0) }}
                </p>
            </div>
            <a href="{{ url()->previous() }}" class="px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-xs font-bold uppercase tracking-wide transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i>Back
            </a>
        </div>
    </header>

    <main class="p-4 md:p-6 max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_22rem] gap-4">
            {{-- Video Player --}}
            <section class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <div class="relative bg-black aspect-video">
                    <video id="playback-video" class="w-full h-full" controls preload="metadata">
                        <source src="{{ $recording->getDownloadUrl() }}" type="{{ $recording->mime_type }}">
                        Your browser does not support video playback.
                    </video>
                </div>

                {{-- Timeline with chapters --}}
                @if(!empty($recording->chapters))
                <div class="px-4 py-3 border-t border-slate-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-bookmark text-amber-400 text-xs"></i>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Chapters</p>
                    </div>
                    <div class="relative h-6 bg-slate-800 rounded-full overflow-hidden">
                        @foreach($recording->chapters as $chapter)
                            @php $pct = ($recording->duration_seconds > 0) ? ($chapter['time'] / $recording->duration_seconds * 100) : 0; @endphp
                            <div class="chapter-marker" style="left: {{ $pct }}%"
                                 data-label="{{ $chapter['label'] }}"
                                 data-time="{{ $chapter['time'] }}"
                                 onclick="seekTo({{ $chapter['time'] }})"></div>
                        @endforeach
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($recording->chapters as $i => $chapter)
                            <button onclick="seekTo({{ $chapter['time'] }})"
                                class="text-[10px] px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors">
                                <span class="text-amber-400 font-mono">{{ gmdate('i:s', $chapter['time']) }}</span>
                                <span class="ml-1 text-slate-300">{{ $chapter['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Playback controls --}}
                <div class="px-4 py-3 border-t border-slate-800 flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Speed:</span>
                        @foreach([0.5, 0.75, 1, 1.25, 1.5, 2] as $speed)
                            <button onclick="setSpeed({{ $speed }})"
                                class="speed-btn text-[10px] px-2 py-0.5 rounded-lg {{ $speed === 1 ? 'bg-emerald-500 text-slate-900' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }} font-bold transition-colors">
                                {{ $speed }}x
                            </button>
                        @endforeach
                    </div>

                    @if($recording->transcript_path)
                        <a href="{{ route('conference.recording.transcript', [$conference, $recording]) }}"
                           class="text-[10px] px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 font-bold text-emerald-300 transition-colors">
                            <i class="fa-solid fa-file-lines mr-1"></i>Download Transcript
                        </a>
                    @endif
                </div>

                {{-- Events Timeline --}}
                @if(!empty($playbackData['events']))
                <div class="px-4 py-3 border-t border-slate-800">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-400 text-xs"></i>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Event Timeline</p>
                    </div>
                    <div class="space-y-1 max-h-40 overflow-y-auto scroll-thin">
                        @foreach($playbackData['events'] as $event)
                            <div class="flex items-center gap-2 text-xs px-2 py-1 rounded-lg hover:bg-slate-800/50 cursor-pointer"
                                 onclick="seekTo({{ $event->conference_elapsed_seconds }})">
                                <span class="font-mono text-slate-500 text-[10px] w-12 flex-shrink-0">{{ gmdate('i:s', $event->conference_elapsed_seconds) }}</span>
                                <span class="flex-1 text-slate-300">
                                    @switch($event->event_type)
                                        @case('participant_joined') <i class="fa-solid fa-arrow-right-to-bracket text-emerald-400 mr-1"></i> @break
                                        @case('participant_left') <i class="fa-solid fa-arrow-right-from-bracket text-rose-400 mr-1"></i> @break
                                        @case('screen_share_started') <i class="fa-solid fa-display text-indigo-400 mr-1"></i> @break
                                        @case('screen_share_stopped') <i class="fa-solid fa-display text-slate-500 mr-1"></i> @break
                                        @case('recording_started') <i class="fa-solid fa-circle text-red-500 mr-1"></i> @break
                                        @default <i class="fa-solid fa-bolt text-amber-400 mr-1"></i>
                                    @endswitch
                                    {{ $event->event_type }}: {{ $event->metadata['name'] ?? $event->actor_id }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </section>

            {{-- Synced Chat Replay --}}
            <aside class="bg-slate-900 border border-slate-800 rounded-2xl flex flex-col overflow-hidden">
                <div class="p-3 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-comments text-emerald-400 text-xs"></i>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Chat Replay</p>
                    </div>
                    <span id="chat-sync-badge" class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold">
                        SYNCED
                    </span>
                </div>
                <div id="chat-replay" class="flex-1 p-3 space-y-2 overflow-y-auto scroll-thin min-h-[300px] max-h-[70vh]">
                    @forelse($playbackData['chat_replay'] as $msg)
                        <div class="chat-msg flex items-start gap-2 px-2 py-1.5 rounded-lg transition-colors"
                             data-elapsed="{{ $msg->conference_elapsed_seconds }}">
                            <span class="text-[9px] font-mono text-slate-500 flex-shrink-0 w-10 pt-0.5">
                                {{ gmdate('i:s', $msg->conference_elapsed_seconds ?? 0) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-bold text-slate-400">{{ $msg->display_name }}</p>
                                @if($msg->type === 'file')
                                    <a href="{{ $msg->file_url ?? '#' }}" target="_blank"
                                       class="text-xs text-emerald-300 hover:text-emerald-200 flex items-center gap-1">
                                        <i class="fa-solid fa-paperclip"></i>{{ $msg->file_name }}
                                    </a>
                                @else
                                    <p class="text-xs text-slate-200 leading-relaxed">{{ $msg->content }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-8">No chat messages in this recording</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </main>

    <script>
        const video = document.getElementById('playback-video');
        const chatMsgs = document.querySelectorAll('.chat-msg');

        function seekTo(seconds) {
            video.currentTime = seconds;
            video.play().catch(() => {});
        }

        function setSpeed(speed) {
            video.playbackRate = speed;
            document.querySelectorAll('.speed-btn').forEach(btn => {
                btn.classList.remove('bg-emerald-500', 'text-slate-900');
                btn.classList.add('bg-slate-800', 'text-slate-300');
            });
            event.target.classList.add('bg-emerald-500', 'text-slate-900');
            event.target.classList.remove('bg-slate-800', 'text-slate-300');
        }

        // Sync chat with video playback
        let lastHighlighted = null;
        video.addEventListener('timeupdate', () => {
            const currentTime = video.currentTime;
            chatMsgs.forEach(el => {
                const elapsed = parseFloat(el.dataset.elapsed);
                if (elapsed <= currentTime && elapsed >= currentTime - 5) {
                    el.classList.add('chat-replay-highlight');
                    if (lastHighlighted !== el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        lastHighlighted = el;
                    }
                } else {
                    el.classList.remove('chat-replay-highlight');
                }
            });
        });
    </script>
</body>
</html>
