<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>My Tasks | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    {{-- SIDEBAR COMPONENT --}}
    @include('components.student.sidebar')

    {{-- MAIN CONTENT WRAPPER --}}
    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        {{-- HEADER --}}
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-3 sm:gap-4">
                {{-- Mobile Sidebar Toggle --}}
                <button onclick="toggleSidebar()" class="md:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                <div class="w-10 h-10 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100 shrink-0">
                    <i class="fa-solid fa-layer-group text-sm"></i>
                </div>
                <div class="flex flex-col">
                    <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Subject Roadmap</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest hidden sm:block">Organized by Curriculum</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @php $student = Auth::guard('student')->user(); @endphp
                
                {{-- Student Info (Desktop) --}}
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 leading-none mb-1">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">
                        {{ $student->section ? 'Grade ' . $student->section->grade_level . ' - ' . $student->section->name : 'No Section' }}
                    </p>
                </div>
                
                {{-- Avatar (S3 / URL / Fallback) --}}
                <img src="{{ 
                        ($student->avatar && $student->avatar !== 'default.png') 
                        ? (
                            \Illuminate\Support\Str::startsWith($student->avatar, 'http') 
                            ? $student->avatar 
                            : \Illuminate\Support\Facades\Storage::disk('s3')->url($student->avatar)
                          ) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode($student->first_name . '+' . $student->last_name) . '&background=0D8ABC&color=fff'
                     }}" 
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff';"
                     alt="Profile" 
                     class="w-10 h-10 rounded-2xl object-cover border-2 border-white shadow-md">
            </div>
        </header>

        {{-- MAIN BODY --}}
        <main class="flex-1 p-6 lg:p-12 max-w-7xl mx-auto w-full">

            {{-- SUCCESS ALERT --}}
            @if(session('success'))
                <script>Swal.fire({ icon: 'success', title: 'Great Job!', text: "{{ session('success') }}", showConfirmButton: false, timer: 2000, borderRadius: '20px' });</script>
            @endif

            {{-- ERROR ALERT --}}
            @if(session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-center gap-3 text-rose-700 font-bold">
                    <i class="fa-solid fa-circle-exclamation text-xl"></i>
                    {{ session('error') }}
                </div>
            @endif

            {{-- VALIDATION ERRORS --}}
            @if($errors->any())
                <div class="mb-8 p-5 bg-rose-50 border border-rose-100 rounded-3xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full bg-rose-200 flex items-center justify-center text-rose-700">
                            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                        </div>
                        <h3 class="font-bold text-rose-800">Submission Failed</h3>
                    </div>
                    <ul class="list-disc list-inside text-xs font-bold text-rose-600 ml-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                // Group assignments by Subject ID
                $groupedAssignments = $assignments->groupBy('subject_id');
            @endphp

            @forelse($groupedAssignments as $subjectId => $subjectGroup)
                @php $subject = $subjectGroup->first()->subject; @endphp
                
                <div class="mb-12">
                    {{-- Sticky Subject Header --}}
                    <div class="flex items-center gap-4 mb-6 sticky top-[80px] bg-[#f8fafc]/95 backdrop-blur-sm py-4 z-20 border-b border-slate-100/50">
                        <div class="px-5 py-2 bg-white border border-slate-200 rounded-2xl shadow-sm">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                                <span class="text-indigo-600 mr-2">{{ $subject->code ?? 'SUB' }}</span> {{ $subject->name }}
                            </h2>
                        </div>
                        <div class="flex-1 h-[2px] bg-slate-100 rounded-full"></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-100 px-3 py-1 rounded-full">
                            {{ $subjectGroup->count() }} Task(s)
                        </span>
                    </div>

                    {{-- Assignments Grid --}}
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        @foreach($subjectGroup as $asn)
                            @php
                                $mySubmission = $asn->submissions->where('student_id', $student->id)->first();
                                $isLate = now() > $asn->deadline;
                            @endphp

                            <div class="group bg-white rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 overflow-hidden flex flex-col sm:flex-row h-full">
                                
                                {{-- LEFT SIDE: Details --}}
                                <div class="flex-1 p-8 border-r border-slate-50 relative flex flex-col">
                                    {{-- Status Indicator Bar --}}
                                    <div class="absolute left-0 top-8 bottom-8 w-1.5 {{ $mySubmission ? 'bg-emerald-500' : ($isLate ? 'bg-rose-500' : 'bg-indigo-500') }} rounded-r-full"></div>
                                    
                                    {{-- Status Badge --}}
                                    <div class="flex items-center gap-2 mb-4 pl-2">
                                        @if($mySubmission)
                                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-100">
                                                <i class="fa-solid fa-check mr-1"></i> Completed
                                            </span>
                                        @elseif($isLate)
                                            <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest bg-rose-50 px-3 py-1 rounded-lg border border-rose-100">
                                                <i class="fa-solid fa-circle-exclamation mr-1"></i> Overdue
                                            </span>
                                        @else
                                            <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-lg border border-indigo-100">
                                                <i class="fa-solid fa-clock mr-1"></i> Active
                                            </span>
                                        @endif
                                    </div>

                                    <h3 class="text-lg font-black text-slate-900 mb-2 leading-tight pl-2 group-hover:text-indigo-700 transition-colors">{{ $asn->title }}</h3>
                                    <p class="text-slate-500 text-xs font-medium line-clamp-3 mb-6 pl-2 leading-relaxed">{{ $asn->description }}</p>

                                    {{-- Teacher Attachment & Deadline --}}
                                    <div class="mt-auto pl-2 pt-4 border-t border-slate-50 flex items-center justify-between gap-4">
                                        @if($asn->file_path)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('s3')->url($asn->file_path) }}" 
                                               target="_blank" 
                                               class="flex items-center gap-2 text-slate-400 hover:text-indigo-600 transition-colors group/link"
                                               title="Download Attachment">
                                                <div class="w-8 h-8 rounded-lg bg-slate-50 group-hover/link:bg-indigo-50 flex items-center justify-center transition-colors">
                                                    <i class="fa-solid fa-paperclip text-sm"></i>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-[8px] font-bold uppercase tracking-wider">Attachment</span>
                                                    <span class="text-[10px] font-bold text-slate-600 underline decoration-slate-300 group-hover/link:decoration-indigo-300 truncate max-w-[100px]">Download</span>
                                                </div>
                                            </a>
                                        @else
                                            <div class="text-slate-300 text-xs italic pl-1">No attachments</div>
                                        @endif

                                        <div class="text-right">
                                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Deadline</p>
                                            <span class="text-[10px] font-bold {{ $isLate && !$mySubmission ? 'text-rose-500' : 'text-slate-600' }} bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                                {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, h:i A') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT SIDE: Submission Area --}}
                                <div class="w-full sm:w-[240px] bg-slate-50/50 p-6 flex flex-col justify-center items-center border-t sm:border-t-0 sm:border-l border-slate-100 relative">
                                    
                                    @if($mySubmission)
                                        {{-- SUBMITTED STATE --}}
                                        <div class="text-center w-full">
                                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-3 text-emerald-500 ring-4 ring-emerald-50">
                                                <i class="fa-solid fa-check-double text-2xl"></i>
                                            </div>
                                            <p class="text-[11px] font-black text-slate-900 uppercase tracking-wide mb-1">Submitted</p>
                                            <p class="text-[9px] font-bold text-slate-400 mb-5">
                                                {{ \Carbon\Carbon::parse($mySubmission->submitted_at)->diffForHumans() }}
                                            </p>

                                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('s3')->url($mySubmission->file_path) }}" 
                                               target="_blank"
                                               class="flex items-center justify-center w-full py-3 rounded-xl bg-white border-2 border-slate-100 shadow-sm text-[10px] font-bold text-slate-600 uppercase tracking-widest hover:border-indigo-500 hover:text-indigo-600 transition-all">
                                                <i class="fa-solid fa-eye mr-2"></i> View File
                                            </a>
                                        </div>
                                    @else
                                        {{-- UPLOAD FORM --}}
                                        <form action="{{ route('student.assignments.submit') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-4 text-center">
                                            @csrf
                                            <input type="hidden" name="assignment_id" value="{{ $asn->id }}">
                                            
                                            <label class="block group/upload relative w-full">
                                                <div class="w-full h-32 bg-white border-2 border-dashed border-slate-300 group-hover/upload:border-indigo-500 group-hover/upload:bg-indigo-50/10 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition-all">
                                                    
                                                    <div class="w-10 h-10 rounded-full bg-slate-50 group-hover/upload:bg-indigo-100 flex items-center justify-center mb-2 transition-colors">
                                                        <i class="fa-solid fa-cloud-arrow-up text-slate-400 group-hover/upload:text-indigo-600 transition-colors"></i>
                                                    </div>
                                                    
                                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest group-hover/upload:text-indigo-700">
                                                        Upload File
                                                    </span>
                                                    <span class="text-[8px] text-slate-400 mt-1">
                                                        PDF, Docx, Img (Max 25MB)
                                                    </span>
                                                </div>
                                                
                                                {{-- File Input --}}
                                                <input type="file" 
                                                       name="file" 
                                                       required 
                                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                                       onchange="showLoading(this)">
                                            </label>
                                            
                                            {{-- Loading Indicator (Hidden by default) --}}
                                            <div class="loading-indicator hidden flex items-center justify-center gap-2 text-indigo-600">
                                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                                                <span class="text-xs font-bold">Uploading...</span>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            @empty
                {{-- EMPTY STATE --}}
                <div class="min-h-[60vh] flex flex-col items-center justify-center text-center">
                    <div class="w-40 h-40 bg-white rounded-full flex items-center justify-center mb-6 shadow-sm border border-slate-100">
                        <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Relax" class="w-20 opacity-60">
                    </div>
                    <h3 class="text-3xl font-black text-slate-900 mb-2">All Caught Up!</h3>
                    <p class="text-slate-400 font-medium max-w-md">
                        You have no pending assignments. Take a break, you've earned it!
                    </p>
                </div>
            @endforelse

        </main>
    </div>

    {{-- SCRIPT FOR LOADING STATE & SIDEBAR --}}
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Close sidebar when clicking overlay
        document.getElementById('overlay').addEventListener('click', toggleSidebar);

        // Show loading when file is selected
        function showLoading(input) {
            if (input.files && input.files[0]) {
                const form = input.closest('form');
                const loadingDiv = form.querySelector('.loading-indicator');
                const labelDiv = form.querySelector('label');

                // Hide label, show loading
                labelDiv.classList.add('opacity-50', 'pointer-events-none');
                loadingDiv.classList.remove('hidden');

                // Submit form
                form.submit();
            }
        }
    </script>
</body>
</html>