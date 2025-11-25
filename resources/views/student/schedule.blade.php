<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-blue-600">Class Schedule</h2>
            <div class="flex items-center gap-3"><span class="font-bold">{{ Auth::guard('student')->user()->section }}</span></div>
        </header>

        <main class="flex-1 p-6">

            @if($schedules->isEmpty())
                <div class="text-center py-20 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-gray-300 dark:border-slate-700">
                    <i class="fa-regular fa-calendar-xmark text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-bold text-gray-600 dark:text-gray-300">No Schedule Found</h3>
                    <p class="text-gray-500">Your section doesn't have a posted schedule yet.</p>
                </div>
            @else
                <div class="grid gap-6">
                    @foreach($schedules as $sched)
                        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border-l-4 border-blue-500 p-6 flex flex-col md:flex-row justify-between items-center hover:shadow-md transition">
                            
                            <div class="flex items-center gap-6 w-full md:w-1/4 mb-4 md:mb-0">
                                <div class="text-center min-w-[80px]">
                                    <p class="text-lg font-bold text-slate-800 dark:text-white">
                                        {{ \Carbon\Carbon::parse($sched->start_time)->format('g:i A') }}
                                    </p>
                                    <p class="text-xs text-gray-500 uppercase">to</p>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($sched->end_time)->format('g:i A') }}
                                    </p>
                                </div>
                                <div class="h-10 w-px bg-gray-200 dark:bg-slate-700 hidden md:block"></div>
                                <div>
                                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded uppercase">
                                        {{ $sched->day }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex-1 w-full text-center md:text-left mb-4 md:mb-0">
                                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1">
                                    {{ $sched->subject->name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    <span class="font-mono text-blue-500">{{ $sched->subject->code }}</span>
                                    &bull; 
                                    {{ $sched->room ?? 'TBA' }}
                                </p>
                            </div>

                            <div class="w-full md:w-1/4 flex items-center justify-center md:justify-end gap-3">
                                <div class="text-right hidden md:block">
                                    <p class="text-sm font-bold text-slate-700 dark:text-gray-300">
                                        {{ $sched->teacher->first_name }} {{ $sched->teacher->last_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">Instructor</p>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
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