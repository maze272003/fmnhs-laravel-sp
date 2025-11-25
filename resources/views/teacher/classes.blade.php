<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-emerald-600">My Classes</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Faculty</span></div>
        </header>

        <main class="flex-1 p-6">

            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Class Overview</h1>
                    <p class="text-gray-500 text-sm">Manage your active subjects and sections.</p>
                </div>
                <a href="{{ route('teacher.grading.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition">
                    <i class="fa-solid fa-plus mr-1"></i> Grade New Class
                </a>
            </div>

            @if($classes->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($classes as $class)
                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition duration-300 overflow-hidden group">
                            
                            <div class="p-6 border-b border-gray-100 dark:border-slate-700 bg-gradient-to-r from-white to-gray-50 dark:from-slate-800 dark:to-slate-700">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-1 rounded uppercase">
                                        {{ $class['subject']->code }}
                                    </span>
                                    <i class="fa-solid fa-book-open text-emerald-200 text-4xl absolute right-4 top-4 opacity-20 group-hover:scale-110 transition-transform"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 dark:text-white truncate" title="{{ $class['subject']->name }}">
                                    {{Str::limit($class['subject']->name, 25) }}
                                </h3>
                                <p class="text-emerald-600 font-medium text-sm">Section: {{ $class['section'] }}</p>
                            </div>

                            <div class="p-6 grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-slate-700 dark:text-gray-300">{{ $class['student_count'] }}</div>
                                    <div class="text-xs text-gray-400 uppercase font-bold">Students</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-slate-700 dark:text-gray-300">{{ number_format($class['average_grade'], 2) }}</div>
                                    <div class="text-xs text-gray-400 uppercase font-bold">Class Avg</div>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-slate-900/50 border-t border-gray-100 dark:border-slate-700">
                                <a href="{{ route('teacher.grading.show', ['subject_id' => $class['subject']->id, 'section' => $class['section']]) }}" 
                                   class="block w-full text-center bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-200 font-bold py-2 rounded-lg hover:bg-emerald-50 dark:hover:bg-slate-600 hover:text-emerald-600 hover:border-emerald-200 transition">
                                    Open Grading Sheet <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i class="fa-solid fa-chalkboard text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 dark:text-gray-300">No Classes Found</h3>
                    <p class="text-gray-500 mb-6">You haven't graded any sections yet.</p>
                    <a href="{{ route('teacher.grading.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-bold shadow transition">
                        Start Grading Now
                    </a>
                </div>
            @endif

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>