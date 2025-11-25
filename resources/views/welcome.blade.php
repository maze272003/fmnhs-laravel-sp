<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Portal System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f3f4f6;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="flex flex-col min-h-screen font-sans text-slate-800">

    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-800">Grand Tech High</span>
                </div>
                <div class="text-sm text-gray-500">
                    School Year 2024-2025
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow flex items-center justify-center p-6">
        <div class="max-w-6xl w-full">
            
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4">
                    Welcome to the <span class="text-indigo-600">School Portal</span>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Access your grades, manage classes, and monitor academic performance in one centralized hub. Please select your portal below.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <a href="{{ route('login') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-200 p-8 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-blue-500 group-hover:h-2 transition-all"></div>
                    <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 text-2xl mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 group-hover:text-blue-600 transition-colors">Student Portal</h2>
                    <p class="text-gray-500 mb-6">View your grades, check schedules, and manage your student profile.</p>
                    <div class="flex items-center text-blue-600 font-semibold group-hover:translate-x-2 transition-transform">
                        Login as Student <i class="fa-solid fa-arrow-right ml-2"></i>
                    </div>
                </a>

                <a href="{{ route('teacher.login') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-200 p-8 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500 group-hover:h-2 transition-all"></div>
                    <div class="w-14 h-14 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 text-2xl mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 group-hover:text-emerald-600 transition-colors">Faculty Portal</h2>
                    <p class="text-gray-500 mb-6">Manage your classes, input grades, and track student progress.</p>
                    <div class="flex items-center text-emerald-600 font-semibold group-hover:translate-x-2 transition-transform">
                        Login as Teacher <i class="fa-solid fa-arrow-right ml-2"></i>
                    </div>
                </a>

                <a href="{{ route('admin.login') }}" class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-200 p-8 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-slate-600 group-hover:h-2 transition-all"></div>
                    <div class="w-14 h-14 bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 text-2xl mb-6 group-hover:bg-slate-700 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 group-hover:text-slate-700 transition-colors">Admin Portal</h2>
                    <p class="text-gray-500 mb-6">Manage users, subjects, and system configurations.</p>
                    <div class="flex items-center text-slate-600 font-semibold group-hover:translate-x-2 transition-transform">
                        Access Control <i class="fa-solid fa-arrow-right ml-2"></i>
                    </div>
                </a>

            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
            &copy; 2025 Grand Tech High School. All rights reserved. <br>
            <span class="text-xs">Powered by Laravel Student Information System</span>
        </div>
    </footer>

</body>
</html>