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
            
            <div class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-lg border border-gray-100 dark:border-slate-700 w-full max-w-lg relative overflow-hidden">
                
                <i class="fa-solid fa-clipboard-check absolute -right-6 -bottom-6 text-9xl text-gray-50 dark:text-slate-700/50 pointer-events-none"></i>

                <div class="text-center mb-8 relative z-10">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl shadow-sm">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h1 class="text-2xl font-bold">Select Class to Grade</h1>
                    <p class="text-gray-500 text-sm mt-1">Only classes assigned to you will appear here.</p>
                </div>

                @if($assignedClasses->isEmpty())
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-center text-amber-800 mb-6">
                        <i class="fa-solid fa-circle-exclamation text-xl mb-2"></i>
                        <p class="font-bold">No Classes Assigned</p>
                        <p class="text-xs mt-1">Please contact the Administrator to assign your teaching load/schedule.</p>
                    </div>
                @else
                    <form action="{{ route('teacher.grading.show') }}" method="GET">
                        
                        <div class="space-y-5 relative z-10">
                            
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded-r">
                                <p class="text-xs text-blue-700">
                                    <i class="fa-solid fa-info-circle mr-1"></i> 
                                    Select a class below to load the student list and input grades.
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-bold mb-2 text-gray-700 dark:text-gray-300">Assigned Class</label>
                                
                                <div class="relative">
                                    <select id="classSelector" required class="w-full p-3 pl-10 border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none dark:bg-slate-700 dark:border-slate-600 transition appearance-none cursor-pointer">
                                        <option value="" disabled selected>-- Select Subject & Section --</option>
                                        
                                        @foreach($assignedClasses as $sched)
                                            <option value="{{ $sched->subject_id }}|{{ $sched->section }}">
                                                {{ $sched->subject->code }} - {{ $sched->subject->name }} &nbsp; ({{ $sched->section }})
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-emerald-600">
                                        <i class="fa-solid fa-chalkboard"></i>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="subject_id" id="input_subject_id">
                            <input type="hidden" name="section" id="input_section">

                        </div>

                        <button type="submit" class="w-full mt-8 bg-emerald-600 text-white font-bold py-3 rounded-lg hover:bg-emerald-700 transition shadow-md flex items-center justify-center gap-2 relative z-10">
                            <span>Load Grading Sheet</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    </form>
                @endif
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>

    <script>
        const selector = document.getElementById('classSelector');
        const subjectInput = document.getElementById('input_subject_id');
        const sectionInput = document.getElementById('input_section');

        if(selector) {
            selector.addEventListener('change', function() {
                // Get value like "1|Rizal"
                const rawValue = this.value;
                
                if(rawValue) {
                    // Split into array ["1", "Rizal"]
                    const parts = rawValue.split('|');
                    
                    // Assign to hidden inputs
                    subjectInput.value = parts[0];
                    sectionInput.value = parts[1];
                }
            });
        }
    </script>
</body>
</html>