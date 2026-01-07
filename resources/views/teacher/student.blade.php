<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Roster | Faculty Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                        <i class="fa-solid fa-user-graduate text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Student Roster</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Master Enrollment List</p>
                    </div>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                    Faculty Access
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200/60 mb-10">
                <form action="{{ route('teacher.students.index') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end">
                    <div class="w-full md:w-1/3 space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Filter by Assigned Section</label>
                        <div class="relative group">
                            <select name="section_id" class="w-full p-4 pl-12 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-bold appearance-none cursor-pointer text-slate-700">
                                <option value="" disabled {{ !$selectedSection ? 'selected' : '' }}>-- Select a Class --</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec->id }}" {{ $selectedSection && $selectedSection->id == $sec->id ? 'selected' : '' }}>
                                        Grade {{ $sec->grade_level }} - {{ $sec->name }} 
                                        {{ $sec->teacher_id == $currentTeacherId ? '(Your Advisory Class)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-emerald-500">
                                <i class="fa-solid fa-layer-group text-xs"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none text-slate-300">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full md:w-auto bg-slate-900 text-white font-black py-4 px-10 rounded-2xl hover:bg-emerald-600 shadow-xl shadow-slate-200 transition-all active:scale-95 flex items-center justify-center gap-3 group">
                        <i class="fa-solid fa-magnifying-glass text-xs group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs uppercase tracking-widest">Load Records</span>
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-200/50 overflow-hidden">
                <div class="px-10 py-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h3 class="font-black text-2xl text-slate-900 tracking-tight">
                        @if($selectedSection)
                            Class List: <span class="text-emerald-600">{{ $selectedSection->name }}</span>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Advisory Teacher:</span>
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black border border-blue-100">
                                    <i class="fa-solid fa-chalkboard-user mr-1"></i>
                                    {{ $selectedSection->teacher ? $selectedSection->teacher->name : 'No Adviser Assigned' }}
                                    {{ $selectedSection->teacher_id == $currentTeacherId ? '(You)' : '' }}
                                </span>
                            </div>
                        @else
                            <span class="text-slate-300 italic font-bold">Waiting for selection...</span>
                        @endif
                    </h3>
                    @if($selectedSection)
                    <div class="flex items-center gap-2">
                        <span class="px-5 py-2 bg-emerald-50 text-emerald-600 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                            {{ $students->count() }} Total Enrolled
                        </span>
                    </div>
                    @endif
                </div>

                @if($selectedSection && $students->count() > 0)
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                                <th class="px-10 py-6">#</th>
                                <th class="px-8 py-6">LRN</th>
                                <th class="px-8 py-6">Student Information</th>
                                <th class="px-8 py-6">Contact Details</th>
                                <th class="px-10 py-6 text-center">Identity Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($students as $index => $student)
                            <tr class="hover:bg-emerald-50/20 transition-all duration-300 group">
                                <td class="px-10 py-6 text-slate-300 font-black text-xs">{{ sprintf('%02d', $index + 1) }}</td>
                                <td class="px-8 py-6">
                                    <span class="px-4 py-1.5 bg-slate-50 text-slate-600 font-mono text-[11px] font-black rounded-xl border border-slate-100 shadow-sm group-hover:bg-white group-hover:border-emerald-200 transition-all">
                                        {{ $student->lrn }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ $student->avatar_url }}" class="w-12 h-12 rounded-[1.25rem] border-2 border-white shadow-md group-hover:scale-110 transition-transform duration-500 object-cover">
                                        <div class="flex flex-col leading-tight">
                                            <p class="font-black text-slate-900 group-hover:text-emerald-600 transition-colors text-base">{{ $student->last_name }}, {{ $student->first_name }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Grade {{ $student->section->grade_level }} Scholar</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2 text-sm font-bold text-slate-600">
                                            <i class="fa-solid fa-envelope-circle-check text-slate-300 text-xs"></i>
                                            {{ $student->email }}
                                        </div>
                                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-tighter">Verified Institutional Email</p>
                                    </div>
                                </td>
                                <td class="px-10 py-6 text-center">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-[10px] font-black bg-white text-slate-500 border border-slate-100 shadow-sm uppercase tracking-widest group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 transition-all">
                                        <i class="fa-solid fa-id-card-clip text-xs"></i>
                                        Official Student
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif($selectedSection)
                    <div class="py-24 text-center">
                        <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6 text-slate-200 border border-slate-100">
                            <i class="fa-solid fa-folder-open text-4xl"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 tracking-tight">No Students Found</h4>
                        <p class="text-slate-400 font-medium max-w-xs mx-auto mt-2">There are currently no active student records linked to this section.</p>
                    </div>
                @else
                    <div class="py-32 text-center relative overflow-hidden group">
                        <i class="fa-solid fa-users absolute -right-10 -bottom-10 text-[15rem] text-slate-50 pointer-events-none"></i>
                        <div class="relative z-10">
                            <div class="w-32 h-32 bg-emerald-50 text-emerald-500 rounded-[2.5rem] flex items-center justify-center mx-auto mb-8 shadow-xl shadow-emerald-50 group-hover:scale-105 transition-transform duration-700">
                                <i class="fa-solid fa-users-viewfinder text-5xl"></i>
                            </div>
                            <h3 class="text-3xl font-black text-slate-900 mb-3 tracking-tight">Roster Ready to Load</h3>
                            <p class="text-slate-400 max-w-sm mx-auto font-medium leading-relaxed px-6">Select one of your handle sections from the filter above to access the official student master list.</p>
                        </div>
                    </div>
                @endif
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>