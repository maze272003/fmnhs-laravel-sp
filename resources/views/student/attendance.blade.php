<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>My Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-slate-800 font-sans">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-gray-200">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600 mr-2">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <h2 class="text-lg md:text-xl font-bold text-blue-600 truncate flex-1">Attendance Record</h2>

            <div class="flex items-center gap-3 shrink-0">
        
        @php
            // Define variables for avatar check
            $student = Auth::guard('student')->user();
            $avatarPath = 'avatars/' . $student->avatar;
            $hasAvatar = !empty($student->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($avatarPath);
        @endphp

        <div class="text-right hidden sm:block">
            <p class="text-sm font-bold">{{ $student->first_name }} {{ $student->last_name }}</p>
            <p class="text-xs text-gray-500">Grade {{ $student->grade_level }} - {{ $student->section }}</p>
        </div>

        @if($hasAvatar)
            <img src="{{ asset('storage/' . $avatarPath) }}" 
                 alt="Profile" 
                 class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover border border-gray-200 shadow-sm">
        @else
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold border border-blue-200 text-sm md:text-base">
                {{ substr($student->first_name, 0, 1) }}
            </div>
        @endif

    </div>
        </header>

        <main class="flex-1 p-4 md:p-6">
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
                <div class="bg-emerald-100 p-3 md:p-4 rounded-xl text-center border border-emerald-200">
                    <span class="text-xl md:text-2xl font-bold text-emerald-700">{{ $summary['present'] ?? 0 }}</span>
                    <p class="text-[10px] md:text-xs text-emerald-600 uppercase font-bold mt-1">Present</p>
                </div>
                <div class="bg-yellow-100 p-3 md:p-4 rounded-xl text-center border border-yellow-200">
                    <span class="text-xl md:text-2xl font-bold text-yellow-700">{{ $summary['late'] ?? 0 }}</span>
                    <p class="text-[10px] md:text-xs text-yellow-600 uppercase font-bold mt-1">Late</p>
                </div>
                <div class="bg-red-100 p-3 md:p-4 rounded-xl text-center border border-red-200">
                    <span class="text-xl md:text-2xl font-bold text-red-700">{{ $summary['absent'] ?? 0 }}</span>
                    <p class="text-[10px] md:text-xs text-red-600 uppercase font-bold mt-1">Absent</p>
                </div>
                <div class="bg-blue-100 p-3 md:p-4 rounded-xl text-center border border-blue-200">
                    <span class="text-xl md:text-2xl font-bold text-blue-700">{{ $summary['excused'] ?? 0 }}</span>
                    <p class="text-[10px] md:text-xs text-blue-600 uppercase font-bold mt-1">Excused</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap md:whitespace-normal">
                        <thead class="bg-gray-100 text-gray-600 uppercase font-bold border-b border-gray-200">
                            <tr>
                                <th class="p-4 min-w-[120px]">Date</th>
                                <th class="p-4 min-w-[200px]">Subject</th>
                                <th class="p-4 text-center min-w-[100px]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($history as $record)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-4 font-mono text-gray-500">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                    <td class="p-4 font-bold text-slate-700">{{ $record->subject->name }}</td>
                                    <td class="p-4 text-center">
                                        @if($record->status == 'present')
                                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold border border-emerald-200">PRESENT</span>
                                        @elseif($record->status == 'absent')
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold border border-red-200">ABSENT</span>
                                        @elseif($record->status == 'late')
                                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold border border-yellow-200">LATE</span>
                                        @else
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold border border-blue-200">EXCUSED</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $history->links() }}
                </div>
            </div>

        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>