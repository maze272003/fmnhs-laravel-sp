@props([
    'id' => 'modal-' . uniqid(),
    'title' => 'Confirm Action',
    'size' => 'md', // sm, md, lg, xl
    'showClose' => true,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        default => 'max-w-lg',
    };
@endphp

<!-- Modal Backdrop & Container -->
<div id="{{ $id }}" 
     class="fixed inset-0 z-50 hidden overflow-y-auto"
     x-data="{ open: false }"
     x-show="open"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="open = false">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
    
    <!-- Modal Panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative {{ $sizeClasses }} w-full bg-white rounded-xl shadow-2xl transform transition-all"
             x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>
            
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                @if($showClose)
                    <button type="button" 
                            class="text-gray-400 hover:text-gray-500 transition-colors"
                            @click="open = false">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
            
            <!-- Body -->
            <div class="px-6 py-4">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            @if(isset($footer))
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    // Global modal helpers
    window.openModal = function(id) {
        const modal = document.getElementById(id);
        if (modal && modal.__x) {
            modal.__x.$data.open = true;
        }
    };
    
    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal && modal.__x) {
            modal.__x.$data.open = false;
        }
    };
</script>
@endpush
@endonce
