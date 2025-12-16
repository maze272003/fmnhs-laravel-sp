<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Custom radio button styles for better visibility */
        input[type="radio"]:checked {
            border-width: 5px;
            border-color: currentColor;
        }
        /* Row focus effect */
        tr:focus-within {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.attendance.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-50 text-slate-400 transition-all">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h2 class="text-lg font-black text-slate-900 leading-none">Attendance Sheet</h2>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mt-1">
                        <i class="fa-regular fa-calendar-check mr-1"></i> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                    </p>
                </div>
            </div>
            <div class="bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 hidden sm:block">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">{{ $section }}</span>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8">
            @if(session('success'))
                <script>
                    Swal.fire({
                        icon: 'success', 
                        title: 'Attendance Saved', 
                        text: '{{ session("success") }}', 
                        timer: 2000, 
                        showConfirmButton: false,
                        borderRadius: '20px'
                    })
                </script>
            @endif

            <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <form action="{{ route('teacher.attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                    <input type="hidden" name="section" value="{{ $section }}">
                    <input type="hidden" name="date" value="{{ $date }}">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                    <th class="px-8 py-5">Student Name</th>
                                    <th class="px-4 py-5 text-center bg-emerald-50/30 text-emerald-600">Present</th>
                                    <th class="px-4 py-5 text-center bg-amber-50/30 text-amber-600">Late</th>
                                    <th class="px-4 py-5 text-center bg-rose-50/30 text-rose-600">Absent</th>
                                    <th class="px-4 py-5 text-center bg-blue-50/30 text-blue-600">Excused</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($students as $student)
                                    @php
                                        $status = isset($attendances[$student->id]) ? $attendances[$student->id]->status : 'present';
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 group-hover:bg-white transition-colors">
                                                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                </div>
                                                <span class="font-bold text-slate-700 text-sm tracking-tight">
                                                    {{ $student->last_name }}, {{ $student->first_name }}
                                                </span>
                                            </div>
                                        </td>
                                        
                                        <td class="px-4 py-5 text-center group-hover:bg-emerald-50/20 transition-colors">
                                            <label class="inline-flex items-center justify-center w-full h-full cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="present" {{ $status == 'present' ? 'checked' : '' }} 
                                                    class="w-6 h-6 text-emerald-600 bg-slate-100 border-slate-200 focus:ring-emerald-500 transition-all cursor-pointer">
                                            </label>
                                        </td>

                                        <td class="px-4 py-5 text-center group-hover:bg-amber-50/20 transition-colors">
                                            <label class="inline-flex items-center justify-center w-full h-full cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="late" {{ $status == 'late' ? 'checked' : '' }} 
                                                    class="w-6 h-6 text-amber-500 bg-slate-100 border-slate-200 focus:ring-amber-500 transition-all cursor-pointer">
                                            </label>
                                        </td>

                                        <td class="px-4 py-5 text-center group-hover:bg-rose-50/20 transition-colors">
                                            <label class="inline-flex items-center justify-center w-full h-full cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="absent" {{ $status == 'absent' ? 'checked' : '' }} 
                                                    class="w-6 h-6 text-rose-600 bg-slate-100 border-slate-200 focus:ring-rose-500 transition-all cursor-pointer">
                                            </label>
                                        </td>

                                        <td class="px-4 py-5 text-center group-hover:bg-blue-50/20 transition-colors">
                                            <label class="inline-flex items-center justify-center w-full h-full cursor-pointer">
                                                <input type="radio" name="status[{{ $student->id }}]" value="excused" {{ $status == 'excused' ? 'checked' : '' }} 
                                                    class="w-6 h-6 text-blue-600 bg-slate-100 border-slate-200 focus:ring-blue-500 transition-all cursor-pointer">
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-6">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Auto-saved Draft</span>
                            </div>
                            <div class="h-4 w-[1px] bg-slate-200 hidden sm:block"></div>
                            <p class="text-[10px] font-bold text-slate-400 italic">Verify all entries before final submission.</p>
                        </div>
                        
                        <button type="submit" class="w-full sm:w-auto bg-slate-900 text-white font-black py-4 px-12 rounded-2xl shadow-xl shadow-slate-200 hover:bg-emerald-600 hover:shadow-emerald-100 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Confirm & Save Attendance</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="mt-8 flex flex-wrap justify-center gap-6">
                <div class="flex items-center gap-2 px-3 py-1 bg-white rounded-lg border border-slate-100 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase">Present</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-white rounded-lg border border-slate-100 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase">Late</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-white rounded-lg border border-slate-100 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase">Absent</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1 bg-white rounded-lg border border-slate-100 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="text-[10px] font-bold text-slate-500 uppercase">Excused</span>
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>