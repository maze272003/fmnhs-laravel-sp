<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                        <i class="fa-solid fa-calendar-day text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Class Schedule</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Academic Timetable</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-900 leading-none mb-1">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">
                        Grade {{ $student->section->grade_level }} - {{ $student->section->name }}
                    </p>
                </div>

                <img src="{{ 
                        ($student->avatar && $student->avatar !== 'default.png') 
                        ? (Str::startsWith($student->avatar, 'http') ? $student->avatar : \Illuminate\Support\Facades\Storage::disk('s3')->url('avatars/' . $student->avatar)) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode($student->first_name . '+' . $student->last_name) . '&background=0D8ABC&color=fff'
                     }}" 
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff';"
                     alt="Profile" class="w-10 h-10 rounded-2xl object-cover border-2 border-white shadow-md">
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-6xl mx-auto w-full">

            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Weekly Timetable</h1>
                <p class="text-slate-500 font-medium">Your official class assignments and room designations for S.Y. 2025-2026.</p>
            </div>

            @if($schedules->isEmpty())
                <div class="py-24 flex flex-col items-center justify-center bg-white rounded-[3.5rem] border-2 border-dashed border-slate-200 shadow-inner">
                    <div class="relative mb-10">
                        <div class="w-48 h-48 bg-indigo-50 rounded-full flex items-center justify-center relative overflow-hidden border border-indigo-100">
                            <i class="fa-solid fa-cloud text-6xl text-white absolute top-8 left-4 opacity-80"></i>
                            <i class="fa-solid fa-sun text-4xl text-amber-200 absolute top-6 right-8 animate-pulse"></i>
                            <i class="fa-solid fa-user-ninja text-8xl text-indigo-200 absolute -bottom-2 rotate-6"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-16 h-16 bg-white rounded-3xl shadow-2xl flex items-center justify-center border border-slate-50">
                            <i class="fa-solid fa-mug-hot text-2xl text-indigo-400"></i>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-3">No Classes Assigned</h3>
                    <p class="text-slate-400 font-medium text-center max-w-xs px-6 leading-relaxed">
                        Your section hasn't been assigned a schedule yet. Take this time to relax and recharge!
                    </p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($schedules as $sched)
                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 p-6 md:p-10 flex flex-col md:flex-row justify-between items-center hover:shadow-2xl hover:shadow-indigo-100/50 transition-all duration-500 group relative overflow-hidden">
                            
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-600 group-hover:w-2.5 transition-all"></div>

                            <div class="flex items-center gap-8 w-full md:w-1/3 mb-6 md:mb-0">
                                <div class="text-center md:text-left min-w-[120px]">
                                    <p class="text-2xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                        {{ \Carbon\Carbon::parse($sched->start_time)->format('g:i A') }}
                                    </p>
                                    <div class="flex items-center gap-3 my-2 opacity-30">
                                        <div class="h-px w-6 bg-slate-400"></div>
                                        <span class="text-[9px] font-black tracking-[0.2em] text-slate-600 uppercase">UNTIL</span>
                                        <div class="h-px w-6 bg-slate-400"></div>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">
                                        {{ \Carbon\Carbon::parse($sched->end_time)->format('g:i A') }}
                                    </p>
                                </div>
                                <div class="h-14 w-px bg-slate-100 hidden md:block"></div>
                                <div>
                                    <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-4 py-2 rounded-xl uppercase tracking-widest border border-indigo-100 shadow-sm">
                                        {{ $sched->day }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex-1 w-full text-center md:text-left mb-6 md:mb-0 px-0 md:px-8">
                                <h3 class="text-xl md:text-2xl font-black text-slate-900 mb-2 group-hover:translate-x-1 transition-transform tracking-tight">
                                    {{ $sched->subject->name }}
                                </h3>
                                <div class="flex items-center justify-center md:justify-start gap-4">
                                    <span class="font-mono text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-1 rounded-lg border border-indigo-100 uppercase">
                                        {{ $sched->subject->code }}
                                    </span>
                                    <span class="h-1 w-1 rounded-full bg-slate-200"></span>
                                    <p class="text-xs font-bold text-slate-500 flex items-center gap-1.5 uppercase tracking-widest">
                                        <i class="fa-solid fa-location-dot text-[10px] text-rose-400"></i>
                                        Room: <span class="text-slate-900">{{ $sched->room ?? 'Laboratory' }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="w-full md:w-1/4 flex items-center justify-center md:justify-end gap-5">
                                <div class="text-right hidden md:block">
                                    <p class="text-sm font-black text-slate-800 leading-none mb-1">
                                        {{ $sched->teacher->first_name }} {{ $sched->teacher->last_name }}
                                    </p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Subject Instructor</p>
                                </div>
                                <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center text-lg font-black group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all duration-300 shadow-sm">
                                    {{ substr($sched->teacher->first_name, 0, 1) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>