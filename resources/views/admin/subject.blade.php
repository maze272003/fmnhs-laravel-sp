<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects | Admin</title>
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
                    Academic Year 2024-2025
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">
            
            @if(session('success'))
                <script>
                    Swal.fire({icon: 'success', title: 'Action Successful', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false, borderRadius: '20px'});
                </script>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Subjects Management</h1>
                    <p class="text-slate-500 font-medium">Define and organize the courses offered in the institution.</p>
                </div>
                <button onclick="openModal('addModal')" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i> Add New Subject
                </button>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">Subject Code</th>
                                <th class="px-6 py-5">Title</th>
                                <th class="px-6 py-5">Description</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($subjects as $subject)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 font-mono text-[11px] font-black rounded-lg border border-indigo-100">
                                        {{ $subject->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 font-bold text-slate-700 tracking-tight group-hover:text-indigo-600 transition-colors">
                                    {{ $subject->name }}
                                </td>
                                <td class="px-6 py-5 text-xs text-slate-400 leading-relaxed max-w-xs truncate">
                                    {{ $subject->description ?: 'No description provided' }}
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center items-center gap-2">
                                        <button onclick="editSubject({{ $subject }})" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all shadow-sm">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                        <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" class="inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm delete-btn">
                                                <i class="fa-solid fa-trash text-xs"></i>
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
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">New Subject</h2>
                <button onclick="closeModal('addModal')" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Subject Code</label>
                    <input type="text" name="code" placeholder="e.g. MATH-101" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Descriptive Title</label>
                    <input type="text" name="name" placeholder="e.g. Calculus I" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">General Description</label>
                    <textarea name="description" rows="3" placeholder="Brief overview of the course content..." 
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
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Subject</h2>
                <button onclick="closeModal('editModal')" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editForm" method="POST" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Subject Code</label>
                    <input type="text" id="edit_code" name="code" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Descriptive Title</label>
                    <input type="text" id="edit_name" name="name" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">General Description</label>
                    <textarea id="edit_description" name="description" rows="3" 
                              class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all font-bold text-sm"></textarea>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95">
                        Update Information
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

        // Close modal when clicking outside
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
                    text: "Warning: All student grades and class records under this subject will be permanently removed.", 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#ef4444', 
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Yes, Delete Record',
                    borderRadius: '25px',
                    customClass: {
                        confirmButton: 'font-bold py-3 px-6 rounded-2xl',
                        cancelButton: 'font-bold py-3 px-6 rounded-2xl'
                    }
                }).then((r) => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });
    </script>
</body>
</html>