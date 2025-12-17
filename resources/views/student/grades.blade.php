<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>My Grades</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 font-sans text-slate-800">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-gray-200">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600 mr-2">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            
            <h2 class="text-lg md:text-xl font-bold text-blue-600 truncate flex-1">My Grades</h2>
            
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

        <main class="flex-1 p-4 md:p-6">
            
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-800">Academic Performance</h1>
                    <p class="text-gray-500 text-sm">School Year 2024-2025</p>
                </div>
                
                <a href="{{ route('student.grades.pdf') }}" class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg shadow-sm flex items-center justify-center gap-2 transition active:scale-95 text-sm font-medium">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead class="bg-blue-50 text-blue-700 uppercase text-xs font-bold border-b border-blue-100">
                            <tr>
                                <th class="px-6 py-4 min-w-[200px]">Subject</th>
                                <th class="px-4 py-4 text-center min-w-[60px]">1st</th>
                                <th class="px-4 py-4 text-center min-w-[60px]">2nd</th>
                                <th class="px-4 py-4 text-center min-w-[60px]">3rd</th>
                                <th class="px-4 py-4 text-center min-w-[60px]">4th</th>
                                <th class="px-4 py-4 text-center min-w-[80px]">Average</th>
                                <th class="px-4 py-4 text-center min-w-[100px]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($subjects as $subject)
                                @php
                                    // Helper logic
                                    $q1 = $subject->grades->where('quarter', 1)->first()?->grade_value;
                                    $q2 = $subject->grades->where('quarter', 2)->first()?->grade_value;
                                    $q3 = $subject->grades->where('quarter', 3)->first()?->grade_value;
                                    $q4 = $subject->grades->where('quarter', 4)->first()?->grade_value;

                                    $grades = collect([$q1, $q2, $q3, $q4])->filter();
                                    $average = $grades->isNotEmpty() ? $grades->avg() : null;
                                    
                                    // Status Logic
                                    $status = $average >= 75 ? 'PASSED' : ($average ? 'FAILED' : 'PENDING');
                                    
                                    // Adjusted colors for light theme
                                    $statusColor = $average >= 75 
                                        ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' 
                                        : ($average ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-gray-100 text-gray-500 border border-gray-200');
                                @endphp

                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-slate-700">{{ $subject->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $subject->code }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q1 ? 'text-gray-300' : 'text-slate-600' }}">{{ $q1 ?? '--' }}</td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q2 ? 'text-gray-300' : 'text-slate-600' }}">{{ $q2 ?? '--' }}</td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q3 ? 'text-gray-300' : 'text-slate-600' }}">{{ $q3 ?? '--' }}</td>
                                    <td class="px-4 py-4 text-center font-medium {{ !$q4 ? 'text-gray-300' : 'text-slate-600' }}">{{ $q4 ?? '--' }}</td>
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
                        <div class="p-10 text-center text-gray-500">
                            <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                            <p class="text-sm">No grades found for this account yet.</p>
                        </div>
                    @endif
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>