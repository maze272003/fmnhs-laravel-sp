<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
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
                        <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Audit Trail</h2>
                </div>
            </div>
            @include('components.admin.header_details')
        </header>

        <main class="flex-1 p-6 lg:p-10">

            <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100 mb-8">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1.5 h-5 bg-indigo-500 rounded-full"></div>
                    <h3 class="font-black text-sm text-slate-800 uppercase tracking-widest">Filter Logs</h3>
                </div>
                
                <form action="{{ route('admin.audit-trail.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="User, model, field..."
                               class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Action</label>
                        <select name="action" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold text-sm appearance-none">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold text-sm">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold text-sm">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-black shadow-lg shadow-indigo-100 transition-all active:scale-95 flex-1 text-sm">
                            Apply
                        </button>
                        <a href="{{ route('admin.audit-trail.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-500 px-4 py-3 rounded-2xl transition-all flex items-center justify-center">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-xl text-slate-800 tracking-tight">Activity Logs</h3>
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-4 py-1.5 rounded-full border border-indigo-100 uppercase tracking-widest">
                        Total: {{ $trails->total() }}
                    </span>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                                <th class="px-8 py-5">Timestamp</th>
                                <th class="px-6 py-5">User</th>
                                <th class="px-6 py-5">Action</th>
                                <th class="px-6 py-5">Model</th>
                                <th class="px-6 py-5">Field</th>
                                <th class="px-6 py-5">Old Value</th>
                                <th class="px-6 py-5">New Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($trails as $trail)
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-8 py-5">
                                        <span class="font-black text-slate-700 text-sm">{{ $trail->created_at->format('M d, Y') }}</span>
                                        <span class="block text-[10px] text-slate-400 font-bold">{{ $trail->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="font-bold text-slate-800">{{ $trail->user_name }}</span>
                                        <span class="block text-[10px] text-slate-400 font-black uppercase tracking-widest">{{ $trail->user_type }}</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        @php
                                            $actionColors = [
                                                'created' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'updated' => 'bg-blue-50 text-blue-600 border-blue-100',
                                                'archived' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                'deleted' => 'bg-rose-50 text-rose-600 border-rose-100',
                                                'restored' => 'bg-violet-50 text-violet-600 border-violet-100',
                                                'promoted' => 'bg-cyan-50 text-cyan-600 border-cyan-100',
                                                'graduated' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                            ];
                                            $colorClass = $actionColors[$trail->action] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $colorClass }}">
                                            {{ $trail->action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 font-bold text-slate-600 text-sm">{{ $trail->auditable_type }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-500">{{ $trail->field ?? '-' }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-500 max-w-[200px] truncate">{{ Str::limit($trail->old_value, 50) ?? '-' }}</td>
                                    <td class="px-6 py-5 text-sm text-slate-500 max-w-[200px] truncate">{{ Str::limit($trail->new_value, 50) ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                                <i class="fa-solid fa-magnifying-glass text-slate-200 text-3xl"></i>
                                            </div>
                                            <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">No audit trail records found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="p-6 bg-slate-50/30 border-t border-slate-50">
                    {{ $trails->links() }}
                </div>
            </div>

        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>
