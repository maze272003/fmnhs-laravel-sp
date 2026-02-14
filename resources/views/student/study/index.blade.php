@extends('layouts.student')

@section('title', 'Study Tools')
@section('header_title', 'Study Tools')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Study Tools</h1>
    <p class="text-slate-500 font-medium">Manage your study sessions and track your goals.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Active Sessions</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $activeSessions->count() ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-bullseye"></i>
            </div>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Goals Completed</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $completedGoals ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Study Hours</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $totalStudyHours ?? 0 }}</h3>
    </div>
</div>

<div class="mb-8 flex justify-end">
    <a href="{{ route('student.study.sessions.start') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors inline-flex items-center gap-2">
        <i class="fa-solid fa-play"></i> Start New Session
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Active Study Sessions --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>
            <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Active Sessions</span>
        </div>
        <div class="p-6">
            @forelse ($activeSessions as $session)
                <div class="flex items-center justify-between py-4 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">{{ $session->subject ?? $session->title ?? 'Study Session' }}</p>
                            <p class="text-xs text-slate-400">Started {{ $session->created_at ? $session->created_at->diffForHumans() : '' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('student.study.index') }}" class="text-indigo-600 hover:text-indigo-700 text-xs font-bold uppercase tracking-widest">
                        Resume <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-slate-400 text-sm">No active study sessions. Start one above!</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Study Goals --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center">
                <i class="fa-solid fa-flag-checkered"></i>
            </div>
            <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Study Goals</span>
        </div>
        <div class="p-6">
            @forelse ($goals as $goal)
                <div class="py-4 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-bold text-slate-800 text-sm">{{ $goal->title ?? '' }}</p>
                        @if ($goal->completed ?? false)
                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border bg-emerald-50 text-emerald-600 border-emerald-100">Done</span>
                        @else
                            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full border bg-amber-50 text-amber-600 border-amber-100">In Progress</span>
                        @endif
                    </div>
                    @php $goalProgress = $goal->progress ?? 0; @endphp
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $goalProgress }}%"></div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-slate-400 text-sm">No study goals set yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
