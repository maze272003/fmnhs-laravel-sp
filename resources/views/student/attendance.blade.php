<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Attendance Record | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; width: 4px; }
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
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                        <i class="fa-solid fa-calendar-check text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Attendance</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Presence Tracking</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-900 leading-none mb-1">{{ $student->first_name }} {{ $student->last_name }}</p>
                    {{-- UPDATED: Accessing via relationship --}}
                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">
                        Grade {{ $student->section->grade_level }} - {{ $student->section->name }}
                    </p>
                </div>

                <img src="{{ $student->avatar_url }}" 
                     alt="Profile" 
                     class="w-10 h-10 rounded-2xl object-cover border-2 border-white shadow-md">
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Presence Overview</h1>
                <p class="text-slate-500 font-medium italic">"Consistency is the key to academic success."</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-xl hover:border-emerald-100 transition-all duration-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-circle-check text-6xl text-emerald-600"></i>
                    </div>
                    <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                    <span class="text-4xl font-black text-slate-900 leading-none mb-2">{{ $summary['present'] ?? 0 }}</span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Days Present</p>
                </div>
                
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-xl hover:border-amber-100 transition-all duration-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-clock text-6xl text-amber-600"></i>
                    </div>
                    <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                    </div>
                    <span class="text-4xl font-black text-slate-900 leading-none mb-2">{{ $summary['late'] ?? 0 }}</span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Late Arrivals</p>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-xl hover:border-rose-100 transition-all duration-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-calendar-xmark text-6xl text-rose-600"></i>
                    </div>
                    <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-slash text-xl"></i>
                    </div>
                    <span class="text-4xl font-black text-slate-900 leading-none mb-2">{{ $summary['absent'] ?? 0 }}</span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Absences</p>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-xl hover:border-blue-100 transition-all duration-500 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                        <i class="fa-solid fa-file-signature text-6xl text-blue-600"></i>
                    </div>
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-envelope-open-text text-xl"></i>
                    </div>
                    <span class="text-4xl font-black text-slate-900 leading-none mb-2">{{ $summary['excused'] ?? 0 }}</span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Excused</p>
                </div>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/60 border border-slate-200/50 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="font-black text-2xl text-slate-900 tracking-tight">Recent Logs</h3>
                        <span class="bg-slate-100 text-slate-500 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-slate-200">History</span>
                    </div>
                    <div class="flex items-center gap-2 text-slate-300">
                        <i class="fa-solid fa-filter-list text-sm"></i>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                                <th class="px-10 py-6">Timestamp</th>
                                <th class="px-8 py-6">Subject / Course</th>
                                <th class="px-10 py-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($history as $record)
                                <tr class="hover:bg-indigo-50/20 transition-all duration-300 group">
                                    <td class="px-10 py-6">
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-800 text-base tracking-tight group-hover:text-indigo-600 transition-colors">
                                                {{ \Carbon\Carbon::parse($record->date)->format('F d, Y') }}
                                            </span>
                                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center text-xs font-black group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                                {{ substr($record->subject->name, 0, 1) }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-700 leading-none mb-1">{{ $record->subject->name }}</span>
                                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">{{ $record->subject->code }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6 text-center">
                                        @if($record->status == 'present')
                                            <span class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm shadow-emerald-50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Present
                                            </span>
                                        @elseif($record->status == 'absent')
                                            <span class="inline-flex items-center gap-2 bg-rose-50 text-rose-600 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-rose-100 shadow-sm shadow-rose-50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Absent
                                            </span>
                                        @elseif($record->status == 'late')
                                            <span class="inline-flex items-center gap-2 bg-amber-50 text-amber-600 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-amber-100 shadow-sm shadow-amber-50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Late
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-blue-100 shadow-sm shadow-blue-50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Excused
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-10 py-24 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 border border-slate-100">
                                                <i class="fa-solid fa-calendar-day text-3xl text-slate-200"></i>
                                            </div>
                                            <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No logs recorded yet</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-8 bg-slate-50/40 border-t border-slate-100">
                    {{ $history->links() }}
                </div>
            </div>

        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>