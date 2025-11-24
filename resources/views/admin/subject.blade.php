<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-indigo-600">Curriculum Management</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Admin</span></div>
        </header>

        <main class="flex-1 p-6">
            
            @if(session('success'))
                <script>Swal.fire({icon: 'success', title: 'Success', text: "{{ session('success') }}", timer: 1500, showConfirmButton: false});</script>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Subjects List</h1>
                <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                    <i class="fa-solid fa-plus"></i> Add Subject
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-slate-700">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Subject Code</th>
                            <th class="px-6 py-4">Descriptive Title</th>
                            <th class="px-6 py-4">Description</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($subjects as $subject)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ $subject->code }}</td>
                            <td class="px-6 py-4 font-medium">{{ $subject->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($subject->description, 50) }}</td>
                            <td class="px-6 py-4 text-center flex justify-center gap-2">
                                <button onclick="editSubject({{ $subject }})" class="text-blue-500 hover:text-blue-700"><i class="fa-solid fa-pen-to-square"></i></button>
                                <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" class="inline delete-form">
                                    @csrf @method('DELETE')
                                    <button type="button" class="text-red-500 hover:text-red-700 delete-btn"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $subjects->links() }}</div>
            </div>
        </main>
    </div>

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg w-full max-w-md p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4 text-indigo-600">Add New Subject</h2>
            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Subject Code</label>
                        <input type="text" name="code" placeholder="e.g. MATH-101" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Subject Name</label>
                        <input type="text" name="name" placeholder="e.g. Calculus I" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Description (Optional)</label>
                        <textarea name="description" rows="3" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('addModal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-slate-800 rounded-lg w-full max-w-md p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4 text-blue-600">Edit Subject</h2>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-1">Subject Code</label>
                        <input type="text" id="edit_code" name="code" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Subject Name</label>
                        <input type="text" id="edit_name" name="name" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1">Description</label>
                        <textarea id="edit_description" name="description" rows="3" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        function editSubject(subject) {
            document.getElementById('edit_code').value = subject.code;
            document.getElementById('edit_name').value = subject.name;
            document.getElementById('edit_description').value = subject.description;
            document.getElementById('editForm').action = `/admin/subjects/${subject.id}`;
            openModal('editModal');
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Delete Subject?', 
                    text: "All grades associated with this subject will be deleted too!", 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#ef4444', 
                    confirmButtonText: 'Yes, delete it!'
                }).then((r) => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });
    </script>
</body>
</html>