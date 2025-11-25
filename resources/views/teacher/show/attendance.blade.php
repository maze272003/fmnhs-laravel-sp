<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Sheet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.attendance.index') }}" class="text-gray-500"><i class="fa-solid fa-arrow-left"></i></a>
                <h2 class="text-xl font-bold text-emerald-600">Attendance: {{ $date }}</h2>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if(session('success'))
                <script>Swal.fire({icon: 'success', title: 'Saved', text: '{{ session('success') }}', timer: 1500, showConfirmButton: false})</script>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow overflow-hidden">
                <form action="{{ route('teacher.attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                    <input type="hidden" name="section" value="{{ $section }}">
                    <input type="hidden" name="date" value="{{ $date }}">

                    <table class="w-full text-left text-sm">
                        <thead class="bg-emerald-50 dark:bg-slate-700 text-emerald-700 dark:text-emerald-300 uppercase font-bold">
                            <tr>
                                <th class="p-4">Student Name</th>
                                <th class="p-4 text-center">Present</th>
                                <th class="p-4 text-center">Late</th>
                                <th class="p-4 text-center">Absent</th>
                                <th class="p-4 text-center">Excused</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($students as $student)
                                @php
                                    // Get saved status or default to 'present'
                                    $status = isset($attendances[$student->id]) ? $attendances[$student->id]->status : 'present';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                    <td class="p-4 font-medium">{{ $student->last_name }}, {{ $student->first_name }}</td>
                                    
                                    <td class="p-4 text-center">
                                        <input type="radio" name="status[{{ $student->id }}]" value="present" {{ $status == 'present' ? 'checked' : '' }} class="w-5 h-5 text-emerald-600 focus:ring-emerald-500">
                                    </td>
                                    <td class="p-4 text-center">
                                        <input type="radio" name="status[{{ $student->id }}]" value="late" {{ $status == 'late' ? 'checked' : '' }} class="w-5 h-5 text-yellow-500 focus:ring-yellow-500">
                                    </td>
                                    <td class="p-4 text-center">
                                        <input type="radio" name="status[{{ $student->id }}]" value="absent" {{ $status == 'absent' ? 'checked' : '' }} class="w-5 h-5 text-red-600 focus:ring-red-500">
                                    </td>
                                    <td class="p-4 text-center">
                                        <input type="radio" name="status[{{ $student->id }}]" value="excused" {{ $status == 'excused' ? 'checked' : '' }} class="w-5 h-5 text-blue-600 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 flex justify-end">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg font-bold shadow">Save Attendance</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>