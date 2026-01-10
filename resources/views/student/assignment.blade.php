<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>My Assignments | Organized View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-3 sm:gap-4">
                
                <!-- MISSING BUTTON ADDED HERE -->
                <button id="mobile-menu-btn" class="md:hidden p-2 -ml-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors">
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
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 leading-none mb-1">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Grade {{ $student->section->grade_level }}</p>
                </div>
                
                <!-- Avatar Logic (Fixed for Local/Hostinger) -->
                @php
                    $avatar = $student->avatar ? (
                        file_exists(public_path('uploads/avatars/' . $student->avatar)) 
                            ? asset('uploads/avatars/' . $student->avatar) 
                            : asset('storage/avatars/' . $student->avatar)
                    ) : "https://ui-avatars.com/api/?name=".urlencode($student->first_name)."&background=4f46e5&color=fff";
                @endphp
                <img src="{{ $avatar }}" class="w-11 h-11 rounded-2xl object-cover border-2 border-white shadow-md">
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-12 max-w-7xl mx-auto w-full">

            @if(session('success'))
                <script>Swal.fire({ icon: 'success', title: 'Task Submitted!', text: "{{ session('success') }}", showConfirmButton: false, timer: 1500, borderRadius: '24px' });</script>
            @endif

            @php
                // I-group ang assignments base sa subject_id
                $groupedAssignments = $assignments->groupBy('subject_id');
            @endphp

            @forelse($groupedAssignments as $subjectId => $subjectGroup)
                @php $subject = $subjectGroup->first()->subject; @endphp
                
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-6 sticky top-[80px] bg-[#f8fafc]/90 backdrop-blur-sm py-2 z-20">
                        <div class="px-5 py-2 bg-white border border-slate-200 rounded-2xl shadow-sm">
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                                <span class="text-indigo-600 mr-2">{{ $subject->code }}</span> {{ $subject->name }}
                            </h2>
                        </div>
                        <div class="flex-1 h-[1px] bg-slate-200"></div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $subjectGroup->count() }} Task(s)</span>
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        @foreach($subjectGroup as $asn)
                            @php
                                $mySubmission = $asn->submissions->where('student_id', $student->id)->first();
                                $isLate = now() > $asn->deadline;
                            @endphp

                            <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col sm:flex-row">
                                <div class="flex-1 p-8 border-r border-slate-50 relative">
                                    <div class="absolute left-0 top-8 bottom-8 w-1 {{ $mySubmission ? 'bg-emerald-500' : ($isLate ? 'bg-rose-500' : 'bg-indigo-500') }} rounded-r-full"></div>
                                    
                                    <div class="flex items-center gap-2 mb-4">
                                        @if($mySubmission)
                                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-lg">Done</span>
                                        @elseif($isLate)
                                            <span class="text-[9px] font-black text-rose-600 uppercase tracking-widest bg-rose-50 px-3 py-1 rounded-lg">Overdue</span>
                                        @else
                                            <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-3 py-1 rounded-lg">Active</span>
                                        @endif
                                    </div>

                                    <h3 class="text-xl font-black text-slate-900 mb-2 leading-tight">{{ $asn->title }}</h3>
                                    <p class="text-slate-500 text-xs font-medium line-clamp-2 mb-6">{{ $asn->description }}</p>

                                    <div class="flex items-center gap-4 mt-auto">
                                        @if($asn->file_path)
                                            @php
                                                // Hybrid Logic for File Attachment
                                                $attachFile = basename($asn->file_path);
                                                $attachSrc = file_exists(public_path('uploads/assignments/' . $attachFile)) 
                                                    ? asset('uploads/assignments/' . $attachFile) 
                                                    : asset('storage/assignments/' . $asn->file_path); // Fallback usually not needed if strict
                                                
                                                // Check for Hostinger path just in case
                                                if(file_exists(public_path('uploads/assignments/' . $asn->file_path))) {
                                                     $attachSrc = asset('uploads/assignments/' . $asn->file_path);
                                                }
                                            @endphp
                                            <a href="{{ $attachSrc }}" target="_blank" class="text-slate-400 hover:text-indigo-600 transition-colors flex items-center gap-1">
                                                <i class="fa-solid fa-paperclip text-sm"></i> <span class="text-[10px] font-bold uppercase">View</span>
                                            </a>
                                        @endif
                                        <span class="text-[10px] font-bold text-slate-400 ml-auto">
                                            <i class="fa-regular fa-clock mr-1 text-rose-400"></i> {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, h:i A') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="w-full sm:w-[220px] bg-slate-50/50 p-8 flex flex-col justify-center items-center border-t sm:border-t-0 sm:border-l border-slate-100">
                                    @if($mySubmission)
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm mx-auto mb-3 text-emerald-500">
                                                <i class="fa-solid fa-check-double"></i>
                                            </div>
                                            <p class="text-[10px] font-black text-slate-900 uppercase">Submitted</p>
                                        </div>
                                    @else
                                        <form action="{{ route('student.assignments.submit') }}" method="POST" enctype="multipart/form-data" class="w-full space-y-3">
                                            @csrf
                                            <input type="hidden" name="assignment_id" value="{{ $asn->id }}">
                                            <label class="block group">
                                                <div class="w-full h-12 bg-white border-2 border-dashed border-slate-200 group-hover:border-indigo-400 rounded-xl flex items-center justify-center cursor-pointer transition-all">
                                                    <i class="fa-solid fa-cloud-arrow-up text-slate-300 group-hover:text-indigo-500 text-lg transition-colors"></i>
                                                    <input type="file" name="file" required class="hidden" onchange="this.form.submit()">
                                                </div>
                                            </label>
                                            <p class="text-[8px] text-center font-bold text-slate-400 uppercase tracking-widest">Click to upload</p>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            @empty
                <div class="py-32 flex flex-col items-center justify-center">
                    <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                        <i class="fa-solid fa-face-smile-beam text-5xl text-slate-200"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">Walang Pending!</h3>
                    <p class="text-slate-400 font-medium">Lahat ng subjects mo ay updated. Good job, {{ $student->first_name }}!</p>
                </div>
            @endforelse

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>