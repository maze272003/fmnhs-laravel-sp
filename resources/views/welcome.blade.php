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
        .modal-enter {
            opacity: 0;
            transform: scale(0.9);
        }
        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 0.3s, transform 0.3s;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen font-sans text-slate-800">

    <nav class="bg-white/90 backdrop-blur-sm shadow-sm border-b border-gray-200 fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-transparent rounded-lg flex items-center justify-center shadow-none">
                        <img src="{{ asset('images/fmnhs.png') }}" alt="Logo" class="w-full h-full object-cover rounded-lg">
                    </div>
                    <div>
                        <span class="block font-bold text-xl tracking-tight text-slate-900 leading-none">FORT-i-FYI</span>
                        <span class="block text-xs text-green-600 font-bold uppercase tracking-wider">High School</span>
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
                <span class="bg-green-100 text-black-500 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-4 inline-block">
                    Research System For education only
                </span>
                <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 mb-6 tracking-tight">
                   Welcome to the
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-green-400">
                    FORT-i-FYI
                    </span>
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Your centralized platform for academic records, class management, and school updates. Please select your specific role below to access the system.
                </p>
            </div>

            <div class="text-center mb-20">
                <button id="openPortalModal" 
                        class="bg-green-600 text-white font-bold py-3 px-8 rounded-full text-lg shadow-xl hover:bg-green-700 transition duration-300 transform hover:scale-105">
                    <i class="fa-solid fa-graduation-cap mr-3"></i> Access Portal by Role
                </button>
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
                                    <div class="w-full h-48 overflow-hidden relative group bg-gray-100">
                                        @php
                                            // 1. Get Extension
                                            $extension = strtolower(pathinfo($news->image, PATHINFO_EXTENSION));

                                            // 2. Generate S3 URL (Ito ang fix)
                                            // Kinukuha nito ang direct link galing sa S3 bucket mo
                                            $finalPath = \Illuminate\Support\Facades\Storage::disk('s3')->url($news->image);
                                        @endphp

                                        {{-- LOGIC: Check if Video or Image --}}
                                        @if(in_array($extension, ['mp4', 'mov', 'avi', 'wmv']))
                                            <video controls class="w-full h-full object-cover bg-black">
                                                <source src="{{ $finalPath }}" type="video/{{ $extension === 'mov' ? 'quicktime' : 'mp4' }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        @else
                                            <img src="{{ $finalPath }}" 
                                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                                alt="{{ $news->title }}"
                                                onerror="this.style.display='none'">
                                        @endif

                                        <div class="absolute top-2 right-2 pointer-events-none">
                                            @if($news->role == 'admin')
                                                <span class="bg-slate-900/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider shadow">
                                                    <i class="fa-solid fa-shield-halved mr-1"></i> Admin
                                                </span>
                                            @else
                                                <span class="bg-green-600/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider shadow">
                                                    <i class="fa-solid fa-chalkboard-user mr-1"></i> Faculty
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="w-full h-2 bg-gradient-to-r {{ $news->role == 'admin' ? 'from-slate-600 to-slate-800' : 'from-green-400 to-green-600' }}"></div>

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
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white shadow-sm {{ $news->role == 'admin' ? 'bg-slate-700' : 'bg-green-500' }}">
                                            {{ substr($news->author_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-500">Posted by</p>
                                            <p class="text-xs font-bold {{ $news->role == 'admin' ? 'text-slate-900' : 'text-green-600' }}">
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

        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-8 mt-auto">
    <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4">
        
        <div class="text-sm text-gray-500 flex items-center gap-2">
            <span class="font-medium text-slate-900">Powered by</span> 
            <a href="#" class="font-bold text-green-600 hover:text-green-700 transition-colors">
                Dokploy X JMDev👩‍💻
            </a>
        </div>
        
        <div class="text-center">
            <p class="text-slate-900 font-bold mb-2">Fort Magsaysay National High School</p>
            <p class="text-sm text-gray-500">
                &copy; {{ date('Y') }} All rights reserved. <br>
                <span class="text-xs opacity-75">Student Information System v1.0</span>
            </p>
        </div>

        <div class="hidden md:block w-[140px]"></div>
    </div>
</footer>

    <div id="portalModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] p-4" onclick="closeModal(event)">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm modal-enter" id="modalContent">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 mb-6">
                <h3 class="text-xl font-bold text-slate-800"><i class="fa-solid fa-user-check mr-2"></i> Select Your Role</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-sm text-gray-600 mb-6">Please identify your role to proceed to the correct login page:</p>

            <div class="space-y-4">
                <a href="{{ route('login') }}" class="block w-full text-center bg-green-100 text-green-800 font-semibold py-3 rounded-lg hover:bg-green-200 transition duration-150 border-b-4 border-green-500 hover:border-green-600">
                    <i class="fa-solid fa-user-graduate mr-2"></i> Student
                </a>
                <a href="{{ route('teacher.login') }}" class="block w-full text-center bg-green-100 text-green-800 font-semibold py-3 rounded-lg hover:bg-green-200 transition duration-150 border-b-4 border-green-500 hover:border-green-600">
                    <i class="fa-solid fa-chalkboard-user mr-2"></i> Faculty
                </a>
                <a href="{{ route('admin.login') }}" class="block w-full text-center bg-slate-100 text-slate-800 font-semibold py-3 rounded-lg hover:bg-slate-200 transition duration-150 border-b-4 border-green-500 hover:border-green-600">
                    <i class="fa-solid fa-shield-halved mr-2"></i> Admin
                </a>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('portalModal');
        const modalContent = document.getElementById('modalContent');
        const openButton = document.getElementById('openPortalModal');

        openButton.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.add('modal-enter-active');
                modalContent.classList.remove('modal-enter');
            }, 50);
        });

        function closeModal(event) {
            if (event && event.target.id === 'portalModal') {
                // Clicked outside
            } else if (event) {
                // Clicked inside
            }

            modalContent.classList.remove('modal-enter-active');
            modalContent.classList.add('modal-enter');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }

        modal.addEventListener('click', (e) => {
            if (e.target.id === 'portalModal') {
                closeModal();
            }
        });
    </script>

</body>
</html>