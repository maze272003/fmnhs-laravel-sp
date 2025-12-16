<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-50 text-gray-500">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-emerald-600 hidden md:block tracking-tight">Faculty Portal</h2>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-800">
                        {{ Auth::guard('teacher')->user()->first_name }} {{ Auth::guard('teacher')->user()->last_name }}
                    </p>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter">
                        {{ Auth::guard('teacher')->user()->department ?? 'No Department' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold border border-emerald-100 shadow-sm">
                    {{ substr(Auth::guard('teacher')->user()->first_name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6">
            
            <div class="bg-gradient-to-br from-emerald-600 to-teal-500 rounded-2xl p-8 text-white shadow-lg shadow-emerald-100 mb-8 relative overflow-hidden">
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Good Day, Teacher {{ Auth::guard('teacher')->user()->last_name }}! 🧑‍🏫</h1>
                    <p class="opacity-90 font-medium">Ready to inspire? Here is your class summary for today.</p>
                </div>
                <i class="fa-solid fa-chalkboard-teacher absolute right-[-20px] bottom-[-20px] text-9xl opacity-10 rotate-12"></i>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">Total Classes</div>
                            <div class="text-2xl font-black text-slate-800">{{ $totalClasses }}</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 italic border-t pt-3">Subject-section assignments</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">Unique Students</div>
                            <div class="text-2xl font-black text-slate-800">{{ $totalStudents }}</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 italic border-t pt-3">Across all your handled classes</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                            <i class="fa-solid fa-award"></i>
                        </div>
                        <div>
                            <div class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">Advisory Class</div>
                            <div class="text-2xl font-black text-emerald-600">{{ $advisoryClass }}</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 italic border-t pt-3">
                        {{ $advisoryClass === 'N/A' ? 'Not assigned as adviser' : 'Current advisory assignment' }}
                    </p>
                </div>
            </div>

            @if(isset($recentAnnouncements) && $recentAnnouncements->count())
            <div class="mt-10">
                <div class="flex items-center gap-3 mb-6">
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Recent Announcements</h2>
                    <div class="h-[1px] flex-grow bg-gray-100"></div>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    @foreach($recentAnnouncements as $announcement)
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-start gap-5 group hover:border-emerald-200 transition-all">
                        <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-bullhorn text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <p class="font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">{{ $announcement->title }}</p>
                                <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-50 px-2 py-1 rounded">
                                    {{ $announcement->created_at->format('M d') }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500 leading-relaxed">{{ $announcement->content }}</p>
                            <span class="text-[10px] text-emerald-600 font-bold mt-2 block uppercase tracking-widest">Official Update</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>