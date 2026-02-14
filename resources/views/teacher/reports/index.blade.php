@extends('layouts.teacher')

@section('title', 'Progress Reports')
@section('header_title', 'Progress Reports')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Progress Reports</h1>
    <p class="text-slate-500 font-medium">Generate, review, and distribute student progress reports.</p>
</div>

<div class="mb-8 flex flex-col sm:flex-row gap-3 justify-end">
    <a href="{{ route('teacher.reports.generate') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors inline-flex items-center gap-2">
        <i class="fa-solid fa-file-circle-plus"></i> Generate Report
    </a>
</div>

<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Report History</span>
        </div>
        <span class="text-xs font-black text-slate-300 uppercase tracking-widest">{{ $reports->count() ?? 0 }} Reports</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Report Title</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Section</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Date Generated</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-4">
                            <span class="font-bold text-slate-800">{{ $report->title ?? '' }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <span class="text-sm text-slate-600">{{ $report->section->name ?? '' }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <span class="text-sm text-slate-500">{{ $report->created_at ? $report->created_at->format('M d, Y') : '' }}</span>
                        </td>
                        <td class="px-8 py-4">
                            @if ($report->sent ?? false)
                                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border bg-emerald-50 text-emerald-600 border-emerald-100">Sent</span>
                            @else
                                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border bg-amber-50 text-amber-600 border-amber-100">Draft</span>
                            @endif
                        </td>
                        <td class="px-8 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('teacher.reports.show', $report->id) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold px-3 py-2 rounded-xl transition-colors" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @if (!($report->sent ?? false))
                                    <form action="{{ route('teacher.reports.send', $report->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold px-3 py-2 rounded-xl transition-colors" title="Send">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-2xl flex items-center justify-center text-3xl">
                                    <i class="fa-solid fa-file-circle-xmark"></i>
                                </div>
                                <p class="text-slate-400 font-medium">No progress reports generated yet.</p>
                                <a href="{{ route('teacher.reports.generate') }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-bold">Generate your first report <i class="fa-solid fa-arrow-right ml-1"></i></a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
