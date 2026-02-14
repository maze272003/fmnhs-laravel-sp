@extends('layouts.parent')

@section('title', 'Schedule - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <a href="{{ route('parent.children') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">&larr; Back to Children</a>
        <h1 class="text-2xl font-bold text-gray-800 mt-2">Schedule: {{ $student->first_name }} {{ $student->last_name }}</h1>
        <p class="text-gray-600">{{ $student->section->name ?? 'No Section' }} &middot; Grade {{ $student->grade_level ?? 'N/A' }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden p-6">
        <p class="text-gray-500 text-center">Schedule information will be available once classes are assigned.</p>
    </div>
</div>
@endsection
