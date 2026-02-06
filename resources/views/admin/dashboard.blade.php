<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SIS Overview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-chart-pie text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Analytics Dashboard</h2>
                </div>
            </div>
            @include('components.admin.header_details')
            {{-- <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-black text-slate-800 uppercase leading-none">{{ Auth::guard('admin')->user()->name }}</p>
                    <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-1">Administrator</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold shadow-lg shadow-slate-200">
                    {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                </div>
            </div> --}}
        </header>

        <main class="flex-1 p-6 lg:p-10">

            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">System Summary</h1>
                <p class="text-slate-500 font-medium">Real-time data from the normalized student information system.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
                    <div class="flex justify-between items-center mb-4">
                        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <span class="text-[10px] font-black text-blue-400 bg-blue-50/50 px-3 py-1 rounded-full border border-blue-100 uppercase tracking-widest">Database Linked</span>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Students</p>
                    <h3 class="text-4xl font-black text-slate-900">{{ $totalStudents }}</h3>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
                    <div class="flex justify-between items-center mb-4">
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <span class="text-[10px] font-black text-emerald-400 bg-emerald-50/50 px-3 py-1 rounded-full border border-emerald-100 uppercase tracking-widest">Faculty Members</span>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Teachers</p>
                    <h3 class="text-4xl font-black text-slate-900">{{ $totalTeachers }}</h3>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
                    <div class="flex justify-between items-center mb-4">
                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </div>
                        <span class="text-[10px] font-black text-indigo-400 bg-indigo-50/50 px-3 py-1 rounded-full border border-indigo-100 uppercase tracking-widest">Active Curriculum</span>
                    </div>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Subjects</p>
                    <h3 class="text-4xl font-black text-slate-900">{{ $totalSubjects }}</h3>
                </div>
            </div>

            {{-- KPI Cards Row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-arrow-up-right-dots"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Promoted</p>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">{{ $promotedCount }}</h3>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Alumni</p>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">{{ $alumniCount }}</h3>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Archived</p>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">{{ $archivedCount }}</h3>
                </div>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-user-xmark"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dropped</p>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900">{{ $droppedCount }}</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-2 h-6 bg-blue-500 rounded-full"></div>
                        <h3 class="font-black text-lg text-slate-800 tracking-tight uppercase">Population by Grade Level</h3>
                    </div>
                    <div class="relative h-72">
                        <canvas id="studentChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-2 h-6 bg-emerald-500 rounded-full"></div>
                        <h3 class="font-black text-lg text-slate-800 tracking-tight uppercase">Faculty Distribution</h3>
                    </div>
                    <div class="relative h-72">
                        <canvas id="teacherChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- Enrollment Trend and Section Capacity --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-2 h-6 bg-indigo-500 rounded-full"></div>
                        <h3 class="font-black text-lg text-slate-800 tracking-tight uppercase">Enrollment Trend by SY</h3>
                    </div>
                    <div class="relative h-72">
                        <canvas id="enrollmentTrendChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-2 h-6 bg-amber-500 rounded-full"></div>
                        <h3 class="font-black text-lg text-slate-800 tracking-tight uppercase">Section Capacity</h3>
                    </div>
                    <div class="overflow-y-auto max-h-72">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                    <th class="pb-3 px-2">Section</th>
                                    <th class="pb-3 px-2 text-center">Grade</th>
                                    <th class="pb-3 px-2 text-center">Students</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($sectionCapacity as $sec)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-2 font-semibold text-sm text-slate-700">{{ $sec->name }}</td>
                                    <td class="py-3 px-2 text-center text-sm text-slate-500">{{ $sec->grade_level }}</td>
                                    <td class="py-3 px-2 text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-full text-xs font-black {{ $sec->students_count > 40 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }}">
                                            {{ $sec->students_count }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Teacher Load Summary --}}
            <div class="mt-8 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-2 h-6 bg-violet-500 rounded-full"></div>
                    <h3 class="font-black text-lg text-slate-800 tracking-tight uppercase">Teacher Load Distribution</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="pb-3 px-4">Teacher</th>
                                <th class="pb-3 px-4 text-center">Classes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($teacherLoad->sortByDesc('schedule_count')->take(10) as $tl)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 font-semibold text-sm text-slate-700">{{ $tl->first_name }} {{ $tl->last_name }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-1 rounded-full text-xs font-black bg-violet-50 text-violet-600">
                                        {{ $tl->schedule_count }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    
    <script>
        // Set dynamic colors
        const colors = ['#6366f1', '#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6'];

        // --- 1. STUDENT POPULATION (Normalized) ---
        const studentData = @json($studentsPerGrade);
        const gradeLabels = studentData.map(item => 'Grade ' + item.grade_level);
        const gradeCounts = studentData.map(item => item.total);

        new Chart(document.getElementById('studentChart'), {
            type: 'doughnut',
            data: {
                labels: gradeLabels,
                datasets: [{
                    data: gradeCounts,
                    backgroundColor: colors,
                    hoverOffset: 25,
                    borderWidth: 5,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: { 
                            usePointStyle: true, 
                            padding: 25, 
                            font: { weight: 'bold', size: 12, family: 'sans-serif' } 
                        }
                    }
                }
            }
        });

        // --- 2. TEACHER DEPARTMENTS ---
        const teacherData = @json($teachersPerDept);
        const deptLabels = teacherData.map(item => item.department);
        const deptCounts = teacherData.map(item => item.total);

        new Chart(document.getElementById('teacherChart'), {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Teachers',
                    data: deptCounts,
                    backgroundColor: '#10b981',
                    borderRadius: 15,
                    barThickness: 35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: { weight: 'black', size: 10 } } },
                    y: { grid: { color: '#f1f5f9' }, border: { display: false }, beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
        // --- 3. ENROLLMENT TREND BY SCHOOL YEAR ---
        const enrollmentData = @json($enrollmentByYear);
        const syLabels = enrollmentData.map(item => 'SY ' + item.school_year);
        const syCounts = enrollmentData.map(item => item.total);

        new Chart(document.getElementById('enrollmentTrendChart'), {
            type: 'line',
            data: {
                labels: syLabels,
                datasets: [{
                    label: 'Students Enrolled',
                    data: syCounts,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: { weight: 'bold', size: 10 } } },
                    y: { grid: { color: '#f1f5f9' }, border: { display: false }, beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    </script>
</body>
</html>