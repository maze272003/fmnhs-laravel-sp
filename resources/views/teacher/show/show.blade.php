<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignment Submissions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.assignments.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <h2 class="text-xl font-bold text-emerald-600">Submissions</h2>
            </div>
            <div class="flex items-center gap-3"><span class="font-bold">Faculty</span></div>
        </header>

        <main class="flex-1 p-6">

            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-1 rounded uppercase mb-2 inline-block">
                            {{ $assignment->subject->code }} - {{ $assignment->section }}
                        </span>
                        <h1 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">{{ $assignment->title }}</h1>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $assignment->description }}</p>
                        
                        @if($assignment->file_path)
                            <a href="{{ asset('uploads/assignments/'.$assignment->file_path) }}" target="_blank" class="text-blue-600 hover:underline text-sm">
                                <i class="fa-solid fa-paperclip"></i> View Reference Material
                            </a>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 font-bold uppercase">Due Date</p>
                        <p class="text-lg font-bold text-red-500">
                            {{ \Carbon\Carbon::parse($assignment->deadline)->format('M d, Y h:i A') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg">Student Work</h3>
                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">
                        Total: {{ $assignment->submissions->count() }}
                    </span>
                </div>

                @if($assignment->submissions->isEmpty())
                    <div class="p-10 text-center text-gray-500">
                        <i class="fa-regular fa-folder-open text-4xl mb-2 text-gray-300"></i>
                        <p>No students have submitted yet.</p>
                    </div>
                @else
                    <table class="w-full text-left text-sm">
                        <thead class="bg-emerald-50 dark:bg-slate-700 text-emerald-700 dark:text-emerald-300 uppercase font-bold">
                            <tr>
                                <th class="p-4">Student Name</th>
                                <th class="p-4">Date Submitted</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($assignment->submissions as $submission)
                                @php
                                    $isLate = $submission->created_at > $assignment->deadline;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                    <td class="p-4 font-bold text-slate-700 dark:text-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                                {{ substr($submission->student->first_name, 0, 1) }}
                                            </div>
                                            {{ $submission->student->last_name }}, {{ $submission->student->first_name }}
                                        </div>
                                    </td>
                                    <td class="p-4 text-gray-500">
                                        {{ $submission->created_at->format('M d, h:i A') }}
                                    </td>
                                    <td class="p-4">
                                        @if($isLate)
                                            <span class="text-red-600 font-bold text-xs bg-red-50 px-2 py-1 rounded">LATE</span>
                                        @else
                                            <span class="text-emerald-600 font-bold text-xs bg-emerald-50 px-2 py-1 rounded">ON TIME</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="{{ asset('uploads/submissions/'.$submission->file_path) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-bold shadow transition inline-flex items-center gap-2">
                                            <i class="fa-solid fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>