@extends('layouts.parent')

@section('title', 'Grades - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('parent.children') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">&larr; Back to Children</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Grades: {{ $student->first_name }} {{ $student->last_name }}</h1>
        <p class="text-gray-600">{{ $student->section->name ?? 'No Section' }} &middot; Grade {{ $student->grade_level ?? 'N/A' }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($grades->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quarter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($grades as $grade)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $grade->subject->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $grade->quarter ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $grade->grade ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $grade->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-6 text-center text-gray-500">
                <p>No grades recorded yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
