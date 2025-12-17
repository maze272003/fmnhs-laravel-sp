<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements | Faculty Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.teacher.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-bullhorn text-sm"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800 tracking-tight">Bulletin Board</h2>
            </div>
            <div class="flex items-center gap-2">
                 <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                    Faculty Broadcaster
                </span>
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">
            
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl mb-6 flex items-center gap-3 shadow-sm animate-fade-in">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            @endif
            
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-100 text-rose-700 px-6 py-4 rounded-2xl mb-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fa-solid fa-circle-exclamation text-xl"></i>
                        <p class="font-bold text-sm">Submission Error</p>
                    </div>
                    <ul class="text-xs font-medium ml-8 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                
                <div class="lg:col-span-4">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 sticky top-24">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                            <h3 class="font-black text-lg text-slate-800 tracking-tight">Post New Update</h3>
                        </div>
                        
                        <form id="announcementForm" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Update Title</label>
                                <input type="text" name="title" id="title" required 
                                    class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-semibold" 
                                    placeholder="e.g. Schedule for Final Exams">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Detailed Content</label>
                                <textarea name="content" id="content" rows="4" required 
                                        class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-semibold" 
                                        placeholder="Share the details with your students..."></textarea>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Attach Media (Optional)</label>
                                <input type="file" name="image" id="image" accept="image/*,video/*" 
                                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-6 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 transition-all cursor-pointer shadow-sm"/>
                            </div>

                            <div id="uploadProgressContainer" class="hidden space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Uploading...</span>
                                    <span id="uploadPercentage" class="text-[10px] font-black text-emerald-600">0%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                    <div id="uploadProgressBar" class="bg-emerald-500 h-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>

                            <button type="submit" id="submitBtn" class="w-full bg-slate-900 text-white font-black py-4 rounded-2xl hover:bg-emerald-600 shadow-xl transition-all flex items-center justify-center gap-3">
                                <span>Publish Post</span>
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-8 space-y-8">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="font-black text-xl text-slate-800 tracking-tight">Recent Broadcasts</h3>
                        <span class="text-xs font-bold text-slate-400 bg-white border border-slate-100 px-4 py-1.5 rounded-full shadow-sm">
                            Real-time Feed
                        </span>
                    </div>

                    <div class="space-y-6">
                        @forelse($announcements as $post)
                            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-300 relative group overflow-hidden">
                                
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shadow-sm">
                                            <i class="fa-solid fa-user-pen"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-black text-xl text-slate-800 leading-none mb-1">{{ $post->title }}</h4>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                                By {{ $post->author_name }} • {{ $post->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <form action="{{ route('teacher.announcements.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete this announcement permanently?')">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-300 hover:bg-rose-50 hover:text-rose-500 transition-all">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>

                                @if($post->image)
                                    <div class="mb-6 rounded-3xl overflow-hidden border border-slate-100 bg-slate-50 relative group/media max-h-[450px]">
                                        @php
                                            $extension = strtolower(pathinfo($post->image, PATHINFO_EXTENSION));
                                            // FETCHING FROM S3 DISK
                                            $finalPath = \Illuminate\Support\Facades\Storage::disk('s3')->url($post->image);
                                        @endphp

                                        @if(in_array($extension, ['mp4', 'mov', 'avi', 'wmv']))
                                            <video controls class="w-full h-full bg-black">
                                                <source src="{{ $finalPath }}" type="video/{{ $extension === 'mov' ? 'quicktime' : 'mp4' }}">
                                            </video>
                                        @else
                                            <img src="{{ $finalPath }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-700" alt="Broadcast Image">
                                        @endif
                                    </div>
                                @endif

                                <div class="relative">
                                    <i class="fa-solid fa-quote-left absolute -top-2 -left-2 text-3xl text-slate-50 group-hover:text-emerald-50 transition-colors"></i>
                                    <p class="text-slate-600 leading-relaxed whitespace-pre-wrap relative z-10 pl-6 border-l-4 border-emerald-100 group-hover:border-emerald-500 transition-all">
                                        {{ $post->content }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="py-20 flex flex-col items-center justify-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
                                <i class="fa-solid fa-folder-open text-5xl text-slate-200 mb-4"></i>
                                <h3 class="text-xl font-black text-slate-800 tracking-tight">No Posts Yet</h3>
                                <p class="text-slate-400 font-medium text-sm">Start broadcasting updates to your students.</p>
                            </div>
                        @endforelse
                        
                        <div class="mt-10 flex justify-center">
                            {{ $announcements->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
    document.getElementById('announcementForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitBtn');
        const progressContainer = document.getElementById('uploadProgressContainer');
        const progressBar = document.getElementById('uploadProgressBar');
        const progressText = document.getElementById('uploadPercentage');

        // Disable button and show progress
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        progressContainer.classList.remove('hidden');

        axios.post("{{ route('teacher.announcements.store') }}", formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress: function(progressEvent) {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                progressBar.style.width = percentCompleted + '%';
                progressText.innerText = percentCompleted + '%';
                
                if (percentCompleted === 100) {
                    progressText.innerText = 'Processing on Server...';
                }
            }
        })
        .then(response => {
            // Success: Refresh page to show new post
            window.location.reload();
        })
        .catch(error => {
            console.error(error);
            alert('Upload failed. Please check file size or connection.');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            progressContainer.classList.add('hidden');
        });
    });
    </script>
</body>
</html>