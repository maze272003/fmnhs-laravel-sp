<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Permanent Record | {{ $student->last_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Smooth dropdown animation */
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down { animation: fadeIn 0.3s ease-out; }

        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .shadow-sm, .shadow-xl { box-shadow: none !important; }
            .border { border: 1px solid #ddd !important; }
            details[open] summary ~ * { animation: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <div class="max-w-5xl mx-auto p-8">
        
        {{-- Header / Navigation --}}
        <div class="flex justify-between items-center mb-8 no-print">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.students.index') }}" class="w-10 h-10 flex items-center justify-center bg-white rounded-full shadow-sm hover:bg-slate-100 transition-colors text-slate-500">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Student Permanent Record</h1>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-widest">Form 137 / Academic History</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="window.print()" class="px-6 py-2 bg-slate-800 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-700 shadow-lg transition-all">
                    <i class="fa-solid fa-print mr-2"></i> Print Record
                </button>
            </div>
        </div>

        {{-- Student Profile Card --}}
        <div class="bg-white rounded-[2rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 flex flex-col md:flex-row gap-8 items-start">
            <div class="w-24 h-24 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-3xl shrink-0">
                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
            </div>
            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Full Name</label>
                    <p class="font-bold text-lg text-slate-800">{{ $student->last_name }}, {{ $student->first_name }}</p>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">LRN</label>
                    <p class="font-mono font-bold text-lg text-slate-600">{{ $student->lrn }}</p>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Current Status</label>
                    <div class="mt-1">
                        @if($student->is_alumni)
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-black uppercase">Alumni / Graduated</span>
                        @else
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-black uppercase">{{ $student->enrollment_status }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email Address</label>
                    <p class="font-bold text-slate-700">{{ $student->email }}</p>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Current/Last Section</label>
                    <p class="font-bold text-slate-700">
                        @if($student->section)
                            Grade {{ $student->section->grade_level }} - {{ $student->section->name }}
                        @else
                            <span class="italic text-slate-400">Not Assigned</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Academic History (Grades) - ACCORDION STYLE --}}
        <div class="space-y-8 mb-12">
            <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-star text-indigo-500"></i> Academic Performance
            </h3>
            
            {{-- 1. Group by School Year --}}
            @php
                $groupedGrades = $student->grades->sortByDesc('school_year_id')->groupBy('school_year_id');
            @endphp

            @forelse($groupedGrades as $yearId => $yearGrades)
                @php
                    $schoolYearConfig = $yearGrades->first()->schoolYearConfig;
                    $yearLabel = $schoolYearConfig->school_year ?? 'Unknown Year';
                    
                    // 2. Group by Subject within the Year
                    $gradesBySubject = $yearGrades->groupBy('subject_id');
                @endphp

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden break-inside-avoid">
                    {{-- School Year Header --}}
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                        <span class="font-black text-indigo-900 text-sm uppercase tracking-widest">
                            SY: {{ $yearLabel }}
                        </span>
                        <span class="text-xs font-bold text-slate-400 bg-white px-3 py-1 rounded-full border border-slate-200">
                            {{ $gradesBySubject->count() }} Subjects
                        </span>
                    </div>

                    {{-- Subjects List --}}
                    <div class="divide-y divide-slate-50">
                        @foreach($gradesBySubject as $subjectId => $subjectGrades)
                            @php
                                $subject = $subjectGrades->first()->subject;
                                // Calculate Final Average
                                $finalGrade = $subjectGrades->avg('grade_value');
                                $isPassed = $finalGrade >= 75;
                            @endphp

                            {{-- Accordion Item --}}
                            <details class="group">
                                {{-- Main Summary Row (Visible) --}}
                                <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-slate-50 transition-colors list-none">
                                    <div class="flex items-center gap-4">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-open:bg-indigo-100 group-open:text-indigo-600 transition-colors">
                                            <i class="fa-solid fa-chevron-right text-[10px] group-open:rotate-90 transition-transform duration-200"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-700 text-sm">{{ $subject->name ?? 'Unknown' }}</h4>
                                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">{{ $subject->code ?? '' }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-6">
                                        <div class="text-right">
                                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-widest">Final</span>
                                            <span class="block font-black text-lg {{ $isPassed ? 'text-slate-800' : 'text-rose-500' }}">
                                                {{ number_format($finalGrade, 2) }}
                                            </span>
                                        </div>
                                        <div class="w-20 text-right">
                                            @if($isPassed)
                                                <span class="px-2 py-1 rounded text-[10px] bg-emerald-50 text-emerald-600 font-black uppercase tracking-wide border border-emerald-100">Passed</span>
                                            @else
                                                <span class="px-2 py-1 rounded text-[10px] bg-rose-50 text-rose-600 font-black uppercase tracking-wide border border-rose-100">Failed</span>
                                            @endif
                                        </div>
                                    </div>
                                </summary>

                                {{-- Dropdown Details (Hidden by default, shown on click) --}}
                                <div class="bg-slate-50/50 p-4 border-t border-slate-100 grid grid-cols-4 gap-4 animate-fade-in-down">
                                    @foreach($subjectGrades->sortBy('quarter') as $g)
                                        <div class="bg-white p-3 rounded-xl border border-slate-100 text-center shadow-sm">
                                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wide">Quarter {{ $g->quarter }}</span>
                                            <span class="block font-black text-slate-700 text-lg mt-1">{{ $g->grade_value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-slate-50 rounded-[2rem] p-10 text-center border border-dashed border-slate-300">
                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2"></i>
                    <p class="text-slate-400 font-bold">No academic records found for this student.</p>
                </div>
            @endforelse
        </div>

        {{-- Promotion History --}}
        <div class="break-inside-avoid">
            <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-indigo-500"></i> Promotion History
            </h3>
            <div class="space-y-4">
                @forelse($student->promotionHistory->sortByDesc('created_at') as $history)
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 flex items-center justify-between shadow-sm">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="font-black text-slate-800 text-sm">
                                    Grade {{ $history->from_grade_level }}
                                    <i class="fa-solid fa-arrow-right text-slate-300 text-xs mx-2"></i>
                                    {{ is_numeric($history->to_grade_level) ? 'Grade ' . $history->to_grade_level : $history->to_grade_level }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">
                                Processed by <span class="text-indigo-600">{{ $history->promoted_by }}</span> on {{ $history->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="block text-xs font-black text-slate-400 uppercase tracking-widest">School Year</span>
                            <span class="block font-bold text-slate-700">{{ $history->to_school_year }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 font-medium bg-slate-100/50 rounded-3xl border border-dashed border-slate-200">
                        No promotion history recorded yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</body>
</html>