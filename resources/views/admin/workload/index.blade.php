@extends('layouts.admin')

@section('title', 'Teacher Workload')
@section('header_title', 'Teacher Workload')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Teacher Workload Overview</h1>
    <p class="text-slate-500 font-medium">Monitor faculty workload distribution across the institution.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Teachers</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $teachers->count() ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-layer-group"></i>
            </div>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Classes</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $totalClasses ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Students</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $totalStudents ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Avg Hours/Week</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $avgHoursPerWeek ?? 0 }}</h3>
    </div>
</div>

<div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center">
            <i class="fa-solid fa-list-check"></i>
        </div>
        <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">Workload Breakdown</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Teacher</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Classes</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Students</th>
                    <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Hours / Week</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-sm shadow">
                                    {{ strtoupper(substr($teacher->first_name ?? $teacher->name ?? '', 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-800">{{ $teacher->first_name ?? '' }} {{ $teacher->last_name ?? '' }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-4">
                            <span class="text-sm font-semibold text-slate-700">{{ $teacher->class_count ?? 0 }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <span class="text-sm font-semibold text-slate-700">{{ $teacher->student_count ?? 0 }}</span>
                        </td>
                        <td class="px-8 py-4">
                            <span class="text-sm font-semibold text-slate-700">{{ $teacher->hours_per_week ?? 0 }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-slate-100 text-slate-300 rounded-2xl flex items-center justify-center text-3xl">
                                    <i class="fa-solid fa-users-slash"></i>
                                </div>
                                <p class="text-slate-400 font-medium">No teacher workload data available.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
