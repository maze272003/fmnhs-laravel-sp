<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Registry | {{ $section->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        
        /* Highlight focus row */
        .attendance-row:hover { background-color: #f0fdf4 !important; }
        input[type="radio"] { cursor: pointer; scale: 1.2; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="glass-header sticky top-0 z-40 px-6 py-4 flex justify-between items-center border-b border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.attendance.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition-all">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h2 class="text-lg font-black text-slate-900 leading-none">Attendance Sheet</h2>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em] mt-1">
                        <i class="fa-regular fa-calendar-check mr-1"></i> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex flex-col text-right">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Target Section</span>
                    <span class="text-xs font-black text-slate-700 uppercase">Grade {{ $section->grade_level }} - {{ $section->name }}</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm">
                    <i class="fa-solid fa-users-viewfinder text-xs"></i>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-10 max-w-7xl mx-auto w-full">
            
            @if(session('success'))
                <script>
                    Swal.fire({
                        icon: 'success', 
                        title: 'Registry Updated', 
                        text: '{{ session("success") }}', 
                        timer: 2000, 
                        showConfirmButton: false,
                        borderRadius: '24px'
                    })
                </script>
            @endif

            <div class="mb-8">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Class Registry</h1>
                <p class="text-slate-500 font-medium">Daily student presence tracking for the current academic session.</p>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-200/50 overflow-hidden">
                <form action="{{ route('teacher.attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                    <input type="hidden" name="section_id" value="{{ $section->id }}">
                    <input type="hidden" name="date" value="{{ $date }}">

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                                    <th class="px-10 py-6 min-w-[320px]">Student Identity</th>
                                    <th class="px-6 py-6 text-center bg-emerald-50/30 text-emerald-600 border-x border-slate-100/50">Present</th>
                                    <th class="px-6 py-6 text-center bg-amber-50/30 text-amber-600 border-x border-slate-100/50">Late</th>
                                    <th class="px-6 py-6 text-center bg-rose-50/30 text-rose-600 border-x border-slate-100/50">Absent</th>
                                    <th class="px-6 py-6 text-center bg-blue-50/30 text-blue-600 border-x border-slate-100/50">Excused</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($students as $student)
                                    @php
                                        $status = isset($attendances[$student->id]) ? $attendances[$student->id]->status : 'present';
                                    @endphp
                                    <tr class="attendance-row transition-all duration-300 group">
                                        <td class="px-10 py-5">
                                            <div class="flex items-center gap-4">
                                                <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-[10px] font-black group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-black text-slate-800 text-sm tracking-tight group-hover:text-emerald-700 transition-colors">
                                                        {{ $student->last_name }}, {{ $student->first_name }}
                                                    </span>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">LRN: {{ $student->lrn }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-5 text-center border-x border-slate-50/50">
                                            <label class="flex items-center justify-center w-full h-10 cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="present" {{ $status == 'present' ? 'checked' : '' }} 
                                                    class="w-5 h-5 text-emerald-600 border-slate-200 focus:ring-emerald-500/20">
                                            </label>
                                        </td>

                                        <td class="px-4 py-5 text-center border-x border-slate-50/50">
                                            <label class="flex items-center justify-center w-full h-10 cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="late" {{ $status == 'late' ? 'checked' : '' }} 
                                                    class="w-5 h-5 text-amber-500 border-slate-200 focus:ring-amber-500/20">
                                            </label>
                                        </td>

                                        <td class="px-4 py-5 text-center border-x border-slate-50/50">
                                            <label class="flex items-center justify-center w-full h-10 cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="absent" {{ $status == 'absent' ? 'checked' : '' }} 
                                                    class="w-5 h-5 text-rose-600 border-slate-200 focus:ring-rose-500/20">
                                            </label>
                                        </td>

                                        <td class="px-4 py-5 text-center border-x border-slate-50/50">
                                            <label class="flex items-center justify-center w-full h-10 cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="excused" {{ $status == 'excused' ? 'checked' : '' }} 
                                                    class="w-5 h-5 text-blue-600 border-slate-200 focus:ring-blue-500/20">
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-8">
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-50">
                                    <i class="fa-solid fa-shield-check text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Status Tracking</p>
                                    <p class="text-[10px] font-bold text-slate-500">Live synchronization enabled</p>
                                </div>
                            </div>
                            <div class="h-8 w-[1px] bg-slate-200 hidden md:block"></div>
                            <p class="text-[10px] font-bold text-slate-400 italic max-w-[200px]">Ensure all radio buttons are correctly marked before saving.</p>
                        </div>
                        
                        <button type="submit" class="w-full sm:w-auto bg-slate-900 text-white font-black py-4 px-14 rounded-[1.5rem] shadow-xl shadow-slate-200 hover:bg-emerald-600 hover:shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center gap-3 uppercase text-xs tracking-widest">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Commit Records</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-12 flex flex-wrap justify-center gap-8">
                <div class="flex items-center gap-3 px-5 py-2.5 bg-white rounded-2xl border border-slate-100 shadow-sm group hover:border-emerald-200 transition-all">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 group-hover:animate-pulse"></span>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Present</span>
                </div>
                <div class="flex items-center gap-3 px-5 py-2.5 bg-white rounded-2xl border border-slate-100 shadow-sm group hover:border-amber-200 transition-all">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 group-hover:animate-pulse"></span>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Late</span>
                </div>
                <div class="flex items-center gap-3 px-5 py-2.5 bg-white rounded-2xl border border-slate-100 shadow-sm group hover:border-rose-200 transition-all">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 group-hover:animate-pulse"></span>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Absent</span>
                </div>
                <div class="flex items-center gap-3 px-5 py-2.5 bg-white rounded-2xl border border-slate-100 shadow-sm group hover:border-blue-200 transition-all">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 group-hover:animate-pulse"></span>
                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Excused</span>
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>