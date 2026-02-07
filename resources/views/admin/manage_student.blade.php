<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-600 antialiased selection:bg-indigo-100 selection:text-indigo-700">

    {{-- 1. SIDEBAR INCLUDE --}}
    @include('components.admin.sidebar')

    {{-- 2. MOBILE OVERLAY --}}
    <div id="mobileBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

    {{-- 3. MAIN CONTENT WRAPPER --}}
    {{-- Adjusted margins to fit your specific sidebar width (md:ml-20 for tablet icon view, lg:ml-64 for full view) --}}
    <div id="main-content" class="flex flex-col min-h-screen transition-all duration-300 md:ml-20 lg:ml-64">
        
        {{-- HEADER --}}
        <header class="bg-white sticky top-0 z-30 px-8 py-4 flex justify-between items-center border-b border-slate-200">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Student Management</h1>
                    <p class="hidden md:block text-xs font-bold text-slate-400 mt-0.5">Active School Year: <span class="text-indigo-600">{{ $activeSchoolYear ?? 'N/A' }}</span></p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.students.archived') }}" class="hidden md:flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-600 rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-slate-200 transition-colors">
                    <i class="fa-solid fa-box-archive"></i> Archives
                </a>
                <button onclick="openModal('addModal')" class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-plus"></i> Add Student
                </button>
            </div>
        </header>

        {{-- MAIN BODY --}}
        <main class="flex-1 p-8 flex gap-8 overflow-hidden">
            
            {{-- LEFT COLUMN: Sections Filter --}}
            <aside class="hidden xl:flex flex-col w-72 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden h-[calc(100vh-140px)] sticky top-24">
                <div class="p-5 border-b border-slate-50 bg-slate-50">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Class Sections</h3>
                </div>
                <div class="overflow-y-auto flex-1 p-3 space-y-1 custom-scrollbar">
                    <a href="{{ route('admin.students.index') }}" class="flex items-center gap-3 p-3 rounded-xl transition-all {{ !$activeSection ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ !$activeSection ? 'bg-indigo-200 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fa-solid fa-layer-group text-xs"></i>
                        </div>
                        <span class="text-sm font-bold">All Students</span>
                    </a>
                    @foreach($sectionsList as $grade => $sections)
                        <div class="mt-4 mb-2 px-3">
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Grade {{ $grade }}</span>
                        </div>
                        @foreach($sections as $sec)
                            @php $isActive = (isset($activeSection) && $activeSection->id == $sec->id); @endphp
                            <a href="{{ route('admin.students.index', ['section_id' => $sec->id]) }}" 
                               class="flex items-center justify-between p-3 rounded-xl transition-all group {{ $isActive ? 'bg-white border border-indigo-100 shadow-md shadow-indigo-50' : 'hover:bg-slate-50' }}">
                                <span class="text-sm font-bold {{ $isActive ? 'text-slate-800' : 'text-slate-500' }}">{{ $sec->name }}</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $isActive ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }}">{{ $sec->students_count }}</span>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </aside>

            {{-- RIGHT COLUMN: Student List --}}
            <div class="flex-1 flex flex-col min-w-0">
                <div class="mb-5 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ $activeSection ? $activeSection->name : 'All Active Students' }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            @if($activeSection)
                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 text-[10px] font-bold uppercase tracking-wider">Grade {{ $activeSection->grade_level }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 text-[10px] font-bold uppercase tracking-wider">{{ $students->total() }} Students</span>
                        </div>
                    </div>
                    @if($activeSection)
                        {{-- PROMOTE BUTTON (Triggers Modal) --}}
                        <button onclick="openModal('promoteModal')" class="flex items-center gap-2 px-5 py-2.5 bg-slate-800 text-white rounded-lg font-bold text-xs uppercase tracking-wider hover:bg-slate-700 hover:-translate-y-0.5 transition-all shadow-lg shadow-slate-200">
                             <i class="fa-solid {{ $activeSection->grade_level == 12 ? 'fa-graduation-cap' : 'fa-level-up-alt' }}"></i>
                             <span>{{ $activeSection->grade_level == 12 ? 'Graduate Class' : 'Promote Class' }}</span>
                        </button>
                    @endif
                </div>

                @if(session('success'))
                    <script>Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });</script>
                @endif
                @if($errors->any())
                    <div class="mb-4 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 font-bold text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex-1 overflow-y-auto custom-scrollbar pb-20 pr-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                        @forelse($students as $student)
                        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all group relative">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center font-black text-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm leading-tight">{{ $student->last_name }}, {{ $student->first_name }}</h4>
                                        <p class="text-xs text-slate-400 mt-0.5 font-medium">{{ $student->email }}</p>
                                    </div>
                                </div>
                                <button onclick="openEditModal({{ json_encode($student) }})" class="text-slate-300 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                <div class="bg-slate-50 rounded-lg p-2 border border-slate-100">
                                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[9px]">LRN</span>
                                    <span class="font-mono font-bold text-slate-600">{{ $student->lrn }}</span>
                                </div>
                                <div class="bg-slate-50 rounded-lg p-2 border border-slate-100">
                                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[9px]">Status</span>
                                    <span class="font-bold text-emerald-600 flex items-center gap-1"><div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> Enrolled</span>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-50 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-500">
                                    {{ $student->section->name ?? 'Unassigned' }}
                                </span>
                                <a href="{{ route('admin.students.show', $student->id) }}" class="text-[10px] font-bold text-indigo-600 hover:underline">View Record <i class="fa-solid fa-arrow-right ml-1"></i></a>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full py-20 text-center opacity-50"><p class="font-bold text-slate-400">No students found.</p></div>
                        @endforelse
                    </div>
                    <div class="mt-6">{{ $students->links() }}</div>
                </div>
            </div>
        </main>
    </div>

    {{-- ================= 1. ADD MODAL ================= --}}
    <div id="addModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal('addModal')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl pointer-events-auto flex flex-col max-h-[90vh]">
                <div class="bg-indigo-600 p-6 rounded-t-2xl flex justify-between items-center shrink-0">
                    <h2 class="text-xl font-bold text-white">Add New Student</h2>
                    <button onclick="closeModal('addModal')" class="text-white/70 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form action="{{ route('admin.students.store') }}" method="POST" class="p-6 overflow-y-auto custom-scrollbar">
                    @csrf
                    <input type="hidden" name="school_year_id" value="{{ $activeSchoolYear ? $schoolYears->firstWhere('school_year', $activeSchoolYear)->id : '' }}">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">First Name</label><input type="text" name="first_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold"></div>
                            <div><label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Last Name</label><input type="text" name="last_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold"></div>
                        </div>
                        <div><label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">LRN</label><input type="number" name="lrn" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold"></div>
                        <div><label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Email</label><input type="email" name="email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Section</label>
                                <select name="section_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold">
                                    <option value="" disabled selected>Select...</option>
                                    @foreach($allSections as $sec) <option value="{{ $sec->id }}">G{{ $sec->grade_level }} - {{ $sec->name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Type</label>
                                <select name="enrollment_type" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold">
                                    <option value="Regular">Regular</option><option value="Transferee">Transferee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3"><button type="button" onclick="closeModal('addModal')" class="px-5 py-2 text-slate-500 font-bold text-sm">Cancel</button><button type="submit" class="px-6 py-2 bg-indigo-600 text-white font-bold text-sm rounded-lg hover:bg-indigo-700">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= 2. EDIT MODAL ================= --}}
    <div id="editModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal('editModal')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl pointer-events-auto flex flex-col max-h-[90vh]">
                <div class="bg-slate-800 p-6 rounded-t-2xl flex justify-between items-center shrink-0">
                    <h2 class="text-xl font-bold text-white">Edit Student</h2>
                    <button onclick="closeModal('editModal')" class="text-white/70 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <form id="editForm" method="POST" class="p-6 overflow-y-auto custom-scrollbar">
                    @csrf @method('PUT')
                    <input type="hidden" name="school_year_id" value="{{ $activeSchoolYear ? $schoolYears->firstWhere('school_year', $activeSchoolYear)->id : '' }}">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">First Name</label><input type="text" id="edit_first_name" name="first_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold"></div>
                            <div><label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Last Name</label><input type="text" id="edit_last_name" name="last_name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold"></div>
                        </div>
                        <div><label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Email</label><input type="email" id="edit_email" name="email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Section</label>
                                <select id="edit_section_id" name="section_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold">
                                    @foreach($allSections as $sec) <option value="{{ $sec->id }}">G{{ $sec->grade_level }} - {{ $sec->name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Type</label>
                                <select id="edit_enrollment_type" name="enrollment_type" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold">
                                    <option value="Regular">Regular</option><option value="Transferee">Transferee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3"><button type="button" onclick="closeModal('editModal')" class="px-5 py-2 text-slate-500 font-bold text-sm">Cancel</button><button type="submit" class="px-6 py-2 bg-slate-800 text-white font-bold text-sm rounded-lg hover:bg-slate-700">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= 3. PROMOTE MODAL (THE FIX) ================= --}}
    @if($activeSection)
    <div id="promoteModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeModal('promoteModal')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl pointer-events-auto flex flex-col max-h-[90vh]">
                {{-- Header changes color based on Grade 12 (Graduation) or Regular Promotion --}}
                <div class="{{ $activeSection->grade_level == 12 ? 'bg-amber-500' : 'bg-emerald-600' }} p-6 rounded-t-2xl flex justify-between items-center shrink-0">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $activeSection->grade_level == 12 ? 'Graduate Students' : 'Promote Class' }}</h2>
                        <p class="text-white/80 text-xs font-bold mt-1">From: {{ $activeSection->name }}</p>
                    </div>
                    <button onclick="closeModal('promoteModal')" class="text-white/70 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                
                <form action="{{ route('admin.students.promote') }}" method="POST" class="p-6 overflow-y-auto custom-scrollbar">
                    @csrf
                    
                    {{-- Student Checklist --}}
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-[11px] font-bold text-slate-500 uppercase">Select Students</label>
                            <label class="flex items-center gap-2 text-xs font-bold text-slate-600 cursor-pointer">
                                <input type="checkbox" onchange="toggleAll(this)" checked class="rounded text-indigo-600 focus:ring-indigo-500"> Select All
                            </label>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 max-h-40 overflow-y-auto custom-scrollbar space-y-1">
                            @foreach($students as $s)
                                <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg transition-colors cursor-pointer">
                                    <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" checked class="student-cb rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-bold text-slate-700">{{ $s->last_name }}, {{ $s->first_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Next School Year --}}
                    <div class="mb-4">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">To School Year</label>
                        <select name="to_school_year_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold focus:outline-none focus:border-indigo-500">
                            @foreach($schoolYears as $sy) <option value="{{ $sy->id }}">{{ $sy->school_year }}</option> @endforeach
                        </select>
                    </div>

                    {{-- Destination Section (Hidden if Graduating) --}}
                    @if($activeSection->grade_level != 12)
                        <div class="mb-4">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Move to Section (Grade {{ $activeSection->grade_level + 1 }})</label>
                            <select name="to_section_id" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold focus:outline-none focus:border-indigo-500">
                                <option value="" disabled selected>Select Destination...</option>
                                @foreach($allSections->where('grade_level', $activeSection->grade_level + 1) as $sec)
                                    <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs font-bold mb-4">
                            <i class="fa-solid fa-graduation-cap mr-1"></i> These students will be marked as Alumni and removed from active lists.
                        </div>
                    @endif

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="closeModal('promoteModal')" class="px-5 py-2 text-slate-500 font-bold text-sm">Cancel</button>
                        <button type="submit" class="{{ $activeSection->grade_level == 12 ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-600 hover:bg-emerald-700' }} px-6 py-2 text-white font-bold text-sm rounded-lg shadow-md">
                            Confirm {{ $activeSection->grade_level == 12 ? 'Graduation' : 'Promotion' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar'); // Must match ID in your include component
            // If user's sidebar has a different ID, update this. Assuming 'sidebar' based on common practice.
            if(sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        }

        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        function openEditModal(student) {
            document.getElementById('editForm').action = `/admin/students/${student.id}`;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_section_id').value = student.section_id;
            document.getElementById('edit_enrollment_type').value = student.enrollment_type;
            openModal('editModal');
        }

        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('.student-cb');
            checkboxes.forEach(cb => cb.checked = source.checked);
        }
    </script>
</body>
</html>