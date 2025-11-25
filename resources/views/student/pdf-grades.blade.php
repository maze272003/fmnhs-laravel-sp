<!DOCTYPE html>
<html>
<head>
    <title>Report Card</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #2563eb; }
        .header p { margin: 5px 0; color: #555; }
        
        .student-info { margin-bottom: 20px; width: 100%; }
        .student-info td { padding: 5px; font-weight: bold; }
        
        table.grades { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.grades th, table.grades td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        table.grades th { background-color: #f3f4f6; color: #333; }
        table.grades td.subject { text-align: left; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>

    <div class="header">
        <h1>OFFICIAL REPORT CARD</h1>
        <p>School Year 2024-2025</p>
    </div>

    <table class="student-info">
        <tr>
            <td>Name: {{ $student->last_name }}, {{ $student->first_name }}</td>
            <td style="text-align: right;">LRN: {{ $student->lrn }}</td>
        </tr>
        <tr>
            <td>Section: {{ $student->section }}</td>
            <td style="text-align: right;">Grade Level: {{ $student->grade_level }}</td>
        </tr>
    </table>

    <table class="grades">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Avg</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subjects as $subject)
                @php
                    $q1 = $subject->grades->where('quarter', 1)->first()?->grade_value;
                    $q2 = $subject->grades->where('quarter', 2)->first()?->grade_value;
                    $q3 = $subject->grades->where('quarter', 3)->first()?->grade_value;
                    $q4 = $subject->grades->where('quarter', 4)->first()?->grade_value;
                    
                    $grades = collect([$q1, $q2, $q3, $q4])->filter();
                    $average = $grades->isNotEmpty() ? $grades->avg() : null;
                    $status = $average >= 75 ? 'PASSED' : ($average ? 'FAILED' : '');
                @endphp
                <tr>
                    <td class="subject">{{ $subject->name }}</td>
                    <td>{{ $q1 ?? '' }}</td>
                    <td>{{ $q2 ?? '' }}</td>
                    <td>{{ $q3 ?? '' }}</td>
                    <td>{{ $q4 ?? '' }}</td>
                    <td>{{ $average ? number_format($average, 2) : '' }}</td>
                    <td style="font-weight: bold; font-size: 12px;">{{ $status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system-generated report. Date Printed: {{ date('Y-m-d') }}</p>
    </div>

</body>
</html>