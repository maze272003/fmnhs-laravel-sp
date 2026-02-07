<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Year Management | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .modal-animate { transition: all 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-calendar-check text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">School Year Management</h2>
                </div>
            </div>
            @include('components.admin.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

            @if(session('success'))
                <script>
                    Swal.fire({icon: 'success', title: 'Success!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false});
                </script>
            @endif

            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">School Years</h1>
                    <p class="text-slate-500 font-medium">Configure and manage academic school years.</p>
                </div>
                <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-[1.5rem] font-black shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center gap-3">
                    <i class="fa-solid fa-plus-circle"></i> New School Year
                </button>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">School Year</th>
                                <th class="px-6 py-5">Start Date</th>
                                <th class="px-6 py-5">End Date</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($schoolYears as $sy)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-8 py-5 font-black text-slate-800 text-base">{{ $sy->school_year }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-600">{{ $sy->start_date ? $sy->start_date->format('M d, Y') : '-' }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-600">{{ $sy->end_date ? $sy->end_date->format('M d, Y') : '-' }}</td>
                                    <td class="px-6 py-5 text-center">
                                        @if($sy->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                            </span>
                                        @elseif($sy->status === 'closed')
                                            <span class="inline-flex items-center gap-1.5 bg-slate-50 text-slate-500 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Closed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Upcoming
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <div class="flex justify-center gap-2">
                                            @if($sy->status !== 'active')
                                                <form action="{{ route('admin.school-years.activate', $sy->id) }}" method="POST" class="activate-form">
                                                    @csrf
                                                    <button type="button" class="activate-btn w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Activate">
                                                        <i class="fa-solid fa-play text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($sy->status === 'active')
                                                <form action="{{ route('admin.school-years.close', $sy->id) }}" method="POST" class="close-form">
                                                    @csrf
                                                    <button type="button" class="close-btn w-10 h-10 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm" title="Close">
                                                        <i class="fa-solid fa-lock text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No school years configured</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $schoolYears->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-10 shadow-2xl modal-animate border border-white">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">New School Year</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Configure academic period</p>
                </div>
                <button onclick="closeModal('addModal')" class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.school-years.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">School Year</label>
                    <input type="text" name="school_year" placeholder="e.g. 2025-2026" required
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Start Date</label>
                    <input type="date" name="start_date" required
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">End Date</label>
                    <input type="date" name="end_date" required
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 uppercase text-xs tracking-[0.2em]">
                        Create School Year
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }

        document.querySelectorAll('.activate-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({ title: 'Activate School Year?', text: 'This will deactivate other school years.', icon: 'question', showCancelButton: true, confirmButtonColor: '#10b981', confirmButtonText: 'Yes, Activate' }).then(r => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });

        document.querySelectorAll('.close-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({ title: 'Close School Year?', text: 'This school year will be marked as closed.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, Close it' }).then(r => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });
    </script>
</body>
</html>
