<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Progress Report: {{ $data['student']['name'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 20px; border-bottom: 2px solid #333; padding-bottom: 6px; }
        h2 { font-size: 16px; margin-top: 20px; }
        .meta { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .summary { margin-top: 16px; padding: 10px; background: #f9f9f9; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Progress Report</h1>
    <div class="meta">
        <strong>Student:</strong> {{ $data['student']['name'] }}<br>
        <strong>Section:</strong> {{ $data['student']['section'] ?? 'N/A' }}<br>
        <strong>LRN:</strong> {{ $data['student']['lrn'] ?? 'N/A' }}<br>
        <strong>Period:</strong> {{ $data['period']['start'] }} to {{ $data['period']['end'] }}<br>
        <strong>Generated:</strong> {{ now()->format('F j, Y') }}
    </div>

    @if(!empty($data['grades']))
        <h2>Grades</h2>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Average</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['grades'] as $subject => $gradeData)
                    <tr>
                        <td>{{ $subject }}</td>
                        <td>{{ $gradeData['average'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Attendance</h2>
    <table>
        <thead>
            <tr>
                <th>Total Classes</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $data['attendance']['total_classes'] }}</td>
                <td>{{ $data['attendance']['present'] }}</td>
                <td>{{ $data['attendance']['absent'] }}</td>
                <td>{{ $data['attendance']['late'] }}</td>
                <td>{{ $data['attendance']['rate'] }}%</td>
            </tr>
        </tbody>
    </table>

    @if(!is_null($data['overall_average']))
        <div class="summary">
            <strong>Overall Average:</strong> {{ $data['overall_average'] }}
        </div>
    @endif
</body>
</html>
