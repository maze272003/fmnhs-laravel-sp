<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Account Settings | Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-200/60 shadow-sm">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Account Settings</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Manage Profile & Security</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                @php $student = Auth::guard('student')->user(); @endphp
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-black text-slate-800 leading-none mb-1">{{ $student->first_name }} {{ $student->last_name }}</p>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">
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

        <main class="flex-1 p-6 lg:p-10 max-w-5xl mx-auto w-full">

            @if(session('success'))
                <script>Swal.fire({icon: 'success', title: 'Updated!', text: "{{ session('success') }}", showConfirmButton: false, timer: 1500, borderRadius: '24px'});</script>
            @endif

            @if ($errors->any())
                <div class="mb-8 bg-rose-50 text-rose-700 p-4 rounded-2xl border border-rose-100 text-xs font-bold">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-10">
                <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-2">My Profile</h1>
                <p class="text-slate-500 font-medium">Personalize your portal identity and keep your account secure.</p>
            </div>

            <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <div class="lg:col-span-4 space-y-6">
                        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 text-center relative overflow-hidden group">
                            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                            
                            <h3 class="font-black text-xs text-slate-400 uppercase tracking-[0.2em] mb-6">Display Avatar</h3>
                            
                            <div class="relative w-36 h-36 mx-auto mb-6">
                                <img src="{{ 
                                        ($student->avatar && $student->avatar !== 'default.png') 
                                        ? (Str::startsWith($student->avatar, 'http') ? $student->avatar : \Illuminate\Support\Facades\Storage::disk('s3')->url('avatars/' . $student->avatar)) 
                                        : 'https://ui-avatars.com/api/?name=' . urlencode($student->first_name . '+' . $student->last_name) . '&background=0D8ABC&color=fff&size=512'
                                     }}" 
                                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff';"
                                     class="w-full h-full rounded-[2.5rem] object-cover border-4 border-white shadow-2xl group-hover:scale-105 transition-transform duration-500">
                                
                                <label class="absolute -bottom-2 -right-2 w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center cursor-pointer hover:bg-indigo-600 transition-colors shadow-lg border-2 border-white">
                                    <i class="fa-solid fa-camera text-xs"></i>
                                    <input type="file" name="avatar" class="hidden" accept=".jpg,.jpeg,.png" onchange="this.form.submit()"/>
                                </label>
                            </div>

                            @if($student->avatar && $student->avatar !== 'default.png')
                            <div class="mt-2">
                                <button type="button" onclick="confirmRemoveAvatar()" class="text-[10px] font-bold text-rose-500 hover:text-rose-700 uppercase tracking-widest transition-colors">
                                    <i class="fa-solid fa-trash-can mr-1"></i> Remove Photo
                                </button>
                            </div>
                            @endif

                            <div class="space-y-1 mt-6">
                                <h4 class="font-black text-xl text-slate-900">{{ $student->first_name }}</h4>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Learner ID: {{ $student->lrn }}</p>
                            </div>

                            <div class="mt-8 pt-8 border-t border-slate-50 grid grid-cols-2 gap-4">
                                <div class="text-center">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-tighter">Level</p>
                                    <p class="text-sm font-black text-indigo-600">Grade {{ $student->section->grade_level }}</p>
                                </div>
                                <div class="text-center border-l border-slate-50">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-tighter">Section</p>
                                    <p class="text-sm font-black text-indigo-600">{{ $student->section->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-indigo-600 p-6 rounded-[2rem] text-white shadow-xl shadow-indigo-100 relative overflow-hidden">
                            <i class="fa-solid fa-user-tie absolute -right-4 -bottom-4 text-7xl opacity-10"></i>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-60 mb-2">Class Advisor</p>
                            <h4 class="text-lg font-black tracking-tight">
                                {{ $student->section->advisor ? 'Mr/Ms. ' . $student->section->advisor->last_name : 'No Advisor Assigned' }}
                            </h4>
                        </div>
                    </div>

                    <div class="lg:col-span-8 space-y-6">
                        <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-sm border border-slate-100">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-address-card"></i>
                                </div>
                                <h3 class="font-black text-lg text-slate-800 tracking-tight">Official Information</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Full Legal Name</label>
                                    <div class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-500 font-bold text-sm">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email Address</label>
                                    <div class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-500 font-bold text-sm">
                                        {{ $student->email }}
                                    </div>
                                </div>
                            </div>
                            <p class="mt-6 text-[10px] text-slate-400 font-medium italic">
                                <i class="fa-solid fa-circle-info mr-1"></i> Official information can only be updated by the Registrar's Office.
                            </p>
                        </div>

                        <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-8 h-8 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <h3 class="font-black text-lg text-slate-800 tracking-tight">Security & Credentials</h3>
                            </div>

                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Current Password</label>
                                    <input type="password" name="current_password" 
                                           class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-semibold text-slate-700" 
                                           placeholder="••••••••">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">New Password</label>
                                        <input type="password" name="new_password" 
                                               class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-semibold text-slate-700" 
                                               placeholder="Minimum 8 characters">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Confirm New Password</label>
                                        <input type="password" name="new_password_confirmation" 
                                               class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-semibold text-slate-700" 
                                               placeholder="Re-type new password">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-10 flex justify-end">
                                <button type="submit" class="w-full md:w-auto bg-slate-900 text-white px-10 py-4 rounded-2xl hover:bg-indigo-600 font-black shadow-xl shadow-slate-200 transition-all active:scale-95 text-xs uppercase tracking-widest">
                                    Update Security Settings
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
            
            <form id="removeAvatarForm" action="{{ route('student.profile.removeAvatar') }}" method="POST" style="display: none;">
                @csrf 
                @method('DELETE')
            </form>

        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function confirmRemoveAvatar() {
            Swal.fire({
                title: 'Remove Profile Picture?',
                text: 'Your profile picture will be reset to the default avatar.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Yes, Remove'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('removeAvatarForm').submit();
                }
            });
        }
    </script>
</body>
</html>