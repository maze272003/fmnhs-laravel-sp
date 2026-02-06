<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Sheet | {{ $section->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        /* High-end input focus effect */
        .grade-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.1), 0 4px 6px -2px rgba(16, 185, 129, 0.05);
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40 px-6 py-4 flex justify-between items-center border-b border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.grading.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition-all">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900 leading-none">{{ $subject->name }}</h2>
                        <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest">{{ $subject->code }}</span>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">
                        Grade {{ $section->grade_level }} — {{ $section->name }}
                    </p>
                </div>
            </div>
            
            @include('components.teacher.header_details')
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">

            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Records Updated', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false, borderRadius: '24px' });
                </script>
            @endif

            <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Grading Sheet</h1>
                    <p class="text-slate-500 font-medium">Input and archive student quarterly assessments.</p>
                </div>
                
                <div class="flex items-center gap-3 bg-emerald-50 px-4 py-2 rounded-2xl border border-emerald-100">
                    <i class="fa-solid fa-circle-info text-emerald-600 text-xs"></i>
                    <p class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Auto-Saving Enabled</p>
                </div>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/60 border border-slate-200/50 overflow-hidden">
                
                <form action="{{ route('teacher.grades.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <input type="hidden" name="school_year" value="{{ \App\Helpers\SchoolYearHelper::current() }}">

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                                    <th class="px-10 py-6 min-w-[300px]">Student Identity</th>
                                    <th class="px-6 py-6 text-center">1st Quarter</th>
                                    <th class="px-6 py-6 text-center">2nd Quarter</th>
                                    <th class="px-6 py-6 text-center">3rd Quarter</th>
                                    <th class="px-6 py-6 text-center">4th Quarter</th>
                                    <th class="px-10 py-6 text-center">Final Avg</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($students as $student)
                                    @php
                                        $q1 = $student->grades->where('quarter', 1)->first()?->grade_value;
                                        $q2 = $student->grades->where('quarter', 2)->first()?->grade_value;
                                        $q3 = $student->grades->where('quarter', 3)->first()?->grade_value;
                                        $q4 = $student->grades->where('quarter', 4)->first()?->grade_value;

                                        $avg = collect([$q1, $q2, $q3, $q4])->filter()->avg();
                                    @endphp
                                    <tr class="hover:bg-emerald-50/20 transition-all duration-300 group">
                                        <td class="px-10 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-[10px] font-black group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-black text-slate-800 text-sm group-hover:text-emerald-600 transition-colors">
                                                        {{ $student->last_name }}, {{ $student->first_name }}
                                                    </span>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">LRN: {{ $student->lrn }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        @for($q = 1; $q <= 4; $q++)
                                            @php 
                                                $currentVal = $student->grades->where('quarter', $q)->first()?->grade_value;
                                                $isFailed = $currentVal && $currentVal < 75;
                                            @endphp
                                            <td class="px-4 py-5 text-center">
                                                <input type="number" step="0.01" min="60" max="100"
                                                    name="grades[{{ $student->id }}][{{ $q }}]" 
                                                    value="{{ $currentVal }}"
                                                    class="grade-input w-20 p-3 text-center font-black text-sm bg-slate-50 border border-slate-100 rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all {{ $isFailed ? 'text-rose-500' : 'text-slate-700' }}"
                                                    placeholder="--">
                                            </td>
                                        @endfor

                                        <td class="px-10 py-5 text-center">
                                            <span class="text-lg font-black {{ $avg && $avg < 75 ? 'text-rose-500' : ($avg ? 'text-emerald-600' : 'text-slate-200') }}">
                                                {{ $avg ? number_format($avg, 1) : '--' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-300 border border-slate-100">
                                <i class="fa-solid fa-keyboard text-xs"></i>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest max-w-[200px] leading-relaxed">
                                Use the <span class="text-emerald-600">TAB</span> key to navigate quickly between columns.
                            </p>
                        </div>
                        
                        <button type="submit" class="w-full sm:w-auto bg-slate-900 text-white font-black py-4 px-12 rounded-[1.5rem] shadow-xl shadow-slate-200 hover:bg-emerald-600 hover:shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-3 uppercase text-xs tracking-widest">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            Archive Grades
                        </button>
                    </div>
                </form>
            </div>

            @if($students->isEmpty())
                <div class="text-center py-24 bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-users-slash text-3xl text-slate-200"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-widest">Section Empty</h3>
                    <p class="text-slate-400 font-medium">No students are currently enrolled in this class load.</p>
                </div>
            @endif

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>