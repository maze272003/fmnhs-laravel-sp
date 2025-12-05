<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-indigo-600">Student Management</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Admin</span></div>
        </header>

        <main class="flex-1 p-6">
            
            @if(session('success'))
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: "{{ session('success') }}",
                        timer: 2000,
                        showConfirmButton: false
                    });
                </script>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const urlParams = new URLSearchParams(window.location.search);
                        // Re-open the correct modal based on context
                        if (urlParams.get('edit_id')) {
                           openModal('editModal');
                        } else {
                           openModal('addModal');
                        }
                    });
                </script>
            @endif

            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold">Student List</h1>
                
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <form action="{{ route('admin.students.index') }}" method="GET" class="flex items-center gap-2">
    <div class="relative w-full md:w-auto">
        <input type="text" 
               name="search" 
               value="{{ request('search') }}" 
               placeholder="Search LRN, Name, or Email..." 
               class="pl-3 pr-10 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-slate-800 dark:border-slate-600 w-full md:w-72">
        
        <button type="submit" class="absolute right-2 top-2 text-gray-400 hover:text-indigo-600">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </div>
    @if(request('search'))
        <a href="{{ route('admin.students.index') }}" class="text-sm text-red-500 hover:underline whitespace-nowrap">Clear</a>
    @endif
</form>

                    <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition whitespace-nowrap">
                        <i class="fa-solid fa-plus"></i> Add Student
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-slate-700">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4">LRN</th>
                                <th class="px-6 py-4">Full Name</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">Grade & Section</th>
                                <th class="px-6 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                            @forelse($students as $student)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-6 py-4 font-medium">{{ $student->lrn }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                            {{ substr($student->first_name, 0, 1) }}
                                        </div>
                                        {{ $student->last_name }}, {{ $student->first_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $student->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700">G{{ $student->grade_level }}</span>
                                    <span class="text-sm ml-1 font-semibold">{{ $student->section }}</span>
                                    @if($student->strand)
                                        <span class="ml-2 px-2 py-0.5 rounded text-[10px] uppercase border border-gray-300 dark:border-gray-600 text-gray-500">
                                            {{ $student->strand }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center flex justify-center gap-2">
                                    <button onclick="editStudent({{ $student }})" class="text-blue-500 hover:text-blue-700 mx-1">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="delete-form inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="text-red-500 hover:text-red-700 delete-btn">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No students found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700">
                    {{ $students->appends(request()->query())->links() }} 
                </div>
            </div>

        </main>
    </div>

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg w-full max-w-md p-6 shadow-2xl relative">
            <h2 class="text-xl font-bold mb-4 text-indigo-600">Add New Student</h2>
            <p class="text-xs text-gray-500 mb-4">Password will be automatically set to the LRN.</p>
            
            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">LRN (Learner ID)</label>
                        <input type="text" name="lrn" value="{{ old('lrn') }}" required 
                               class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required 
                               class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Grade Level (7-12)</label>
                            <input type="number" id="add_grade_level" name="grade_level" value="{{ old('grade_level') }}" min="7" max="12" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600"
                                   oninput="toggleStrand('add_grade_level', 'add_strand_container')">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Section</label>
                            <input type="text" name="section" value="{{ old('section') }}" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                    </div>

                    <div id="add_strand_container" class="hidden">
                        <label class="block text-sm font-medium text-indigo-600">Strand (SHS Only)</label>
                        <select name="strand" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                            <option value="">Select Strand</option>
                            <option value="STEM">STEM</option>
                            <option value="ABM">ABM</option>
                            <option value="HUMSS">HUMSS</option>
                            <option value="GAS">GAS</option>
                            <option value="TVL">TVL</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg w-full max-w-md p-6 shadow-2xl relative">
            <h2 class="text-xl font-bold mb-4 text-blue-600">Edit Student</h2>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="mb-3">
                        <label class="block text-sm font-medium">LRN (Read Only)</label>
                        <input type="text" id="edit_lrn" disabled 
                               class="w-full p-2 bg-gray-100 border rounded cursor-not-allowed dark:bg-slate-700">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Email</label>
                        <input type="email" id="edit_email" name="email" required 
                               class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Grade Level</label>
                            <input type="number" id="edit_grade_level" name="grade_level" min="7" max="12" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600"
                                   oninput="toggleStrand('edit_grade_level', 'edit_strand_container')">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Section</label>
                            <input type="text" id="edit_section" name="section" required 
                                   class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                    </div>

                    <div id="edit_strand_container" class="hidden">
                        <label class="block text-sm font-medium text-blue-600">Strand</label>
                        <select id="edit_strand" name="strand" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                            <option value="">Select Strand</option>
                            <option value="STEM">STEM</option>
                            <option value="ABM">ABM</option>
                            <option value="HUMSS">HUMSS</option>
                            <option value="GAS">GAS</option>
                            <option value="TVL">TVL</option>
                        </select>
                    </div>

                    <hr class="my-3 border-gray-200 dark:border-gray-700">
                    <div>
                        <label class="block text-sm font-medium text-red-600">New Password (Optional Reset)</label>
                        <input type="password" name="new_password" placeholder="Leave blank to keep current" 
                               class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update Details</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        // --- Strand Visibility Logic ---
        function toggleStrand(gradeInputId, containerId) {
            const grade = parseInt(document.getElementById(gradeInputId).value);
            const container = document.getElementById(containerId);
            
            // Show Strand if Grade is 11 or 12
            if (grade === 11 || grade === 12) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                // Optional: clear selection when hiding
                const select = container.querySelector('select');
                if(select) select.value = "";
            }
        }

        // --- Modal Control ---
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        // --- Edit Logic ---
        function editStudent(student) {
            // Populate Fields
            document.getElementById('edit_lrn').value = student.lrn;
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_grade_level').value = student.grade_level;
            document.getElementById('edit_section').value = student.section;
            
            // Handle Strand Value
            const strandSelect = document.getElementById('edit_strand');
            if (student.strand) {
                strandSelect.value = student.strand;
            } else {
                strandSelect.value = "";
            }

            // Trigger Strand Logic Immediately (so it shows/hides correctly on open)
            toggleStrand('edit_grade_level', 'edit_strand_container');

            // Set Form Action
            let form = document.getElementById('editForm');
            form.action = `/admin/students/${student.id}`;

            // Update URL for error handling context
            let url = new URL(window.location.href);
            url.searchParams.set('edit_id', student.id);
            window.history.replaceState({}, '', url);

            openModal('editModal');
        }

        // --- Delete Confirmation ---
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.closest('form').submit();
                    }
                })
            });
        });
    </script>
</body>
</html>