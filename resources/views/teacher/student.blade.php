<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-emerald-600">My Students</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Faculty</span></div>
        </header>

        <main class="flex-1 p-6">

            <div class="bg-white dark:bg-slate-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 mb-6">
                <form action="{{ route('teacher.students.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-sm font-bold mb-2 text-gray-600 dark:text-gray-300">Filter by Section</label>
                        <div class="relative">
                            <select name="section" class="w-full p-2.5 pl-10 border rounded-lg bg-gray-50 dark:bg-slate-700 dark:border-slate-600 appearance-none">
                                <option value="" disabled {{ !$selectedSection ? 'selected' : '' }}>-- Select a Section --</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec }}" {{ $selectedSection == $sec ? 'selected' : '' }}>{{ $sec }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                <i class="fa-solid fa-filter"></i>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-lg transition shadow-md">
                        View List
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden border border-gray-200 dark:border-slate-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700 flex justify-between items-center">
                    <h3 class="font-bold text-lg">
                        @if($selectedSection)
                            Class List: <span class="text-emerald-600">{{ $selectedSection }}</span>
                            <span class="ml-2 text-sm font-normal text-gray-500">({{ $students->count() }} students)</span>
                        @else
                            <span class="text-gray-500 italic">Please select a section to view students</span>
                        @endif
                    </h3>
                </div>

                @if($selectedSection && $students->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 dark:bg-slate-700 text-gray-600 dark:text-gray-300 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4">No.</th>
                                <th class="px-6 py-4">LRN</th>
                                <th class="px-6 py-4">Student Name</th>
                                <th class="px-6 py-4">Email Address</th>
                                <th class="px-6 py-4 text-center">Grade Level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($students as $index => $student)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                <td class="px-6 py-4 text-gray-400 text-sm">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 font-mono text-emerald-600 text-sm font-bold">{{ $student->lrn }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @php
                                            $avatar = $student->avatar ? asset('avatars/'.$student->avatar) : "https://ui-avatars.com/api/?name=".urlencode($student->first_name)."&background=10b981&color=fff";
                                        @endphp
                                        <img src="{{ $avatar }}" class="w-8 h-8 rounded-full border border-gray-200">
                                        <span class="font-medium">{{ $student->last_name }}, {{ $student->first_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $student->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                        Grade {{ $student->grade_level }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @elseif($selectedSection)
                    <div class="p-10 text-center text-gray-500">
                        <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                        <p>No students found in this section.</p>
                    </div>
                @else
                    <div class="p-10 text-center text-gray-400 bg-gray-50 dark:bg-slate-800/50">
                        <i class="fa-solid fa-users-viewfinder text-4xl mb-3 text-gray-300"></i>
                        <p>Select a section above to load the master list.</p>
                    </div>
                @endif
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>