<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classwork | Google Classroom Style</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
        body { font-family: 'Roboto', sans-serif; background-color: #f8fafc; }
        
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem; }
        .page-link { padding: 0.5rem 1rem; border-radius: 0.375rem; background: white; border: 1px solid #e2e8f0; color: #64748b; font-size: 0.875rem; }
        .page-item.active .page-link { background: #10b981; color: white; border-color: #10b981; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        {{-- HEADER --}}
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 px-8 py-4 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </div>
                    <h2 class="text-xl font-medium text-slate-700">Classwork</h2>
                </div>
            </div>
            @include('components.teacher.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });
                </script>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- LEFT: CREATE FORM --}}
                <div class="lg:col-span-4">
                    <div class="bg-white p-6 rounded-lg shadow border border-slate-200 sticky top-24">
                        <h3 class="text-lg font-medium text-slate-800 mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-plus text-emerald-600"></i> Create
                        </h3>

                        <form action="{{ route('teacher.assignments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">For</label>
                                <select name="class_info" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 outline-none text-sm text-slate-700">
                                    <option value="" disabled selected>Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->subject_id }}|{{ $class->section_id }}">
                                            {{ $class->section->name }} ({{ $class->subject->code }}) 
                                            @if($class->section->schoolYear)
                                                - SY {{ $class->section->schoolYear->school_year }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Title</label>
                                <input type="text" name="title" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 outline-none text-sm" placeholder="Assignment Title">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Instructions</label>
                                <textarea name="description" rows="3" class="w-full p-3 bg-slate-50 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 outline-none text-sm custom-scrollbar" placeholder="Instructions (optional)"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Due Date</label>
                                <input type="datetime-local" name="deadline" required class="w-full p-3 bg-slate-50 border border-slate-300 rounded focus:ring-2 focus:ring-emerald-500 outline-none text-sm text-slate-500">
                            </div>
                            <div class="border-2 border-dashed border-slate-300 rounded p-4 text-center hover:bg-slate-50 transition cursor-pointer relative">
                                <i class="fa-solid fa-paperclip text-slate-400 text-lg mb-1"></i>
                                <p class="text-xs text-slate-500 font-medium">Add Attachment</p>
                                <input type="file" name="attachment" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded shadow transition-colors text-sm uppercase tracking-wide">
                                Assign
                            </button>
                        </form>
                    </div>
                </div>

                {{-- RIGHT: STREAM --}}
                <div class="lg:col-span-8">
                    
                    {{-- HEADER & SEARCH BAR --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-2xl font-normal text-slate-800">Stream</h2>
                            <span class="text-sm text-slate-500">
                                {{ $assignments->total() }} assignments posted
                                @if($search)
                                    <span class="text-emerald-600 font-bold">• Filtering by "{{ $search }}"</span>
                                @endif
                            </span>
                        </div>

                        {{-- SEARCH FORM --}}
                        <form action="{{ route('teacher.assignments.index') }}" method="GET" class="relative w-full sm:w-64">
                            <input type="text" name="search" value="{{ $search }}" 
                                   placeholder="Search classwork..." 
                                   class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-full focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-sm transition-shadow shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                            </div>
                            @if($search)
                                <a href="{{ route('teacher.assignments.index') }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </a>
                            @endif
                        </form>
                    </div>

                    {{-- ASSIGNMENTS LIST --}}
                    <div class="space-y-4">
                        @forelse($assignments as $asn)
                            <div class="bg-white border border-slate-200 rounded-lg hover:shadow-md transition-shadow group overflow-hidden">
                                <div class="px-6 py-3 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">
                                            {{ $asn->subject->code }}
                                        </span>
                                        <span class="text-slate-300 text-xs">•</span>
                                        <span class="text-xs font-medium text-slate-600">
                                            {{ $asn->section->name }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">
                                        Posted {{ $asn->created_at->format('M d') }}
                                    </span>
                                </div>

                                <div class="p-6 flex flex-col sm:flex-row gap-6">
                                    <div class="hidden sm:flex shrink-0 w-12 h-12 bg-emerald-100 rounded-full items-center justify-center text-emerald-600">
                                        <i class="fa-solid fa-clipboard-list text-xl"></i>
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h3 class="text-lg font-medium text-slate-800 hover:text-emerald-600 transition-colors cursor-pointer" 
                                                onclick="window.location='{{ route('teacher.assignments.show', $asn->id) }}'">
                                                {{ $asn->title }}
                                            </h3>
                                            <button class="text-slate-400 hover:text-slate-600">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                        </div>

                                        <p class="text-xs text-slate-500 mt-1 mb-4 font-medium">
                                            Due {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, h:i A') }}
                                        </p>

                                        @if($asn->file_path)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('s3')->url($asn->file_path) }}" target="_blank" 
                                               class="inline-flex items-center gap-3 border border-slate-200 rounded-md p-2 pr-4 hover:bg-slate-50 transition-colors group/file mb-4 max-w-sm">
                                                <div class="w-10 h-10 bg-red-100 rounded flex items-center justify-center text-red-500 shrink-0">
                                                    <i class="fa-solid fa-file-pdf"></i>
                                                </div>
                                                <div class="overflow-hidden">
                                                    <p class="text-xs font-medium text-slate-700 truncate group-hover/file:text-emerald-600 group-hover/file:underline">
                                                        {{ basename($asn->file_path) }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 uppercase">PDF / File</p>
                                                </div>
                                            </a>
                                        @endif
                                        
                                        <div class="border-t border-slate-100 pt-4 mt-2 flex justify-between items-center">
                                            <div class="flex -space-x-2">
                                                 @foreach($asn->submissions->take(3) as $sub)
                                                    <img src="{{ $sub->student->avatar_url ?? 'https://ui-avatars.com/api/?name='.$sub->student->first_name }}" 
                                                         class="w-8 h-8 rounded-full border-2 border-white" title="{{ $sub->student->first_name }}">
                                                 @endforeach
                                                 @if($asn->submissions->count() > 3)
                                                    <div class="w-8 h-8 rounded-full border-2 border-white bg-slate-100 text-[10px] flex items-center justify-center font-bold text-slate-500">
                                                        +{{ $asn->submissions->count() - 3 }}
                                                    </div>
                                                 @endif
                                            </div>

                                            <div class="flex items-center gap-4">
                                                <span class="text-sm text-slate-500">
                                                    <strong class="text-slate-800">{{ $asn->submissions->count() }}</strong> turned in
                                                </span>
                                                <div class="h-4 w-[1px] bg-slate-300"></div>
                                                <a href="{{ route('teacher.assignments.show', $asn->id) }}" class="text-sm font-medium text-emerald-600 hover:underline">
                                                    View Instructions
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 bg-white border border-slate-200 rounded-lg">
                                <img src="https://www.gstatic.com/classroom/empty_states_home.svg" class="h-32 mx-auto opacity-50 mb-4">
                                <p class="text-slate-500">No assignments found</p>
                                @if($search)
                                    <a href="{{ route('teacher.assignments.index') }}" class="text-sm text-emerald-600 font-bold hover:underline mt-2 inline-block">Clear Search</a>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    {{-- PAGINATION LINKS (Appends search query) --}}
                    <div class="mt-8">
                        {{ $assignments->appends(['search' => $search])->links() }}
                    </div>

                </div>
            </div>
        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>