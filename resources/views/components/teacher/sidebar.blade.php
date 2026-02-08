<div id="overlay" class="fixed inset-0 bg-slate-900 bg-opacity-40 z-40 hidden transition-opacity duration-300"></div>

<nav id="sidebar" class="fixed top-0 left-0 h-full bg-white shadow-xl w-64 p-4 flex flex-col transition-all duration-300 z-50 transform -translate-x-full md:translate-x-0 md:w-20 lg:w-64 border-r border-gray-100">
    
    <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
        <div class="flex items-center gap-3 px-2">
            <div class="w-9 h-9 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-xl shadow-sm shadow-emerald-200">
                T
            </div>
            <span class="nav-text font-bold text-xl text-slate-800 lg:block md:hidden tracking-tight">
                Faculty
            </span>
        </div>
        
        <button id="desktop-collapse-btn" class="hidden lg:block p-1.5 rounded-lg hover:bg-slate-50 text-slate-400 transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </button>
    </div>

    <ul class="flex flex-col flex-1 space-y-1 overflow-y-auto custom-scrollbar">
        
        <li>
            <a href="{{ route('teacher.dashboard') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-emerald-50 active:bg-emerald-100">
                <i class="fa-solid fa-house nav-icon w-6 text-center text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                <span class="nav-text ml-3 font-semibold text-slate-600 group-hover:text-emerald-700 md:hidden lg:block">Overview</span>
            </a>
        </li>

        <li>
            <a href="{{ route('teacher.assignments.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-emerald-50">
                <i class="fa-solid fa-list-check nav-icon w-6 text-center text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                <span class="nav-text ml-3 font-semibold text-slate-600 group-hover:text-emerald-700 md:hidden lg:block">Task</span>
            </a>
        </li>

        <li>
            <a href="{{ route('teacher.classes.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-emerald-50">
                <i class="fa-solid fa-chalkboard-user nav-icon w-6 text-center text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                <span class="nav-text ml-3 font-semibold text-slate-600 group-hover:text-emerald-700 md:hidden lg:block">My Classes</span>
            </a>
        </li>

        <li>
            <a href="{{ route('teacher.attendance.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-emerald-50">
                <i class="fa-solid fa-clipboard-user nav-icon w-6 text-center text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                <span class="nav-text ml-3 font-semibold text-slate-600 group-hover:text-emerald-700 md:hidden lg:block">Attendance</span>
            </a>
        </li>

        <li>
            <a href="{{ route('teacher.grading.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-emerald-50">
                <i class="fa-solid fa-pen-to-square nav-icon w-6 text-center text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                <span class="nav-text ml-3 font-semibold text-slate-600 group-hover:text-emerald-700 md:hidden lg:block">Grading Sheet</span>
            </a>
        </li>

        <li>
            <a href="{{ route('teacher.students.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-emerald-50">
                <i class="fa-solid fa-users nav-icon w-6 text-center text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                <span class="nav-text ml-3 font-semibold text-slate-600 group-hover:text-emerald-700 md:hidden lg:block">Students</span>
            </a>
        </li>

        <li class="relative">
            <div
                class="nav-link flex items-center px-3 py-3 rounded-xl 
                    cursor-not-allowed opacity-60 select-none"
            >
                <i class="fa-solid fa-video nav-icon w-6 text-center text-slate-400"></i>

                <span class="nav-text ml-3 font-semibold text-slate-500 md:hidden lg:block">
                    Live Class
                </span>

                <!-- Coming Soon badge -->
                <span
                    class="ml-auto text-[10px] font-semibold px-2 py-1 rounded-full 
                        bg-amber-100 text-amber-700"
                >
                    Coming Soon
                </span>
            </div>
        </li>


        <li>
            <a href="{{ route('teacher.announcements.index')}}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-emerald-50">
                <i class="fa-solid fa-bullhorn nav-icon w-6 text-center text-slate-400 group-hover:text-emerald-600 transition-colors"></i>
                <span class="nav-text ml-3 font-semibold text-slate-600 group-hover:text-emerald-700 md:hidden lg:block">Announcement</span>
            </a>
        </li>
    </ul>

    <ul class="mt-auto pt-4 border-t border-gray-100 space-y-1">
        <li>
            <form action="{{ route('teacher.logout') }}" id="logout-form" method="POST">
                @csrf
                <button type="button" id="logout-btn" class="w-full flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-red-50 text-red-500 hover:text-red-700">
                    <i class="fa-solid fa-right-from-bracket nav-icon w-6 text-center group-hover:rotate-12 transition-transform"></i>
                    <span class="nav-text ml-3 font-bold md:hidden lg:block">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</nav>
