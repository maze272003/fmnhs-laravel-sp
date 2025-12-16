<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-shapes text-sm"></i>
                    </div>
                    <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight truncate">Student Portal</h2>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php
                    $student = Auth::guard('student')->user();
                    $avatarPath = 'avatars/' . $student->avatar;
                    $hasAvatar = !empty($student->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($avatarPath);
                @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-xs font-black text-slate-800 uppercase leading-none">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mt-1">Grade {{ $student->grade_level }} — {{ $student->section }}</p>
                </div>

                @if($hasAvatar)
                    <img src="{{ asset('storage/' . $avatarPath) }}" alt="Profile" class="w-9 h-9 md:w-10 md:h-10 rounded-xl object-cover border-2 border-white shadow-sm">
                @else
                    <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black shadow-lg shadow-blue-100 text-sm">
                        {{ substr($student->first_name, 0, 1) }}
                    </div>
                @endif
            </div>
        </header>

        <main class="flex-1 p-4 md:p-8 lg:p-10">
            
            <div id="welcome-banner" class="hidden bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[2.5rem] p-8 md:p-10 text-white shadow-xl shadow-blue-100 mb-10 relative overflow-hidden">
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-4xl font-black mb-3 tracking-tight">Hello, {{ Auth::guard('student')->user()->first_name }}! 👋</h1>
                    <p class="opacity-90 text-sm md:text-lg font-medium max-w-md">Your academic journey continues. Here's a quick look at your profile today.</p>
                </div>
                <i class="fa-solid fa-graduation-cap absolute right-[-20px] bottom-[-20px] text-9xl opacity-10 rotate-12"></i>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Current Section</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800">{{ Auth::guard('student')->user()->section }}</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Class Adviser</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-sm">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800">--</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 group hover:shadow-md transition-all">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Portal Status</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-sm animate-pulse">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <h3 class="text-xl font-black text-emerald-600 tracking-tight">Enrolled</h3>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-8 px-2">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 bg-blue-600 rounded-full"></div>
                        <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">Bulletin Board</h3>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 bg-white px-4 py-1.5 rounded-full border border-slate-100 uppercase tracking-widest shadow-sm">Updates</span>
                </div>

                <div class="space-y-8">
                    @forelse($announcements as $announcement)
                        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl hover:border-blue-100 transition-all duration-300 group overflow-hidden">
                            
                            <div class="flex flex-col gap-1 mb-6 border-b border-slate-50 pb-5">
                                <h4 class="font-black text-xl md:text-2xl text-slate-800 group-hover:text-blue-700 transition-colors leading-tight mb-2">
                                    {{ $announcement->title }}
                                </h4>
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="flex items-center text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-lg uppercase tracking-wider border border-blue-100">
                                        <i class="fa-solid fa-user-shield mr-1.5"></i> {{ $announcement->author_name }}
                                    </div>
                                    <span class="text-slate-200 text-xs">•</span>
                                    <div class="flex items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        <i class="fa-regular fa-clock mr-1.5"></i> {{ $announcement->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>

                            @if($announcement->image)
                                <div class="mb-6 rounded-3xl overflow-hidden border border-slate-100 bg-slate-50">
                                    @php
                                        $extension = strtolower(pathinfo($announcement->image, PATHINFO_EXTENSION));
                                        $finalPath = \Illuminate\Support\Facades\Storage::disk('s3')->url($announcement->image);
                                    @endphp

                                    @if(in_array($extension, ['mp4', 'mov', 'avi', 'wmv']))
                                        <video controls class="w-full max-h-[500px] bg-black">
                                            <source src="{{ $finalPath }}" type="video/{{ $extension === 'mov' ? 'quicktime' : 'mp4' }}">
                                        </video>
                                    @else
                                        <img src="{{ $finalPath }}" class="w-full h-auto max-h-[600px] object-cover hover:scale-105 transition-transform duration-700" alt="Broadcast Media">
                                    @endif
                                </div>
                            @endif

                            <p class="text-slate-600 text-sm md:text-lg leading-relaxed whitespace-pre-wrap font-medium">{{ $announcement->content }}</p>
                        </div>
                    @empty
                        <div class="py-24 flex flex-col items-center justify-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                            <div class="relative mb-8">
                                <div class="w-48 h-48 bg-blue-50 rounded-full flex items-center justify-center relative overflow-hidden">
                                    <i class="fa-solid fa-newspaper text-8xl text-blue-100 absolute -bottom-4"></i>
                                    <i class="fa-solid fa-search text-6xl text-blue-200 absolute opacity-50 -right-2 top-4 rotate-12"></i>
                                </div>
                                <div class="absolute -top-4 -right-2 w-16 h-16 bg-white rounded-2xl shadow-xl flex items-center justify-center">
                                    <i class="fa-solid fa-ghost text-slate-300 text-2xl animate-bounce"></i>
                                </div>
                            </div>
                            
                            <h3 class="text-3xl font-black text-slate-800 tracking-tight mb-2">No News Yet</h3>
                            <p class="text-slate-500 font-medium text-center max-w-xs px-6">
                                The bulletin board is currently empty. Check back later for important school updates!
                            </p>
                            
                            <div class="mt-10 flex gap-4">
                                <div class="px-6 py-2 bg-slate-50 rounded-full text-[10px] font-black text-slate-400 uppercase tracking-widest border border-slate-100">
                                    #AllCaughtUp
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

        </main>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const welcomeBanner = document.getElementById('welcome-banner');
        
        // Check kung may record na sa Local Storage
        const hasVisited = localStorage.getItem('fmnhs_dashboard_visited');

        if (!hasVisited) {
            // Kung wala pang record, ipakita ang banner
            welcomeBanner.classList.remove('hidden');
            
            // I-save sa local storage para sa susunod na visit
            localStorage.setItem('fmnhs_dashboard_visited', 'true');
        } else {
            // Kung may record na, siguraduhing nakatago ang banner (optional dahil naka-hidden na sa HTML)
            welcomeBanner.style.display = 'none';
        }
    });
</script>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>