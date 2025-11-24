<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-blue-600 hidden md:block">My Grades</h2>
            
            <div class="flex items-center gap-3">
                <span class="font-bold text-sm">{{ Auth::guard('student')->user()->first_name }}</span>
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                    {{ substr(Auth::guard('student')->user()->first_name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6">
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Academic Performance</h1>
                <p class="text-gray-500 text-sm">School Year 2024-2025</p>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-blue-50 dark:bg-slate-700 text-blue-700 dark:text-blue-300 uppercase text-xs font-bold">
                            <tr>
                                <th class="px-6 py-4">Subject</th>
                                <th class="px-4 py-4 text-center">1st</th>
                                <th class="px-4 py-4 text-center">2nd</th>
                                <th class="px-4 py-4 text-center">3rd</th>
                                <th class="px-4 py-4 text-center">4th</th>
                                <th class="px-4 py-4 text-center">Average</th>
                                <th class="px-4 py-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($subjects as $subject)
                                @php
                                    // Helper logic to find grades per quarter
                                    $q1 = $subject->grades->where('quarter', 1)->first()?->grade_value;
                                    $q2 = $subject->grades->where('quarter', 2)->first()?->grade_value;
                                    $q3 = $subject->grades->where('quarter', 3)->first()?->grade_value;
                                    $q4 = $subject->grades->where('quarter', 4)->first()?->grade_value;

                                    // Calculate Average (only for existing grades)
                                    $grades = collect([$q1, $q2, $q3, $q4])->filter();
                                    $average = $grades->isNotEmpty() ? $grades->avg() : null;
                                    
                                    // Status Logic
                                    $status = $average >= 75 ? 'PASSED' : ($average ? 'FAILED' : 'PENDING');
                                    $statusColor = $average >= 75 ? 'bg-emerald-100 text-emerald-700' : ($average ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500');
                                @endphp

                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ $subject->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $subject->code }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q1 ? 'text-gray-300' : '' }}">{{ $q1 ?? '--' }}</td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q2 ? 'text-gray-300' : '' }}">{{ $q2 ?? '--' }}</td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q3 ? 'text-gray-300' : '' }}">{{ $q3 ?? '--' }}</td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q4 ? 'text-gray-300' : '' }}">{{ $q4 ?? '--' }}</td>
                                    <td class="px-4 py-4 text-center font-bold text-blue-600">
                                        {{ $average ? number_format($average, 2) : '--' }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="px-2 py-1 rounded text-[10px] font-bold {{ $statusColor }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($subjects->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            <i class="fa-regular fa-folder-open text-4xl mb-2 text-gray-300"></i>
                            <p>No grades found for this account yet.</p>
                        </div>
                    @endif
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>