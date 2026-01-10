<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Teaching Loads | Faculty Portal</title>
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
        
        <header class="glass-header border-b border-slate-200/60 sticky top-0 z-40 px-8 py-5 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-100">
                        <i class="fa-solid fa-chalkboard text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">My Classes</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Teaching Assignments</p>
                    </div>
                </div>
            </div>
            @include('components.teacher.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

            <div class="mb-12 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Class Overview</h1>
                    <p class="text-slate-500 font-medium">Monitoring student population and academic performance per section.</p>
                </div>
                <a href="{{ route('teacher.grading.index') }}" class="w-full md:w-auto bg-slate-900 hover:bg-emerald-600 text-white px-8 py-4 rounded-[1.5rem] font-black text-[11px] uppercase tracking-widest shadow-xl shadow-slate-200 transition-all active:scale-95 flex items-center justify-center gap-3">
                    <i class="fa-solid fa-plus-circle text-xs"></i> Open Grading Sheet
                </a>
            </div>

            @if($classes->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($classes as $class)
                        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200/60 hover:shadow-2xl hover:shadow-emerald-100/50 transition-all duration-500 overflow-hidden group relative">
                            
                            <div class="p-8 pb-6 relative overflow-hidden">
                                <div class="flex justify-between items-start mb-6 relative z-10">
                                    <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-4 py-1.5 rounded-xl uppercase tracking-widest border border-emerald-100 shadow-sm">
                                        {{ $class['subject']->code }}
                                    </span>
                                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300 shadow-inner">
                                        <i class="fa-solid fa-layer-group text-xs"></i>
                                    </div>
                                </div>
                                
                                <h3 class="text-2xl font-black text-slate-900 mb-2 group-hover:text-emerald-600 transition-colors truncate tracking-tight" title="{{ $class['subject']->name }}">
                                    {{ $class['subject']->name }}
                                </h3>
                                
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Assigned to:</span>
                                    <span class="text-xs font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100">
                                        Grade {{ $class['section']->grade_level }} - {{ $class['section']->name }}
                                    </span>
                                </div>

                                <i class="fa-solid fa-graduation-cap text-[8rem] absolute right-[-20px] bottom-[-30px] text-slate-50 opacity-50 group-hover:rotate-12 group-hover:scale-110 transition-transform duration-700"></i>
                            </div>

                            <div class="px-8 py-8 grid grid-cols-2 gap-4 border-t border-slate-50 bg-slate-50/30">
                                <div class="text-left border-r border-slate-100">
                                    <div class="text-3xl font-black text-slate-900 tracking-tighter">{{ $class['student_count'] }}</div>
                                    <div class="text-[9px] text-slate-400 uppercase font-black tracking-[0.2em] mt-1">Students</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-3xl font-black text-emerald-600 tracking-tighter">{{ number_format($class['average_grade'], 1) }}</div>
                                    <div class="text-[9px] text-slate-400 uppercase font-black tracking-[0.2em] mt-1">Class Avg</div>
                                </div>
                            </div>

                            <div class="p-8 pt-0">
                                <a href="{{ route('teacher.grading.show', ['subject_id' => $class['subject']->id, 'section_id' => $class['section']->id]) }}" 
                                   class="flex items-center justify-center gap-3 w-full py-4 bg-white text-slate-900 font-black text-[10px] uppercase tracking-widest rounded-2xl border-2 border-slate-100 hover:border-emerald-600 hover:text-emerald-600 hover:bg-emerald-50 transition-all group/btn shadow-sm">
                                    <span>Manage Record</span>
                                    <i class="fa-solid fa-arrow-right-long group-hover/btn:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-32 bg-white rounded-[4rem] border-2 border-dashed border-slate-200 shadow-inner">
                    <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 relative">
                        <i class="fa-solid fa-folder-open text-5xl text-slate-200"></i>
                        <div class="absolute -top-2 -right-2 w-10 h-10 bg-white rounded-2xl shadow-xl flex items-center justify-center border border-slate-50">
                            <i class="fa-solid fa-magnifying-glass text-emerald-400 text-xs"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-3 uppercase">No Assigned Classes</h3>
                    <p class="text-slate-400 max-w-sm mx-auto mb-10 font-medium leading-relaxed">It looks like your teaching assignments haven't been synchronized yet. Contact the registrar if this is a mistake.</p>
                    <a href="{{ route('teacher.grading.index') }}" class="bg-emerald-600 hover:bg-slate-900 text-white px-10 py-4 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-emerald-100 transition-all inline-flex items-center gap-3">
                        <i class="fa-solid fa-arrow-rotate-right text-xs"></i> Sync Database
                    </a>
                </div>
            @endif

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>