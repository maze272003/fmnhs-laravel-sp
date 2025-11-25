<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-indigo-600">Admin Dashboard</h2>
            <div class="flex items-center gap-3"><span class="font-bold">{{ Auth::guard('admin')->user()->name }}</span></div>
        </header>

        <main class="flex-1 p-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-bold">Total Students</p>
                            <h3 class="text-3xl font-bold">{{ $totalStudents }}</h3>
                        </div>
                        <div class="text-blue-500 text-3xl"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border-l-4 border-emerald-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-bold">Faculty Members</p>
                            <h3 class="text-3xl font-bold">{{ $totalTeachers }}</h3>
                        </div>
                        <div class="text-emerald-500 text-3xl"><i class="fa-solid fa-chalkboard-user"></i></div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 uppercase font-bold">Active Subjects</p>
                            <h3 class="text-3xl font-bold">{{ $totalSubjects }}</h3>
                        </div>
                        <div class="text-indigo-500 text-3xl"><i class="fa-solid fa-book"></i></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm">
                    <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-300">Student Population by Grade</h3>
                    <div class="relative h-64">
                        <canvas id="studentChart"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm">
                    <h3 class="font-bold text-lg mb-4 text-gray-700 dark:text-gray-300">Faculty by Department</h3>
                    <div class="relative h-64">
                        <canvas id="teacherChart"></canvas>
                    </div>
                </div>

            </div>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    
    <script>
        // --- 1. SETUP STUDENT DATA ---
        // Convert PHP Collection to JS Array
        const studentData = @json($studentsPerGrade);
        
        const gradeLabels = studentData.map(item => 'Grade ' + item.grade_level);
        const gradeCounts = studentData.map(item => item.total);

        // Render Pie Chart
        new Chart(document.getElementById('studentChart'), {
            type: 'doughnut',
            data: {
                labels: gradeLabels,
                datasets: [{
                    data: gradeCounts,
                    backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#10b981'], // Tailwind colors
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });

        // --- 2. SETUP TEACHER DATA ---
        const teacherData = @json($teachersPerDept);
        const deptLabels = teacherData.map(item => item.department);
        const deptCounts = teacherData.map(item => item.total);

        // Render Bar Chart
        new Chart(document.getElementById('teacherChart'), {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Teachers',
                    data: deptCounts,
                    backgroundColor: '#10b981',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    </script>
</body>
</html>