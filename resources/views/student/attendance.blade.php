<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-blue-600">Attendance Record</h2>
        </header>

        <main class="flex-1 p-6">
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-emerald-100 p-4 rounded-xl text-center">
                    <span class="text-2xl font-bold text-emerald-700">{{ $summary['present'] ?? 0 }}</span>
                    <p class="text-xs text-emerald-600 uppercase font-bold">Present</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-xl text-center">
                    <span class="text-2xl font-bold text-yellow-700">{{ $summary['late'] ?? 0 }}</span>
                    <p class="text-xs text-yellow-600 uppercase font-bold">Late</p>
                </div>
                <div class="bg-red-100 p-4 rounded-xl text-center">
                    <span class="text-2xl font-bold text-red-700">{{ $summary['absent'] ?? 0 }}</span>
                    <p class="text-xs text-red-600 uppercase font-bold">Absent</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-xl text-center">
                    <span class="text-2xl font-bold text-blue-700">{{ $summary['excused'] ?? 0 }}</span>
                    <p class="text-xs text-blue-600 uppercase font-bold">Excused</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300 uppercase font-bold">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Subject</th>
                            <th class="p-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @foreach($history as $record)
                            <tr>
                                <td class="p-4 font-mono text-gray-500">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                <td class="p-4 font-bold">{{ $record->subject->name }}</td>
                                <td class="p-4 text-center">
                                    @if($record->status == 'present')
                                        <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold">PRESENT</span>
                                    @elseif($record->status == 'absent')
                                        <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">ABSENT</span>
                                    @elseif($record->status == 'late')
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold">LATE</span>
                                    @else
                                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">EXCUSED</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $history->links() }}
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>