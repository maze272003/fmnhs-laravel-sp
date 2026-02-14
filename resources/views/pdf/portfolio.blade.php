<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Portfolio: {{ $portfolio->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 20px; border-bottom: 2px solid #333; padding-bottom: 6px; }
        h2 { font-size: 16px; margin-top: 20px; }
        .meta { color: #666; margin-bottom: 20px; }
        .item { margin-bottom: 12px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .item-title { font-weight: bold; }
        .item-type { color: #888; font-size: 11px; }
        .item-description { margin-top: 4px; }
    </style>
</head>
<body>
    <h1>{{ $portfolio->title }}</h1>
    <div class="meta">
        <strong>Student:</strong> {{ $portfolio->student->first_name }} {{ $portfolio->student->last_name }}<br>
        @if($portfolio->description)
            <strong>Description:</strong> {{ $portfolio->description }}<br>
        @endif
        <strong>Generated:</strong> {{ now()->format('F j, Y') }}
    </div>

    <h2>Portfolio Items</h2>
    @forelse($portfolio->items as $index => $item)
        <div class="item">
            <span class="item-title">{{ $index + 1 }}. {{ $item->title }}</span>
            <span class="item-type">({{ $item->type }})</span>
            @if($item->description)
                <div class="item-description">{{ $item->description }}</div>
            @endif
        </div>
    @empty
        <p>No items in this portfolio.</p>
    @endforelse
</body>
</html>
