<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grade Sheet - {{ $subject->code }} - {{ $section->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; font-size: 12px; }
        .school-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        .deped-tag { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #666; margin-bottom: 5px; }
        .school-name { font-size: 20px; font-weight: 800; color: #1e3a8a; margin: 0; }
        .school-info { font-size: 10px; color: #666; margin-top: 5px; }
        .report-title { text-align: center; font-size: 14px; font-weight: bold; background: #f3f4f6; padding: 6px; margin: 15px 0; border: 1px solid #ddd; }
        .info-table { width: 100%; margin-bottom: 15px; font-size: 12px; }
        .info-table td { padding: 3px 0; }
        .label { font-weight: bold; color: #555; width: 120px; }
        .value { border-bottom: 1px solid #ccc; padding-left: 5px; }
        table.grades { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        table.grades th { background-color: #1e3a8a; color: white; padding: 8px; text-transform: uppercase; border: 1px solid #1e3a8a; }
        table.grades td { border: 1px solid #ddd; padding: 6px; text-align: center; }
        table.grades td.student-name { text-align: left; padding-left: 10px; }
        .passed { color: #059669; font-weight: bold; }
        .failed { color: #dc2626; font-weight: bold; }
        .signature-section { margin-top: 40px; width: 100%; }
        .sig-box { width: 45%; display: inline-block; text-align: center; }
        .sig-line { border-top: 1px solid #333; margin-top: 35px; padding-top: 5px; font-weight: bold; font-size: 12px; }
        .sig-title { font-size: 10px; color: #666; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding-bottom: 10px; }
    </style>
</head>
<body>

    <div class="school-header">
        <div class="deped-tag">Republic of the Philippines • Department of Education</div>
        <h1 class="school-name">FORT MAGSAYSAY NATIONAL HIGH SCHOOL</h1>
        <div class="school-info">Fort Magsaysay, Palayan City, Nueva Ecija • School ID: 305123</div>
    </div>

    <div class="report-title">CLASS GRADE SHEET</div>

    <table class="info-table">
        <tr>
            <td class="label">Subject:</td>
            <td class="value">{{ $subject->name }} ({{ $subject->code }})</td>
            <td class="label" style="padding-left: 20px;">School Year:</td>
            <td class="value">{{ $schoolYear }}</td>
        </tr>
        <tr>
            <td class="label">Section:</td>
            <td class="value">Grade {{ $section->grade_level }} - {{ $section->name }}</td>
            <td class="label" style="padding-left: 20px;">Teacher:</td>
            <td class="value">{{ $teacher->first_name }} {{ $teacher->last_name }}</td>
        </tr>
    </table>

    <table class="grades">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%; text-align: left; padding-left: 10px;">Student Name</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Final</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                @php
                    $q1 = $student->grades->where('quarter', 1)->first()?->grade_value;
                    $q2 = $student->grades->where('quarter', 2)->first()?->grade_value;
                    $q3 = $student->grades->where('quarter', 3)->first()?->grade_value;
                    $q4 = $student->grades->where('quarter', 4)->first()?->grade_value;
                    $grades = collect([$q1, $q2, $q3, $q4])->filter();
                    $average = $grades->isNotEmpty() ? $grades->avg() : null;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="student-name">{{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }}</td>
                    <td>{{ $q1 ?? '' }}</td>
                    <td>{{ $q2 ?? '' }}</td>
                    <td>{{ $q3 ?? '' }}</td>
                    <td>{{ $q4 ?? '' }}</td>
                    <td style="font-weight: bold;">{{ $average ? number_format($average, 0) : '' }}</td>
                    <td>
                        @if($average)
                            <span class="{{ $average >= 75 ? 'passed' : 'failed' }}">
                                {{ $average >= 75 ? 'PASSED' : 'FAILED' }}
                            </span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="sig-box" style="float: left;">
            <div class="sig-line">{{ $teacher->first_name }} {{ $teacher->last_name }}</div>
            <div class="sig-title">Subject Teacher</div>
        </div>
        <div class="sig-box" style="float: right;">
            <div class="sig-line">MARIA LIZA R. CANLAS</div>
            <div class="sig-title">School Principal</div>
        </div>
    </div>

    <div class="footer">
        <p>This is a system-generated document. Date Printed: {{ date('F d, Y') }}</p>
    </div>

</body>
</html>
