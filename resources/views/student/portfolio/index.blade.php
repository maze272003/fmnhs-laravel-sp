@extends('layouts.student')

@section('title', 'My Portfolio')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Portfolio</h1>
            <p class="text-gray-600">Showcase your best work and track your growth</p>
        </div>
        <button onclick="openAddItemModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Add Item
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Portfolio Items</h3>
                
                @if($items->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($items as $item)
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition">
                                @if($item->type === 'image' && $item->file_path)
                                    <img src="{{ Storage::url($item->file_path) }}" alt="{{ $item->title }}" class="w-full h-40 object-cover">
                                @else
                                    <div class="w-full h-40 bg-gray-100 flex items-center justify-center">
                                        <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h4 class="font-medium text-gray-800">{{ $item->title }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($item->description, 80) }}</p>
                                    <div class="flex justify-between items-center mt-3">
                                        <span class="text-xs text-gray-400">{{ $item->created_at->format('M d, Y') }}</span>
                                        <div class="flex gap-2">
                                            <button onclick="editItem({{ $item->id }})" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</button>
                                            <button onclick="deleteItem({{ $item->id }})" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="h-12 w-12 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <p class="text-gray-500 mt-2">No portfolio items yet. Add your first item!</p>
                    </div>
                @endif
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Learning Reflections</h3>
                <button onclick="openReflectionModal()" class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg text-sm font-medium mb-4">
                    Add Reflection
                </button>
                
                @if($reflections->count() > 0)
                    <div class="space-y-3">
                        @foreach($reflections->take(5) as $reflection)
                            <div class="border-l-4 border-indigo-500 pl-3 py-2">
                                <p class="text-sm text-gray-700">{{ Str::limit($reflection->content, 100) }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $reflection->created_at->format('M d, Y') }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No reflections yet</p>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Portfolio Stats</h3>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Items</span>
                        <span class="font-medium">{{ $items->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Reflections</span>
                        <span class="font-medium">{{ $reflections->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Updated</span>
                        @php $lastItem = $items->first(); @endphp
                        <span class="font-medium">{{ $lastItem ? $lastItem->updated_at->format('M d, Y') : 'Never' }}</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="{{ route('student.portfolio.export') ?? '#' }}" class="block text-center bg-green-50 hover:bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm font-medium">
                        Export as PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addItemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Add Portfolio Item</h3>
            <form action="{{ route('student.portfolio.items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" name="title" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="type" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="image">Image</option>
                            <option value="document">Document</option>
                            <option value="video">Video Link</option>
                            <option value="project">Project</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File</label>
                        <input type="file" name="file" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeAddItemModal()" class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">Cancel</button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddItemModal() {
    document.getElementById('addItemModal').classList.remove('hidden');
}

function closeAddItemModal() {
    document.getElementById('addItemModal').classList.add('hidden');
}

function openReflectionModal() {}

function editItem(id) {}

function deleteItem(id) {
    if (confirm('Are you sure you want to delete this item?')) {}
}
</script>
@endsection
