<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>My Assignments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-slate-800 font-sans">

    @include('components.student.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-4 md:px-6 py-4 flex justify-between items-center border-b border-gray-200">
            
            <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 text-gray-600 mr-2">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <h2 class="text-lg md:text-xl font-bold text-blue-600 truncate flex-1">My Tasks</h2>

            <div class="flex items-center gap-3 shrink-0">
        
        @php
            // Define variables for avatar check
            $student = Auth::guard('student')->user();
            $avatarPath = 'avatars/' . $student->avatar;
            $hasAvatar = !empty($student->avatar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($avatarPath);
        @endphp

        <div class="text-right hidden sm:block">
            <p class="text-sm font-bold">{{ $student->first_name }} {{ $student->last_name }}</p>
            <p class="text-xs text-gray-500">Grade {{ $student->grade_level }} - {{ $student->section }}</p>
        </div>

        @if($hasAvatar)
            <img src="{{ asset('storage/' . $avatarPath) }}" 
                 alt="Profile" 
                 class="w-8 h-8 md:w-10 md:h-10 rounded-full object-cover border border-gray-200 shadow-sm">
        @else
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold border border-blue-200 text-sm md:text-base">
                {{ substr($student->first_name, 0, 1) }}
            </div>
        @endif

    </div>
        </header>

        <main class="flex-1 p-4 md:p-6 space-y-4 md:space-y-6">
            
            @foreach($assignments as $asn)
                @php
                    $mySubmission = $asn->submissions->first(); 
                    $isLate = now() > $asn->deadline;
                @endphp

                <div class="bg-white p-4 md:p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 md:gap-6">
                    
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="bg-blue-100 text-blue-700 text-[10px] md:text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">{{ $asn->subject->code }}</span>
                            @if($mySubmission)
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] md:text-xs font-bold px-2 py-1 rounded uppercase"><i class="fa-solid fa-check mr-1"></i>Turned In</span>
                            @elseif($isLate)
                                <span class="bg-red-100 text-red-700 text-[10px] md:text-xs font-bold px-2 py-1 rounded uppercase">Missing / Late</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 text-[10px] md:text-xs font-bold px-2 py-1 rounded uppercase">Assigned</span>
                            @endif
                        </div>
                        
                        <h3 class="text-lg md:text-xl font-bold mb-2 leading-tight">{{ $asn->title }}</h3>
                        <p class="text-gray-600 text-sm mb-4 leading-relaxed">{{ $asn->description }}</p>
                        
                        @if($asn->file_path)
                            <a href="{{ asset('uploads/assignments/'.$asn->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 rounded-lg text-sm font-medium hover:bg-gray-200 transition text-gray-700 w-full md:w-auto justify-center md:justify-start">
                                <i class="fa-solid fa-file-arrow-down text-blue-500"></i> View Material
                            </a>
                        @endif
                        
                        <div class="flex items-center gap-2 mt-4 text-xs text-gray-400 font-mono">
                            <i class="fa-regular fa-clock"></i>
                            <span>Due: {{ \Carbon\Carbon::parse($asn->deadline)->format('M d, h:i A') }}</span>
                        </div>
                    </div>

                    <div class="w-full md:w-80 bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300 shrink-0">
                        @if($mySubmission)
                            <div class="text-center py-2 md:py-4">
                                <div class="w-10 h-10 md:w-12 md:h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2 text-emerald-600 text-lg md:text-xl">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <p class="font-bold text-emerald-600 text-sm md:text-base">Submitted!</p>
                                <a href="{{ asset('uploads/submissions/'.$mySubmission->file_path) }}" class="text-xs text-blue-500 hover:underline block mt-1 py-2">View my work</a>
                            </div>
                        @else
                            <h4 class="font-bold text-sm mb-3 text-gray-600">Your Work</h4>
                            <form action="{{ route('student.assignments.submit') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="assignment_id" value="{{ $asn->id }}">
                                
                                <input type="file" name="file" required class="block w-full text-xs text-slate-500 file:mr-2 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 mb-3 cursor-pointer"/>
                                
                                <button type="submit" class="w-full bg-blue-600 text-white text-sm font-bold py-2.5 rounded-lg hover:bg-blue-700 shadow-sm transition active:scale-95">
                                    Mark as Done
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            @endforeach

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>