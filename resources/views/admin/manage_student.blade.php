<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | Admin</title>
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
                        <i class="fa-solid fa-user-graduate text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Students</h2>
                </div>
            </div>
            <div class="hidden sm:block">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Student Information System
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">
            
            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Action Successful', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false, borderRadius: '20px' });
                </script>
            @endif

            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Student Management</h1>
                    <p class="text-slate-500 font-medium">Register, update, and manage student official records.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 w-full xl:w-auto">
                    <form action="{{ route('admin.students.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search LRN, Name..." 
                                   class="w-full sm:w-72 pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-semibold text-sm shadow-sm">
                            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        </div>
                        @if(request('search'))
                            <a href="{{ route('admin.students.index') }}" class="text-rose-500 hover:text-rose-600 font-bold text-xs">Clear</a>
                        @endif
                    </form>

                    <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fa-solid fa-user-plus"></i> Add Student
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">LRN / ID</th>
                                <th class="px-6 py-5">Student Information</th>
                                <th class="px-6 py-5">Email Address</th>
                                <th class="px-6 py-5">Level & Section</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($students as $student)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 font-mono text-[11px] font-black rounded-lg border border-slate-200">
                                        {{ $student->lrn }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-xs shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 tracking-tight group-hover:text-indigo-600 transition-colors">{{ $student->last_name }}, {{ $student->first_name }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">Registered Student</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-sm font-medium text-slate-500 italic">{{ $student->email }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-indigo-600 text-white uppercase tracking-tighter">Grade {{ $student->grade_level }}</span>
                                        <span class="text-xs font-bold text-slate-700">{{ $student->section }}</span>
                                        @if($student->strand)
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black border border-indigo-200 text-indigo-500 uppercase tracking-widest bg-indigo-50">
                                                {{ $student->strand }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center items-center gap-2">
                                        <button onclick="editStudent({{ $student }})" class="w-9 h-9 flex items-center justify-center rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all shadow-sm">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>
                                        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="delete-form inline">
                                            @csrf @method('DELETE')
                                            <button type="button" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm delete-btn">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-solid fa-users-viewfinder text-4xl text-slate-200 mb-4"></i>
                                        <p class="text-slate-400 font-bold">No student records found.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $students->appends(request()->query())->links() }} 
                </div>
            </div>

        </main>
    </div>

    <div id="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl modal-animate border border-white max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight leading-none">New Student</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Password defaults to LRN</p>
                </div>
                <button onclick="closeModal('addModal')" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            
            <form action="{{ route('admin.students.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">LRN (Learner Reference No.)</label>
                    <input type="text" name="lrn" value="{{ old('lrn') }}" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required 
                               class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required 
                               class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Grade (7-12)</label>
                        <input type="number" id="add_grade_level" name="grade_level" value="{{ old('grade_level') }}" min="7" max="12" required 
                               class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm"
                               oninput="toggleStrand('add_grade_level', 'add_strand_container')">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Section</label>
                        <input type="text" name="section" value="{{ old('section') }}" required 
                               class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                    </div>
                </div>

                <div id="add_strand_container" class="hidden animate-fade-in">
                    <label class="block text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1.5 ml-1">SHS Strand Selection</label>
                    <select name="strand" class="w-full p-3.5 bg-indigo-50 border border-indigo-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm cursor-pointer">
                        <option value="">-- No Strand --</option>
                        <option value="STEM">STEM</option>
                        <option value="ABM">ABM</option>
                        <option value="HUMSS">HUMSS</option>
                        <option value="GAS">GAS</option>
                        <option value="TVL">TVL</option>
                    </select>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                        Register Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl modal-animate border border-white max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Student</h2>
                <button onclick="closeModal('editModal')" class="text-slate-300 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form id="editForm" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-4">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Permanent LRN</label>
                    <input type="text" id="edit_lrn" disabled class="bg-transparent border-none p-0 text-slate-800 font-black focus:ring-0 cursor-not-allowed">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="edit_first_name" name="first_name" required placeholder="First Name" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 outline-none">
                    <input type="text" id="edit_last_name" name="last_name" required placeholder="Last Name" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 outline-none">
                </div>
                <input type="email" id="edit_email" name="email" required placeholder="Email Address" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 outline-none">
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="number" id="edit_grade_level" name="grade_level" min="7" max="12" required class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 outline-none" oninput="toggleStrand('edit_grade_level', 'edit_strand_container')">
                    <input type="text" id="edit_section" name="section" required placeholder="Section" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold focus:ring-4 focus:ring-blue-500/10 outline-none">
                </div>

                <div id="edit_strand_container" class="hidden">
                    <select id="edit_strand" name="strand" class="w-full p-3.5 bg-blue-50 border border-blue-100 rounded-2xl font-bold text-sm cursor-pointer outline-none">
                        <option value="">-- No Strand --</option>
                        <option value="STEM">STEM</option>
                        <option value="ABM">ABM</option>
                        <option value="HUMSS">HUMSS</option>
                        <option value="GAS">GAS</option>
                        <option value="TVL">TVL</option>
                    </select>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <label class="block text-[10px] font-black text-rose-500 uppercase tracking-widest mb-1.5">Reset Password</label>
                    <input type="password" name="new_password" placeholder="Leave blank to keep current" class="w-full p-3.5 bg-rose-50/30 border border-rose-100 rounded-2xl text-sm font-bold outline-none focus:ring-4 focus:ring-rose-500/10">
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                        Update Student Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function toggleStrand(gradeId, containerId) {
            const grade = parseInt(document.getElementById(gradeId).value);
            const container = document.getElementById(containerId);
            if (grade === 11 || grade === 12) container.classList.remove('hidden');
            else {
                container.classList.add('hidden');
                const sel = container.querySelector('select'); if(sel) sel.value = "";
            }
        }

        function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }

        function editStudent(student) {
            document.getElementById('edit_lrn').value = student.lrn;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_grade_level').value = student.grade_level;
            document.getElementById('edit_section').value = student.section;
            document.getElementById('edit_strand').value = student.strand || "";
            toggleStrand('edit_grade_level', 'edit_strand_container');
            document.getElementById('editForm').action = `/admin/students/${student.id}`;
            openModal('editModal');
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Delete Student?', text: "This action cannot be undone!", icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#cbd5e1', confirmButtonText: 'Yes, Delete', borderRadius: '25px'
                }).then((r) => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });
    </script>
</body>
</html>