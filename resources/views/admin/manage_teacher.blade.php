<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-indigo-600">Faculty Management</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Admin</span></div>
        </header>

        <main class="flex-1 p-6">
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Teacher List</h1>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                    <i class="fa-solid fa-plus"></i> Add Teacher
                </button>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-slate-700">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Emp ID</th>
                            <th class="px-6 py-4">Teacher Name</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Department</th>
                            <th class="px-6 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($teachers as $teacher)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                            <td class="px-6 py-4 font-medium">{{ $teacher->employee_id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($teacher->first_name, 0, 1) }}
                                    </div>
                                    {{ $teacher->last_name }}, {{ $teacher->first_name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $teacher->email }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-100 text-emerald-700">{{ $teacher->department }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button class="text-blue-500 hover:text-blue-700 mx-1"><i class="fa-solid fa-pen-to-square"></i></button>
                                <button class="text-red-500 hover:text-red-700 mx-1"><i class="fa-solid fa-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700">
                    {{ $teachers->links() }}
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>