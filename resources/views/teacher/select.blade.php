<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class | Grading Portal</title>
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
        
        {{-- HEADER --}}
        <header class="glass-header border-b border-slate-200/60 sticky top-0 z-40 px-8 py-5 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-100">
                        <i class="fa-solid fa-pen-nib text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Grading Portal</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Subject Selection</p>
                    </div>
                </div>
            </div>
            @include('components.teacher.header_details')
        </header>

        <main class="flex-1 p-6 flex items-center justify-center max-w-7xl mx-auto w-full">
            
            <div class="w-full max-w-lg">
                <div class="bg-white p-10 md:p-12 rounded-[3rem] shadow-2xl shadow-emerald-100/50 border border-slate-200/50 relative overflow-hidden group">
                    
                    {{-- DECORATIVE BACKGROUND ICON --}}
                    <i class="fa-solid fa-stamp absolute -right-12 -bottom-12 text-[12rem] text-slate-50 opacity-80 pointer-events-none rotate-12 group-hover:rotate-[20deg] transition-transform duration-700"></i>

                    <div class="text-center mb-10 relative z-10">
                        <div class="w-24 h-24 bg-emerald-50 text-emerald-600 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 text-4xl shadow-inner border border-emerald-100/50">
                            <i class="fa-solid fa-scroll"></i>
                        </div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Select Load</h1>
                        <p class="text-slate-500 font-medium mt-3 leading-relaxed">Choose the subject and section to begin recording student assessments.</p>
                    </div>

                    @if($assignedClasses->isEmpty())
                        <div class="bg-rose-50 border border-rose-100 rounded-[2rem] p-8 text-center text-rose-800 mb-6 relative z-10 shadow-sm">
                            <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm text-rose-500">
                                <i class="fa-solid fa-user-lock text-2xl"></i>
                            </div>
                            <h4 class="font-black uppercase text-[10px] tracking-[0.2em] mb-2">Access Denied</h4>
                            <p class="text-sm font-bold opacity-80 leading-relaxed">No classes have been synchronized with your faculty account. Please coordinate with the Registrar.</p>
                        </div>
                    @else
                        <form action="{{ route('teacher.grading.show') }}" method="GET" class="relative z-10 space-y-8">
                            
                            <div class="bg-indigo-50/50 border border-indigo-100 p-5 rounded-3xl flex items-start gap-4">
                                <div class="w-8 h-8 bg-white rounded-xl flex items-center justify-center text-indigo-500 shadow-sm shrink-0">
                                    <i class="fa-solid fa-circle-info text-xs"></i>
                                </div>
                                <p class="text-[11px] text-indigo-700 font-bold leading-relaxed tracking-tight">
                                    Selecting a class will retrieve the official enrollment list and quarterly records for the current term.
                                </p>
                            </div>

                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-2">Class Assignment</label>
                                
                                <div class="relative group/select">
                                    <select id="classSelector" required 
                                            class="w-full p-5 pl-14 bg-slate-50 border border-slate-200 rounded-[1.5rem] focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer font-black text-slate-700 text-sm shadow-sm group-hover/select:border-emerald-300">
                                        <option value="" disabled selected>-- Identify Subject & Section --</option>
                                        
                                        @foreach($assignedClasses as $sched)
                                            <option value="{{ $sched->subject_id }}|{{ $sched->section_id }}">
                                                {{-- DISPLAY: Subject Code — Section (Grade) --}}
                                                {{ $sched->subject->code }} — {{ $sched->section->name }} (Grade {{ $sched->section->grade_level }})
                                                
                                                {{-- ADDED: School Year Display --}}
                                                @if($sched->section->schoolYear)
                                                    [SY {{ $sched->section->schoolYear->school_year }}]
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-emerald-500 text-lg">
                                        <i class="fa-solid fa-chalkboard-user"></i>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none text-slate-300 group-hover/select:text-emerald-500 transition-colors">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- HIDDEN INPUTS POPULATED BY JS --}}
                            <input type="hidden" name="subject_id" id="input_subject_id">
                            <input type="hidden" name="section_id" id="input_section_id">

                            <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-[1.5rem] hover:bg-emerald-600 shadow-xl shadow-slate-200 hover:shadow-emerald-100 transition-all active:scale-[0.98] flex items-center justify-center gap-3 group/btn">
                                <span class="tracking-tight uppercase text-xs tracking-widest">Launch Grading Sheet</span>
                                <i class="fa-solid fa-arrow-right-long text-[10px] group-hover/btn:translate-x-2 transition-transform"></i>
                            </button>

                        </form>
                    @endif
                </div>

                <div class="mt-12 text-center">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.3em] flex items-center justify-center gap-3">
                        <span class="w-8 h-[1px] bg-slate-200"></span>
                        FMNHS SIS v2.0
                        <span class="w-8 h-[1px] bg-slate-200"></span>
                    </p>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>

    <script>
        // Logic to split the value "subject_id|section_id" and populate hidden inputs
        const selector = document.getElementById('classSelector');
        const subjectInput = document.getElementById('input_subject_id');
        const sectionIdInput = document.getElementById('input_section_id');

        if(selector) {
            selector.addEventListener('change', function() {
                const rawValue = this.value;
                if(rawValue) {
                    const parts = rawValue.split('|');
                    subjectInput.value = parts[0];
                    sectionIdInput.value = parts[1];
                }
            });
        }
    </script>
</body>
</html>