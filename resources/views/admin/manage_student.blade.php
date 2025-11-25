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
                        // Check if it's a store/create error (missing student ID)
                        // If there are errors AND we don't have an ID in the URL, assume ADD modal failed
                        const urlParams = new URLSearchParams(window.location.search);
                        if (!urlParams.get('edit_id')) {
                           openModal('addModal');
                        } else {
                           // If we have an ID in URL, assume EDIT modal failed
                           openModal('editModal');
                        }
                    });
                </script>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Student List</h1>
                <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                    <i class="fa-solid fa-plus"></i> Add Student
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-slate-700">
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
                        @foreach($students as $student)
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
                                <span class="text-sm ml-1">{{ $student->section }}</span>
                            </td>
                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                <button onclick="editStudent({{ $student }})" 
                                    class="text-blue-500 hover:text-blue-700 mx-1">
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
                        @endforeach
                    </tbody>
                </table>
                
                <div class="p-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700">
                    {{ $students->links() }} 
                </div>
            </div>

        </main>
    </div>

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg w-full max-w-md p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4 text-indigo-600">Add New Student</h2>
            <form action="{{ route('admin.students.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium">LRN (Learner ID)</label>
                        <input type="text" name="lrn" value="{{ old('lrn') }}" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Grade Level (7-12)</label>
                            <input type="number" name="grade_level" value="{{ old('grade_level') }}" min="7" max="12" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Section</label>
                            <input type="text" name="section" value="{{ old('section') }}" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
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
        <div class="bg-white dark:bg-slate-800 rounded-lg w-full max-w-md p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4 text-blue-600">Edit Student</h2>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="mb-3">
                        <label class="block text-sm font-medium">LRN (Read Only)</label>
                        <input type="text" id="edit_lrn" disabled class="w-full p-2 bg-gray-100 border rounded cursor-not-allowed dark:bg-slate-700">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Email</label>
                        <input type="email" id="edit_email" name="email" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Grade Level</label>
                            <input type="number" id="edit_grade_level" name="grade_level" min="7" max="12" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Section</label>
                            <input type="text" id="edit_section" name="section" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                        </div>
                    </div>
                    <hr class="my-3">
                    <div>
                        <label class="block text-sm font-medium text-red-600">New Password (Optional Reset)</label>
                        <input type="password" name="new_password" placeholder="Leave blank to keep current password" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
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
        // --- Modal Control ---
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        // --- Edit Logic ---
        function editStudent(student) {
            // Set values
            document.getElementById('edit_lrn').value = student.lrn; // Read-only field
            document.getElementById('edit_first_name').value = student.first_name;
            document.getElementById('edit_last_name').value = student.last_name;
            document.getElementById('edit_email').value = student.email;
            document.getElementById('edit_grade_level').value = student.grade_level;
            document.getElementById('edit_section').value = student.section;

            // Set Form Action URL dynamically for PUT request
            let form = document.getElementById('editForm');
            form.action = `/admin/students/${student.id}`;

            // Add URL parameter to track which modal was open if validation fails
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