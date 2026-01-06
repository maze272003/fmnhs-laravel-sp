<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>My Assignments | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-[#f8fafc] text-slate-800 font-sans antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-2 md:gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500 mr-1">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-list-check text-[10px]"></i>
                    </div>
                    <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight truncate">Academic Tasks</h2>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 leading-none mb-1">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Grade {{ $student->section->grade_level }} - {{ $student->section->name }}
                    </p>
                </div>

                <img src="{{ $student->avatar_url }}" 
                     alt="Profile" 
                     class="w-9 h-9 md:w-11 md:h-11 rounded-2xl object-cover border-2 border-white shadow-md">
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-12 max-w-6xl mx-auto w-full space-y-8">
            
            <div class="mb-2">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Assignment Feed</h1>
                <p class="text-slate-500 text-sm font-medium">Manage your submissions and monitor upcoming deadlines.</p>
            </div>

            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Success!', text: "{{ session('success') }}", showConfirmButton: false, timer: 2000, borderRadius: '24px' });
                </script>
            @endif

            <div class="space-y-6">
                @forelse($assignments as $asn)
                    @php
                        $mySubmission = $asn->submissions->first(); 
                        $isLate = now() > $asn->deadline;
                    @endphp

                    <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm hover:shadow-2xl hover:shadow-blue-100/40 transition-all duration-500 overflow-hidden group flex flex-col lg:flex-row">
                        
                        <div class="flex-1 p-8 md:p-10 relative">
                            <div class="absolute left-0 top-12 bottom-12 w-1.5 {{ $mySubmission ? 'bg-emerald-500' : ($isLate ? 'bg-rose-500' : 'bg-blue-500') }} rounded-r-full shadow-[0_0_15px_rgba(0,0,0,0.1)]"></div>
                            
                            <div class="flex flex-wrap items-center gap-3 mb-6">
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-black px-4 py-1.5 rounded-xl uppercase tracking-widest border border-slate-200">{{ $asn->subject->code }}</span>
                                
                                @if($mySubmission)
                                    <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-4 py-1.5 rounded-xl uppercase tracking-widest border border-emerald-100 flex items-center gap-1.5">
                                        <i class="fa-solid fa-circle-check"></i> Handed In
                                    </span>
                                @elseif($isLate)
                                    <span class="bg-rose-50 text-rose-600 text-[10px] font-black px-4 py-1.5 rounded-xl uppercase tracking-widest border border-rose-100 flex items-center gap-1.5">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Overdue
                                    </span>
                                @else
                                    <span class="bg-blue-50 text-blue-600 text-[10px] font-black px-4 py-1.5 rounded-xl uppercase tracking-widest border border-blue-100 flex items-center gap-1.5">
                                        <i class="fa-solid fa-thumbtack"></i> Pending
                                    </span>
                                @endif
                            </div>
                            
                            <h3 class="text-2xl font-black text-slate-900 mb-4 leading-tight group-hover:text-blue-600 transition-colors">{{ $asn->title }}</h3>
                            <p class="text-slate-500 text-sm md:text-base mb-8 leading-relaxed font-medium line-clamp-3">{{ $asn->description }}</p>
                            
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                                @if($asn->file_path)
                                    <a href="{{ asset('uploads/assignments/'.$asn->file_path) }}" target="_blank" class="flex items-center gap-3 px-6 py-3 bg-slate-900 text-white rounded-2xl text-[11px] font-black hover:bg-blue-600 transition-all shadow-xl shadow-slate-200 uppercase tracking-widest group/btn">
                                        <i class="fa-solid fa-download group-hover:-translate-y-1 transition-transform"></i> Reference Materials
                                    </a>
                                @endif
                                
                                <div class="flex items-center gap-2 text-[11px] font-bold text-slate-400 uppercase tracking-[0.15em]">
                                    <i class="fa-solid fa-calendar-day text-rose-400"></i>
                                    <span>Deadline: {{ \Carbon\Carbon::parse($asn->deadline)->format('M d • h:i A') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="w-full lg:w-[350px] bg-slate-50/50 p-8 md:p-10 border-t lg:border-t-0 lg:border-l border-slate-100 flex flex-col justify-center">
                            @if($mySubmission)
                                <div class="text-center space-y-4">
                                    <div class="w-20 h-20 bg-white shadow-xl shadow-emerald-100/50 text-emerald-500 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-3xl">
                                        <i class="fa-solid fa-envelope-circle-check"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-black text-slate-900 text-xl tracking-tight">Work Turned In</h4>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em]">Validated on {{ $mySubmission->created_at->format('M d') }}</p>
                                    </div>
                                    <div class="pt-4">
                                        <a href="{{ asset('uploads/submissions/'.$mySubmission->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-slate-900 rounded-2xl text-[10px] font-black border border-slate-200 hover:border-blue-600 hover:text-blue-600 transition-all shadow-sm uppercase tracking-widest">
                                            <i class="fa-solid fa-paperclip"></i> My Attachment
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-6">
                                    <div class="text-center lg:text-left">
                                        <h4 class="font-black text-xs text-slate-400 uppercase tracking-[0.2em]">Upload Submission</h4>
                                        <p class="text-[10px] font-medium text-slate-400 mt-1">PDF, DOCX, or ZIP supported (Max 10MB)</p>
                                    </div>
                                    
                                    <form action="{{ route('student.assignments.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                        @csrf
                                        <input type="hidden" name="assignment_id" value="{{ $asn->id }}">
                                        
                                        <div class="relative group">
                                            <div class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 rounded-3xl group-hover:border-blue-400 group-hover:bg-blue-50/50 transition-all cursor-pointer">
                                                <i class="fa-solid fa-cloud-arrow-up text-slate-300 group-hover:text-blue-500 text-2xl mb-2 transition-colors"></i>
                                                <span class="text-[9px] font-black text-slate-400 group-hover:text-blue-600 uppercase tracking-widest">Select File</span>
                                                <input type="file" name="file" required class="absolute inset-0 opacity-0 cursor-pointer"/>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="w-full bg-blue-600 text-white text-[11px] font-black py-4 rounded-2xl hover:bg-slate-900 shadow-xl shadow-blue-100 hover:shadow-slate-200 transition-all active:scale-[0.98] flex items-center justify-center gap-3 uppercase tracking-[0.15em]">
                                            <span>Submit Work</span>
                                            <i class="fa-solid fa-paper-plane text-[9px]"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                @empty
                    <div class="py-24 flex flex-col items-center justify-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">
                        <div class="relative mb-10">
                            <div class="w-48 h-48 bg-slate-50 rounded-full flex items-center justify-center relative overflow-hidden border border-slate-100">
                                <i class="fa-solid fa-mug-hot text-8xl text-slate-100 absolute -bottom-4"></i>
                                <i class="fa-solid fa-check-double text-6xl text-blue-100 opacity-50 absolute -right-2 top-6 rotate-12"></i>
                            </div>
                            <div class="absolute -top-4 -right-4 w-16 h-16 bg-white rounded-3xl shadow-2xl flex items-center justify-center border border-slate-50">
                                <i class="fa-solid fa-sparkles text-amber-400 text-2xl"></i>
                            </div>
                        </div>
                        
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight mb-3">All Caught Up!</h3>
                        <p class="text-slate-400 font-medium text-center max-w-xs px-6 leading-relaxed">
                            There are no pending assignments for your section. Take a break and recharge!
                        </p>
                    </div>
                @endforelse
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>