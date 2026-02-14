@extends('layouts.admin')

@section('title', 'Intervention Alerts')
@section('header_title', 'Intervention Alerts')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Intervention Alerts</h1>
    <p class="text-slate-500 font-medium">Monitor and manage student intervention alerts.</p>
</div>

<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Active Alerts</span>
        </div>
        <span class="text-xs font-black text-slate-300 uppercase tracking-widest">{{ $alerts->count() ?? 0 }} Total</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student Name</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Alert Type</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Severity</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alerts as $alert)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-4">
                            <span class="font-bold text-slate-800">{{ $alert->student->first_name ?? '' }} {{ $alert->student->last_name ?? '' }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <span class="text-sm text-slate-600">{{ $alert->type ?? '' }}</span>
                        </td>
                        <td class="px-8 py-4">
                            @php
                                $severityColors = [
                                    'high' => 'bg-red-50 text-red-600 border-red-100',
                                    'medium' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    'low' => 'bg-blue-50 text-blue-600 border-blue-100',
                                ];
                                $color = $severityColors[strtolower($alert->severity ?? '')] ?? 'bg-slate-50 text-slate-600 border-slate-100';
                            @endphp
                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border {{ $color }}">{{ $alert->severity ?? 'N/A' }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <span class="text-sm text-slate-500">{{ $alert->created_at ? $alert->created_at->format('M d, Y') : '' }}</span>
                        </td>
                        <td class="px-8 py-4">
                            @if ($alert->status === 'resolved')
                                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border bg-emerald-50 text-emerald-600 border-emerald-100">Resolved</span>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border bg-amber-50 text-amber-600 border-amber-100">Pending</span>
                            @endif
                        </td>
                        <td class="px-8 py-4">
                            @if ($alert->status !== 'resolved')
                                <form action="{{ route('admin.alerts.resolve', $alert->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors">
                                        <i class="fa-solid fa-check mr-1"></i> Resolve
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-2xl flex items-center justify-center text-3xl">
                                    <i class="fa-solid fa-bell-slash"></i>
                                </div>
                                <p class="text-slate-400 font-medium">No intervention alerts found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
