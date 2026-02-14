@extends('layouts.parent')

@section('title', 'Parent Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Welcome, {{ auth('parent')->user()->name }}</h1>
        <p class="text-gray-600">Monitor your children's academic progress</p>
    </div>

    @if($children->count() === 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">No children are linked to your account yet. Please contact the school administration.</p>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
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
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="{{ route('parent.children.grades', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">
                                View Grades
                            </a>
                            <a href="{{ route('parent.children.attendance', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mr-3">
                                Attendance
                            </a>
                            <a href="{{ route('parent.children.assignments', $child->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                Assignments
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Announcements</h3>
                @if($announcements->count() > 0)
                    <div class="space-y-4">
                        @foreach($announcements as $announcement)
                            <div class="border-l-4 border-indigo-500 pl-4">
                                <h4 class="font-medium text-gray-800">{{ $announcement->title }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($announcement->content, 100) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $announcement->created_at->format('M d, Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No recent announcements</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('parent.children') }}" class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-indigo-900">View Children</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-900">Message Teacher</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-purple-900">School Calendar</p>
                        </div>
                    </a>
                    <a href="#" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-900">Download Reports</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
