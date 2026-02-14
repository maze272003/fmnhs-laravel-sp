@extends('layouts.student')

@section('title', 'Learning Paths')
@section('header_title', 'Learning Paths')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">My Learning Paths</h1>
    <p class="text-slate-500 font-medium">Track your progress through personalized learning journeys.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse ($learningPaths as $path)
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all overflow-hidden group">
            <div class="p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-800 text-lg leading-tight">{{ $path->title ?? '' }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $path->subject->name ?? 'General' }}</p>
                    </div>
                </div>

                <p class="text-sm text-slate-500 mb-6 line-clamp-2">{{ $path->description ?? 'No description available.' }}</p>

                @php
                    $progress = $path->progress ?? 0;
                    $progressColor = $progress >= 75 ? 'bg-emerald-500' : ($progress >= 40 ? 'bg-amber-500' : 'bg-indigo-500');
                @endphp

                <div class="mb-2 flex justify-between items-center">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Progress</span>
                    <span class="text-sm font-black text-slate-700">{{ $progress }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                    <div class="{{ $progressColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('student.learning-paths.show', $path->id) }}" class="block text-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold px-4 py-3 rounded-xl transition-colors">
                        {{ $progress > 0 ? 'Continue Learning' : 'Start Path' }}
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 p-16 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-2xl flex items-center justify-center text-3xl">
                        <i class="fa-solid fa-road"></i>
                    </div>
                    <p class="text-slate-400 font-medium">No learning paths available yet.</p>
                    <p class="text-sm text-slate-400">Check back later for new learning paths assigned to you.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
