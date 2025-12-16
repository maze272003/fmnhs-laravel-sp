<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Attendance Record | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-2 md:gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500 mr-1">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-check text-sm"></i>
                    </div>
                    <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight">Attendance</h2>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php
                    $student = Auth::guard('student')->user();
                    $avatarPath = 'avatars/' . $student->avatar;
                    $hasAvatar = !empty($student->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($avatarPath);
                @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-xs font-black text-slate-800 uppercase leading-none">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-1">Grade {{ $student->grade_level }} — {{ $student->section }}</p>
                </div>

                @if($hasAvatar)
                    <img src="{{ asset('storage/' . $avatarPath) }}" alt="Profile" class="w-9 h-9 md:w-10 md:h-10 rounded-xl object-cover border-2 border-white shadow-sm">
                @else
                    <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black shadow-lg shadow-blue-100 text-sm">
                        {{ substr($student->first_name, 0, 1) }}
                    </div>
                @endif
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-10">
            
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Presence Overview</h1>
                <p class="text-slate-500 text-sm font-medium">Detailed summary of your class attendance logs.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-md transition-all">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <span class="text-3xl font-black text-slate-800">{{ $summary['present'] ?? 0 }}</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Present</p>
                </div>
                
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-md transition-all">
                    <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <span class="text-3xl font-black text-slate-800">{{ $summary['late'] ?? 0 }}</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Late</p>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-md transition-all">
                    <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                    <span class="text-3xl font-black text-slate-800">{{ $summary['absent'] ?? 0 }}</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Absent</p>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex flex-col items-center text-center group hover:shadow-md transition-all">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <span class="text-3xl font-black text-slate-800">{{ $summary['excused'] ?? 0 }}</span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Excused</p>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-xl text-slate-800 tracking-tight">Recent Logs</h3>
                    <i class="fa-solid fa-filter text-slate-300"></i>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">Date Recorded</th>
                                <th class="px-6 py-5">Subject Title</th>
                                <th class="px-8 py-5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($history as $record)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-5">
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-700 text-sm tracking-tight">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ \Carbon\Carbon::parse($record->date)->format('l') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center text-[10px] font-black group-hover:bg-blue-600 group-hover:text-white transition-all">
                                                {{ substr($record->subject->name, 0, 1) }}
                                            </div>
                                            <span class="font-bold text-slate-700 group-hover:text-blue-600 transition-colors">{{ $record->subject->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        @if($record->status == 'present')
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Present
                                            </span>
                                        @elseif($record->status == 'absent')
                                            <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Absent
                                            </span>
                                        @elseif($record->status == 'late')
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Late
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
                                    <td colspan="3" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fa-solid fa-folder-open text-4xl text-slate-200 mb-4"></i>
                                            <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No logs found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $history->links() }}
                </div>
            </div>

        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>