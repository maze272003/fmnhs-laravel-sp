<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meeting Digest: {{ $conference->title }}</title>
    <style>
        body { margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #059669, #0d9488); color: #fff; padding: 24px; }
        .header h1 { margin: 0 0 4px; font-size: 20px; }
        .header p { margin: 0; opacity: 0.85; font-size: 14px; }
        .body { padding: 24px; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .stat { background: #f1f5f9; border-radius: 8px; padding: 12px; text-align: center; }
        .stat .value { font-size: 24px; font-weight: 800; color: #059669; }
        .stat .label { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        h2 { font-size: 16px; margin: 20px 0 10px; color: #334155; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; padding: 8px; background: #f1f5f9; color: #475569; font-size: 12px; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .footer { padding: 16px 24px; background: #f8fafc; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $conference->title }}</h1>
            <p>Meeting Digest &mdash; {{ $conference->ended_at?->format('F j, Y g:i A') ?? 'In Progress' }}</p>
        </div>
        <div class="body">
            <div class="stat-grid">
                <div class="stat">
                    <div class="value">{{ count($summary['attendance'] ?? []) }}</div>
                    <div class="label">Participants</div>
                </div>
                <div class="stat">
                    <div class="value">{{ gmdate('H:i:s', $summary['conference']['duration_seconds'] ?? 0) }}</div>
                    <div class="label">Duration</div>
                </div>
                <div class="stat">
                    <div class="value">{{ $summary['message_count'] ?? 0 }}</div>
                    <div class="label">Chat Messages</div>
                </div>
                <div class="stat">
                    <div class="value">{{ count($summary['recordings'] ?? []) }}</div>
                    <div class="label">Recordings</div>
                </div>
            </div>

            @if(!empty($summary['attendance']))
            <h2>Attendance</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Duration</th>
                        <th>Joins</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($summary['attendance'] as $attendee)
                    <tr>
                        <td>{{ $attendee['display_name'] }}</td>
                        <td>{{ ucfirst($attendee['role']) }}</td>
                        <td>{{ gmdate('H:i:s', $attendee['total_duration_seconds']) }}</td>
                        <td>{{ $attendee['join_count'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        <div class="footer">
            This digest was auto-generated after your meeting ended.
        </div>
    </div>
</body>
</html>
