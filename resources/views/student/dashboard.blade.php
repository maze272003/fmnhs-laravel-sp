<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <h2 class="text-xl font-bold text-blue-600 hidden md:block">Student Portal</h2>

            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">{{ Auth::guard('student')->user()->first_name }} {{ Auth::guard('student')->user()->last_name }}</p>
                    <p class="text-xs text-gray-500">Grade {{ Auth::guard('student')->user()->grade_level }} - {{ Auth::guard('student')->user()->section }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold border border-blue-200">
                    {{ substr(Auth::guard('student')->user()->first_name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6">
            
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg mb-6">
                <h1 class="text-2xl font-bold mb-2">Hello, {{ Auth::guard('student')->user()->first_name }}! 👋</h1>
                <p class="opacity-90">Welcome to your student dashboard. Here is your summary for today.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="text-gray-500 text-sm font-medium uppercase mb-1">Current Section</div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ Auth::guard('student')->user()->section }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="text-gray-500 text-sm font-medium uppercase mb-1">Adviser</div>
                    <div class="text-2xl font-bold text-slate-800 dark:text-white">--</div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700">
                    <div class="text-gray-500 text-sm font-medium uppercase mb-1">Status</div>
                    <div class="text-2xl font-bold text-emerald-500">Enrolled</div>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>