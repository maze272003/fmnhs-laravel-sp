@extends('layouts.teacher')

@section('title', 'Seating Arrangements')
@section('header_title', 'Seating Arrangements')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Seating Arrangements</h1>
    <p class="text-slate-500 font-medium">Manage and organize classroom seating for your sections.</p>
</div>

<div class="mb-8 flex flex-col sm:flex-row gap-3 justify-end">
    <a href="{{ route('teacher.seating.index') }}" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors inline-flex items-center gap-2">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Arrange
    </a>
    <a href="{{ route('teacher.seating.index') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors inline-flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Create Arrangement
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse ($arrangements as $arrangement)
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all overflow-hidden group">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-grip"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-lg leading-tight">{{ $arrangement->name ?? '' }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $arrangement->section->name ?? '' }}</p>
                    </div>
                </div>

                <div class="space-y-2 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Room</span>
                        <span class="font-semibold text-slate-700">{{ $arrangement->room->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Students</span>
                        <span class="font-semibold text-slate-700">{{ $arrangement->seats_count ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Last Updated</span>
                        <span class="font-semibold text-slate-700">{{ $arrangement->updated_at ? $arrangement->updated_at->format('M d, Y') : '' }}</span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('teacher.seating.show', $arrangement->id) }}" class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold px-4 py-3 rounded-xl transition-colors">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                    </a>
                    <a href="{{ route('teacher.seating.show', $arrangement->id) }}" class="flex-1 text-center bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-4 py-3 rounded-xl transition-colors">
                        <i class="fa-solid fa-eye mr-1"></i> View
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-16 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-2xl flex items-center justify-center text-3xl">
                        <i class="fa-solid fa-chair"></i>
                    </div>
                    <p class="text-slate-400 font-medium">No seating arrangements created yet.</p>
                    <a href="{{ route('teacher.seating.index') }}" class="text-emerald-600 hover:text-emerald-700 text-sm font-bold">Create your first arrangement <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
