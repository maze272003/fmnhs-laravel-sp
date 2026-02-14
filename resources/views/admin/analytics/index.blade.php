@extends('layouts.admin')

@section('title', 'Analytics Dashboard')
@section('header_title', 'Analytics Dashboard')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Analytics Dashboard</h1>
    <p class="text-slate-500 font-medium">Comprehensive overview of institutional data and performance metrics.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <span class="text-[10px] font-black text-blue-400 bg-blue-50/50 px-3 py-1 rounded-full border border-blue-100 uppercase tracking-widest">Students</span>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Students</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $totalStudents ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <span class="text-[10px] font-black text-emerald-400 bg-emerald-50/50 px-3 py-1 rounded-full border border-emerald-100 uppercase tracking-widest">Faculty</span>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Teachers</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $totalTeachers ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-book"></i>
            </div>
            <span class="text-[10px] font-black text-amber-400 bg-amber-50/50 px-3 py-1 rounded-full border border-amber-100 uppercase tracking-widest">Curriculum</span>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Subjects</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $totalSubjects ?? 0 }}</h3>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
        <div class="flex justify-between items-center mb-4">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-users-rectangle"></i>
            </div>
            <span class="text-[10px] font-black text-indigo-400 bg-indigo-50/50 px-3 py-1 rounded-full border border-indigo-100 uppercase tracking-widest">Classes</span>
        </div>
        <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Total Sections</p>
        <h3 class="text-4xl font-black text-slate-900">{{ $totalSections ?? 0 }}</h3>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <a href="{{ route('admin.analytics.students') }}" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group block">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <h3 class="text-lg font-black text-slate-800">Enrollment Analytics</h3>
        </div>
        <p class="text-sm text-slate-500">Track enrollment trends, grade distribution, and section capacity over time.</p>
        <div class="mt-4 text-xs font-bold text-blue-500 uppercase tracking-widest">View Details <i class="fa-solid fa-arrow-right ml-1"></i></div>
    </a>

    <a href="{{ route('admin.analytics.students') }}" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group block">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-ranking-star"></i>
            </div>
            <h3 class="text-lg font-black text-slate-800">Performance Reports</h3>
        </div>
        <p class="text-sm text-slate-500">Analyze academic performance across subjects, sections, and grade levels.</p>
        <div class="mt-4 text-xs font-bold text-emerald-500 uppercase tracking-widest">View Details <i class="fa-solid fa-arrow-right ml-1"></i></div>
    </a>

    <a href="{{ route('admin.analytics.index') }}" class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:shadow-xl transition-all group block">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-clipboard-user"></i>
            </div>
            <h3 class="text-lg font-black text-slate-800">Attendance Insights</h3>
        </div>
        <p class="text-sm text-slate-500">Monitor attendance rates, tardiness patterns, and absence trends.</p>
        <div class="mt-4 text-xs font-bold text-amber-500 uppercase tracking-widest">View Details <i class="fa-solid fa-arrow-right ml-1"></i></div>
    </a>
</div>
@endsection
