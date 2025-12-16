<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Submissions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.assignments.index') }}" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-50 text-slate-400 transition-all">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">Student Submissions</h2>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Faculty Portal
                </span>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-10">

            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 mb-8 relative overflow-hidden">
                <i class="fa-solid fa-file-export absolute -right-6 -bottom-6 text-9xl text-slate-50 opacity-50 pointer-events-none rotate-12"></i>
                
                <div class="flex flex-col md:flex-row justify-between items-start gap-6 relative z-10">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-3 py-1 rounded-lg uppercase tracking-wider border border-emerald-100">
                                {{ $assignment->subject->code }}
                            </span>
                            <span class="text-slate-300 text-xs">•</span>
                            <span class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                                Section {{ $assignment->section }}
                            </span>
                        </div>
                        <h1 class="text-3xl font-black text-slate-900 mb-3 tracking-tight">{{ $assignment->title }}</h1>
                        <p class="text-slate-500 font-medium leading-relaxed max-w-2xl">{{ $assignment->description }}</p>
                        
                        @if($assignment->file_path)
                            <div class="mt-6 flex items-center gap-3">
                                <a href="{{ asset('uploads/assignments/'.$assignment->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white transition-all shadow-sm border border-blue-100">
                                    <i class="fa-solid fa-paperclip"></i> View Instructions Reference
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="w-full md:w-auto shrink-0">
                        <div class="bg-rose-50 border border-rose-100 p-6 rounded-2xl text-center md:text-right">
                            <p class="text-[10px] text-rose-400 font-black uppercase tracking-[0.2em] mb-1">Final Deadline</p>
                            <p class="text-xl font-black text-rose-600">
                                {{ \Carbon\Carbon::parse($assignment->deadline)->format('M d, Y') }}
                            </p>
                            <p class="text-sm font-bold text-rose-500 opacity-80 uppercase tracking-tighter mt-0.5">
                                {{ \Carbon\Carbon::parse($assignment->deadline)->format('h:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/30">
                    <h3 class="font-black text-xl text-slate-800 tracking-tight">Student Work</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Turned In:</span>
                        <span class="bg-slate-900 text-white text-xs font-black px-3 py-1 rounded-lg">
                            {{ $assignment->submissions->count() }}
                        </span>
                    </div>
                </div>

                @if($assignment->submissions->isEmpty())
                    <div class="py-24 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200">
                            <i class="fa-regular fa-folder-open text-4xl"></i>
                        </div>
                        <h4 class="text-slate-800 font-black">No Submissions Yet</h4>
                        <p class="text-slate-400 text-sm font-medium">Students haven't uploaded their work for this task.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                    <th class="px-8 py-5">Full Name</th>
                                    <th class="px-6 py-5">Date & Time</th>
                                    <th class="px-6 py-5">Submission Status</th>
                                    <th class="px-8 py-5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($assignment->submissions as $submission)
                                    @php
                                        $isLate = $submission->created_at > $assignment->deadline;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                                                    {{ substr($submission->student->first_name, 0, 1) }}{{ substr($submission->student->last_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-800 tracking-tight">{{ $submission->student->last_name }}, {{ $submission->student->first_name }}</p>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">Verified Student</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-sm font-medium text-slate-500">
                                            {{ $submission->created_at->format('M d, Y') }}
                                            <span class="block text-[10px] text-slate-400 font-bold uppercase">{{ $submission->created_at->format('h:i A') }}</span>
                                        </td>
                                        <td class="px-6 py-5">
                                            @if($isLate)
                                                <span class="inline-flex items-center gap-1.5 text-rose-600 font-black text-[10px] bg-rose-50 px-3 py-1.5 rounded-full border border-rose-100 uppercase tracking-widest">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Late
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-emerald-600 font-black text-[10px] bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-100 uppercase tracking-widest">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> On Time
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <a href="{{ asset('uploads/submissions/'.$submission->file_path) }}" target="_blank" class="inline-flex items-center gap-2 bg-slate-900 text-white px-5 py-2.5 rounded-xl text-xs font-black shadow-lg shadow-slate-100 hover:bg-emerald-600 hover:shadow-emerald-100 transition-all active:scale-95 group/btn">
                                                <i class="fa-solid fa-download group-hover/btn:scale-110 transition-transform"></i> 
                                                <span>Download Work</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>