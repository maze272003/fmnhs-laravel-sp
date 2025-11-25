<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assignments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-blue-600">My Tasks</h2>
        </header>

        <main class="flex-1 p-6 space-y-6">
            
            @foreach($assignments as $asn)
                @php
                    $mySubmission = $asn->submissions->first(); // Check if submitted
                    $isLate = now() > $asn->deadline;
                @endphp

                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 flex flex-col md:flex-row gap-6">
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded uppercase">{{ $asn->subject->code }}</span>
                            @if($mySubmission)
                                <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-1 rounded uppercase"><i class="fa-solid fa-check"></i> Turned In</span>
                            @elseif($isLate)
                                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded uppercase">Missing / Late</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-1 rounded uppercase">Assigned</span>
                            @endif
                        </div>
                        
                        <h3 class="text-xl font-bold mb-2">{{ $asn->title }}</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">{{ $asn->description }}</p>
                        
                        @if($asn->file_path)
                            <a href="{{ asset('uploads/assignments/'.$asn->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-slate-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                                <i class="fa-solid fa-file-arrow-down text-blue-500"></i> View Material
                            </a>
                        @endif
                        
                        <p class="text-xs text-gray-400 mt-4 font-mono">Due: {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, h:i A') }}</p>
                    </div>

                    <div class="w-full md:w-80 bg-gray-50 dark:bg-slate-900/50 p-4 rounded-lg border border-dashed border-gray-300 dark:border-slate-600">
                        @if($mySubmission)
                            <div class="text-center py-4">
                                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2 text-emerald-600 text-xl">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <p class="font-bold text-emerald-600">Submitted!</p>
                                <a href="{{ asset('uploads/submissions/'.$mySubmission->file_path) }}" class="text-xs text-blue-500 hover:underline block mt-1">View my work</a>
                            </div>
                        @else
                            <h4 class="font-bold text-sm mb-3 text-gray-600 dark:text-gray-300">Your Work</h4>
                            <form action="{{ route('student.assignments.submit') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="assignment_id" value="{{ $asn->id }}">
                                <input type="file" name="file" required class="block w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-3"/>
                                <button type="submit" class="w-full bg-blue-600 text-white text-sm font-bold py-2 rounded hover:bg-blue-700 shadow-sm transition">Mark as Done</button>
                            </form>
                        @endif
                    </div>

                </div>
            @endforeach

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>