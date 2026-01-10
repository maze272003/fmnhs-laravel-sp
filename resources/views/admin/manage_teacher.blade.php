<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Faculty Management | {{ ($viewArchived ?? false) ? 'Archive' : 'Registry' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="glass-header sticky top-0 z-40 px-8 py-5 flex justify-between items-center border-b border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 {{ ($viewArchived ?? false) ? 'bg-amber-600' : 'bg-emerald-600' }} text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-100">
                        <i class="fa-solid {{ ($viewArchived ?? false) ? 'fa-box-archive' : 'fa-chalkboard-user' }} text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Faculty Roster</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ ($viewArchived ?? false) ? 'Archived Records' : 'Active Educators' }}</p>
                    </div>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                    SY 2025-2026
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            @if(session('success'))
                <script>Swal.fire({icon: 'success', title: 'Action Successful', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false, borderRadius: '24px'});</script>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl mb-6 border border-rose-100 text-sm font-bold">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-10">
                <div class="w-full lg:w-auto">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Faculty Management</h1>
                    <p class="text-slate-500 font-medium">Overview of educators and their advisory assignments.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <form action="{{ route('admin.teachers.index') }}" method="GET" class="flex-1 lg:min-w-[320px] relative group">
                        @if($viewArchived ?? false) <input type="hidden" name="archived" value="1"> @endif
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search ID or name..." 
                            class="w-full pl-12 pr-10 py-4 bg-white border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-hover:text-emerald-500 transition-colors">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        @if(request('search'))
                            <a href="{{ route('admin.teachers.index', ($viewArchived ?? false) ? ['archived' => 1] : []) }}" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-300 hover:text-rose-500">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </a>
                        @endif
                    </form>

                    <div class="flex items-center gap-3">
                        @if($viewArchived ?? false)
                            <a href="{{ route('admin.teachers.index') }}" class="bg-slate-900 text-white px-6 py-4 rounded-[1.5rem] font-black text-[11px] uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-slate-200 transition-all active:scale-95">
                                <i class="fa-solid fa-arrow-left"></i> Active List
                            </a>
                        @else
                            <a href="{{ route('admin.teachers.index', ['archived' => 1]) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-4 rounded-[1.5rem] font-black text-[11px] uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-amber-100 transition-all active:scale-95">
                                <i class="fa-solid fa-box-archive"></i> Archive
                            </a>
                            <!-- FIXED: Added onclick event -->
                            <button onclick="openModal('addModal')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-4 rounded-[1.5rem] font-black shadow-xl shadow-emerald-100 transition-all active:scale-95 flex items-center justify-center gap-3">
                                <i class="fa-solid fa-user-plus text-xs"></i> Add Faculty
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                                <th class="px-10 py-6">Emp ID</th>
                                <th class="px-8 py-6">Faculty Member</th>
                                <th class="px-8 py-6">Department</th>
                                <th class="px-8 py-6">Advisory Assignment</th>
                                <th class="px-10 py-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($teachers as $teacher)
                            <tr class="hover:bg-emerald-50/20 transition-all duration-300 group">
                                <td class="px-10 py-6">
                                    <span class="px-4 py-1.5 bg-slate-50 text-slate-600 font-mono text-[11px] font-black rounded-xl border border-slate-100">
                                        {{ $teacher->employee_id }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xs shadow-sm group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500">
                                            {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col leading-tight">
                                            <p class="font-black text-slate-900 group-hover:text-emerald-700 transition-colors text-base">
                                                {{ $teacher->last_name }}, {{ $teacher->first_name }}
                                            </p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ $teacher->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="inline-flex px-4 py-2 rounded-xl text-[10px] font-black bg-white text-slate-500 border border-slate-100 uppercase tracking-widest shadow-sm">
                                        {{ $teacher->department }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    @if($teacher->advisorySection)
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                                            <span class="text-sm font-black text-indigo-600 tracking-tight uppercase">
                                                Grade {{ $teacher->advisorySection->grade_level }} - {{ $teacher->advisorySection->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-300 uppercase italic tracking-widest">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex justify-center items-center gap-3">
                                        @if($teacher->trashed())
                                            <form action="{{ route('admin.teachers.restore', $teacher->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Restore Faculty">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button onclick="editTeacher({{ $teacher }})" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-blue-50 text-blue-500 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form action="{{ route('admin.teachers.archive', $teacher->id) }}" method="POST">
                                                @csrf
                                                <button type="button" class="archive-btn w-11 h-11 flex items-center justify-center rounded-2xl bg-amber-50 text-amber-500 hover:bg-amber-600 hover:text-white transition-all shadow-sm">
                                                    <i class="fa-solid fa-box-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-8 bg-slate-50/40 border-t border-slate-100">
                    {{ $teachers->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- NEW ADD MODAL -->
    <div id="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-10 shadow-2xl border border-white">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">New Faculty</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Onboard new educator</p>
                </div>
                <button onclick="closeModal('addModal')" class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Employee ID</label>
                    <input type="text" name="employee_id" required placeholder="T-2025-XXXX"
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">First Name</label>
                        <input type="text" name="first_name" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Last Name</label>
                        <input type="text" name="last_name" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                    <input type="email" name="email" required 
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Department</label>
                    <select name="department" required 
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm outline-none appearance-none">
                        <option value="" disabled selected>Select Department</option>
                        <option>Mathematics</option>
                        <option>Science</option>
                        <option>English</option>
                        <option>Filipino</option>
                        <option>Araling Panlipunan</option>
                        <option>TLE (Livelihood Education)</option>
                        <option>MAPEH</option>
                        <option>Values Education</option>
                    </select>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-emerald-600 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-emerald-200 hover:bg-emerald-700 transition-all active:scale-95 uppercase text-xs tracking-widest">
                        Add to Registry
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-10 shadow-2xl border border-white">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">Edit Faculty</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Update professional profile</p>
                </div>
                <button onclick="closeModal('editModal')" class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editForm" method="POST" class="space-y-6">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                    <input type="email" id="edit_email" name="email" required 
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none transition-all font-bold text-sm">
                </div>
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Department</label>
                    <select id="edit_department" name="department" required 
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm outline-none appearance-none">
                        <option>Mathematics</option>
                        <option>Science</option>
                        <option>English</option>
                        <option>Filipino</option>
                        <option>Araling Panlipunan</option>
                        <option>TLE (Livelihood Education)</option>
                        <option>MAPEH</option>
                        <option>Values Education</option>
                    </select>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-slate-200 hover:bg-emerald-600 transition-all active:scale-95 uppercase text-xs tracking-widest">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function editTeacher(teacher) {
            document.getElementById('edit_first_name').value = teacher.first_name;
            document.getElementById('edit_last_name').value = teacher.last_name;
            document.getElementById('edit_email').value = teacher.email;
            document.getElementById('edit_department').value = teacher.department;
            document.getElementById('editForm').action = `/admin/teachers/${teacher.id}`;
            openModal('editModal');
        }

        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Archive Faculty?', 
                    text: "Access to the portal will be suspended, but all historic records will be preserved.", 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#f59e0b', 
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Archive it',
                    borderRadius: '25px',
                }).then((r) => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });

        // Close modals on escape key
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal('addModal');
                closeModal('editModal');
            }
        });
    </script>
</body>
</html>