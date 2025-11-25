<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-emerald-600">Classwork</h2>
        </header>

        <main class="flex-1 p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
                    <h3 class="font-bold text-lg mb-4">Create Assignment</h3>
                    <form action="{{ route('teacher.assignments.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">Target Class</label>
                            <select name="class_info" required class="w-full p-2 border rounded dark:bg-slate-700">
                                <option value="" disabled selected>-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->subject_id }}|{{ $class->section }}">
                                        {{ $class->subject->code }} - {{ $class->section }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">Title</label>
                            <input type="text" name="title" required class="w-full p-2 border rounded dark:bg-slate-700">
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">Instructions</label>
                            <textarea name="description" rows="3" class="w-full p-2 border rounded dark:bg-slate-700"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-1">Due Date</label>
                            <input type="datetime-local" name="deadline" required class="w-full p-2 border rounded dark:bg-slate-700">
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-bold mb-1">Attach File</label>
                            <input type="file" name="attachment" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"/>
                        </div>

                        <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-2 rounded hover:bg-emerald-700">Assign</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4">
                <h3 class="font-bold text-lg">Posted Assignments</h3>
                @foreach($assignments as $asn)
    <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 hover:shadow-md transition">
        
        <div class="flex justify-between items-start">
            <div>
                <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2 py-1 rounded uppercase mb-2 inline-block">
                    {{ $asn->subject->code }} - {{ $asn->section }}
                </span>
                <h4 class="font-bold text-xl text-slate-800 dark:text-white">{{ $asn->title }}</h4>
                <p class="text-sm text-gray-500 mt-1">
                    Due: {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, Y h:i A') }}
                </p>
            </div>

            <div class="text-right">
                <span class="text-3xl font-bold text-emerald-600">{{ $asn->submissions->count() }}</span>
                <p class="text-xs text-gray-400 uppercase font-bold">Turned In</p>
            </div>
        </div>
        
        @if($asn->file_path)
            <div class="mt-4 p-3 bg-gray-50 dark:bg-slate-700 rounded-lg flex items-center gap-3 border border-gray-100 dark:border-slate-600">
                <i class="fa-solid fa-paperclip text-gray-400"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-500 uppercase font-bold">Reference Material</p>
                    <a href="{{ asset('uploads/assignments/'.$asn->file_path) }}" target="_blank" class="text-blue-600 hover:underline text-sm font-medium truncate block">
                        Download Attachment
                    </a>
                </div>
            </div>
        @endif

        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-end">
            <a href="{{ route('teacher.assignments.show', $asn->id) }}" class="text-emerald-600 hover:text-emerald-700 font-bold text-sm hover:underline flex items-center gap-2 transition">
                View Submissions <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
@endforeach
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>