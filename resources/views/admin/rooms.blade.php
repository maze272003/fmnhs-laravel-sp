<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .modal-animate { transition: all 0.3s ease-in-out; }
    </style>
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
                    <div class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-door-open text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Room Management</h2>
                </div>
            </div>
            @include('components.admin.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

            @if(session('success'))
                <script>
                    Swal.fire({icon: 'success', title: 'Success!', text: "{{ session('success') }}", timer: 2000, showConfirmButton: false});
                </script>
            @endif

            @if($errors->any())
                <script>
                    Swal.fire({icon: 'error', title: 'Error', text: "{{ $errors->first() }}"});
                </script>
            @endif

            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Rooms</h1>
                    <p class="text-slate-500 font-medium">Manage classroom and facility assignments.</p>
                </div>
                <div class="flex items-center gap-4">
                    <form action="{{ route('admin.rooms.index') }}" method="GET" class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search rooms..."
                            class="pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm shadow-sm">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </form>
                    <button onclick="openModal('addModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl font-black shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center gap-3">
                        <i class="fa-solid fa-plus-circle"></i> Add Room
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">Room Name</th>
                                <th class="px-6 py-5">Building</th>
                                <th class="px-6 py-5">Capacity</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-8 py-5 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($rooms as $room)
                                <tr class="hover:bg-indigo-50/30 transition-colors group">
                                    <td class="px-8 py-5 font-black text-slate-800">{{ $room->name }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-600">{{ $room->building ?? '-' }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-600">{{ $room->capacity ?? '-' }}</td>
                                    <td class="px-6 py-5 text-center">
                                        @if($room->is_available)
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Available
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-600 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Unavailable
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button onclick="editRoom({{ json_encode($room) }})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-50 text-blue-500 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                                            </button>
                                            <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="delete-btn w-10 h-10 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-20 text-center">
                                        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No rooms found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $rooms->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-10 shadow-2xl modal-animate border border-white">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">New Room</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Add a classroom or facility</p>
                </div>
                <button onclick="closeModal('addModal')" class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('admin.rooms.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Room Name</label>
                    <input type="text" name="name" placeholder="e.g. Room 101" required
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Building</label>
                    <input type="text" name="building" placeholder="e.g. Main Building"
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Capacity</label>
                    <input type="number" name="capacity" placeholder="e.g. 40" min="1"
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-slate-200 hover:bg-indigo-600 transition-all active:scale-95 uppercase text-xs tracking-[0.2em]">
                        Create Room
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-[3rem] w-full max-w-md p-10 shadow-2xl modal-animate border border-white">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">Edit Room</h2>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-2">Update room details</p>
                </div>
                <button onclick="closeModal('editModal')" class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editForm" method="POST" class="space-y-6">
                @csrf @method('PUT')
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Room Name</label>
                    <input type="text" id="edit_name" name="name" required
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Building</label>
                    <input type="text" id="edit_building" name="building"
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Capacity</label>
                    <input type="number" id="edit_capacity" name="capacity" min="1"
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Available</label>
                    <select id="edit_is_available" name="is_available"
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-sm appearance-none">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all active:scale-95 uppercase text-xs tracking-[0.2em]">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }

        function editRoom(room) {
            document.getElementById('edit_name').value = room.name;
            document.getElementById('edit_building').value = room.building || '';
            document.getElementById('edit_capacity').value = room.capacity || '';
            document.getElementById('edit_is_available').value = room.is_available ? '1' : '0';
            document.getElementById('editForm').action = `/admin/rooms/${room.id}`;
            openModal('editModal');
        }

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({ title: 'Delete Room?', text: 'This action cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, Delete' }).then(r => { if(r.isConfirmed) this.closest('form').submit(); });
            });
        });
    </script>
</body>
</html>
