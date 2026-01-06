<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Hub | Teacher Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="glass-header border-b border-slate-200/60 sticky top-0 z-40 px-8 py-5 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex flex-col">
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Faculty Hub</h2>
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-[0.2em]">Academic Management</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-900 leading-none mb-1">
                        {{ Auth::guard('teacher')->user()->first_name }} {{ Auth::guard('teacher')->user()->last_name }}
                    </p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        {{ Auth::guard('teacher')->user()->department ?? 'Academic Faculty' }}
                    </p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-lg shadow-emerald-100 border-2 border-white">
                    {{ substr(Auth::guard('teacher')->user()->first_name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-[2.5rem] p-10 md:p-12 text-white shadow-2xl shadow-emerald-100 mb-12 relative overflow-hidden">
                <div class="relative z-10">
                    <span class="bg-white/20 backdrop-blur-md px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest mb-6 inline-block">S.Y. 2025-2026</span>
                    <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight leading-tight">
                        Good Day, Teacher {{ Auth::guard('teacher')->user()->last_name }}! 🧑‍🏫
                    </h1>
                    <p class="opacity-90 text-sm md:text-lg font-medium max-w-lg leading-relaxed">
                        Ready to inspire your students today? Here's an overview of your current academic assignments.
                    </p>
                </div>
                <i class="fa-solid fa-chalkboard-user absolute right-[-40px] bottom-[-40px] text-[16rem] opacity-10 rotate-12"></i>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-2xl hover:shadow-emerald-100/50 transition-all duration-500 group">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-[1.5rem] flex items-center justify-center text-2xl shadow-inner group-hover:bg-blue-600 group-hover:text-white transition-all">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest mt-2">Active handled</span>
                    </div>
                    <div>
                        <h3 class="text-4xl font-black text-slate-900 tracking-tighter mb-1">{{ $totalClasses }}</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Teaching Loads</p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-50 flex items-center gap-2 text-blue-500">
                        <i class="fa-solid fa-circle-info text-[10px]"></i>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Subject-Section Pairs</span>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-2xl hover:shadow-emerald-100/50 transition-all duration-500 group">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-[1.5rem] flex items-center justify-center text-2xl shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest mt-2">Total Managed</span>
                    </div>
                    <div>
                        <h3 class="text-4xl font-black text-slate-900 tracking-tighter mb-1">{{ $totalStudents }}</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Enrolled Students</p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-50 flex items-center gap-2 text-indigo-500">
                        <i class="fa-solid fa-users-viewfinder text-[10px]"></i>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Across Handle Sections</span>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-2xl hover:shadow-emerald-100/50 transition-all duration-500 group">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-[1.5rem] flex items-center justify-center text-2xl shadow-inner group-hover:bg-emerald-600 group-hover:text-white transition-all">
                            <i class="fa-solid fa-award"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest mt-2">Designation</span>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-emerald-600 tracking-tight mb-1">{{ $advisoryClass }}</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Advisory Assignment</p>
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-50 flex items-center gap-2 text-emerald-500">
                        <i class="fa-solid fa-shield-check text-[10px]"></i>
                        <span class="text-[10px] font-black uppercase tracking-tighter">Verified by Registrar</span>
                    </div>
                </div>
            </div>

            @if(isset($recentAnnouncements))
            <div class="mt-16">
                <div class="flex items-center justify-between mb-10 px-4">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-10 bg-emerald-600 rounded-full"></div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Recent Broadcasts</h2>
                    </div>
                    <a href="{{ route('teacher.announcements.index') }}" class="text-[10px] font-black text-slate-400 hover:text-emerald-600 transition-colors uppercase tracking-[0.2em] bg-white px-5 py-2.5 rounded-2xl border border-slate-100 shadow-sm">View All</a>
                </div>
                
                <div class="grid grid-cols-1 gap-8">
                    @forelse($recentAnnouncements as $announcement)
                    <div class="bg-white rounded-[3rem] border border-slate-200/60 shadow-sm hover:shadow-2xl transition-all duration-500 overflow-hidden group">
                        <div class="p-8 md:p-10">
                            <div class="flex justify-between items-start mb-8">
                                <div class="flex items-center gap-5">
                                    <div class="w-14 h-14 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0 group-hover:rotate-12 transition-transform shadow-sm">
                                        <i class="fa-solid fa-bullhorn text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-xl md:text-2xl text-slate-900 group-hover:text-emerald-600 transition-colors tracking-tight">
                                            {{ $announcement->title }}
                                        </h4>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg uppercase tracking-widest">OFFICIAL BOARD</span>
                                            <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $announcement->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($announcement->image)
                                <div class="rounded-[2rem] overflow-hidden border border-slate-100 bg-slate-50 max-h-[500px] mb-8 shadow-inner">
                                    @php
                                        $extension = strtolower(pathinfo($announcement->image, PATHINFO_EXTENSION));
                                        $finalPath = \Illuminate\Support\Facades\Storage::disk('s3')->url($announcement->image);
                                    @endphp

                                    @if(in_array($extension, ['mp4', 'mov', 'avi', 'wmv']))
                                        <video controls class="w-full h-full object-contain bg-black">
                                            <source src="{{ $finalPath }}" type="video/{{ $extension === 'mov' ? 'quicktime' : 'mp4' }}">
                                        </video>
                                    @else
                                        <img src="{{ $finalPath }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-1000" alt="Announcement Media">
                                    @endif
                                </div>
                            @endif

                            <div class="relative pl-8 border-l-4 border-emerald-100 group-hover:border-emerald-500 transition-all duration-500">
                                <p class="text-slate-600 leading-relaxed whitespace-pre-wrap font-medium text-base md:text-lg">
                                    {{ $announcement->content }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-24 flex flex-col items-center justify-center bg-white rounded-[4rem] border-2 border-dashed border-slate-200 shadow-inner">
                        <div class="relative mb-10">
                            <div class="w-48 h-48 bg-emerald-50 rounded-full flex items-center justify-center relative overflow-hidden">
                                <i class="fa-solid fa-mug-hot text-8xl text-emerald-100 absolute -bottom-4"></i>
                                <i class="fa-solid fa-face-smile-beam text-7xl text-emerald-200 opacity-40 absolute -right-4 top-6 rotate-12"></i>
                            </div>
                        </div>
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-2 uppercase tracking-widest">Quiet on the board</h3>
                        <p class="text-slate-400 font-medium text-center max-w-xs px-6">
                            No new school updates as of the moment. Have a wonderful day teaching!
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endif

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>