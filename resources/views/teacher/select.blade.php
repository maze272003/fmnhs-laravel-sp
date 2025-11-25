<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-emerald-600">Grading Sheet</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Faculty</span></div>
        </header>

        <main class="flex-1 p-6 flex items-center justify-center">
            
            <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-lg border border-gray-100 dark:border-slate-700 w-full max-w-lg">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Select Class to Grade</h1>
                    <p class="text-gray-500">Choose a subject and section to start.</p>
                </div>

                <form action="{{ route('teacher.grading.show') }}" method="GET">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-2">Subject</label>
                            <select name="subject_id" required class="w-full p-3 border rounded-lg bg-gray-50 dark:bg-slate-700 dark:border-slate-600">
                                <option value="" disabled selected>-- Select Subject --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->code }} - {{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Section</label>
                            <select name="section" required class="w-full p-3 border rounded-lg bg-gray-50 dark:bg-slate-700 dark:border-slate-600">
                                <option value="" disabled selected>-- Select Section --</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section }}">{{ $section }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-8 bg-emerald-600 text-white font-bold py-3 rounded-lg hover:bg-emerald-700 transition shadow-md">
                        Load Students <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>

                </form>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>