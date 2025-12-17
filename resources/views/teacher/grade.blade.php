<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Sheet - {{ $section }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom scrollbar para sa table */
        .overflow-x-auto::-webkit-scrollbar { height: 6px; }
        .overflow-x-auto::-webkit-scrollbar-track { background: #f1f5f9; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        
        /* Highlight focus row */
        tr-focus:focus-within { background-color: #f0fdf4 !important; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600 mr-2 transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <a href="{{ route('teacher.grading.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 transition-colors">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h2 class="text-lg font-black text-slate-800 leading-none">
                        {{ $subject->code }}
                    </h2>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-1">{{ $section }}</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                <i class="fa-solid fa-user-tie text-emerald-600 text-xs"></i>
                <span class="text-xs font-black text-slate-500 uppercase tracking-tighter">Faculty Access</span>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8">

            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Saved!', text: "{{ session('success') }}", timer: 1500, showConfirmButton: false, borderRadius: '15px' });
                </script>
            @endif

            <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                
                <form action="{{ route('teacher.grades.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <input type="hidden" name="section" value="{{ $section }}">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-400 uppercase text-[10px] font-black tracking-[0.1em]">
                                    <th class="px-8 py-5 w-1/3">Student Name</th>
                                    <th class="px-4 py-5 text-center">1st Qtr</th>
                                    <th class="px-4 py-5 text-center">2nd Qtr</th>
                                    <th class="px-4 py-5 text-center">3rd Qtr</th>
                                    <th class="px-4 py-5 text-center">4th Qtr</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($students as $student)
                                    @php
                                        $grades = $student->grades->where('subject_id', $subject->id);
                                    @endphp
                                    <tr class="tr-focus transition-colors duration-200">
                                        <td class="px-8 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                </div>
                                                <span class="font-bold text-slate-700 text-sm">
                                                    {{ $student->last_name }}, {{ $student->first_name }}
                                                </span>
                                            </div>
                                        </td>
                                        
                                        @for($q = 1; $q <= 4; $q++)
                                            @php
                                                $grade = $grades->where('quarter', $q)->first();
                                                $val = $grade ? $grade->grade_value : '';
                                                // Dynamic color based on grade
                                                $colorClass = ($val != '' && $val < 75) ? 'text-rose-500' : 'text-slate-700';
                                            @endphp
                                            <td class="px-2 py-4 text-center">
                                                <input type="number" step="0.01" min="60" max="100"
                                                    name="grades[{{ $student->id }}][{{ $q }}]" 
                                                    value="{{ $val }}"
                                                    class="w-16 h-10 text-center font-bold text-sm bg-slate-50 border border-slate-100 rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all {{ $colorClass }}"
                                                    placeholder="--">
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-2 text-slate-400">
                            <i class="fa-solid fa-circle-info text-xs"></i>
                            <p class="text-[10px] font-bold uppercase tracking-wider">Grades are auto-validated (60-100)</p>
                        </div>
                        <button type="submit" class="w-full sm:w-auto bg-slate-900 text-white font-black py-3 px-10 rounded-2xl shadow-lg shadow-slate-200 hover:bg-emerald-600 hover:shadow-emerald-100 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            Update Records
                        </button>
                    </div>
                </form>
            </div>

            @if($students->isEmpty())
                <div class="text-center py-20 bg-white rounded-[2rem] border-2 border-dashed border-slate-200">
                    <i class="fa-solid fa-user-slash text-4xl text-slate-200 mb-4"></i>
                    <p class="text-slate-400 font-bold">No students found in this section.</p>
                </div>
            @endif

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>