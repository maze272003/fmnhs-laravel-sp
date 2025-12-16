<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm border-b border-slate-100 sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h2 class="text-xl font-bold text-emerald-600 tracking-tight">My Classes</h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                    Faculty Portal
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">

            <div class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Class Overview</h1>
                    <p class="text-slate-500 font-medium">Manage your active subjects and sections for this semester.</p>
                </div>
                <a href="{{ route('teacher.grading.index') }}" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-emerald-100 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Grade New Class
                </a>
            </div>

            @if($classes->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($classes as $class)
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:border-emerald-100 transition-all duration-500 overflow-hidden group relative">
                            
                            <div class="p-8 pb-6 bg-gradient-to-br from-white to-slate-50 relative overflow-hidden">
                                <div class="flex justify-between items-start mb-4 relative z-10">
                                    <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-wider border border-emerald-100">
                                        {{ $class['subject']->code }}
                                    </span>
                                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-slate-400 group-hover:text-emerald-500 transition-colors">
                                        <i class="fa-solid fa-layer-group text-sm"></i>
                                    </div>
                                </div>
                                
                                <h3 class="text-xl font-black text-slate-800 mb-1 group-hover:text-emerald-600 transition-colors truncate" title="{{ $class['subject']->name }}">
                                    {{ Str::limit($class['subject']->name, 25) }}
                                </h3>
                                <p class="text-slate-400 font-bold text-xs uppercase tracking-tighter">
                                    Section: <span class="text-emerald-600">{{ $class['section'] }}</span>
                                </p>

                                <i class="fa-solid fa-graduation-cap text-7xl absolute right-[-10px] bottom-[-10px] text-slate-100 opacity-50 group-hover:rotate-12 transition-transform duration-500"></i>
                            </div>

                            <div class="px-8 py-6 grid grid-cols-2 gap-4 border-t border-slate-50">
                                <div class="text-left border-r border-slate-100">
                                    <div class="text-2xl font-black text-slate-800">{{ $class['student_count'] }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Enrolled</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-black text-emerald-600">{{ number_format($class['average_grade'], 1) }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase font-black tracking-widest">Avg Grade</div>
                                </div>
                            </div>

                            <div class="p-6 pt-0">
                                <a href="{{ route('teacher.grading.show', ['subject_id' => $class['subject']->id, 'section' => $class['section']]) }}" 
                                   class="flex items-center justify-center gap-2 w-full py-3 bg-slate-900 text-white font-bold rounded-2xl hover:bg-emerald-600 shadow-lg shadow-slate-100 hover:shadow-emerald-100 transition-all group/btn">
                                    <span>Manage Grades</span>
                                    <i class="fa-solid fa-chevron-right text-[10px] group-hover/btn:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-[2.5rem] border-2 border-dashed border-slate-200">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                        <i class="fa-solid fa-folder-open text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">No Classes Assigned</h3>
                    <p class="text-slate-500 max-w-sm mx-auto mb-8 font-medium">It looks like you haven't started grading any sections yet for this school year.</p>
                    <a href="{{ route('teacher.grading.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-2xl font-black shadow-xl shadow-emerald-100 transition-all inline-flex items-center gap-3">
                        <i class="fa-solid fa-plus text-sm"></i> Start Your First Class
                    </a>
                </div>
            @endif

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>