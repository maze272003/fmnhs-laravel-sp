<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Class Schedules | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-calendar-plus text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Scheduling</h2>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    SY 2025-2026 | Normalized
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">
            
            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Class Scheduling</h1>
                <p class="text-slate-500 font-medium">Assign subjects and faculty to specific sections in the database.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <div class="lg:col-span-4">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 sticky top-28 transition-all">
                        <div class="flex items-center gap-2 mb-8">
                            <div class="w-1.5 h-6 bg-indigo-500 rounded-full"></div>
                            <h3 class="font-black text-lg text-slate-800 tracking-tight uppercase">Assign Class</h3>
                        </div>
                        
                        <form action="{{ route('admin.schedules.store') }}" method="POST" class="space-y-5">
                            @csrf
                            
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Target Section</label>
                                <select name="section_id" required class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm cursor-pointer appearance-none">
                                    <option value="">-- Select Section --</option>
                                    @foreach($sections as $sec)
                                        <option value="{{ $sec->id }}">Grade {{ $sec->grade_level }} - {{ $sec->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Course Subject</label>
                                <select name="subject_id" required class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm cursor-pointer appearance-none">
                                    <option value="">-- Select Subject --</option>
                                    @foreach($subjects as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->code }} — {{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Faculty Member</label>
                                <select name="teacher_id" required class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm cursor-pointer appearance-none">
                                    <option value="">-- Select Teacher --</option>
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->last_name }}, {{ $t->first_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Day(s)</label>
                                    <select name="day" required class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm outline-none">
                                        <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                                        <option>Thursday</option><option>Friday</option><option>MWF</option><option>TTH</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Room No.</label>
                                    <input type="text" name="room" placeholder="e.g. 102" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl outline-none font-bold text-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Time In</label>
                                    <input type="time" name="start_time" required class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Time Out</label>
                                    <input type="time" name="end_time" required class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm outline-none">
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-slate-900 text-white font-black py-4 rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 hover:shadow-indigo-100 transition-all active:scale-[0.98] flex items-center justify-center gap-3 group mt-4">
                                <span>Save Schedule</span>
                                <i class="fa-solid fa-plus-circle text-xs group-hover:rotate-90 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-8">
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                        <th class="px-8 py-5">Class Details</th>
                                        <th class="px-6 py-5">Time Slot</th>
                                        <th class="px-6 py-5">Instructor</th>
                                        <th class="px-8 py-5 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($schedules as $sched)
                                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-xs border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                                    {{ $sched->subject?->code ?? 'N/A' }}
                                                </div>
                                                <div>
                                                    <p class="font-black text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors">
                                                        {{ $sched->section?->name ?? 'No Section' }}
                                                    </p>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">
                                                        Grade {{ $sched->section?->grade_level ?? '?' }} • Room {{ $sched->room ?? 'N/A' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-slate-700 tracking-tight">{{ $sched->day }}</span>
                                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100 w-fit mt-1">
                                                    {{ \Carbon\Carbon::parse($sched->start_time)->format('g:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($sched->end_time)->format('g:i A') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500">
                                                    {{ $sched->teacher ? substr($sched->teacher->first_name, 0, 1) : '?' }}
                                                </div>
                                                <span class="text-sm font-bold text-slate-700">
                                                    {{ $sched->teacher ? $sched->teacher->last_name . ', ' . $sched->teacher->first_name : 'No Instructor Assigned' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 text-right">
                                            <form action="{{ route('admin.schedules.destroy', $sched->id) }}" method="POST" class="delete-form inline">
                                                @csrf @method('DELETE')
                                                <button type="button" class="w-10 h-10 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm delete-btn">
                                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                            {{ $schedules->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Remove Schedule?',
                    text: "Class records linked to this schedule may be affected.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f43f5e',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Delete',
                    borderRadius: '25px',
                }).then((result) => {
                    if (result.isConfirmed) this.closest('form').submit();
                });
            });
        });
    </script>
</body>
</html>