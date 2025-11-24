<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600"><i class="fa-solid fa-bars text-xl"></i></button>
            <h2 class="text-xl font-bold text-blue-600">Account Settings</h2>
            <div class="flex items-center gap-3"><span class="font-bold">Student</span></div>
        </header>

        <main class="flex-1 p-6">

            @if(session('success'))
                <script>Swal.fire({icon: 'success', title: 'Updated!', text: "{{ session('success') }}", timer: 1500, showConfirmButton: false});</script>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded mb-4 border border-red-400">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="max-w-4xl mx-auto">
                <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 text-center">
                            <h3 class="font-bold text-lg mb-4">Profile Picture</h3>
                            
                            <div class="relative w-32 h-32 mx-auto mb-4">
                                @php
                                    // 1. Check if user has an avatar filename saved in DB
                                    $hasAvatar = !empty(Auth::guard('student')->user()->avatar);
                                    
                                    // 2. Define the path relative to the public disk
                                    $avatarPath = 'avatars/' . Auth::guard('student')->user()->avatar;
                                    
                                    // 3. Check if file exists using the Full Namespace
                                    // NOTICE: We added '\Illuminate\Support\Facades\' before Storage
                                    if ($hasAvatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($avatarPath)) {
                                        $avatarSrc = asset('storage/' . $avatarPath);
                                    } else {
                                        // Fallback to Initials
                                        $name = urlencode(Auth::guard('student')->user()->first_name);
                                        $avatarSrc = "https://ui-avatars.com/api/?name={$name}&background=2563eb&color=fff";
                                    }
                                @endphp
                                
                                <img src="{{ $avatarSrc }}" class="w-32 h-32 rounded-full object-cover border-4 border-blue-100 shadow-md">
                            </div>

                            <label class="block">
                                <span class="sr-only">Choose profile photo</span>
                                <input type="file" name="avatar" class="block w-full text-sm text-slate-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100
                                "/>
                            </label>
                            <p class="text-xs text-gray-400 mt-2">JPG, PNG up to 2MB</p>
                        </div>

                        <div class="md:col-span-2 bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700">
                            <h3 class="font-bold text-lg mb-6 border-b pb-2">Security Settings</h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold mb-1 text-gray-500">First Name</label>
                                        <input type="text" value="{{ Auth::guard('student')->user()->first_name }}" disabled class="w-full p-2 bg-gray-100 border rounded cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold mb-1 text-gray-500">Last Name</label>
                                        <input type="text" value="{{ Auth::guard('student')->user()->last_name }}" disabled class="w-full p-2 bg-gray-100 border rounded cursor-not-allowed">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold mb-1 text-gray-500">Email Address</label>
                                    <input type="text" value="{{ Auth::guard('student')->user()->email }}" disabled class="w-full p-2 bg-gray-100 border rounded cursor-not-allowed">
                                </div>

                                <hr class="my-6">

                                <h4 class="font-bold text-blue-600 mb-2">Change Password</h4>
                                
                                <div>
                                    <label class="block text-sm font-bold mb-1">Current Password</label>
                                    <input type="password" name="current_password" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold mb-1">New Password</label>
                                        <input type="password" name="new_password" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold mb-1">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-bold shadow-md transition">
                                    Save Changes
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>