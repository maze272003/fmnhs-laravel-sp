<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

<nav id="sidebar" class="fixed top-0 left-0 h-full bg-white shadow-xl w-64 p-4 flex flex-col transition-all duration-300 z-50 transform -translate-x-full md:translate-x-0 md:w-20 lg:w-64 border-r border-gray-200">
    
    <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
        <div class="flex items-center gap-3 px-2">
            
            @php
                $student = Auth::guard('student')->user();
                $avatarPath = 'avatars/' . $student->avatar;
                $hasAvatar = !empty($student->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($avatarPath);
            @endphp

            @if($hasAvatar)
                <img src="{{ asset('storage/' . $avatarPath) }}" 
                     alt="Profile" 
                     class="w-8 h-8 rounded object-cover border border-gray-200 shadow-sm">
            @else
                <div class="w-8 h-8 rounded bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                    {{ substr($student->first_name, 0, 1) }}
                </div>
            @endif

            <span class="nav-text font-bold text-xl text-slate-800 lg:block md:hidden">Student</span>
        </div>
        
        <button id="desktop-collapse-btn" class="hidden lg:block p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </button>
    </div>

    <ul class="flex flex-col flex-1 space-y-1 overflow-y-auto">
        
        <li>
            <a href="{{ route('student.dashboard') }}" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-blue-50">
                <i class="fa-solid fa-gauge nav-icon w-6 text-center text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 md:hidden lg:block">Overview</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.assignments.index') }}" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-blue-50">
                <i class="fa-solid fa-list-check nav-icon w-6 text-center text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 md:hidden lg:block">My Task</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.attendance.index') }}" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-blue-50">
                <i class="fa-solid fa-clipboard-user nav-icon w-6 text-center text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 md:hidden lg:block">Attendance</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.grades') }}" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-blue-50">
                <i class="fa-solid fa-ranking-star nav-icon w-6 text-center text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 md:hidden lg:block">My Grades</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.schedule') }}" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-blue-50">
                <i class="fa-regular fa-calendar-days nav-icon w-6 text-center text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 md:hidden lg:block">Schedule</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.profile') }}" class="nav-link flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-blue-50">
                <i class="fa-solid fa-user-gear nav-icon w-6 text-center text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                <span class="nav-text ml-3 font-medium text-gray-700 md:hidden lg:block">Profile</span>
            </a>
        </li>
    </ul>

    <ul class="mt-auto pt-4 border-t border-gray-200 space-y-1">
        <li>
            <form action="{{ route('logout') }}" id="logout-form" method="POST">
                @csrf
                <button type="button" id="logout-btn" class="w-full flex items-center px-3 py-3 rounded-lg group transition-colors hover:bg-red-50 text-red-600">
                    <i class="fa-solid fa-right-from-bracket nav-icon w-6 text-center group-hover:text-red-700"></i>
                    <span class="nav-text ml-3 font-medium md:hidden lg:block">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</nav>