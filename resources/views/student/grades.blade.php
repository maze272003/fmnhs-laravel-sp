<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Academic Performance | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
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
                    <div class="w-10 h-10 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-medal text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Scholarship</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grades & Performance</p>
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

                <img src="{{ $student->avatar_url }}" 
                     alt="Profile" 
                     class="w-10 h-10 rounded-2xl object-cover border-2 border-white shadow-md">
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            <div class="flex flex-col md:flex-row md:justify-between md:items-end gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Academic Records</h1>
                    <p class="text-slate-500 font-medium">Detailed breakdown of your quarterly performance{{ $schoolYear ? " for S.Y. {$schoolYear}" : '' }}.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    {{-- School Year Filter --}}
                    <form method="GET" action="{{ route('student.grades') }}" class="flex items-center gap-2">
                        <select name="school_year" onchange="this.form.submit()" class="px-4 py-3 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                            <option value="">All School Years</option>
                            @foreach($schoolYears as $sy)
                                <option value="{{ $sy }}" {{ $schoolYear == $sy ? 'selected' : '' }}>S.Y. {{ $sy }}</option>
                            @endforeach
                        </select>
                    </form>

                    <a href="{{ route('student.grades.pdf', ['school_year' => $schoolYear]) }}" class="flex items-center justify-center gap-3 px-8 py-4 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl shadow-rose-100 transition-all active:scale-95 group">
                        <i class="fa-solid fa-file-pdf text-sm group-hover:scale-110 transition-transform"></i>
                        Generate Report Card
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                @php
                    $allAverages = [];
                    $passedCount = 0;
                    foreach($subjects as $sub) {
                        $g = $sub->grades->pluck('grade_value')->filter();
                        if($g->isNotEmpty()) {
                            $avg = $g->avg();
                            $allAverages[] = $avg;
                            if($avg >= 75) $passedCount++;
                        }
                    }
                    $gwa = count($allAverages) > 0 ? array_sum($allAverages) / count($allAverages) : 0;
                @endphp

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex items-center gap-6">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-[1.5rem] flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">General Average</p>
                        <h3 class="text-3xl font-black text-slate-900">{{ $gwa > 0 ? number_format($gwa, 2) : '--' }}</h3>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex items-center gap-6">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-[1.5rem] flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Subjects Passed</p>
                        <h3 class="text-3xl font-black text-slate-900">{{ $passedCount }} / {{ $subjects->count() }}</h3>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex items-center gap-6">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-[1.5rem] flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-ranking-star"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                        <h3 class="text-xl font-black text-indigo-600 uppercase tracking-tight">
                            {{ $gwa >= 75 ? 'Good Standing' : ($gwa > 0 ? 'Probation' : 'No Data') }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/60 border border-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                                <th class="px-10 py-6 min-w-[280px]">Subject Information</th>
                                <th class="px-6 py-6 text-center">Q1</th>
                                <th class="px-6 py-6 text-center">Q2</th>
                                <th class="px-6 py-6 text-center">Q3</th>
                                <th class="px-6 py-6 text-center">Q4</th>
                                <th class="px-8 py-6 text-center">Average</th>
                                <th class="px-10 py-6 text-center">Result</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($subjects as $subject)
                                @php
                                    $q1 = $subject->grades->where('quarter', 1)->first()?->grade_value;
                                    $q2 = $subject->grades->where('quarter', 2)->first()?->grade_value;
                                    $q3 = $subject->grades->where('quarter', 3)->first()?->grade_value;
                                    $q4 = $subject->grades->where('quarter', 4)->first()?->grade_value;

                                    $gradeList = collect([$q1, $q2, $q3, $q4])->filter();
                                    $average = $gradeList->isNotEmpty() ? $gradeList->avg() : null;
                                    $isPassed = $average >= 75;
                                @endphp

                                <tr class="hover:bg-indigo-50/20 transition-all duration-300 group">
                                    <td class="px-10 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-black group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm">
                                                {{ substr($subject->code, 0, 2) }}
                                            </div>
                                            <div class="flex flex-col leading-tight">
                                                <span class="font-black text-slate-800 text-base tracking-tight group-hover:text-blue-600 transition-colors">{{ $subject->name }}</span>
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">{{ $subject->code }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-6 text-center text-sm font-bold {{ $q1 ? 'text-slate-600' : 'text-slate-200' }}">{{ $q1 ?? '--' }}</td>
                                    <td class="px-6 py-6 text-center text-sm font-bold {{ $q2 ? 'text-slate-600' : 'text-slate-200' }}">{{ $q2 ?? '--' }}</td>
                                    <td class="px-6 py-6 text-center text-sm font-bold {{ $q3 ? 'text-slate-600' : 'text-slate-200' }}">{{ $q3 ?? '--' }}</td>
                                    <td class="px-6 py-6 text-center text-sm font-bold {{ $q4 ? 'text-slate-600' : 'text-slate-200' }}">{{ $q4 ?? '--' }}</td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="text-lg font-black {{ $isPassed ? 'text-blue-600' : 'text-rose-500' }}">
                                            {{ $average ? number_format($average, 2) : '--' }}
                                        </span>
                                    </td>
                                    <td class="px-10 py-6 text-center">
                                        @if($average)
                                            <span class="inline-flex items-center gap-2 {{ $isPassed ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100' }} px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $isPassed ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                {{ $isPassed ? 'Passed' : 'Failed' }}
                                            </span>
                                        @else
                                            <span class="bg-slate-50 text-slate-400 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-slate-100">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-10 py-24 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 border border-slate-100">
                                                <i class="fa-solid fa-folder-closed text-3xl text-slate-200"></i>
                                            </div>
                                            <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No grades recorded yet</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/40 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Official Academic Record {{ $schoolYear ? "• S.Y. {$schoolYear}" : '' }}
                    </p>
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-blue-200"></i>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Verified by Registrar</span>
                    </div>
                </div>
            </div>

        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>