<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workload Report</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        h1 { color: #333; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .meta { color: #666; margin-bottom: 20px; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #4f46e5; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .stat { font-size: 24px; font-weight: bold; color: #4f46e5; }
    </style>
</head>
<body>
    <h1>Workload Report</h1>
    <div class="meta">
        <p><strong>Teacher:</strong> {{ $teacher->first_name }} {{ $teacher->last_name }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y H:i') }}</p>
    </div>

    <div class="section">
        <h2>Weekly Summary</h2>
        <p><span class="stat">{{ $weekly['total_activities'] }}</span> activities</p>
        <p><span class="stat">{{ $weekly['total_hours'] }}</span> hours</p>
        @if(!empty($weekly['by_type']))
        <table>
            <thead>
                <tr>
                    <th>Activity Type</th>
                    <th>Count</th>
                    <th>Minutes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($weekly['by_type'] as $type => $data)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td>{{ $data['total_minutes'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div class="section">
        <h2>Monthly Summary</h2>
        <p><span class="stat">{{ $monthly['total_activities'] }}</span> activities</p>
        <p><span class="stat">{{ $monthly['total_hours'] }}</span> hours</p>
    </div>
</body>
</html>
