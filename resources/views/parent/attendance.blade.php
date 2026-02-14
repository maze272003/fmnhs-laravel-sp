@extends('layouts.parent')

@section('title', 'Attendance - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('parent.children') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">&larr; Back to Children</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Attendance: {{ $student->first_name }} {{ $student->last_name }}</h1>
        <p class="text-gray-600">{{ $student->section->name ?? 'No Section' }} &middot; Grade {{ $student->grade_level ?? 'N/A' }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($attendance->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attendance as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($record->status === 'present') bg-green-100 text-green-800
                                    @elseif($record->status === 'absent') bg-red-100 text-red-800
                                    @elseif($record->status === 'late') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($record->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $record->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">
                {{ $attendance->links() }}
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <p>No attendance records found.</p>
            </div>
        @endif
    </div>
</div>
@endsection
