<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Submissions | Faculty Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="glass-header border-b border-slate-200/60 sticky top-0 z-40 px-8 py-5 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.assignments.index') }}" 
                   class="w-11 h-11 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition-all shadow-sm">
                    <i class="fa-solid fa-arrow-left-long text-sm"></i>
                </a>
                <div class="flex flex-col">
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Submission Review</h2>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em]">Academic Evaluation</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                    Faculty Access
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

            <div class="bg-white p-8 md:p-10 rounded-[3rem] shadow-sm border border-slate-100 mb-10 relative overflow-hidden group">
                <i class="fa-solid fa-file-signature absolute -right-8 -bottom-8 text-[12rem] text-slate-50 pointer-events-none rotate-12 transition-transform group-hover:rotate-[20deg] duration-700"></i>
                
                <div class="flex flex-col lg:flex-row justify-between items-start gap-8 relative z-10">
                    <div class="flex-1 space-y-6">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="bg-emerald-600 text-white text-[10px] font-black px-4 py-1.5 rounded-xl uppercase tracking-widest shadow-lg shadow-emerald-100">
                                {{ $assignment->subject?->code ?? 'N/A' }}
                            </span>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-users text-[10px]"></i>
                               {{ $assignment->section?->name ?? 'No Section' }} (Grade {{ $assignment->section?->grade_level ?? '--' }})
                            </span>
                        </div>
                        
                        <div>
                            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mb-4 tracking-tight leading-tight">{{ $assignment->title }}</h1>
                            <p class="text-slate-500 font-medium text-lg leading-relaxed max-w-3xl">{{ $assignment->description }}</p>
                        </div>
                        
                        @if($assignment->file_path)
                            <div class="pt-2">
                                <a href="{{ asset('uploads/assignments/'.$assignment->file_path) }}" target="_blank" 
                                   class="inline-flex items-center gap-3 px-6 py-3 bg-indigo-50 text-indigo-600 rounded-2xl text-[11px] font-black hover:bg-indigo-600 hover:text-white transition-all shadow-sm border border-indigo-100 uppercase tracking-widest">
                                    <i class="fa-solid fa-paperclip"></i> View Instructions Reference
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="w-full lg:w-auto shrink-0">
                        <div class="bg-rose-50 border border-rose-100 p-8 rounded-[2.5rem] text-center lg:text-right relative overflow-hidden group/deadline">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-rose-100 rounded-bl-full opacity-30 group-hover/deadline:scale-150 transition-transform duration-500"></div>
                            <p class="text-[10px] text-rose-400 font-black uppercase tracking-[0.2em] mb-3 relative z-10">Final Cutoff</p>
                            <p class="text-3xl font-black text-rose-600 tracking-tighter relative z-10">
                                {{ \Carbon\Carbon::parse($assignment->deadline)->format('M d, Y') }}
                            </p>
                            <p class="text-xs font-bold text-rose-500 opacity-80 uppercase tracking-widest mt-2 relative z-10">
                                <i class="fa-regular fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($assignment->deadline)->format('h:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/60 border border-slate-200/50 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-slate-50/40">
                    <div class="flex items-center gap-4">
                        <h3 class="font-black text-2xl text-slate-900 tracking-tight">Handed In</h3>
                        <span class="bg-slate-900 text-white text-[10px] font-black px-4 py-1.5 rounded-xl shadow-lg shadow-slate-200 uppercase tracking-widest">
                            {{ $assignment->submissions->count() }} Submissions
                        </span>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                        <i class="fa-solid fa-filter text-[10px] text-slate-300 uppercase tracking-widest"></i>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sort: Latest First</span>
                    </div>
                </div>

                @if($assignment->submissions->isEmpty())
                    <div class="py-32 text-center relative">
                        <div class="w-32 h-32 bg-slate-50 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 text-slate-200 border border-slate-50">
                            <i class="fa-solid fa-box-open text-5xl"></i>
                        </div>
                        <h4 class="text-2xl font-black text-slate-900 tracking-tight mb-2 uppercase">No files found</h4>
                        <p class="text-slate-400 font-medium max-w-xs mx-auto">None of your students have turned in their work for this assignment yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50/30 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                                    <th class="px-10 py-6">Student Information</th>
                                    <th class="px-8 py-6">Date of Submission</th>
                                    <th class="px-8 py-6 text-center">Status</th>
                                    <th class="px-10 py-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($assignment->submissions as $submission)
                                    @php $isLate = $submission->created_at > $assignment->deadline; @endphp
                                    <tr class="hover:bg-indigo-50/20 transition-all duration-300 group">
                                        <td class="px-10 py-6">
                                            <div class="flex items-center gap-4">
                                                <img src="{{ $submission->student->avatar_url }}" 
                                                     class="w-12 h-12 rounded-2xl border-2 border-white shadow-md group-hover:scale-110 transition-transform duration-500 object-cover">
                                                <div class="flex flex-col">
                                                    <p class="font-black text-slate-900 group-hover:text-emerald-700 transition-colors text-base">{{ $submission->student->last_name }}, {{ $submission->student->first_name }}</p>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">LRN: {{ $submission->student->lrn }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-700">{{ $submission->created_at->format('M d, Y') }}</span>
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ $submission->created_at->format('h:i A') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            @if($isLate)
                                                <span class="inline-flex items-center gap-2 text-rose-600 font-black text-[9px] bg-rose-50 px-4 py-2 rounded-2xl border border-rose-100 uppercase tracking-widest shadow-sm shadow-rose-50">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Late Submission
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-2 text-emerald-600 font-black text-[9px] bg-emerald-50 px-4 py-2 rounded-2xl border border-emerald-100 uppercase tracking-widest shadow-sm shadow-emerald-50">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> On Time
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-10 py-6 text-right">
                                            <a href="{{ asset('uploads/submissions/'.$submission->file_path) }}" target="_blank" 
                                               class="inline-flex items-center justify-center gap-3 bg-slate-900 text-white px-6 py-3 rounded-2xl text-[10px] font-black shadow-xl shadow-slate-200 hover:bg-emerald-600 hover:shadow-emerald-200 transition-all active:scale-95 group/btn uppercase tracking-widest">
                                                <i class="fa-solid fa-cloud-arrow-down group-hover/btn:-translate-y-1 transition-transform"></i> 
                                                <span>Download Work</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                
                <div class="p-8 bg-slate-50/40 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-emerald-500 text-xs"></i>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">Academic integrity verified by institutional portal v2.0</p>
                    </div>
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>