@props([
    'id' => 'confirm-modal-' . uniqid(),
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmClass' => 'bg-red-600 hover:bg-red-700 text-white',
    'method' => 'POST',
    'action' => '',
])

<div id="{{ $id }}" 
     class="fixed inset-0 z-50 hidden overflow-y-auto"
     x-data="{ open: false, loading: false }"
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
        <div class="relative max-w-md w-full bg-white rounded-xl shadow-2xl transform transition-all"
             x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop>
            
            <!-- Icon -->
            <div class="flex justify-center pt-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                </div>
            </div>
            
            <!-- Content -->
            <div class="text-center px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $title }}</h3>
                <p class="text-gray-500">{{ $message }}</p>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3 px-6 pb-6">
                <button type="button"
                        class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium"
                        @click="open = false"
                        :disabled="loading">
                    {{ $cancelText }}
                </button>
                
                @if($action)
                    <form method="POST" action="{{ $action }}" class="flex-1" @submit.prevent="loading = true">
                        @csrf
                        @if($method !== 'POST')
                            @method($method)
                        @endif
                        
                        <button type="submit"
                                class="w-full px-4 py-2.5 {{ $confirmClass }} rounded-lg transition-colors font-medium flex items-center justify-center gap-2"
                                :disabled="loading">
                            <span x-show="loading"><i class="fas fa-spinner fa-spin"></i></span>
                            <span>{{ $confirmText }}</span>
                        </button>
                    </form>
                @else
                    <button type="button"
                            class="flex-1 px-4 py-2.5 {{ $confirmClass }} rounded-lg transition-colors font-medium"
                            @click="$dispatch('confirmed'); open = false"
                            :disabled="loading">
                        {{ $confirmText }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
    // Global confirm modal helpers
    window.openConfirmModal = function(id) {
        const modal = document.getElementById(id);
        if (modal && modal.__x) {
            modal.__x.$data.open = true;
        }
    };
    
    window.closeConfirmModal = function(id) {
        const modal = document.getElementById(id);
        if (modal && modal.__x) {
            modal.__x.$data.open = false;
        }
    };
</script>
@endpush
@endonce
