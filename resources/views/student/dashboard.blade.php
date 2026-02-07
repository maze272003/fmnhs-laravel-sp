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
                    <div class="w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center shadow-lg shadow-indigo-100">
                        <i class="fa-solid fa-shapes text-[10px]"></i>
                    </div>
                    <h2 class="text-lg md:text-xl font-black text-slate-800 tracking-tight truncate">Student Portal</h2>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">{{ $student->first_name }} {{ $student->last_name }}</p>
                    {{-- UPDATED: Accessing grade and section name via relationship --}}
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Grade {{ $student->section->grade_level }} - {{ $student->section->name }}
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
            
            {{-- Welcome Banner --}}
            <div id="welcome-banner" class="hidden bg-gradient-to-br from-indigo-600 to-blue-700 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl shadow-indigo-100 mb-10 relative overflow-hidden">
                <div class="relative z-10">
                    <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">Hello, {{ $student->first_name }}! 👋</h1>
                    <p class="opacity-90 text-sm md:text-lg font-medium max-w-md leading-relaxed">Welcome back to your portal. Here's a quick overview of your academic status today.</p>
                </div>
                <i class="fa-solid fa-graduation-cap absolute right-[-30px] bottom-[-30px] text-[15rem] opacity-10 rotate-12"></i>
            </div>

            {{-- Quick Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-12">
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 group hover:shadow-xl transition-all duration-500">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Current Section</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-lg shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        {{-- UPDATED: Accessing section name --}}
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ $student->section->name }}</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 group hover:shadow-xl transition-all duration-500">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Class Adviser</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-lg shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        {{-- UPDATED: Accessing Advisor name via relationship --}}
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">
                            {{ $student->section->advisor ? 'Mr/Ms. ' . $student->section->advisor->last_name : 'No Adviser' }}
                        </h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 group hover:shadow-xl transition-all duration-500">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Portal Status</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-lg shadow-inner animate-pulse">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <h3 class="text-xl font-black text-emerald-600 tracking-tight">Enrolled</h3>
                    </div>
                </div>
            </div>

            {{-- Bulletin Board --}}
            <div>
                <div class="flex items-center justify-between mb-8 px-2">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-8 bg-indigo-600 rounded-full"></div>
                        <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">Bulletin Board</h3>
                    </div>
                    <span class="text-[10px] font-black text-slate-400 bg-white px-4 py-2 rounded-full border border-slate-100 uppercase tracking-widest shadow-sm">Official Updates</span>
                </div>

                <div class="space-y-8">
                    @forelse($announcements as $announcement)
                        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-2xl hover:border-indigo-100 transition-all duration-500 group overflow-hidden">
                            
                            <div class="flex flex-col gap-1 mb-6 border-b border-slate-50 pb-6">
                                <h4 class="font-black text-xl md:text-3xl text-slate-800 group-hover:text-indigo-700 transition-colors leading-tight mb-3">
                                    {{ $announcement->title }}
                                </h4>
                                <div class="flex flex-wrap items-center gap-4">
                                    <div class="flex items-center text-[10px] font-black text-indigo-600 bg-indigo-50 px-4 py-1.5 rounded-xl uppercase tracking-widest border border-indigo-100 shadow-sm">
                                        <i class="fa-solid fa-user-shield mr-2"></i> {{ $announcement->author_name }}
                                    </div>
                                    <div class="flex items-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        <i class="fa-regular fa-calendar-check mr-2"></i> {{ $announcement->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </div>

                            @if($announcement->image)
                                <div class="mb-8 rounded-[2rem] overflow-hidden border border-slate-100 bg-slate-50 shadow-inner">
                                    @php
                                        $extension = strtolower(pathinfo($announcement->image, PATHINFO_EXTENSION));
                                        $finalPath = \Illuminate\Support\Facades\Storage::disk('s3')->url($announcement->image);
                                    @endphp

                                    @if(in_array($extension, ['mp4', 'mov', 'avi', 'wmv']))
                                        <video controls class="w-full max-h-[550px] bg-black">
                                            <source src="{{ $finalPath }}" type="video/{{ $extension === 'mov' ? 'quicktime' : 'mp4' }}">
                                        </video>
                                    @else
                                        <img src="{{ $finalPath }}" class="w-full h-auto max-h-[650px] object-cover hover:scale-105 transition-transform duration-1000" alt="Broadcast Media">
                                    @endif
                                </div>
                            @endif

                            <p class="text-slate-600 text-sm md:text-lg leading-relaxed whitespace-pre-wrap font-medium">{{ $announcement->content }}</p>
                        </div>
                    @empty
                        <div class="py-24 flex flex-col items-center justify-center bg-white rounded-[3.5rem] border-2 border-dashed border-slate-200 shadow-inner">
                            <div class="relative mb-8">
                                <div class="w-48 h-48 bg-slate-50 rounded-full flex items-center justify-center relative overflow-hidden">
                                    <i class="fa-solid fa-newspaper text-8xl text-slate-100 absolute -bottom-4"></i>
                                    <i class="fa-solid fa-search text-6xl text-slate-200 absolute opacity-30 -right-2 top-4 rotate-12"></i>
                                </div>
                            </div>
                            
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2 uppercase tracking-widest">Quiet on the Front</h3>
                            <p class="text-slate-400 font-medium text-center max-w-xs px-6 leading-relaxed">
                                No new announcements today. We'll let you know when there's something important!
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

        </main>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const welcomeBanner = document.getElementById('welcome-banner');
        const hasVisited = localStorage.getItem('fmnhs_dashboard_visited');

        if (!hasVisited) {
            welcomeBanner.classList.remove('hidden');
            localStorage.setItem('fmnhs_dashboard_visited', 'true');
        } else {
            welcomeBanner.style.display = 'none';
        }
    });
</script>
<script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>