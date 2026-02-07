<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

<nav id="sidebar" class="fixed top-0 left-0 h-full bg-white shadow-xl w-64 p-4 flex flex-col transition-all duration-300 z-50 transform -translate-x-full md:translate-x-0 md:w-20 lg:w-64 border-r border-gray-200">
    
    <div class="flex items-center justify-between border-b border-gray-100 pb-5 mb-5">
        <div class="flex items-center gap-3 px-1 w-full overflow-hidden">
            
            @php
                $student = Auth::guard('student')->user();
            @endphp

            {{-- PROFILE PICTURE LOGIC --}}
            <div class="shrink-0 relative group">
                <img src="{{ 
                        ($student->avatar && $student->avatar !== 'default.png') 
                        ? (
                            \Illuminate\Support\Str::startsWith($student->avatar, 'http') 
                            ? $student->avatar 
                            : \Illuminate\Support\Facades\Storage::disk('s3')->url('avatars/' . $student->avatar)
                          ) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode($student->first_name . '+' . $student->last_name) . '&background=0D8ABC&color=fff'
                     }}" 
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff';"
                     alt="Profile" 
                     class="w-10 h-10 rounded-2xl object-cover border-2 border-white shadow-md transition-transform transform group-hover:scale-105">
                
                {{-- Online Status Dot --}}
                <div class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></div>
            </div>

            {{-- STUDENT NAME (Desktop) --}}
            <div class="nav-text hidden lg:block overflow-hidden">
                <h4 class="font-bold text-slate-800 text-sm truncate leading-tight">
                    {{ $student->first_name }}
                </h4>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest truncate">
                    Student Portal
                </p>
            </div>
        </div>
        
        {{-- COLLAPSE BUTTON --}}
        <button id="desktop-collapse-btn" class="hidden lg:block p-1.5 rounded-lg text-slate-400 hover:bg-slate-50 hover:text-indigo-600 transition-colors">
            <i class="fa-solid fa-chevron-left text-xs"></i>
        </button>
    </div>

    {{-- MENU ITEMS --}}
    <ul class="flex flex-col flex-1 space-y-1 overflow-y-auto custom-scrollbar">
        <li>
            <a href="{{ route('student.dashboard') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50 {{ request()->routeIs('student.dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-gauge nav-icon w-6 text-center {{ request()->routeIs('student.dashboard') ? 'text-indigo-600' : 'text-slate-400' }} group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-sm md:hidden lg:block">Overview</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.assignments.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50 {{ request()->routeIs('student.assignments.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-list-check nav-icon w-6 text-center {{ request()->routeIs('student.assignments.*') ? 'text-indigo-600' : 'text-slate-400' }} group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-sm md:hidden lg:block">My Tasks</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.attendance.index') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50 {{ request()->routeIs('student.attendance.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-clipboard-user nav-icon w-6 text-center {{ request()->routeIs('student.attendance.*') ? 'text-indigo-600' : 'text-slate-400' }} group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-sm md:hidden lg:block">Attendance</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.grades') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50 {{ request()->routeIs('student.grades') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-ranking-star nav-icon w-6 text-center {{ request()->routeIs('student.grades') ? 'text-indigo-600' : 'text-slate-400' }} group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-sm md:hidden lg:block">My Grades</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.schedule') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50 {{ request()->routeIs('student.schedule') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">
                <i class="fa-regular fa-calendar-days nav-icon w-6 text-center {{ request()->routeIs('student.schedule') ? 'text-indigo-600' : 'text-slate-400' }} group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-sm md:hidden lg:block">Schedule</span>
            </a>
        </li>

        <li>
            <a href="{{ route('student.profile') }}" class="nav-link flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-indigo-50 {{ request()->routeIs('student.profile') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">
                <i class="fa-solid fa-user-gear nav-icon w-6 text-center {{ request()->routeIs('student.profile') ? 'text-indigo-600' : 'text-slate-400' }} group-hover:text-indigo-600 transition-colors"></i>
                <span class="nav-text ml-3 font-bold text-sm md:hidden lg:block">Profile</span>
            </a>
        </li>
    </ul>

    {{-- LOGOUT --}}
    <ul class="mt-auto pt-4 border-t border-gray-100 space-y-1">
        <li>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center px-3 py-3 rounded-xl group transition-all hover:bg-rose-50 text-rose-500">
                    <i class="fa-solid fa-right-from-bracket nav-icon w-6 text-center group-hover:text-rose-600 transition-colors"></i>
                    <span class="nav-text ml-3 font-bold text-sm md:hidden lg:block">Sign Out</span>
                </button>
            </form>
        </li>
    </ul>
</nav>