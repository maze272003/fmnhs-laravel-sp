@extends('layouts.parent')

@section('title', 'My Children')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">My Children</h1>
        <p class="text-gray-600">View and manage your children's information</p>
    </div>

    @if($children->count() === 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <p class="text-sm text-yellow-700">No children are linked to your account yet. Please contact the school administration.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($children as $child)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-4 py-3">
                        <h3 class="text-lg font-semibold text-white">{{ $child->first_name }} {{ $child->last_name }}</h3>
                        <p class="text-indigo-100 text-sm">{{ $child->section->name ?? 'No Section' }}</p>
                    </div>
                    <div class="p-4">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Grade Level:</span>
                                <span class="font-medium">{{ $child->grade_level ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">LRN:</span>
                                <span class="font-medium">{{ $child->lrn ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ $child->status === 'enrolled' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($child->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 flex flex-wrap gap-3">
                            <a href="{{ route('parent.children.grades', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Grades</a>
                            <a href="{{ route('parent.children.attendance', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Attendance</a>
                            <a href="{{ route('parent.children.schedule', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Schedule</a>
                            <a href="{{ route('parent.children.assignments', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Assignments</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
