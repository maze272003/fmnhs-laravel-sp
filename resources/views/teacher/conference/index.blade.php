<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Class | Teacher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">
    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/70 sticky top-0 z-40 px-8 py-5 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex flex-col">
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Live Class Studio</h2>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em]">Realtime Video Conference</p>
                </div>
            </div>
            @include('components.teacher.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full space-y-8">
            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700 text-sm font-semibold">
                    <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-700 text-sm font-semibold">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <section class="xl:col-span-1 bg-white rounded-[2rem] border border-slate-100 shadow-sm p-7">
                    <div class="mb-6">
                        <h3 class="text-lg font-extrabold tracking-tight text-slate-900">Create Meeting Link</h3>
                        <p class="text-xs text-slate-500 mt-1">Share this link to students. They join using email/LRN + password.</p>
                    </div>

                    <form method="POST" action="{{ route('teacher.conferences.store') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Meeting Title</label>
                            <input
                                type="text"
                                name="title"
                                value="{{ old('title') }}"
                                required
                                maxlength="120"
                                placeholder="Ex: Grade 10 Math Consultation"
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 outline-none"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Limit by Section (optional)</label>
                            <select
                                name="section_id"
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 outline-none"
                            >
                                <option value="">All Assigned Sections</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ (string) old('section_id') === (string) $section->id ? 'selected' : '' }}>
                                        Grade {{ $section->grade_level }} - {{ $section->name }}
                                    </option>
                                @endforeach
                                </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Privacy</label>
                            <select
                                name="visibility"
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 outline-none"
                            >
                                <option value="public" {{ old('visibility', 'public') === 'public' ? 'selected' : '' }}>Public (All system users)</option>
                                <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Private (Secret Key)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Secret Key (Private Rooms)</label>
                            <input
                                type="text"
                                name="secret_key"
                                value="{{ old('secret_key') }}"
                                minlength="6"
                                maxlength="32"
                                placeholder="Ex: FMNHS2026"
                                class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-slate-50 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 outline-none"
                            >
                            <p class="text-[11px] text-slate-500 mt-1">Alphanumeric only, 6-32 characters.</p>
                        </div>

                        <button type="submit" class="w-full px-5 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm tracking-wide transition-colors">
                            <i class="fa-solid fa-link mr-2"></i>Generate Live Link
                        </button>
                    </form>
                </section>

                <section class="xl:col-span-2 bg-white rounded-[2rem] border border-slate-100 shadow-sm p-7">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-extrabold tracking-tight text-slate-900">Meeting Rooms</h3>
                        <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Newest first</span>
                    </div>

                    @if($conferences->count() === 0)
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 px-6 py-12 text-center">
                            <p class="text-slate-500 font-semibold">No meetings created yet.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($conferences as $conference)
                                <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-base font-black text-slate-800">{{ $conference->title }}</h4>
                                                @if($conference->is_active && !$conference->ended_at)
                                                    <span class="text-[10px] px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-black uppercase tracking-wider">Active</span>
                                                @else
                                                    <span class="text-[10px] px-2 py-1 rounded-full bg-rose-100 text-rose-700 font-black uppercase tracking-wider">Ended</span>
                                                @endif
                                                @if(($conference->visibility ?? 'private') === 'public')
                                                    <span class="text-[10px] px-2 py-1 rounded-full bg-sky-100 text-sky-700 font-black uppercase tracking-wider">Public</span>
                                                @else
                                                    <span class="text-[10px] px-2 py-1 rounded-full bg-amber-100 text-amber-700 font-black uppercase tracking-wider">Private</span>
                                                @endif
                                            </div>

                                            <p class="text-xs text-slate-500">
                                                <i class="fa-solid fa-users mr-1"></i>
                                                {{ $conference->section ? 'Grade '.$conference->section->grade_level.' - '.$conference->section->name : 'All assigned sections' }}
                                            </p>

                                            <p class="text-xs text-slate-500 break-all">
                                                <i class="fa-solid fa-link mr-1"></i>
                                                <a href="{{ route('conference.join.form', $conference) }}" target="_blank" class="underline decoration-dotted underline-offset-4">
                                                    {{ route('conference.join.form', $conference) }}
                                                </a>
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap gap-2 md:justify-end">
                                            <button
                                                type="button"
                                                onclick="copyLink('{{ route('conference.join.form', $conference) }}')"
                                                class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold uppercase tracking-wide hover:bg-black transition-colors"
                                            >
                                                <i class="fa-solid fa-copy mr-1"></i>Copy Link
                                            </button>

                                            <a
                                                href="{{ route('conference.room', $conference) }}"
                                                class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold uppercase tracking-wide hover:bg-emerald-700 transition-colors"
                                            >
                                                <i class="fa-solid fa-video mr-1"></i>Open Room
                                            </a>

                                            @if($conference->is_active && !$conference->ended_at)
                                                <form method="POST" action="{{ route('teacher.conferences.privacy', $conference) }}" class="flex flex-wrap gap-2 items-center">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="visibility" class="px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700">
                                                        <option value="private" {{ ($conference->visibility ?? 'private') === 'private' ? 'selected' : '' }}>Private</option>
                                                        <option value="public" {{ ($conference->visibility ?? 'private') === 'public' ? 'selected' : '' }}>Public</option>
                                                    </select>
                                                    <input
                                                        type="text"
                                                        name="secret_key"
                                                        minlength="6"
                                                        maxlength="32"
                                                        placeholder="New key (private)"
                                                        class="px-3 py-2 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-700"
                                                    >
                                                    <button
                                                        type="submit"
                                                        class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold uppercase tracking-wide hover:bg-indigo-700 transition-colors"
                                                    >
                                                        <i class="fa-solid fa-shield-halved mr-1"></i>Privacy
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('teacher.conferences.end', $conference) }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold uppercase tracking-wide hover:bg-rose-700 transition-colors"
                                                    >
                                                        <i class="fa-solid fa-phone-slash mr-1"></i>Terminate
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $conferences->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Link copied',
                    text: 'You can now share this with your students.',
                    timer: 1600,
                    showConfirmButton: false,
                });
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Copy failed',
                    text: 'Please copy the link manually.',
                });
            });
        }
    </script>
</body>
</html>
