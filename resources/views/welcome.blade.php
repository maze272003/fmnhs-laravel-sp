<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grand Tech High - School Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8fafc;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen font-sans text-slate-800">

    <nav class="bg-white/90 backdrop-blur-sm shadow-sm border-b border-gray-200 fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-blue-500 rounded-lg flex items-center justify-center text-white font-bold shadow-lg">
                        <i class="fa-solid fa-graduation-cap text-lg"></i>
                    </div>
                    <div>
                        <span class="block font-bold text-xl tracking-tight text-slate-900 leading-none">Grand Tech</span>
                        <span class="block text-xs text-indigo-600 font-bold uppercase tracking-wider">High School</span>
                    </div>
                </div>
                <div class="hidden md:block text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full border border-gray-200">
                    <i class="fa-regular fa-calendar-check mr-1"></i> S.Y. 2024-2025
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow pt-28 pb-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <div class="text-center mb-16">
                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-4 inline-block">
                    Official School System
                </span>
                <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 mb-6 tracking-tight">
                    Welcome to the <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-500">Portal Hub</span>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Your centralized platform for academic records, class management, and school updates. Please select your specific portal below to get started.
                </p>
            </div>

            <div class="mb-20">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-8 bg-red-500 rounded-full"></div>
                    <h2 class="text-2xl font-bold text-slate-800">Latest Updates</h2>
                </div>

                @if(isset($announcements) && $announcements->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($announcements as $news)
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col h-full">
                                
                                @if($news->image)
                                    <div class="w-full h-48 overflow-hidden relative group">
                                        <img src="{{ asset('storage/' . $news->image) }}" 
                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                             alt="Announcement Image"
                                             onerror="this.style.display='none'">
                                        
                                        <div class="absolute top-2 right-2">
                                            @if($news->role == 'admin')
                                                <span class="bg-slate-900/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider shadow">
                                                    <i class="fa-solid fa-shield-halved mr-1"></i> Admin
                                                </span>
                                            @else
                                                <span class="bg-emerald-600/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider shadow">
                                                    <i class="fa-solid fa-chalkboard-user mr-1"></i> Faculty
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="w-full h-2 bg-gradient-to-r {{ $news->role == 'admin' ? 'from-slate-600 to-slate-800' : 'from-emerald-400 to-emerald-600' }}"></div>
                                @endif

                                <div class="p-6 flex flex-col flex-grow">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs text-gray-400 font-medium">
                                            <i class="fa-regular fa-clock mr-1"></i> {{ $news->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    
                                    <h3 class="font-bold text-lg text-slate-800 mb-3 leading-tight">{{ $news->title }}</h3>
                                    <p class="text-gray-600 text-sm mb-6 flex-grow line-clamp-3">
                                        {{ $news->content }}
                                    </p>

                                    <div class="pt-4 border-t border-gray-100 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-sm {{ $news->role == 'admin' ? 'bg-slate-700' : 'bg-emerald-500' }}">
                                            {{ substr($news->author_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-500">Posted by</p>
                                            <p class="text-xs font-bold {{ $news->role == 'admin' ? 'text-slate-900' : 'text-emerald-600' }}">
                                                {{ $news->author_name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border border-gray-200 rounded-xl p-10 text-center opacity-75 shadow-sm">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-regular fa-newspaper text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700">No Announcements</h3>
                        <p class="text-gray-500">Check back later for school updates.</p>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                <a href="{{ route('login') }}" class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-blue-300 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-blue-600 group-hover:h-2 transition-all"></div>
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 text-3xl mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors">Student Portal</h2>
                    <p class="text-gray-500 text-sm mb-6 leading-relaxed">Access your grades, view class schedules, and manage your student profile.</p>
                    <div class="flex items-center text-blue-600 text-sm font-bold uppercase tracking-wide group-hover:gap-2 transition-all">
                        Login <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>

                <a href="{{ route('teacher.login') }}" class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-emerald-300 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 to-emerald-600 group-hover:h-2 transition-all"></div>
                    <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 text-3xl mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300 shadow-inner">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2 group-hover:text-emerald-600 transition-colors">Faculty Portal</h2>
                    <p class="text-gray-500 text-sm mb-6 leading-relaxed">Manage your advisory classes, input student grades, and track academic performance.</p>
                    <div class="flex items-center text-emerald-600 text-sm font-bold uppercase tracking-wide group-hover:gap-2 transition-all">
                        Login <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>

                <a href="{{ route('admin.login') }}" class="group relative bg-white rounded-2xl p-8 border border-gray-200 hover:border-slate-400 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-slate-600 to-slate-800 group-hover:h-2 transition-all"></div>
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-700 text-3xl mb-6 group-hover:bg-slate-800 group-hover:text-white transition-colors duration-300 shadow-inner">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2 group-hover:text-slate-700 transition-colors">Admin Portal</h2>
                    <p class="text-gray-500 text-sm mb-6 leading-relaxed">System configuration, user management, subject encoding, and school data analytics.</p>
                    <div class="flex items-center text-slate-700 text-sm font-bold uppercase tracking-wide group-hover:gap-2 transition-all">
                        Access <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>

            </div>

        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-slate-900 font-bold mb-2">Grand Tech High School</p>
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} All rights reserved. <br>
                <span class="text-xs opacity-75">Student Information System v1.0</span>
            </p>
        </div>
    </footer>

</body>
</html>