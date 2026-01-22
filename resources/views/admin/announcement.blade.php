<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communications Hub | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40 px-8 py-5 flex justify-between items-center border-b border-slate-200/60">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
                <div class="flex flex-col">
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Communications Hub</h2>
                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-[0.2em]">Institutional Broadcaster</p>
                </div>
            </div>
            @include('components.admin.header_details')
            
            {{-- <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col text-right mr-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Server Status</span>
                    <span class="text-[10px] font-black text-emerald-500 flex items-center justify-end gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> LIVE
                    </span>
                </div>
                <div class="h-10 w-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold shadow-lg shadow-slate-200">
                    <i class="fa-solid fa-tower-broadcast text-xs"></i>
                </div>
            </div> --}}
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            
            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Broadcast Sent', text: "{{ session('success') }}", showConfirmButton: false, timer: 2500, borderRadius: '24px' });
                </script>
            @endif

            @if(session('error'))
                <script>
                    Swal.fire({ icon: 'error', title: 'Access Denied', text: "{{ session('error') }}", showConfirmButton: true, confirmButtonColor: '#0f172a', borderRadius: '24px' });
                </script>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-5 xl:col-span-4">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-indigo-100/50 border border-slate-200/50 sticky top-28">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                                <i class="fa-solid fa-pen-nib text-sm"></i>
                            </div>
                            <h3 class="font-black text-lg text-slate-800 tracking-tight">Compose Update</h3>
                        </div>
                        
                        <form action="{{ route('admin.announcements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Headline</label>
                                <input type="text" name="title" required 
                                       class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-semibold text-slate-700" 
                                       placeholder="Enter catchy headline...">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Announcement Details</label>
                                <textarea name="content" rows="5" required 
                                          class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-slate-600 leading-relaxed custom-scrollbar" 
                                          placeholder="Describe the update in detail..."></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Media Attachment</label>
                                <div class="relative group cursor-pointer">
                                    <div class="flex items-center justify-center w-full h-32 border-2 border-dashed border-slate-200 rounded-3xl group-hover:border-indigo-400 group-hover:bg-indigo-50/30 transition-all">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fa-solid fa-cloud-arrow-up text-slate-300 group-hover:text-indigo-500 transition-colors text-2xl"></i>
                                            <span class="text-[10px] font-bold text-slate-400 group-hover:text-indigo-600 uppercase tracking-widest">Click to upload media</span>
                                        </div>
                                    </div>
                                    <input type="file" name="image" accept="image/*,video/*" class="absolute inset-0 opacity-0 cursor-pointer"/>
                                </div>
                                <div class="flex items-center gap-2 px-2">
                                    <i class="fa-solid fa-circle-info text-[10px] text-indigo-400"></i>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Supports JPG, PNG, MP4 (Max 40MB)</p>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-slate-900 text-white font-black py-4 rounded-2xl hover:bg-indigo-600 shadow-xl shadow-slate-200 hover:shadow-indigo-200 transition-all active:scale-[0.98] flex items-center justify-center gap-3 group">
                                <span class="tracking-tight">Broadcast to Students</span>
                                <i class="fa-solid fa-paper-plane text-[10px] group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-7 xl:col-span-8 space-y-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <h3 class="font-black text-2xl text-slate-900 tracking-tight">Recent Activity</h3>
                            <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1 rounded-full border border-indigo-100 uppercase tracking-widest">
                                {{ $announcements->total() }} Posts
                            </span>
                        </div>
                    </div>

                    <div class="space-y-8">
                        @forelse($announcements as $post)
                            <div class="bg-white rounded-[2.5rem] border border-slate-200/60 shadow-sm hover:shadow-2xl hover:shadow-indigo-100/40 transition-all duration-500 overflow-hidden group">
                                <div class="p-8">
                                    <div class="flex justify-between items-start mb-6">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                                                <i class="fa-solid fa-user-shield text-lg"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-extrabold text-xl text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors">{{ $post->title }}</h4>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $post->author_name }}</span>
                                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $post->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('admin.announcements.destroy', $post) }}" method="POST" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="delete-btn w-10 h-10 flex items-center justify-center rounded-xl text-slate-300 hover:bg-rose-50 hover:text-rose-500 transition-all">
                                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="relative pl-6 border-l-4 border-slate-100 group-hover:border-indigo-500 transition-all duration-300 mb-8">
                                        <p class="text-slate-600 leading-relaxed font-medium whitespace-pre-wrap">
                                            {{ $post->content }}
                                        </p>
                                    </div>

                                    @if($post->image)
                                        <div class="rounded-[2rem] overflow-hidden border border-slate-100 bg-slate-50 relative group/media max-h-[500px] shadow-inner">
                                            @php
                                                $extension = strtolower(pathinfo($post->image, PATHINFO_EXTENSION));
                                                $finalPath = \Illuminate\Support\Facades\Storage::disk('s3')->url($post->image);
                                            @endphp

                                            @if(in_array($extension, ['mp4', 'mov', 'avi', 'wmv']))
                                                <video controls class="w-full h-full bg-black">
                                                    <source src="{{ $finalPath }}" type="video/{{ $extension === 'mov' ? 'quicktime' : 'mp4' }}">
                                                </video>
                                            @else
                                                <img src="{{ $finalPath }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-1000" alt="Media Content">
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                                <i class="fa-solid fa-box-open text-5xl text-slate-200 mb-4"></i>
                                <h3 class="text-slate-400 font-bold uppercase tracking-widest">No broadcasts found</h3>
                            </div>
                        @endforelse
                        
                        <div class="py-6">
                            {{ $announcements->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Retract Broadcast?',
                    text: "This will remove the announcement for all students.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0f172a',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Yes, Delete',
                    borderRadius: '24px'
                }).then((result) => {
                    if (result.isConfirmed) this.closest('form').submit();
                });
            });
        });
    </script>
</body>
</html>
