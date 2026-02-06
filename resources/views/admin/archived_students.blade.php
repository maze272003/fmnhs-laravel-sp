<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Students | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        
        <header class="bg-white shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center border-b border-slate-100">
            <div class="flex items-center gap-4">
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-50 text-slate-500">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-box-archive text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Archived Students</h2>
                </div>
            </div>
            @include('components.admin.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10">
            
            @if(session('success'))
                <script>
                    Swal.fire({ icon: 'success', title: 'Action Successful', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false });
                </script>
            @endif

            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Archived Student Records</h1>
                    <p class="text-slate-500 font-medium">These students have been archived and are not visible in active enrollment lists.</p>
                </div>
                <a href="{{ route('admin.students.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Back to Active Students
                </a>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">LRN</th>
                                <th class="px-6 py-5">Student Information</th>
                                <th class="px-6 py-5">Last Section</th>
                                <th class="px-6 py-5">Archived On</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($students as $student)
                            <tr class="hover:bg-amber-50/30 transition-colors group">
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-600 font-mono text-[11px] font-black rounded-lg border border-slate-200">
                                        {{ $student->lrn }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-xs shadow-sm">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 tracking-tight">{{ $student->last_name }}, {{ $student->first_name }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ $student->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($student->section)
                                        <span class="text-xs font-bold text-slate-700">Grade {{ $student->section->grade_level }} - {{ $student->section->name }}</span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-xs font-bold text-slate-500">{{ $student->deleted_at->format('M d, Y') }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center items-center gap-2">
                                        <form action="{{ route('admin.students.restore', $student->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-all shadow-sm font-bold text-xs">
                                                <i class="fa-solid fa-rotate-left mr-1"></i> Restore
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-solid fa-box-open text-4xl text-slate-200 mb-4"></i>
                                        <p class="text-slate-400 font-bold">No archived students found.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $students->links() }} 
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
