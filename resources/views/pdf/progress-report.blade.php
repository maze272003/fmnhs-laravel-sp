<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Report - {{ $studentName ?? 'Student' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24pt;
            color: #1a365d;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14pt;
            color: #4a5568;
            font-weight: normal;
        }
        .student-info {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .student-info h3 {
            font-size: 14pt;
            margin-bottom: 10px;
            color: #2d3748;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            font-size: 11pt;
        }
        .info-label {
            font-weight: bold;
            color: #4a5568;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14pt;
            color: #1a365d;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #edf2f7;
            font-weight: bold;
            color: #2d3748;
        }
        .grade-average {
            text-align: center;
            font-weight: bold;
        }
        .attendance-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            text-align: center;
        }
        .attendance-item {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
        }
        .attendance-number {
            font-size: 24pt;
            font-weight: bold;
            color: #1a365d;
        }
        .attendance-label {
            font-size: 10pt;
            color: #718096;
        }
        .overall-average {
            text-align: center;
            background: #1a365d;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .overall-average .value {
            font-size: 36pt;
            font-weight: bold;
        }
        .overall-average .label {
            font-size: 12pt;
            opacity: 0.9;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10pt;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .signature-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FMNHS Student Information System</h1>
        <h2>Progress Report</h2>
    </div>

    <div class="student-info">
        <h3>Student Information</h3>
        <div class="info-grid">
            <div class="info-item"><span class="info-label">Name:</span> {{ $data['student']['name'] ?? $studentName ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">LRN:</span> {{ $data['student']['lrn'] ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">Section:</span> {{ $data['student']['section'] ?? 'N/A' }}</div>
            <div class="info-item"><span class="info-label">Period:</span> {{ $data['period']['start'] ?? '' }} to {{ $data['period']['end'] ?? '' }}</div>
        </div>
    </div>

    @if(isset($data['grades']) && !empty($data['grades']))
    <div class="section">
        <h3 class="section-title">Academic Performance</h3>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Average Grade</th>
                    <th>Grades Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['grades'] as $subject => $gradeData)
                <tr>
                    <td>{{ $subject }}</td>
                    <td class="grade-average">{{ $gradeData['average'] ?? 'N/A' }}</td>
                    <td>{{ count($gradeData['grades'] ?? []) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($data['attendance']))
    <div class="section">
        <h3 class="section-title">Attendance Summary</h3>
        <div class="attendance-summary">
            <div class="attendance-item">
                <div class="attendance-number">{{ $data['attendance']['total_classes'] ?? 0 }}</div>
                <div class="attendance-label">Total Classes</div>
            </div>
            <div class="attendance-item">
                <div class="attendance-number" style="color: #38a169;">{{ $data['attendance']['present'] ?? 0 }}</div>
                <div class="attendance-label">Present</div>
            </div>
            <div class="attendance-item">
                <div class="attendance-number" style="color: #e53e3e;">{{ $data['attendance']['absent'] ?? 0 }}</div>
                <div class="attendance-label">Absent</div>
            </div>
            <div class="attendance-item">
                <div class="attendance-number" style="color: #d69e2e;">{{ $data['attendance']['late'] ?? 0 }}</div>
                <div class="attendance-label">Late</div>
            </div>
        </div>
        <div class="overall-average" style="margin-top: 15px; background: #f7fafc; color: #333;">
            <div class="label">Attendance Rate</div>
            <div class="value" style="font-size: 24pt;">{{ $data['attendance']['rate'] ?? 0 }}%</div>
        </div>
    </div>
    @endif

    @if(isset($data['overall_average']))
    <div class="overall-average">
        <div class="label">Overall Average</div>
        <div class="value">{{ $data['overall_average'] }}</div>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Teacher's Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Parent/Guardian's Signature</div>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('F d, Y \a\t h:i A') ?? now()->format('F d, Y \a\t h:i A') }}</p>
        <p>This report was generated by the FMNHS Student Information System</p>
    </div>
</body>
</html>
