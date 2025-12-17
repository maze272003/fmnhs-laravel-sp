<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600 mr-2 transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Classwork</h2>
            </div>
        </header>

        <main class="flex-1 p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-24">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                        <h3 class="font-black text-lg text-slate-800">Create Assignment</h3>
                    </div>

                    <form action="{{ route('teacher.assignments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Target Class</label>
                            <select name="class_info" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none text-sm">
                                <option value="" disabled selected>-- Select Class --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->subject_id }}|{{ $class->section }}">
                                        {{ $class->subject->code }} - {{ $class->section }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Title</label>
                            <input type="text" name="title" required placeholder="e.g. Midterm Project" 
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Instructions</label>
                            <textarea name="description" rows="3" placeholder="Explain the tasks..." 
                                      class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Due Date</label>
                            <input type="datetime-local" name="deadline" required 
                                   class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 tracking-wider">Attach Reference</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-emerald-400 transition-colors group">
                                <div class="space-y-1 text-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-slate-300 group-hover:text-emerald-500 text-2xl mb-2 transition-colors"></i>
                                    <div class="flex text-sm text-slate-600">
                                        <label class="relative cursor-pointer bg-white rounded-md font-bold text-emerald-600 hover:text-emerald-500">
                                            <span>Upload a file</span>
                                            <input type="file" name="attachment" class="sr-only">
                                        </label>
                                    </div>
                                    <p class="text-[10px] text-slate-400">PDF, DOCX, JPG up to 10MB</p>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all active:scale-[0.98] mt-4">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Assign to Students
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-xl text-slate-800 tracking-tight">Posted Assignments</h3>
                    <span class="text-xs font-bold text-slate-400 bg-white px-3 py-1 rounded-full border border-slate-100 shadow-sm">
                        Total: {{ $assignments->count() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-4">
                @foreach($assignments as $asn)
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-100 transition-all group">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-wider border border-emerald-100">
                                        {{ $asn->subject->code }}
                                    </span>
                                    <span class="text-slate-300 text-xs">•</span>
                                    <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                                        Section {{ $asn->section }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-xl text-slate-800 group-hover:text-emerald-600 transition-colors leading-tight">
                                    {{ $asn->title }}
                                </h4>
                                <div class="flex items-center gap-4 mt-3">
                                    <p class="text-[11px] font-bold text-slate-400 flex items-center gap-1.5 uppercase">
                                        <i class="fa-regular fa-clock text-rose-400"></i>
                                        Due: {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, Y • h:i A') }}
                                    </p>
                                </div>
                            </div>

                            <div class="bg-slate-50 rounded-2xl p-4 text-center min-w-[100px] border border-slate-100 group-hover:bg-emerald-50 group-hover:border-emerald-100 transition-colors">
                                <span class="text-3xl font-black text-slate-800 group-hover:text-emerald-600 transition-colors">
                                    {{ $asn->submissions->count() }}
                                </span>
                                <p class="text-[10px] text-slate-400 uppercase font-black leading-none mt-1">Submissions</p>
                            </div>
                        </div>
                        
                        @if($asn->file_path)
                            <div class="mt-6 p-3 bg-slate-50 rounded-xl flex items-center gap-3 border border-slate-100 group-hover:bg-white transition-colors">
                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] text-slate-400 uppercase font-black">Reference File</p>
                                    <a href="{{ asset('uploads/assignments/'.$asn->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm font-bold truncate block">
                                        {{ basename($asn->file_path) }}
                                    </a>
                                </div>
                                <i class="fa-solid fa-download text-slate-300 mr-2"></i>
                            </div>
                        @endif

                        <div class="mt-6 pt-5 border-t border-slate-50 flex justify-between items-center">
                             <div class="flex -space-x-2">
                                <div class="w-7 h-7 rounded-full border-2 border-white bg-slate-200"></div>
                                <div class="w-7 h-7 rounded-full border-2 border-white bg-slate-300"></div>
                                <div class="w-7 h-7 rounded-full border-2 border-white bg-emerald-500 flex items-center justify-center text-[8px] text-white font-bold">+{{ max(0, $asn->submissions->count() - 2) }}</div>
                             </div>
                            
                            <a href="{{ route('teacher.assignments.show', $asn->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl font-bold text-xs transition-all">
                                Review Submissions <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>