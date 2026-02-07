<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Hub | Faculty Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        {{-- HEADER WITH PROFILE PICTURE --}}
        <header class="glass-header border-b border-slate-200/60 sticky top-0 z-40 px-8 py-5 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-100">
                        <i class="fa-solid fa-clipboard-check text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Attendance</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Registry Hub</p>
                    </div>
                </div>
            </div>

            {{-- PROFILE SECTION --}}
            <div class="flex items-center gap-3">
                @php $teacher = Auth::guard('teacher')->user(); @endphp
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 leading-none mb-1">{{ $teacher->first_name }} {{ $teacher->last_name }}</p>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Faculty Member</p>
                </div>
                
                {{-- AVATAR LOGIC (S3 / URL / Fallback) --}}
                <img src="{{ 
                        ($teacher->avatar && $teacher->avatar !== 'default.png') 
                        ? (
                            \Illuminate\Support\Str::startsWith($teacher->avatar, 'http') 
                            ? $teacher->avatar 
                            : \Illuminate\Support\Facades\Storage::disk('s3')->url($teacher->avatar)
                        ) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->first_name . '+' . $teacher->last_name) . '&background=059669&color=fff'
                     }}" 
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=Teacher&background=059669&color=fff';"
                     alt="Profile" 
                     class="w-10 h-10 rounded-2xl object-cover border-2 border-white shadow-md bg-slate-100">
            </div>
        </header>

        <main class="flex-1 p-6 flex flex-col justify-center items-center max-w-7xl mx-auto w-full">
            
            <div class="w-full max-w-md">
                <div class="text-center mb-10">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Class Registry</h1>
                    <p class="text-slate-500 font-medium leading-relaxed">Select a handled class and date to begin recording student presence.</p>
                </div>

                <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl shadow-emerald-100/50 border border-slate-200/50 relative overflow-hidden group">
                    <i class="fa-solid fa-calendar-check absolute -right-4 -top-4 text-9xl text-slate-50 group-hover:rotate-12 transition-transform duration-700"></i>

                    <div class="relative z-10">
                        <div class="flex justify-center mb-8">
                            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center text-3xl shadow-inner border border-emerald-100">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>

                        <form action="{{ route('teacher.attendance.show') }}" method="GET" class="space-y-6">
                            
                            <div class="space-y-2">
                                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Assigned Load</label>
                                <div class="relative">
                                    <select id="classSelector" required
                                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-bold text-slate-700 appearance-none cursor-pointer">
                                        <option value="" disabled selected>-- Select Class Pairing --</option>
                                        
                                        @foreach($assignedClasses as $class)
                                            <option value="{{ $class->subject_id }}|{{ $class->section_id }}">
                                                {{-- DISPLAY: Subject - Section (Grade) [SY] --}}
                                                {{ $class->subject->code }} — {{ $class->section->name }} (Grade {{ $class->section->grade_level }})
                                                
                                                {{-- Added School Year Check --}}
                                                @if($class->section->schoolYear)
                                                    [SY {{ $class->section->schoolYear->school_year }}]
                                                @endif
                                            </option>
                                        @endforeach

                                    </select>
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">Logging Date</label>
                                <div class="relative group">
                                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                                           class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-bold text-slate-700">
                                </div>
                            </div>

                            <input type="hidden" name="subject_id" id="input_subject_id">
                            <input type="hidden" name="section_id" id="input_section_id">

                            <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-2xl hover:bg-emerald-600 shadow-xl shadow-slate-200 hover:shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-3 mt-6 group/btn">
                                <span class="tracking-tight uppercase text-xs tracking-widest">Open Attendance Sheet</span>
                                <i class="fa-solid fa-arrow-right-long text-[10px] group-hover/btn:translate-x-1 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-center gap-2 px-6">
                    <i class="fa-solid fa-circle-info text-[10px] text-emerald-500"></i>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                        Records are synchronized with the central school database.
                    </p>
                </div>
            </div>
        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        // Updated JavaScript to handle the subject_id | section_id split
        document.getElementById('classSelector').addEventListener('change', function() {
            const parts = this.value.split('|');
            document.getElementById('input_subject_id').value = parts[0];
            document.getElementById('input_section_id').value = parts[1];
        });
    </script>
</body>
</html>