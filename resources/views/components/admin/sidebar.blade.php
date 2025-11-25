<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

<nav id="sidebar" class="fixed top-0 left-0 h-full bg-white dark:bg-slate-900 shadow-xl w-64 p-4 flex flex-col transition-all duration-300 z-50 transform -translate-x-full md:translate-x-0 md:w-20 lg:w-64 border-r border-gray-200 dark:border-slate-800">
    
    <div class="flex items-center justify-between border-b border-gray-200 dark:border-slate-700 pb-4 mb-4">
        <div class="flex items-center gap-3 px-2">
            <div class="w-8 h-8 rounded bg-indigo-600 flex items-center justify-center text-white font-bold text-xl">A</div>
            <span class="nav-text font-bold text-xl text-slate-800 dark:text-white lg:block md:hidden">Admin</span>
        </div>
        
        <button id="desktop-collapse-btn" class="hidden lg:block p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-500 transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </button>
    </div>

    <ul class="flex flex-col flex-1 space-y-1 overflow-y-auto">
        
        <li>
            <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-gray-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-house nav-icon w-6 text-center text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 dark:text-gray-300 md:hidden lg:block">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.subjects.index') }}" 
            class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-gray-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-book nav-icon w-6 text-center text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 dark:text-gray-300 md:hidden lg:block">Manage Subjects</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.students.index') }}" 
            class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-gray-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-user-graduate nav-icon w-6 text-center text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 dark:text-gray-300 md:hidden lg:block">Manage Students</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.teachers.index') }}" 
            class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-gray-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-chalkboard-user nav-icon w-6 text-center text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 dark:text-gray-300 md:hidden lg:block">Manage Teachers</span>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.announcements.index') }}" 
            class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-gray-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-chalkboard-user nav-icon w-6 text-center text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 dark:text-gray-300 md:hidden lg:block">Announcement</span>
            </a>
        </li>

        <li>
            <a href="#" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-gray-50 dark:hover:bg-slate-800">
                <i class="fa-solid fa-gear nav-icon w-6 text-center text-gray-500 dark:text-gray-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 dark:text-gray-300 md:hidden lg:block">Settings</span>
            </a>
        </li>
    </ul>

    <ul class="mt-auto pt-4 border-t border-gray-200 dark:border-slate-700 space-y-1">
        <li>
            <form action="{{ route('admin.logout') }}" id="logout-form" method="POST">
                @csrf
                <button type="button" onclick="confirmLogout()" class="w-full flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600">
                    <i class="fa-solid fa-right-from-bracket nav-icon w-6 text-center group-hover:text-red-700"></i>
                    <span class="nav-text ml-3 font-medium md:hidden lg:block">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</nav>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out of the admin panel.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#6b7280', 
            confirmButtonText: 'Yes, Logout'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form manually
                document.getElementById('logout-form').submit();
            }
        });
    }
</script>