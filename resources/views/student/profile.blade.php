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
<body class="bg-gray-50 font-sans text-slate-800">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-gray-200">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600 mr-2">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            
            <h2 class="text-lg md:text-xl font-bold text-blue-600 truncate flex-1">Account Settings</h2>
            
            <div class="flex items-center gap-3 shrink-0">
                
                {{-- HELPER USED: Logic is now inside Student Model (avatar_url) --}}
                @php $student = Auth::guard('student')->user(); @endphp

                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-xs text-gray-500">Grade {{ $student->grade_level }} - {{ $student->section }}</p>
                </div>

                {{-- Since avatar_url always returns a valid link (S3 or UI Avatars), we always show the IMG tag --}}
                <img src="{{ $student->avatar_url }}" 
                     alt="Profile" 
                     class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover border border-gray-200 shadow-sm">

            </div>
        </header>

        <main class="flex-1 p-4 md:p-6">

            @if(session('success'))
                <script>Swal.fire({icon: 'success', title: 'Updated!', text: "{{ session('success') }}", timer: 1500, showConfirmButton: false});</script>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 border border-red-200 shadow-sm text-sm">
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
                        
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 text-center h-fit">
                            <h3 class="font-bold text-lg mb-4 text-slate-800">Profile Picture</h3>
                            
                            <div class="relative w-32 h-32 mx-auto mb-4 group">
                                {{-- HELPER USED: Clean access to avatar_url --}}
                                <img src="{{ Auth::guard('student')->user()->avatar_url }}" 
                                     class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                            </div>

                            <label class="block w-full">
                                <span class="sr-only">Choose profile photo</span>
                                <input type="file" name="avatar" class="block w-full text-xs text-slate-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-xs file:font-bold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100
                                cursor-pointer
                                "/>
                            </label>
                            <p class="text-[10px] text-gray-400 mt-3">JPG, PNG up to 15MB</p>
                        </div>

                        <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                            <h3 class="font-bold text-lg mb-6 border-b border-gray-100 pb-4 text-slate-800">Security Settings</h3>
                            
                            <div class="space-y-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold mb-1.5 text-gray-500 uppercase tracking-wide">First Name</label>
                                        <input type="text" value="{{ Auth::guard('student')->user()->first_name }}" disabled class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold mb-1.5 text-gray-500 uppercase tracking-wide">Last Name</label>
                                        <input type="text" value="{{ Auth::guard('student')->user()->last_name }}" disabled class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold mb-1.5 text-gray-500 uppercase tracking-wide">Email Address</label>
                                    <input type="text" value="{{ Auth::guard('student')->user()->email }}" disabled class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-lg text-gray-500 cursor-not-allowed text-sm">
                                </div>

                                <div class="border-t border-gray-100 my-6"></div>

                                <h4 class="font-bold text-blue-600 mb-4 flex items-center gap-2">
                                    <i class="fa-solid fa-lock text-sm"></i> Change Password
                                </h4>
                                
                                <div>
                                    <label class="block text-sm font-bold mb-1.5 text-slate-700">Current Password</label>
                                    <input type="password" name="current_password" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm">
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold mb-1.5 text-slate-700">New Password</label>
                                        <input type="password" name="new_password" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold mb-1.5 text-slate-700">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 font-bold shadow-sm transition active:scale-95 text-sm">
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