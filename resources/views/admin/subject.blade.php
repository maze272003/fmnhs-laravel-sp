<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Management | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .modal-animate { transition: all 0.3s ease-in-out; }
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
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
                        <i class="fa-solid fa-book-open text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Curriculum</h2>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Master Subject List
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">
            
            @if(session('success'))
                <script>
                    Swal.fire({icon: 'success', title: 'Action Successful', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false, borderRadius: '20px'});
                </script>
            @endif

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Subjects Management</h1>
                    <p class="text-slate-500 font-medium">Define core, applied, and specialized subjects for the institution.</p>
                </div>
                
                <div class="flex items-center gap-4 w-full lg:w-auto">
                    <div class="bg-white px-6 py-3 rounded-2xl border border-slate-100 shadow-sm hidden md:block">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Courses</p>
                        <p class="text-xl font-black text-indigo-600 leading-none mt-1">{{ $subjects->total() }}</p>
                    </div>
                    <button onclick="openModal('addModal')" class="flex-1 lg:flex-none bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i class="fa-solid fa-plus-circle text-xs"></i> 
                        <span>Add Subject</span>
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">Code</th>
                                <th class="px-6 py-5">Descriptive Title</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Description</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($subjects as $subject)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 font-mono text-[11px] font-black rounded-lg border border-slate-200 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all">
                                        {{ $subject->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="font-bold text-slate-800 tracking-tight group-hover:text-indigo-600 transition-colors leading-tight mb-1">{{ $subject->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Academic Course</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-xs text-slate-500 leading-relaxed max-w-xs line-clamp-2">
                                        {{ $subject->description ?: 'No detailed description available for this course module.' }}
                                    </p>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center items-center gap-2">
                                        <button onclick="editSubject({{ $subject }})" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                        <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" class="inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all shadow-sm delete-btn">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $subjects->links() }}
                </div>
            </div>
        </main>
    </div>

    <div id="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl modal-animate border border-white">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight leading-none">New Subject</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Add course to curriculum</p>
                </div>
                <button onclick="closeModal('addModal')" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Subject Code</label>
                    <input type="text" name="code" placeholder="e.g. STEM-PC-11" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Subject Title</label>
                    <input type="text" name="name" placeholder="e.g. Pre-Calculus" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Module Description</label>
                    <textarea name="description" rows="3" placeholder="Overview of topics and objectives..." 
                              class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm"></textarea>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                        Create Subject
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl modal-animate border border-white">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Information</h2>
                <button onclick="closeModal('editModal')" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="editForm" method="POST" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Subject Code</label>
                    <input type="text" id="edit_code" name="code" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Subject Title</label>
                    <input type="text" id="edit_name" name="name" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Module Description</label>
                    <textarea id="edit_description" name="description" rows="3" 
                              class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm"></textarea>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                        Update Subject
                    </button>
                </div>
            </form>
        </div>
    </div>

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

        window.onclick = function(event) {
            if (event.target.classList.contains('bg-slate-900/60')) {
                closeModal('addModal');
                closeModal('editModal');
            }
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Delete Subject?', 
                    text: "Warning: All student grades and class schedules linked to this subject will be permanently removed.", 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#f43f5e', 
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Delete Subject',
                    borderRadius: '25px',
                }).then((r) => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });
    </script>
</body>
</html>