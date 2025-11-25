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
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-emerald-600 hidden md:block">Faculty Portal</h2>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">
                        {{ Auth::guard('teacher')->user()->first_name }} {{ Auth::guard('teacher')->user()->last_name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ Auth::guard('teacher')->user()->department ?? 'No Department' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold border border-emerald-200">
                    {{ substr(Auth::guard('teacher')->user()->first_name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6">
            
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl p-6 text-white shadow-lg mb-6">
                <h1 class="text-2xl font-bold mb-2">Good Day, Teacher {{ Auth::guard('teacher')->user()->last_name }}! 🍎</h1>
                <p class="opacity-90">Ready to inspire? Here is your class summary.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- DYNAMIC: Total Classes -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="text-gray-500 text-sm font-medium uppercase mb-1">Total Classes Taught</div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ $totalClasses }}</div>
                    <p class="text-xs text-gray-400 mt-1">Unique subject-section assignments</p>
                </div>

                <!-- DYNAMIC: Total Students -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="text-gray-500 text-sm font-medium uppercase mb-1">Total Unique Students</div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ $totalStudents }}</div>
                    <p class="text-xs text-gray-400 mt-1">Across all your classes</p>
                </div>

                <!-- DYNAMIC: Advisory Class -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="text-gray-500 text-sm font-medium uppercase mb-1">Advisory Class</div>
                    <div class="text-2xl font-bold text-emerald-500">{{ $advisoryClass }}</div>
                    <p class="text-xs text-gray-400 mt-1">{{ $advisoryClass === 'N/A' ? 'Not assigned' : 'Handle for this school year' }}</p>
                </div>
            </div>

            <!-- Optional: Recent Announcements from Admin/Teachers -->
            @if(isset($recentAnnouncements) && $recentAnnouncements->count())
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-4 text-emerald-700 dark:text-emerald-500">Recent Announcements</h2>
                <div class="space-y-4">
                    @foreach($recentAnnouncements as $announcement)
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 flex items-start gap-4">
                        <i class="fa-solid fa-bullhorn text-emerald-500 mt-1"></i>
                        <div>
                            <p class="font-semibold">{{ $announcement->title }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $announcement->content }}</p>
                            <span class="text-xs text-gray-400 mt-1 block">Posted: {{ $announcement->created_at->format('M d, Y') }}</span>
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