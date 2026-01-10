<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum | {{ ($viewArchived ?? false) ? 'Archive' : 'Management' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .modal-animate { transition: all 0.3s ease-in-out; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-200/60">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 {{ $viewArchived ? 'bg-amber-100 text-amber-600' : 'bg-indigo-100 text-indigo-600' }} rounded-2xl flex items-center justify-center shadow-sm">
                        <i class="fa-solid {{ $viewArchived ? 'fa-box-archive' : 'fa-book-open' }} text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight leading-none">Curriculum</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                            {{ $viewArchived ? 'Archived Records' : 'Active Subject List' }}
                        </p>
                    </div>
                </div>
            </div>
            @include('components.admin.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            @if(session('success'))
                <script>
                    Swal.fire({icon: 'success', title: 'Success!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false, borderRadius: '24px'});
                </script>
            @endif

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-10">
                <div class="w-full lg:w-auto">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Subjects Management</h1>
                    <p class="text-slate-500 font-medium">Manage institutional course offerings and academic modules.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <form action="{{ route('admin.subjects.index') }}" method="GET" class="flex-1 lg:min-w-[300px] relative group">
                        <input type="hidden" name="archived" value="{{ $viewArchived ? 1 : 0 }}">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Search code or title..." 
                            class="w-full pl-12 pr-10 py-4 bg-white border border-slate-200 rounded-[1.5rem] focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-hover:text-indigo-500 transition-colors">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                    </form>

                    @if($viewArchived)
                        <a href="{{ route('admin.subjects.index') }}" class="bg-slate-900 text-white px-6 py-4 rounded-[1.5rem] font-black text-[11px] uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-slate-200 transition-all active:scale-95">
                            <i class="fa-solid fa-arrow-left"></i> Active List
                        </a>
                    @else
                        <a href="{{ route('admin.subjects.index', ['archived' => 1]) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-4 rounded-[1.5rem] font-black text-[11px] uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-amber-100 transition-all active:scale-95">
                            <i class="fa-solid fa-box-archive"></i> Archive Folder
                        </a>
                        <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-[1.5rem] font-black shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-3">
                            <i class="fa-solid fa-plus-circle"></i> Add Subject
                        </button>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/50 border border-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-100">
                                <th class="px-10 py-6">Code</th>
                                <th class="px-8 py-6">Course Description</th>
                                <th class="px-8 py-6">Status</th>
                                <th class="px-10 py-6 text-center">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($subjects as $subject)
                            <tr class="hover:bg-indigo-50/20 transition-all duration-300 group">
                                <td class="px-10 py-6">
                                    <span class="px-4 py-2 bg-slate-100 text-slate-600 font-mono text-[11px] font-black rounded-xl border border-slate-200 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                        {{ $subject->code }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="font-black text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors text-base">{{ $subject->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1 truncate max-w-[300px]">
                                        {{ $subject->description ?: 'No detailed summary provided.' }}
                                    </p>
                                </td>
                                <td class="px-8 py-6">
                                    @if($subject->trashed())
                                        <span class="inline-flex items-center gap-2 bg-rose-50 text-rose-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100 shadow-sm">
                                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span> Archived
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100 shadow-sm">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
                                        </span>
                                    @endif
                                </td>
                                <td class="px-10 py-6">
                                    <div class="flex justify-center items-center gap-3">
                                        @if($subject->trashed())
                                            <form action="{{ route('admin.subjects.restore', $subject->id) }}" method="POST" class="restore-form">
                                                @csrf
                                                <button type="button" class="restore-btn w-11 h-11 flex items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm shadow-emerald-50">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button onclick="editSubject({{ $subject }})" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-blue-50 text-blue-500 hover:bg-blue-600 hover:text-white transition-all shadow-sm shadow-blue-50">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            
                                            <form action="{{ route('admin.subjects.archive', $subject->id) }}" method="POST" class="archive-form">
                                                @csrf
                                                <button type="button" class="archive-btn w-11 h-11 flex items-center justify-center rounded-2xl bg-amber-50 text-amber-500 hover:bg-amber-600 hover:text-white transition-all shadow-sm shadow-amber-50">
                                                    <i class="fa-solid fa-box-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-10 py-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                                            <i class="fa-solid fa-folder-open text-4xl text-slate-200"></i>
                                        </div>
                                        <h3 class="text-xl font-black text-slate-900 mb-2 uppercase tracking-widest">No Records Found</h3>
                                        <p class="text-slate-400 font-medium max-w-xs mx-auto">Either there are no subjects in this view or your search filter didn't return any matches.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-8 bg-slate-50/40 border-t border-slate-100">
                    {{ $subjects->links() }}
                </div>
            </div>
        </main>
    </div>

    @if(!$viewArchived)
        <div id="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
            <div class="bg-white rounded-[3rem] w-full max-w-md p-10 shadow-2xl modal-animate border border-white">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">New Subject</h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Add course to curriculum</p>
                    </div>
                    <button onclick="closeModal('addModal')" class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Subject Code</label>
                        <input type="text" name="code" placeholder="e.g. MATH-101" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Descriptive Title</label>
                        <input type="text" name="name" placeholder="e.g. Advanced Calculus" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Module Description</label>
                        <textarea name="description" rows="3" placeholder="Brief overview of course objectives..." 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-sm"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 uppercase text-xs tracking-[0.2em]">
                            Create Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
            <div class="bg-white rounded-[3rem] w-full max-w-md p-10 shadow-2xl modal-animate border border-white">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">Edit Subject</h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Update curriculum data</p>
                    </div>
                    <button onclick="closeModal('editModal')" class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="editForm" method="POST" class="space-y-6">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Subject Code</label>
                        <input type="text" id="edit_code" name="code" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Descriptive Title</label>
                        <input type="text" id="edit_name" name="name" required 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Module Description</label>
                        <textarea id="edit_description" name="description" rows="3" 
                            class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-medium text-sm"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 uppercase text-xs tracking-[0.2em]">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function openModal(id) { 
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeModal(id) { 
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function editSubject(subject) {
            document.getElementById('edit_code').value = subject.code;
            document.getElementById('edit_name').value = subject.name;
            document.getElementById('edit_description').value = subject.description;
            document.getElementById('editForm').action = `/admin/subjects/${subject.id}`;
            openModal('editModal');
        }

        // Close on backdrop click
        window.onclick = function(event) {
            if (event.target.classList.contains('bg-slate-900/60')) {
                closeModal('addModal');
                closeModal('editModal');
            }
        }

        // SweetAlert for Archive
        document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Move to Archive?', 
                    text: "The subject will be hidden from students and teachers, but records will be kept safe.", 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#f59e0b', 
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Archive it',
                    borderRadius: '25px',
                }).then((r) => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });

        // SweetAlert for Restore
        document.querySelectorAll('.restore-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Restore Subject?', 
                    text: "This will make the subject active and visible again in the curriculum.", 
                    icon: 'question', 
                    showCancelButton: true, 
                    confirmButtonColor: '#10b981', 
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Restore it',
                    borderRadius: '25px',
                }).then((r) => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });
    </script>
</body>
</html>