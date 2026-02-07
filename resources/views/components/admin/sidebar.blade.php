<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="overlay" class="fixed inset-0 bg-slate-900/40 z-40 hidden transition-opacity duration-300 backdrop-blur-sm"></div>

<nav id="sidebar" class="fixed top-0 left-0 h-full bg-white shadow-2xl w-64 p-4 flex flex-col transition-all duration-300 z-50 transform -translate-x-full md:translate-x-0 md:w-20 lg:w-64 border-r border-slate-100">
    
    <div class="flex items-center justify-between border-b border-slate-50 pb-5 mb-5 px-1">
        <div class="flex items-center gap-3 px-2">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-100 transition-transform hover:rotate-12">
                A
            </div>
            <div class="nav-text lg:block md:hidden">
                <span class="block font-black text-xl text-slate-800 tracking-tight leading-none">Admin</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Control Panel</span>
            </div>
        </div>
        
        <button id="desktop-collapse-btn" class="hidden lg:block p-1.5 rounded-lg hover:bg-slate-50 text-slate-400 transition-all active:scale-95">
            <i class="fa-solid fa-chevron-left text-xs"></i>
        </button>
    </div>

    <ul class="flex flex-col flex-1 space-y-1 overflow-y-auto custom-scrollbar pr-1">
        
        <li>
            <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50 active:bg-indigo-100">
                <i class="fa-solid fa-house nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Dashboard</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.subjects.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-book nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Manage Subjects</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.students.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-user-graduate nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Manage Students</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.schedules.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-calendar-days nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Manage Schedules</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.teachers.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-chalkboard-user nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Manage Teachers</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.announcements.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-bullhorn nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Announcements</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.attendance.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-clipboard-list nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Attendance Logs</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.rooms.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-door-open nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Manage Rooms</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.school-years.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-calendar-check nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">School Years</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.audit-trail.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-clock-rotate-left nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Audit Trail</span>
            </a>
        </li>

        {{-- <li>
            <a href="#" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50">
                <i class="fa-solid fa-gear nav-icon w-6 text-center text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-slate-600 group-hover:text-indigo-700 md:hidden lg:block">Settings</span>
            </a>
        </li> --}}
    </ul>

    <ul class="mt-auto pt-4 border-t border-slate-50 space-y-1">
        <li>
            <form action="{{ route('admin.logout') }}" id="logout-form" method="POST">
                @csrf
                <button type="button" id="logout-btn" class="w-full flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-rose-50 text-rose-500 hover:text-rose-700">
                    <i class="fa-solid fa-right-from-bracket nav-icon w-6 text-center group-hover:-translate-x-1 transition-transform"></i>
                    <span class="nav-text ml-3 font-black uppercase text-[11px] tracking-widest md:hidden lg:block">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</nav>

<style>
    /* Malinis na Scrollbar para sa Sidebar Menu */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background: #cbd5e1;
    }
</style>