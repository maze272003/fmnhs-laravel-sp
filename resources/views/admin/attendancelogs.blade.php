<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Logs | Admin Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-list text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight text-center md:text-left">Attendance Monitoring</h2>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Security Logs
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">

            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 mb-8">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1.5 h-5 bg-indigo-500 rounded-full"></div>
                    <h3 class="font-black text-sm text-slate-800 uppercase tracking-widest">Filter Records</h3>
                </div>
                
                <form action="{{ route('admin.attendance.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Specific Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" 
                               class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Marked By (Teacher)</label>
                        <select name="teacher_id" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold text-sm cursor-pointer appearance-none">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->last_name }}, {{ $teacher->first_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Section</label>
                        <select name="section" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold text-sm cursor-pointer appearance-none">
                            <option value="">All Sections</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec }}" {{ request('section') == $sec ? 'selected' : '' }}>{{ $sec }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Status Type</label>
                        <select name="status" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold text-sm cursor-pointer appearance-none">
                            <option value="">All Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="excused" {{ request('status') == 'excused' ? 'selected' : '' }}>Excused</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black shadow-lg shadow-indigo-100 transition-all active:scale-95 flex-1 text-sm">
                            Apply Filter
                        </button>
                        <a href="{{ route('admin.attendance.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-500 px-4 py-3 rounded-2xl transition-all flex items-center justify-center">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h3 class="font-black text-xl text-slate-800 tracking-tight">Attendance Logs</h3>
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-4 py-1.5 rounded-full border border-indigo-100 uppercase tracking-widest">
                        Total Records: {{ $records->total() }}
                    </span>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">Date</th>
                                <th class="px-6 py-5">Student Information</th>
                                <th class="px-6 py-5">Class Details</th>
                                <th class="px-6 py-5">Marked By</th>
                                <th class="px-8 py-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($records as $record)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-700 text-sm tracking-tight">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center font-black text-[10px] group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                                {{ substr($record->student->first_name, 0, 1) }}{{ substr($record->student->last_name, 0, 1) }}
                                            </div>
                                            <span class="font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">
                                                {{ $record->student->last_name }}, {{ $record->student->first_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg border border-indigo-100 mb-1">
                                            <span class="text-[10px] font-black uppercase">{{ $record->subject->code }}</span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase ml-1 tracking-widest">{{ $record->section }}</p>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-user-tie text-slate-300 text-xs"></i>
                                            <span class="text-sm font-bold text-slate-600 tracking-tight">Teacher {{ $record->teacher->last_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        @if($record->status == 'present')
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Present
                                            </span>
                                        @elseif($record->status == 'late')
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Late
                                            </span>
                                        @elseif($record->status == 'absent')
                                            <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Absent
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-blue-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Excused
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                                <i class="fa-solid fa-magnifying-glass text-slate-200 text-3xl"></i>
                                            </div>
                                            <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No matching logs found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $records->links() }}
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>