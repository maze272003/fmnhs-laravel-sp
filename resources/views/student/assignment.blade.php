<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>My Assignments | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-2 md:gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500 mr-1">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-list-check text-sm"></i>
                    </div>
                    <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight truncate">My Tasks</h2>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                
                {{-- HELPER USED: Logic is now inside Student Model (avatar_url) --}}
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-xs text-gray-500">Grade {{ $student->grade_level }} - {{ $student->section }}</p>
                </div>

                {{-- Since avatar_url always returns a valid link (S3 or UI Avatars), we always show the IMG tag --}}
                <img src="{{ $student->avatar_url }}" 
                     alt="Profile" 
                     class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover border border-gray-200 shadow-sm">

            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-10 space-y-6">
            
            <div class="mb-2">
                <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Assignment Feed</h1>
                <p class="text-slate-500 text-sm font-medium">Keep track of your academic responsibilities and deadlines.</p>
            </div>

            @forelse($assignments as $asn)
                @php
                    $mySubmission = $asn->submissions->first(); 
                    $isLate = now() > $asn->deadline;
                @endphp

                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl hover:border-blue-100 transition-all duration-300 group flex flex-col md:flex-row">
                    
                    <div class="flex-1 p-6 md:p-8 relative">
                        <div class="absolute left-0 top-10 bottom-10 w-1.5 {{ $mySubmission ? 'bg-emerald-500' : ($isLate ? 'bg-rose-500' : 'bg-blue-500') }} rounded-r-full"></div>
                        
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="bg-slate-100 text-slate-600 text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-wider border border-slate-200">{{ $asn->subject->code }}</span>
                            
                            @if($mySubmission)
                                <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1 rounded-lg uppercase border border-emerald-100"><i class="fa-solid fa-check-circle mr-1"></i>Turned In</span>
                            @elseif($isLate)
                                <span class="bg-rose-50 text-rose-600 text-[10px] font-black px-3 py-1 rounded-lg uppercase border border-rose-100"><i class="fa-solid fa-circle-exclamation mr-1"></i>Missing / Late</span>
                            @else
                                <span class="bg-blue-50 text-blue-600 text-[10px] font-black px-3 py-1 rounded-lg uppercase border border-blue-100"><i class="fa-solid fa-clock mr-1"></i>Assigned</span>
                            @endif
                        </div>
                        
                        <h3 class="text-xl md:text-2xl font-black text-slate-800 mb-3 leading-tight group-hover:text-blue-600 transition-colors">{{ $asn->title }}</h3>
                        <p class="text-slate-500 text-sm md:text-base mb-6 leading-relaxed font-medium line-clamp-3">{{ $asn->description }}</p>
                        
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            @if($asn->file_path)
                                <a href="{{ asset('uploads/assignments/'.$asn->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-50 text-blue-600 rounded-xl text-xs font-black hover:bg-blue-600 hover:text-white transition-all shadow-sm border border-blue-100 w-full sm:w-auto justify-center">
                                    <i class="fa-solid fa-file-arrow-down"></i> Download Material
                                </a>
                            @endif
                            
                            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                                <i class="fa-regular fa-calendar-times text-rose-400 text-sm"></i>
                                <span>Due: {{ \Carbon\Carbon::parse($asn->deadline)->format('M d • h:i A') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-80 bg-slate-50/50 p-6 md:p-8 border-t md:border-t-0 md:border-l border-slate-100 flex flex-col justify-center">
                        @if($mySubmission)
                            <div class="text-center">
                                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-[1.2rem] flex items-center justify-center mx-auto mb-4 text-xl shadow-inner">
                                    <i class="fa-solid fa-check-double"></i>
                                </div>
                                <h4 class="font-black text-slate-800 text-lg">Work Submitted</h4>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-4">Well done!</p>
                                <a href="{{ asset('uploads/submissions/'.$mySubmission->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-blue-600 rounded-xl text-[10px] font-black border border-slate-200 hover:border-blue-300 transition-all shadow-sm uppercase">
                                    <i class="fa-solid fa-eye"></i> View Attachment
                                </a>
                            </div>
                        @else
                            <h4 class="font-black text-xs mb-4 text-slate-400 uppercase tracking-[0.2em] text-center md:text-left">Your Submission</h4>
                            <form action="{{ route('student.assignments.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <input type="hidden" name="assignment_id" value="{{ $asn->id }}">
                                
                                <div class="relative group">
                                    <input type="file" name="file" required class="block w-full text-[10px] text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer transition-all"/>
                                </div>
                                
                                <button type="submit" class="w-full bg-slate-900 text-white text-xs font-black py-4 rounded-2xl hover:bg-emerald-600 shadow-xl shadow-slate-100 transition-all active:scale-[0.98] flex items-center justify-center gap-2 uppercase tracking-widest">
                                    <span>Turn in Work</span>
                                    <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            @empty
                <div class="py-20 flex flex-col items-center justify-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                    <div class="relative mb-8">
                        <div class="w-40 h-40 bg-blue-50 rounded-full flex items-center justify-center relative overflow-hidden">
                            <i class="fa-solid fa-mug-hot text-7xl text-blue-200 absolute -bottom-2"></i>
                            <i class="fa-solid fa-face-smile-beam text-8xl text-blue-100 opacity-50 absolute -right-4 top-2 rotate-12"></i>
                        </div>
                        <div class="absolute -top-2 -right-2 w-12 h-12 bg-white rounded-full shadow-lg flex items-center justify-center">
                            <i class="fa-solid fa-sparkles text-amber-400"></i>
                        </div>
                    </div>
                    
                    <h3 class="text-3xl font-black text-slate-800 tracking-tight mb-2 text-center px-4">All Caught Up!</h3>
                    <p class="text-slate-500 font-medium text-center max-w-xs px-6">
                        No assignments found in your feed. Relax, or enjoy some free time before the next task arrives!
                    </p>
                    
                    <div class="mt-10 flex gap-4">
                        <div class="px-6 py-2 bg-slate-50 rounded-full text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-100">
                            FMNHS Student Portal
                        </div>
                    </div>
                </div>
            @endforelse

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>