<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student') | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">

        <header class="bg-white shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-2 md:gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500 mr-1">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center shadow-lg shadow-indigo-100">
                        <i class="fa-solid fa-shapes text-[10px]"></i>
                    </div>
                    <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight truncate">@yield('header_title', 'Student Portal')</h2>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Grade {{ $student->section->grade_level ?? '' }} - {{ $student->section->name ?? '' }}
                    </p>
                </div>

                <img src="{{
                        ($student->avatar && $student->avatar !== 'default.png')
                        ? (Str::startsWith($student->avatar, 'http') ? $student->avatar : \Illuminate\Support\Facades\Storage::disk('s3')->url('avatars/' . $student->avatar))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($student->first_name . '+' . $student->last_name) . '&background=0D8ABC&color=fff'
                     }}"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff';"
                     alt="Profile" class="w-10 h-10 rounded-2xl object-cover border-2 border-white shadow-md">
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-10">

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    @stack('scripts')
</body>
</html>
