@props([
    'columns' => [],
    'data' => [],
    'emptyMessage' => 'No records found.',
    'sortable' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $column)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $column['label'] ?? $column['key'] ?? '' }}
                        </th>
                    @endforeach
                    
                    @if(isset($actions) || $slot->isNotEmpty())
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        @foreach($columns as $column)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $key = $column['key'] ?? '';
                                    $value = data_get($row, $key);
                                    $format = $column['format'] ?? null;
                                @endphp
                                
                                @if($format === 'date' && $value)
                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y') }}
                                @elseif($format === 'datetime' && $value)
                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y H:i') }}
                                @elseif($format === 'boolean')
                                    <span class="px-2 py-1 text-xs rounded-full {{ $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $value ? 'Yes' : 'No' }}
                                    </span>
                                @elseif($format === 'status')
                                    <span class="px-2 py-1 text-xs rounded-full {{ $column['status_colors'][$value] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($value) }}
                                    </span>
                                @elseif(isset($column['render']))
                                    {!! $column['render']($row) !!}
                                @else
                                    {{ $value ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                        
                        @if(isset($actions) || $slot->isNotEmpty())
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                {{ $actions ?? $slot }}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + (isset($actions) || $slot->isNotEmpty() ? 1 : 0) }}" 
                            class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                <p>{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if(method_exists($data, 'links'))
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $data->links() }}
        </div>
    @endif
</div>
