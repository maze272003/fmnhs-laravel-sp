<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seating Chart</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        h1 { color: #333; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .meta { color: #666; margin-bottom: 20px; }
        .front-label { text-align: center; font-weight: bold; color: #4f46e5; margin: 20px 0; }
        .seating-grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .seating-grid td { border: 1px solid #ddd; padding: 8px; text-align: center; min-width: 80px; }
        .seating-grid td.empty { background-color: #f9fafb; color: #9ca3af; }
        .seating-grid td.occupied { background-color: #eff6ff; }
        .footer { margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <h1>Seating Chart: {{ $layout['name'] }}</h1>
    <div class="meta">
        <p><strong>Generated:</strong> {{ now()->format('F d, Y H:i') }}</p>
        <p><strong>Occupied:</strong> {{ $layout['occupied_seats'] }} / {{ $layout['total_seats'] }} seats</p>
    </div>

    <div class="front-label">FRONT OF CLASSROOM</div>

    <table class="seating-grid">
        @foreach($layout['grid'] as $row)
        <tr>
            @foreach($row as $seat)
            <td class="{{ $seat['student_name'] ? 'occupied' : 'empty' }}">
                {{ $seat['student_name'] ?? '[ empty ]' }}
            </td>
            @endforeach
        </tr>
        @endforeach
    </table>

    <div class="footer">
        <p>FMNHS Student Information System</p>
    </div>
</body>
</html>
