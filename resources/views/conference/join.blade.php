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

            <div class="flex items-center gap-2 mb-5">
                @if(($conference->visibility ?? 'private') === 'public')
                    <span class="inline-flex items-center gap-2 rounded-full bg-sky-500/20 text-sky-300 px-3 py-1 text-[11px] font-black uppercase tracking-wider">
                        <i class="fa-solid fa-earth-asia"></i>Public Room
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-500/20 text-amber-200 px-3 py-1 text-[11px] font-black uppercase tracking-wider">
                        <i class="fa-solid fa-key"></i>Private Room
                    </span>
                @endif
            </div>

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

                @if($requiresSecretKey)
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Secret Key</label>
                        <input
                            type="text"
                            name="secret_key"
                            value="{{ old('secret_key') }}"
                            required
                            minlength="6"
                            maxlength="32"
                            placeholder="Enter teacher-provided key"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-700 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-400 outline-none"
                        >
                    </div>
                @endif

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-sm uppercase tracking-wider transition-colors">
                    <i class="fa-solid fa-video mr-2"></i>Join with Student Account
                </button>
            </form>

            @if($supportsGuestJoin)
                <div class="mt-6 pt-6 border-t border-slate-800">
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-300 mb-3">Guest Entry</h2>

                    @if(!$guestKeyValidated)
                        <form method="POST" action="{{ route('conference.join.guest.validate', $conference) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Secret Key</label>
                                <input
                                    type="text"
                                    name="guest_secret_key"
                                    value="{{ old('guest_secret_key') }}"
                                    required
                                    minlength="6"
                                    maxlength="32"
                                    placeholder="Enter private room key"
                                    class="w-full px-4 py-3 rounded-2xl border border-slate-700 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-amber-500/40 focus:border-amber-400 outline-none"
                                >
                            </div>
                            <button type="submit" class="w-full py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs uppercase tracking-wider transition-colors">
                                <i class="fa-solid fa-circle-check mr-2"></i>Validate Key
                            </button>
                        </form>
                    @else
                        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-emerald-200 text-xs font-semibold mb-4">
                            <i class="fa-solid fa-circle-check mr-2"></i>Secret key validated. Enter temporary name.
                        </div>
                        <form method="POST" action="{{ route('conference.join.guest', $conference) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Temporary Name</label>
                                <input
                                    type="text"
                                    name="temporary_name"
                                    value="{{ old('temporary_name') }}"
                                    required
                                    minlength="2"
                                    maxlength="40"
                                    placeholder="Ex: Parent - Maria"
                                    class="w-full px-4 py-3 rounded-2xl border border-slate-700 bg-slate-800 text-slate-100 focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500 outline-none"
                                >
                            </div>
                            <button type="submit" class="w-full py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs uppercase tracking-wider transition-colors">
                                <i class="fa-solid fa-door-open mr-2"></i>Join as Guest
                            </button>
                            <a href="{{ route('conference.join.form', ['conference' => $conference, 'reset_guest' => 1]) }}" class="block text-center text-xs text-slate-400 hover:text-slate-200 underline underline-offset-4">
                                Use a different secret key
                            </a>
                        </form>
                    @endif
                </div>
            @endif

            <p class="text-xs text-slate-500 mt-5">
                @if(($conference->visibility ?? 'private') === 'public')
                    Public room: any system user can join.
                @else
                    Private room: join requires the teacher's secret key.
                @endif
            </p>
        @endif
    </div>
</body>
</html>
