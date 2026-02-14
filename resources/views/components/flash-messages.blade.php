@props([
    'dismissible' => true,
])

<div {{ $attributes->merge(['class' => 'fixed top-4 right-4 z-50 space-y-2 max-w-sm']) }}
     x-data="{ messages: [] }"
     x-init="
         @if(session('success'))
             messages.push({ type: 'success', text: '{{ session('success') }}' });
         @endif
         @if(session('error'))
             messages.push({ type: 'error', text: '{{ session('error') }}' });
         @endif
         @if(session('warning'))
             messages.push({ type: 'warning', text: '{{ session('warning') }}' });
         @endif
         @if(session('info'))
             messages.push({ type: 'info', text: '{{ session('info') }}' });
         @endif
         
         // Auto-dismiss after 5 seconds
         setTimeout(() => { messages = []; }, 5000);
     ">
    
    <template x-for="(message, index) in messages" :key="index">
        <div x-show="message.text"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4"
             :class="{
                 'bg-green-50 border-green-500 text-green-800': message.type === 'success',
                 'bg-red-50 border-red-500 text-red-800': message.type === 'error',
                 'bg-yellow-50 border-yellow-500 text-yellow-800': message.type === 'warning',
                 'bg-blue-50 border-blue-500 text-blue-800': message.type === 'info'
             }"
             class="border-l-4 rounded-lg p-4 shadow-lg flex items-start gap-3">
            
            <!-- Icon -->
            <div class="flex-shrink-0">
                <i :class="{
                    'fa-check-circle text-green-500': message.type === 'success',
                    'fa-exclamation-circle text-red-500': message.type === 'error',
                    'fa-exclamation-triangle text-yellow-500': message.type === 'warning',
                    'fa-info-circle text-blue-500': message.type === 'info'
                }" class="fas text-lg"></i>
            </div>
            
            <!-- Message -->
            <div class="flex-1">
                <p class="text-sm font-medium" x-text="message.text"></p>
            </div>
            
            <!-- Dismiss Button -->
            @if($dismissible)
                <button type="button" 
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                        @click="messages.splice(index, 1)">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
    </template>
</div>

@error('email')
<div class="fixed top-4 right-4 z-50 max-w-sm"
     x-data="{ show: true }"
     x-show="show"
     x-transition
     x-init="setTimeout(() => { show = false; }, 5000)">
    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-lg flex items-start gap-3">
        <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
        <p class="text-sm font-medium text-red-800">{{ $message }}</p>
        <button type="button" class="text-gray-400 hover:text-gray-600" @click="show = false">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@enderror
