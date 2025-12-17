<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-calendar-days text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Class Schedule</h2>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                
                {{-- HELPER USED: Logic is now inside Student Model (avatar_url) --}}
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-xs text-gray-500">Grade {{ $student->grade_level }} - {{ $student->section }}</p>
                </div>

                {{-- Since avatar_url always returns a valid link (S3 or UI Avatars), we always show the IMG tag --}}
                <img src="{{ $student->avatar_url }}" 
                     alt="Profile" 
                     class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover border border-gray-200 shadow-sm">

            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">

            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Weekly Timetable</h1>
                <p class="text-slate-500 font-medium">Your official class schedule for the current semester.</p>
            </div>

            @if($schedules->isEmpty())
                <div class="py-24 flex flex-col items-center justify-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="relative mb-8">
                        <div class="w-48 h-48 bg-blue-50 rounded-full flex items-center justify-center relative overflow-hidden">
                            <i class="fa-solid fa-cloud text-6xl text-white absolute top-8 left-4 opacity-80"></i>
                            <i class="fa-solid fa-sun text-4xl text-amber-200 absolute top-6 right-8 animate-pulse"></i>
                            
                            <i class="fa-solid fa-user-ninja text-8xl text-blue-200 absolute -bottom-2 rotate-6"></i>
                            <i class="fa-solid fa-couch text-7xl text-blue-100 absolute -bottom-4 -left-2"></i>
                        </div>
                        
                        <div class="absolute -top-2 -right-2 w-14 h-14 bg-white rounded-2xl shadow-xl flex items-center justify-center border border-slate-100">
                            <i class="fa-solid fa-z text-2xl text-blue-400 animate-bounce"></i>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2">No Classes Found</h3>
                    <p class="text-slate-500 font-medium text-center max-w-xs px-6 leading-relaxed">
                        It looks like your schedule is currently clear. Enjoy your break or check back later once the registrar updates your section!
                    </p>
                    
                    <div class="mt-10 flex flex-wrap justify-center gap-3">
                        <span class="px-5 py-2 bg-slate-50 rounded-full text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-100">
                            #VacationMode
                        </span>
                        <span class="px-5 py-2 bg-blue-50 rounded-full text-[10px] font-black text-blue-400 uppercase tracking-widest border border-blue-100">
                            S.Y. 2024-2025
                        </span>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:gap-6">
                    @foreach($schedules as $sched)
                        <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 p-6 md:p-8 flex flex-col md:flex-row justify-between items-center hover:shadow-xl hover:border-blue-100 transition-all duration-300 group relative overflow-hidden">
                            
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500 group-hover:w-2 transition-all"></div>

                            <div class="flex items-center gap-6 w-full md:w-1/3 mb-6 md:mb-0">
                                <div class="text-center md:text-left min-w-[100px]">
                                    <p class="text-xl font-black text-slate-800 group-hover:text-blue-600 transition-colors">
                                        {{ \Carbon\Carbon::parse($sched->start_time)->format('g:i A') }}
                                    </p>
                                    <div class="flex items-center gap-2 my-1">
                                        <div class="h-[1px] w-4 bg-slate-200"></div>
                                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">TO</span>
                                        <div class="h-[1px] w-4 bg-slate-200"></div>
                                    </div>
                                    <p class="text-sm font-bold text-slate-400 uppercase">
                                        {{ \Carbon\Carbon::parse($sched->end_time)->format('g:i A') }}
                                    </p>
                                </div>
                                <div class="h-12 w-px bg-slate-100 hidden md:block"></div>
                                <div>
                                    <span class="bg-blue-50 text-blue-600 text-[10px] font-black px-3 py-1.5 rounded-lg uppercase tracking-widest border border-blue-100 shadow-sm">
                                        {{ $sched->day }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex-1 w-full text-center md:text-left mb-6 md:mb-0 px-0 md:px-6">
                                <h3 class="text-xl font-black text-slate-900 mb-1 group-hover:translate-x-1 transition-transform">
                                    {{ $sched->subject->name }}
                                </h3>
                                <div class="flex items-center justify-center md:justify-start gap-3">
                                    <span class="font-mono text-[11px] font-bold text-blue-500 bg-blue-50/50 px-2 py-0.5 rounded border border-blue-100 italic">
                                        {{ $sched->subject->code }}
                                    </span>
                                    <span class="text-slate-300">•</span>
                                    <p class="text-sm font-bold text-slate-500">
                                        <i class="fa-solid fa-door-open text-xs mr-1 opacity-50"></i>
                                        Room: <span class="text-slate-800">{{ $sched->room ?? 'TBA' }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="w-full md:w-1/4 flex items-center justify-center md:justify-end gap-4">
                                <div class="text-right hidden md:block">
                                    <p class="text-sm font-black text-slate-800">
                                        {{ $sched->teacher->first_name }} {{ $sched->teacher->last_name }}
                                    </p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Subject Instructor</p>
                                </div>
                                <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center font-black group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all shadow-sm">
                                    {{ substr($sched->teacher->first_name, 0, 1) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>