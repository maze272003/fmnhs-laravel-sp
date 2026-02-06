<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Enrollment History | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
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
                        <i class="fa-solid fa-timeline text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Timeline</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Enrollment History</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-5xl mx-auto w-full">
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Enrollment History</h1>
                <p class="text-slate-500 font-medium">Your academic journey and promotion timeline.</p>
            </div>

            {{-- Current Enrollment --}}
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Current Enrollment</p>
                        <h3 class="text-xl font-black text-slate-900">
                            Grade {{ $student->section->grade_level ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }}
                        </h3>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4 text-sm">
                    <span class="bg-slate-50 px-4 py-2 rounded-xl font-semibold text-slate-600">
                        <i class="fa-solid fa-calendar mr-2 text-slate-400"></i>S.Y. {{ $student->school_year ?? 'N/A' }}
                    </span>
                    <span class="bg-slate-50 px-4 py-2 rounded-xl font-semibold text-slate-600">
                        <i class="fa-solid fa-tag mr-2 text-slate-400"></i>{{ $student->enrollment_status ?? 'Enrolled' }}
                    </span>
                    @if($student->enrollment_badge)
                        <span class="bg-amber-50 text-amber-700 px-4 py-2 rounded-xl font-semibold">
                            <i class="fa-solid fa-star mr-2"></i>{{ $student->enrollment_badge }}
                        </span>
                    @endif
                    @if($student->is_alumni)
                        <span class="bg-purple-50 text-purple-700 px-4 py-2 rounded-xl font-semibold">
                            <i class="fa-solid fa-graduation-cap mr-2"></i>Alumni
                        </span>
                    @endif
                </div>
            </div>

            {{-- Promotion Timeline --}}
            @if($history->isNotEmpty())
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-100">
                        <h3 class="text-lg font-black text-slate-900">Promotion History</h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @foreach($history as $record)
                            <div class="p-8 flex items-start gap-6 hover:bg-slate-50/50 transition-colors">
                                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-arrow-up-right-dots"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-slate-800 mb-1">
                                        Grade {{ $record->from_grade_level }}
                                        <i class="fa-solid fa-arrow-right mx-2 text-slate-300 text-xs"></i>
                                        Grade {{ $record->to_grade_level }}
                                    </p>
                                    <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                                        <span>
                                            <i class="fa-solid fa-building-columns mr-1"></i>
                                            {{ $record->fromSection->name ?? 'N/A' }} → {{ $record->toSection->name ?? 'N/A' }}
                                        </span>
                                        <span>
                                            <i class="fa-solid fa-calendar mr-1"></i>
                                            S.Y. {{ $record->from_school_year }} → {{ $record->to_school_year }}
                                        </span>
                                        <span>
                                            <i class="fa-solid fa-user-tie mr-1"></i>
                                            {{ $record->promoted_by ?? 'Admin' }}
                                        </span>
                                        <span>
                                            <i class="fa-solid fa-clock mr-1"></i>
                                            {{ $record->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-12 text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 mx-auto border border-slate-100">
                        <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-200"></i>
                    </div>
                    <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No promotion history recorded yet</p>
                </div>
            @endif
        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
