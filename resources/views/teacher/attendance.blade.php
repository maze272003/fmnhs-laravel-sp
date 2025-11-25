<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-emerald-600">Attendance</h2>
        </header>

        <main class="flex-1 p-6 flex justify-center items-center">
            <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-lg w-full max-w-md">
                <h3 class="text-lg font-bold mb-6 text-center">Select Class & Date</h3>
                
                <form action="{{ route('teacher.attendance.show') }}" method="GET">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Class</label>
                        <select id="classSelector" class="w-full p-3 border rounded-lg bg-gray-50 dark:bg-slate-700" required>
                            <option value="" disabled selected>-- Select --</option>
                            @foreach($assignedClasses as $class)
                                <option value="{{ $class->subject_id }}|{{ $class->section }}">
                                    {{ $class->subject->code }} - {{ $class->section }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold mb-2">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full p-3 border rounded-lg bg-gray-50 dark:bg-slate-700" required>
                    </div>

                    <input type="hidden" name="subject_id" id="input_subject_id">
                    <input type="hidden" name="section" id="input_section">

                    <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-lg hover:bg-emerald-700">
                        Proceed to Sheet
                    </button>
                </form>
            </div>
        </main>
    </div>
    
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        document.getElementById('classSelector').addEventListener('change', function() {
            const parts = this.value.split('|');
            document.getElementById('input_subject_id').value = parts[0];
            document.getElementById('input_section').value = parts[1];
        });
    </script>
</body>
</html>