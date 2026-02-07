<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased h-screen flex overflow-hidden">

    @include('components.admin.sidebar')

    <div class="flex-1 flex flex-col h-full transition-all duration-300 md:ml-64">
        
        {{-- Header --}}
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center shrink-0">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Student Management</h1>
            <div class="flex gap-3">
                <a href="{{ route('admin.students.archived') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-slate-200">
                    <i class="fa-solid fa-box-archive mr-1"></i> Alumni / Archive
                </a>
                <button onclick="openModal('addModal')" class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-lg shadow-indigo-200">
                    <i class="fa-solid fa-plus mr-1"></i> Add New
                </button>
            </div>
        </header>

        {{-- Main Content: Split View --}}
        <div class="flex flex-1 overflow-hidden">
            
            {{-- LEFT SIDEBAR: Section List Grouped by Grade --}}
            <aside class="w-72 bg-white border-r border-slate-200 overflow-y-auto p-4 hidden lg:block">
                <div class="mb-4">
                    <a href="{{ route('admin.students.index') }}" class="block p-3 rounded-xl {{ !$activeSection ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'text-slate-600 hover:bg-slate-50' }} font-bold text-sm">
                        <i class="fa-solid fa-users mr-2"></i> All Students
                    </a>
                </div>

                @foreach($sectionsList as $grade => $sections)
                    <div class="mb-4">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-2">Grade {{ $grade }}</h3>
                        <div class="space-y-1">
                            @foreach($sections as $sec)
                                <a href="{{ route('admin.students.index', ['section_id' => $sec->id]) }}" 
                                   class="flex justify-between items-center p-3 rounded-xl text-sm font-bold transition-all {{ (isset($activeSection) && $activeSection->id == $sec->id) ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span>{{ $sec->name }}</span>
                                    <span class="text-[10px] py-0.5 px-2 rounded-md {{ (isset($activeSection) && $activeSection->id == $sec->id) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $sec->students_count }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </aside>

            {{-- RIGHT CONTENT: Student Table --}}
            <main class="flex-1 overflow-y-auto p-6 bg-slate-50">
                
                @if(session('success'))
                    <script>Swal.fire({ icon: 'success', title: 'Success', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });</script>
                @endif

                {{-- Toolbar --}}
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            {{ $activeSection ? "Grade {$activeSection->grade_level} - {$activeSection->name}" : 'All Active Students' }}
                        </h2>
                        <p class="text-xs text-slate-500 font-bold">SY {{ $activeSchoolYear ?? 'N/A' }}</p>
                    </div>
                    
                    {{-- Bulk Actions (Only show if a section is selected) --}}
                    @if($activeSection)
                        <button onclick="openPromoteModal('{{ $activeSection->grade_level }}', '{{ $activeSection->id }}')" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl font-bold text-xs uppercase tracking-widest shadow-lg shadow-emerald-200 transition-all">
                            @if($activeSection->grade_level == 12)
                                <i class="fa-solid fa-graduation-cap mr-1"></i> Graduate Class
                            @else
                                <i class="fa-solid fa-level-up-alt mr-1"></i> Promote Class
                            @endif
                        </button>
                    @endif
                </div>

                {{-- Table --}}
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-widest">
                            <tr>
                                <th class="px-6 py-4">LRN</th>
                                <th class="px-6 py-4">Student Name</th>
                                <th class="px-6 py-4">Section</th>
                                <th class="px-6 py-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students as $student)
                            <tr class="hover:bg-indigo-50/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $student->lrn }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-800">{{ $student->last_name }}, {{ $student->first_name }}</div>
                                    <div class="text-xs text-slate-400">{{ $student->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($student->section)
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-xs font-bold text-slate-600">
                                            G{{ $student->section->grade_level }} - {{ $student->section->name }}
                                        </span>
                                    @else
                                        <span class="text-rose-500 font-bold text-xs italic">No Section</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    {{-- View Immutable Record --}}
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs mr-3">
                                        <i class="fa-solid fa-file-lines"></i> View Record
                                    </a>
                                    <button onclick="editStudent({{ $student }})" class="text-blue-500 hover:text-blue-700 font-bold text-xs">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-10 text-center text-slate-400 font-bold">No students found in this view.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="p-4 border-t border-slate-100">{{ $students->links() }}</div>
                </div>
            </main>
        </div>
    </div>

    {{-- PROMOTE / GRADUATE MODAL --}}
    <div id="promoteModal" class="fixed inset-0 bg-slate-900/50 hidden z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white w-full max-w-lg p-8 rounded-3xl shadow-2xl">
            <h2 id="promoteTitle" class="text-2xl font-black text-slate-800 mb-2">Promote Students</h2>
            <p id="promoteDesc" class="text-sm text-slate-500 mb-6">Select students to move to the next grade level.</p>

            <form action="{{ route('admin.students.promote') }}" method="POST">
                @csrf
                {{-- Hidden input for selecting all in this section --}}
                <div class="mb-4 bg-slate-50 p-3 rounded-xl border border-slate-200 max-h-40 overflow-y-auto">
                    <label class="flex items-center gap-2 font-bold text-sm text-slate-700 mb-2 border-b pb-2">
                        <input type="checkbox" onchange="toggleAll(this)" checked class="rounded text-indigo-600"> Select All
                    </label>
                    <div class="space-y-1">
                        @foreach($students as $s)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" checked class="student-checkbox rounded text-indigo-600">
                                <span class="text-xs">{{ $s->last_name }}, {{ $s->first_name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Destination Logic --}}
                <div id="destinationWrapper">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Target Section</label>
                    <select name="to_section_id" class="w-full p-3 bg-slate-100 rounded-xl font-bold text-sm outline-none border border-slate-200 focus:border-indigo-500">
                        @foreach($allSections as $sec)
                            <option value="{{ $sec->id }}">Grade {{ $sec->grade_level }} - {{ $sec->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Graduation Message (Hidden by default) --}}
                <div id="graduationMessage" class="hidden p-4 bg-amber-50 border border-amber-200 rounded-xl mb-4">
                    <div class="flex gap-3">
                        <div class="text-amber-500"><i class="fa-solid fa-graduation-cap text-xl"></i></div>
                        <div>
                            <h4 class="font-bold text-amber-800 text-sm">Graduation Confirmation</h4>
                            <p class="text-xs text-amber-700 mt-1">
                                These students are in <strong>Grade 12</strong>. Proceeding will mark them as <strong>Alumni</strong>, remove them from active class lists, and lock their records.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1">New School Year</label>
                    <select name="to_school_year_id" class="w-full p-3 bg-slate-100 rounded-xl font-bold text-sm outline-none border border-slate-200">
                        @foreach($schoolYears as $sy)
                            <option value="{{ $sy->id }}">{{ $sy->school_year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('promoteModal').classList.add('hidden')" class="px-5 py-3 text-slate-500 font-bold text-sm hover:bg-slate-100 rounded-xl">Cancel</button>
                    <button type="submit" id="promoteBtn" class="px-6 py-3 bg-emerald-500 text-white font-bold text-sm rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-200">
                        Confirm Promotion
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add/Edit Modals here (Hidden for brevity, same as previous) --}}

    <script>
        function openPromoteModal(currentGrade, sectionId) {
            const modal = document.getElementById('promoteModal');
            const title = document.getElementById('promoteTitle');
            const destWrapper = document.getElementById('destinationWrapper');
            const gradMsg = document.getElementById('graduationMessage');
            const btn = document.getElementById('promoteBtn');

            modal.classList.remove('hidden');

            if (currentGrade == 12) {
                // GRADUATION MODE
                title.innerText = "Confirm Graduation";
                title.classList.add('text-amber-600');
                destWrapper.classList.add('hidden'); // Hide section selector
                gradMsg.classList.remove('hidden');  // Show warning
                btn.innerText = "Graduate Students";
                btn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600', 'shadow-emerald-200');
                btn.classList.add('bg-amber-500', 'hover:bg-amber-600', 'shadow-amber-200');
            } else {
                // NORMAL PROMOTION MODE
                title.innerText = "Promote Students";
                title.classList.remove('text-amber-600');
                destWrapper.classList.remove('hidden');
                gradMsg.classList.add('hidden');
                btn.innerText = "Confirm Promotion";
                btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600', 'shadow-emerald-200');
                btn.classList.remove('bg-amber-500', 'hover:bg-amber-600', 'shadow-amber-200');
            }
        }

        function toggleAll(source) {
            checkboxes = document.querySelectorAll('.student-checkbox');
            for(var i=0, n=checkboxes.length;i<n;i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</body>
</html>
