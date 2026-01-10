<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classwork Management | Faculty</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="glass-header border-b border-slate-200/60 sticky top-0 z-40 px-8 py-5 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-100">
                        <i class="fa-solid fa-file-signature text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Classwork</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Assignments & Submissions</p>
                    </div>
                </div>
            </div>

            @include('components.teacher.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">Manage Assignments</h1>
                <p class="text-slate-500 font-medium">Create tasks, attach resources, and monitor student progress per section.</p>
            </div>

            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Task Created', text: "{{ session('success') }}", showConfirmButton: false, timer: 2000, borderRadius: '24px' });
                </script>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-5 xl:col-span-4">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-emerald-100/50 border border-slate-200/50 sticky top-28">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center shadow-inner">
                                <i class="fa-solid fa-plus text-sm"></i>
                            </div>
                            <h3 class="font-black text-lg text-slate-800 tracking-tight">Post New Task</h3>
                        </div>

                        <form action="{{ route('teacher.assignments.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Target Class & Section</label>
                                <select name="class_info" required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none font-bold text-sm cursor-pointer appearance-none">
                                    <option value="" disabled selected>-- Select Assignment Target --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->subject_id }}|{{ $class->section_id }}">
                                            {{ $class->subject->code }} — {{ $class->section->name }} (Grade {{ $class->section->grade_level }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Assignment Title</label>
                                <input type="text" name="title" required placeholder="e.g. Laboratory Report #1" 
                                       class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Instructions</label>
                                <textarea name="description" rows="4" placeholder="Provide clear steps for the students..." 
                                          class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-medium text-slate-600 custom-scrollbar"></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Submission Deadline</label>
                                <input type="datetime-local" name="deadline" required 
                                       class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-slate-700">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Supporting Material</label>
                                <div class="relative group cursor-pointer">
                                    <div class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 rounded-[2rem] group-hover:border-emerald-400 group-hover:bg-emerald-50/30 transition-all">
                                        <i class="fa-solid fa-file-arrow-up text-slate-300 group-hover:text-emerald-500 text-2xl mb-2 transition-colors"></i>
                                        <span class="text-[9px] font-black text-slate-400 group-hover:text-emerald-600 uppercase tracking-widest">Attach File (Max 10MB)</span>
                                        <input type="file" name="attachment" class="absolute inset-0 opacity-0 cursor-pointer"/>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-[2rem] hover:bg-emerald-600 shadow-xl shadow-slate-200 hover:shadow-emerald-200 transition-all active:scale-[0.98] mt-4 flex items-center justify-center gap-3 group">
                                <span>Assign to Class</span>
                                <i class="fa-solid fa-paper-plane text-[10px] group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-7 xl:col-span-8 space-y-8">
                    <div class="flex items-center justify-between mb-4 px-4">
                        <div class="flex items-center gap-3">
                            <h3 class="font-black text-2xl text-slate-900 tracking-tight">Active Tasks</h3>
                            <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-4 py-1.5 rounded-full border border-emerald-100 uppercase tracking-widest">
                                {{ $assignments->count() }} Posted
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        @forelse($assignments as $asn)
                            <div class="bg-white p-8 rounded-[3rem] border border-slate-200/60 shadow-sm hover:shadow-2xl hover:shadow-emerald-100/30 transition-all duration-500 group overflow-hidden relative">
                                
                                <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                                    <div class="flex-1 space-y-4">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="bg-emerald-600 text-white text-[9px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest shadow-lg shadow-emerald-100">
                                                {{ $asn->subject->code }}
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                                <i class="fa-solid fa-users text-[10px]"></i>
                                                {{ $asn->section->name }} (Grade {{ $asn->section->grade_level }})
                                            </span>
                                        </div>
                                        
                                        <h4 class="font-extrabold text-2xl text-slate-900 group-hover:text-emerald-600 transition-colors leading-tight">
                                            {{ $asn->title }}
                                        </h4>
                                        
                                        <div class="flex items-center gap-6">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center text-xs">
                                                    <i class="fa-regular fa-clock"></i>
                                                </div>
                                                <p class="text-[11px] font-black text-slate-500 uppercase tracking-tight">
                                                    Due: {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, Y • h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-slate-50 rounded-[2.5rem] p-6 text-center min-w-[140px] border border-slate-100 group-hover:bg-emerald-50 group-hover:border-emerald-100 transition-all duration-500">
                                        <span class="text-4xl font-black text-slate-900 group-hover:text-emerald-600 transition-colors tracking-tighter">
                                            {{ $asn->submissions->count() }}
                                        </span>
                                        <p class="text-[9px] text-slate-400 uppercase font-black leading-none mt-2 tracking-widest">Student Replies</p>
                                    </div>
                                </div>
                                
                                @if($asn->file_path)
                                    <div class="mt-8 p-4 bg-[#f8fafc] rounded-2xl flex items-center justify-between border border-slate-100 group-hover:bg-white transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                                                <i class="fa-solid fa-file-lines text-sm"></i>
                                            </div>
                                            <div class="flex flex-col">
                                                <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest">Teacher Attachment</p>
                                                <a href="{{ asset('uploads/assignments/'.$asn->file_path) }}" target="_blank" class="text-blue-600 hover:text-indigo-600 text-sm font-bold truncate max-w-[200px] md:max-w-md">
                                                    {{ basename($asn->file_path) }}
                                                </a>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-arrow-down-long text-slate-200 mr-4"></i>
                                    </div>
                                @endif

                                <div class="mt-8 pt-6 border-t border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-6">
                                     <div class="flex items-center gap-3">
                                        <div class="flex -space-x-3">
                                            @foreach($asn->submissions->take(4) as $sub)
                                                <div class="w-9 h-9 rounded-full border-4 border-white bg-slate-200 overflow-hidden shadow-sm">
                                                    <img src="{{ $sub->student->avatar_url }}" class="w-full h-full object-cover">
                                                </div>
                                            @endforeach
                                            @if($asn->submissions->count() > 4)
                                                <div class="w-9 h-9 rounded-full border-4 border-white bg-emerald-500 flex items-center justify-center text-[10px] text-white font-black shadow-sm">
                                                    +{{ $asn->submissions->count() - 4 }}
                                                </div>
                                            @endif
                                        </div>
                                        @if($asn->submissions->count() > 0)
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Recent Activity</p>
                                        @endif
                                     </div>
                                    
                                    <a href="{{ route('teacher.assignments.show', $asn->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-3.5 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-2xl font-black text-[11px] uppercase tracking-widest transition-all shadow-sm active:scale-95 group/link">
                                        Review Submissions 
                                        <i class="fa-solid fa-arrow-right text-[10px] group-hover/link:translate-x-1 transition-transform"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-32 flex flex-col items-center justify-center bg-white rounded-[4rem] border-2 border-dashed border-slate-200 shadow-inner">
                                <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mb-8 relative">
                                    <i class="fa-solid fa-folder-open text-5xl text-slate-200"></i>
                                    <div class="absolute -top-2 -right-2 w-10 h-10 bg-white rounded-2xl shadow-xl flex items-center justify-center border border-slate-50">
                                        <i class="fa-solid fa-plus text-emerald-400 text-xs"></i>
                                    </div>
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight mb-2 uppercase">No assignments yet</h3>
                                <p class="text-slate-400 font-medium text-center max-w-xs px-6">
                                    Ready to start class? Use the form on the left to create your first academic task.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>