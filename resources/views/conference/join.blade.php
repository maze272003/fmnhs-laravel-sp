<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Meeting | {{ $conference->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-xl rounded-[2rem] border border-slate-800 bg-slate-900/80 backdrop-blur-sm shadow-2xl shadow-black/40 p-8">
        <div class="mb-8">
            <p class="text-[10px] uppercase tracking-[0.25em] font-black text-emerald-400 mb-2">Live Class Room</p>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight">{{ $conference->title }}</h1>
            <p class="text-sm text-slate-400 mt-2">
                Teacher: {{ $conference->teacher ? trim($conference->teacher->first_name.' '.$conference->teacher->last_name) : 'Faculty' }}
            </p>
            @if($conference->section)
                <p class="text-xs text-slate-500 mt-1">Section: Grade {{ $conference->section->grade_level }} - {{ $conference->section->name }}</p>
            @endif
        </div>

        @if(!$conference->is_active || $conference->ended_at)
            <div class="rounded-2xl border border-rose-500/30 bg-rose-500/10 p-5 text-rose-200">
                <p class="font-semibold"><i class="fa-solid fa-circle-exclamation mr-2"></i>This meeting has already ended.</p>
            </div>
            <div class="mt-6">
                <a href="{{ url('/') }}" class="inline-flex items-center text-sm font-bold text-emerald-300 hover:text-emerald-200">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Portal
                </a>
            </div>
        @else
            @if($errors->any())
                <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-rose-200 text-sm mb-5">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('conference.join.attempt', $conference) }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Student Email or LRN</label>
                    <input
                        type="text"
                        name="credential"
                        value="{{ old('credential') }}"
                        required
                        placeholder="example@student.com or 123456789012"
                        class="w-full px-4 py-3 rounded-2xl border border-slate-700 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full px-4 py-3 rounded-2xl border border-slate-700 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none"
                    >
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-sm uppercase tracking-wider transition-colors">
                    <i class="fa-solid fa-video mr-2"></i>Join Video Class
                </button>
            </form>

            <p class="text-xs text-slate-500 mt-5">Only students assigned to this teacher/section can enter.</p>
        @endif
    </div>
</body>
</html>
