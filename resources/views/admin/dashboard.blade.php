<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200 transition-colors duration-300">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center transition-all duration-300">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-600 dark:text-gray-300">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <h2 class="text-xl font-semibold hidden md:block">Dashboard Overview</h2>

            <div class="flex items-center gap-3">
                <span class="text-sm font-medium">{{ Auth::guard('admin')->user()->name }}</span>
                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                    {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6">
            <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700">
                <h1 class="text-2xl font-bold mb-4">Welcome, Admin!</h1>
                <p class="text-gray-600 dark:text-gray-400">This layout is fully responsive. Try resizing your browser or clicking the arrow in the sidebar.</p>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>