<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Sheet - {{ $section }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.grading.index') }}" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <h2 class="text-xl font-bold text-emerald-600">
                    {{ $subject->code }} <span class="text-gray-400">|</span> {{ $section }}
                </h2>
            </div>
            <div class="flex items-center gap-3"><span class="font-bold">Faculty</span></div>
        </header>

        <main class="flex-1 p-6">

            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Saved!', text: "{{ session('success') }}", timer: 1500, showConfirmButton: false });
                </script>
            @endif

            <div class="bg-white dark:bg-slate-800 rounded-lg shadow border border-gray-200 dark:border-slate-700 overflow-hidden">
                
                <form action="{{ route('teacher.grades.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <input type="hidden" name="section" value="{{ $section }}">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-emerald-50 dark:bg-slate-700 text-emerald-700 dark:text-emerald-300 uppercase text-xs font-bold">
                                <tr>
                                    <th class="px-6 py-4 w-1/4">Student Name</th>
                                    <th class="px-4 py-4 text-center">1st Quarter</th>
                                    <th class="px-4 py-4 text-center">2nd Quarter</th>
                                    <th class="px-4 py-4 text-center">3rd Quarter</th>
                                    <th class="px-4 py-4 text-center">4th Quarter</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach($students as $student)
                                    @php
                                        // Helper to find existing grades for this specific student & subject
                                        // Note: Efficient way is eager loading in controller, but this works for small classes
                                        $grades = $student->grades->where('subject_id', $subject->id);
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition">
                                        <td class="px-6 py-4 font-medium">
                                            {{ $student->last_name }}, {{ $student->first_name }}
                                        </td>
                                        
                                        @for($q = 1; $q <= 4; $q++)
                                            @php
                                                $grade = $grades->where('quarter', $q)->first();
                                                $val = $grade ? $grade->grade_value : '';
                                            @endphp
                                            <td class="px-2 py-3 text-center">
                                                <input type="number" step="0.01" min="60" max="100"
                                                    name="grades[{{ $student->id }}][{{ $q }}]" 
                                                    value="{{ $val }}"
                                                    class="w-20 text-center p-2 border rounded bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none dark:bg-slate-600 dark:border-slate-500"
                                                    placeholder="--">
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 flex justify-end">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> Save Grades
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>