<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-indigo-600">Attendance Monitoring</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Admin</span></div>
        </header>

        <main class="flex-1 p-6">

            <div class="bg-white dark:bg-slate-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 mb-6">
                <form action="{{ route('admin.attendance.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700 dark:border-slate-600">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teacher</label>
                        <select name="teacher_id" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700 dark:border-slate-600">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->last_name }}, {{ $teacher->first_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Section</label>
                        <select name="section" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700 dark:border-slate-600">
                            <option value="">All Sections</option>
                            @foreach($sections as $sec)
                                <option value="{{ $sec }}" {{ request('section') == $sec ? 'selected' : '' }}>{{ $sec }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                        <select name="status" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700 dark:border-slate-600">
                            <option value="">All Status</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="excused" {{ request('status') == 'excused' ? 'selected' : '' }}>Excused</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-bold flex-1">
                            Filter
                        </button>
                        <a href="{{ route('admin.attendance.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-600 px-3 py-2 rounded text-center">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-slate-700">
                <div class="p-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="font-bold">Attendance Logs</h3>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">Total: {{ $records->total() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 uppercase font-bold">
                            <tr>
                                <th class="p-4">Date</th>
                                <th class="p-4">Student</th>
                                <th class="p-4">Class Details</th>
                                <th class="p-4">Marked By</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @forelse($records as $record)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                    <td class="p-4 font-mono text-gray-500">
                                        {{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}
                                    </td>
                                    <td class="p-4 font-bold text-slate-700 dark:text-white">
                                        {{ $record->student->last_name }}, {{ $record->student->first_name }}
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold text-indigo-600">{{ $record->subject->code }}</div>
                                        <div class="text-xs text-gray-500">{{ $record->section }}</div>
                                    </td>
                                    <td class="p-4 text-sm">
                                        Teacher {{ $record->teacher->last_name }}
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($record->status == 'present')
                                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold uppercase">Present</span>
                                        @elseif($record->status == 'late')
                                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold uppercase">Late</span>
                                        @elseif($record->status == 'absent')
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold uppercase">Absent</span>
                                        @else
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold uppercase">Excused</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400">
                                        No attendance records found for this filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-t border-gray-100 dark:border-slate-700">
                    {{ $records->links() }}
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>