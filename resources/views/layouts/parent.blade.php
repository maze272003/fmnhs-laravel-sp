<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Parent') | Parent Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans text-gray-800 antialiased">

    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-100">
                        P
                    </div>
                    <span class="font-bold text-lg text-gray-800 tracking-tight">Parent Portal</span>
                </div>

                <div class="flex items-center gap-6">
                    @auth('parent')
                        <a href="{{ route('parent.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">
                            <i class="fa-solid fa-gauge mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('parent.children') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">
                            <i class="fa-solid fa-users mr-1"></i> My Children
                        </a>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-gray-700 hidden sm:inline">{{ auth('parent')->user()->name }}</span>
                            <form action="{{ route('parent.logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-rose-500 hover:text-rose-700 transition-colors">
                                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
