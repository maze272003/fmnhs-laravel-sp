<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm border-b border-slate-100 sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-user-graduate text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Student Roster</h2>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Faculty Portal
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
                <form action="{{ route('teacher.students.index') }}" method="GET" class="flex flex-col md:flex-row gap-6 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2 ml-1">Filter by Section</label>
                        <div class="relative group">
                            <select name="section" class="w-full p-3 pl-10 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-bold appearance-none cursor-pointer">
                                <option value="" disabled {{ !$selectedSection ? 'selected' : '' }}>-- Select a Section --</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec }}" {{ $selectedSection == $sec ? 'selected' : '' }}>{{ $sec }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-emerald-500">
                                <i class="fa-solid fa-layer-group text-xs"></i>
                            </div>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-300">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full md:w-auto bg-slate-900 text-white font-black py-3.5 px-8 rounded-2xl hover:bg-emerald-600 shadow-lg shadow-slate-100 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        <span>Load Records</span>
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h3 class="font-black text-xl text-slate-800 tracking-tight">
                        @if($selectedSection)
                            Class List: <span class="text-emerald-600">{{ $selectedSection }}</span>
                        @else
                            <span class="text-slate-400 font-bold italic">No Section Selected</span>
                        @endif
                    </h3>
                    @if($selectedSection)
                    <span class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                        Total: {{ $students->count() }} Students
                    </span>
                    @endif
                </div>

                @if($selectedSection && $students->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">#</th>
                                <th class="px-6 py-5">LRN</th>
                                <th class="px-6 py-5">Student Information</th>
                                <th class="px-6 py-5">Contact Email</th>
                                <th class="px-8 py-5 text-center">Year Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($students as $index => $student)
                            <tr class="hover:bg-emerald-50/30 transition-colors group">
                                <td class="px-8 py-4 text-slate-300 font-bold text-xs">{{ sprintf('%02d', $index + 1) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 font-mono text-[11px] font-bold rounded-lg border border-slate-200">
                                        {{ $student->lrn }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $avatar = $student->avatar ? asset('avatars/'.$student->avatar) : "https://ui-avatars.com/api/?name=".urlencode($student->first_name)."&background=f1f5f9&color=64748b&bold=true";
                                        @endphp
                                        <img src="{{ $avatar }}" class="w-10 h-10 rounded-xl border border-slate-100 shadow-sm group-hover:scale-110 transition-transform">
                                        <div>
                                            <p class="font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">{{ $student->last_name }}, {{ $student->first_name }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">Official Student</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-slate-500">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-regular fa-envelope text-slate-300"></i>
                                        {{ $student->email }}
                                    </div>
                                </td>
                                <td class="px-8 py-4 text-center">
                                    <span class="inline-flex px-3 py-1 rounded-lg text-[10px] font-black bg-white text-slate-600 border border-slate-200 shadow-sm uppercase tracking-widest group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600 transition-all">
                                        Grade {{ $student->grade_level }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif($selectedSection)
                    <div class="py-20 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-200">
                            <i class="fa-solid fa-folder-open text-3xl"></i>
                        </div>
                        <h4 class="text-slate-800 font-black">No Students Found</h4>
                        <p class="text-slate-400 text-sm">There are no records found for this specific section.</p>
                    </div>
                @else
                    <div class="py-24 text-center">
                        <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-[2rem] flex items-center justify-center mx-auto mb-6 animate-bounce shadow-lg shadow-emerald-50">
                            <i class="fa-solid fa-users-viewfinder text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 mb-2 tracking-tight">Ready to view your class?</h3>
                        <p class="text-slate-400 max-w-xs mx-auto font-medium leading-relaxed">Please select a section from the dropdown above to load the student master list.</p>
                    </div>
                @endif
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>