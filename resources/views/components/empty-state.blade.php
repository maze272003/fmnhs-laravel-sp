@props([
    'title' => 'No data available',
    'description' => '',
    'icon' => 'fa-folder-open',
    'actionText' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12']) }}>
    <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <i class="fas {{ $icon }} text-4xl text-gray-400"></i>
    </div>
    
    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $title }}</h3>
    
    @if($description)
        <p class="text-gray-500 mb-6 max-w-md mx-auto">{{ $description }}</p>
    @endif
    
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            {{ $actionText }}
        </a>
    @endif
    
    {{ $slot }}
</div>
