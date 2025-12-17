<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm border-b border-slate-100 sticky top-0 z-30 px-6 py-4">
            <div class="flex items-center gap-3">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600 mr-2 transition-colors">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-clipboard-check text-sm"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Attendance</h2>
            </div>
        </header>

        <main class="flex-1 p-6 flex justify-center items-center">
            
            <div class="w-full max-w-md">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-black text-slate-900 mb-2">Class Attendance</h1>
                    <p class="text-slate-500 text-sm">Select the class and date to record student attendance.</p>
                </div>

                <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100">
                    <div class="flex justify-center mb-6">
                        <div class="w-16 h-16 bg-slate-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                    </div>

                    <form action="{{ route('teacher.attendance.show') }}" method="GET" class="space-y-5">
                        
                        <div>
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Target Class</label>
                            <div class="relative">
                                <select id="classSelector" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-semibold appearance-none cursor-pointer" required>
                                    <option value="" disabled selected>-- Choose Class --</option>
                                    @foreach($assignedClasses as $class)
                                        <option value="{{ $class->subject_id }}|{{ $class->section }}">
                                            {{ $class->subject->code }} - {{ $class->section }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Attendance Date</label>
                            <div class="relative">
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" 
                                       class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-semibold" required>
                            </div>
                        </div>

                        <input type="hidden" name="subject_id" id="input_subject_id">
                        <input type="hidden" name="section" id="input_section">

                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-2xl hover:bg-emerald-600 shadow-lg shadow-slate-200 hover:shadow-emerald-200 transition-all active:scale-95 flex items-center justify-center gap-2 mt-4 group">
                            <span>Open Attendance Sheet</span>
                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </form>
                </div>

                <p class="text-center mt-6 text-[11px] text-slate-400 font-medium">
                    <i class="fa-solid fa-lightbulb text-emerald-400 mr-1"></i>
                    Tip: You can modify past attendance by changing the date.
                </p>
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