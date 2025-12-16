<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-pen-nib text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Grading Portal</h2>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Faculty Access
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 flex items-center justify-center">
            
            <div class="w-full max-w-lg">
                <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden">
                    
                    <i class="fa-solid fa-stamp absolute -right-8 -bottom-8 text-9xl text-slate-50 opacity-50 pointer-events-none rotate-12"></i>

                    <div class="text-center mb-10 relative z-10">
                        <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-3xl shadow-inner group transition-transform hover:scale-105">
                            <i class="fa-solid fa-scroll"></i>
                        </div>
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Select Class</h1>
                        <p class="text-slate-400 font-medium mt-2">Pick a subject and section to start grading students.</p>
                    </div>

                    @if($assignedClasses->isEmpty())
                        <div class="bg-rose-50 border border-rose-100 rounded-2xl p-6 text-center text-rose-800 mb-6 relative z-10">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm text-rose-500">
                                <i class="fa-solid fa-user-lock text-xl"></i>
                            </div>
                            <p class="font-black uppercase text-xs tracking-widest mb-1">Access Restricted</p>
                            <p class="text-sm font-medium opacity-80 leading-relaxed">No classes assigned to your account yet. Please coordinate with the Registrar or Admin.</p>
                        </div>
                    @else
                        <form action="{{ route('teacher.grading.show') }}" method="GET" class="relative z-10">
                            
                            <div class="space-y-6">
                                <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-2xl flex items-start gap-3">
                                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                                    <p class="text-xs text-blue-700 font-bold leading-relaxed tracking-tight">
                                        Selecting a class will load the official student roster and current quarterly grade records.
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Assigned Workload</label>
                                    
                                    <div class="relative group">
                                        <select id="classSelector" required class="w-full p-4 pl-12 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all appearance-none cursor-pointer font-bold text-slate-700 text-sm">
                                            <option value="" disabled selected>-- Search Subject or Section --</option>
                                            
                                            @foreach($assignedClasses as $sched)
                                                <option value="{{ $sched->subject_id }}|{{ $sched->section }}">
                                                    {{ $sched->subject->code }} — {{ $sched->section }} ({{ $sched->subject->name }})
                                                </option>
                                            @endforeach
                                        </select>
                                        
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-emerald-500">
                                            <i class="fa-solid fa-chalkboard-user"></i>
                                        </div>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-300 group-hover:text-emerald-500 transition-colors">
                                            <i class="fa-solid fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="subject_id" id="input_subject_id">
                                <input type="hidden" name="section" id="input_section">
                            </div>

                            <button type="submit" class="w-full mt-10 bg-slate-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 shadow-xl shadow-slate-200 hover:shadow-emerald-100 transition-all active:scale-[0.98] flex items-center justify-center gap-3 group">
                                <span class="tracking-tight">Open Grading Sheet</span>
                                <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                            </button>

                        </form>
                    @endif
                </div>

                <div class="mt-8 text-center">
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest flex items-center justify-center gap-2">
                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                        Student Information System v1.0
                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                    </p>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>

    <script>
        const selector = document.getElementById('classSelector');
        const subjectInput = document.getElementById('input_subject_id');
        const sectionInput = document.getElementById('input_section');

        if(selector) {
            selector.addEventListener('change', function() {
                const rawValue = this.value;
                if(rawValue) {
                    const parts = rawValue.split('|');
                    subjectInput.value = parts[0];
                    sectionInput.value = parts[1];
                }
            });
        }
    </script>
</body>
</html>