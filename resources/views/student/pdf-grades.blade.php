<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Official Report Card - {{ $student->last_name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; }
        
        /* Header Style */
        .school-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        .deped-tag { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #666; margin-bottom: 5px; }
        .school-name { font-size: 22px; font-weight: 800; color: #1e3a8a; margin: 0; }
        .school-info { font-size: 11px; color: #666; margin-top: 5px; }
        
        .report-title { text-align: center; font-size: 16px; font-weight: bold; background: #f3f4f6; padding: 8px; margin: 20px 0; border: 1px solid #ddd; }

        /* Student Info Grid */
        .info-table { width: 100%; margin-bottom: 20px; font-size: 13px; }
        .info-table td { padding: 4px 0; }
        .label { font-weight: bold; color: #555; width: 100px; }
        .value { border-bottom: 1px solid #ccc; padding-left: 5px; }

        /* Grades Table */
        table.grades { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        table.grades th { background-color: #1e3a8a; color: white; padding: 10px; text-transform: uppercase; border: 1px solid #1e3a8a; }
        table.grades td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        table.grades td.subject { text-align: left; font-weight: bold; padding-left: 15px; width: 35%; }
        
        .passed { color: #059669; font-weight: bold; }
        .failed { color: #dc2626; font-weight: bold; }

        /* Summary Section */
        .summary-row { background-color: #f9fafb; font-weight: bold; font-size: 14px; }
        
        /* Signatures */
        .signature-section { margin-top: 50px; width: 100%; }
        .sig-box { width: 45%; display: inline-block; text-align: center; }
        .sig-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-weight: bold; font-size: 13px; }
        .sig-title { font-size: 11px; color: #666; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; padding-bottom: 10px; }
    </style>
</head>
<body>

    <div class="school-header">
        <div class="deped-tag">Republic of the Philippines • Department of Education</div>
        <h1 class="school-name">FORT MAGSAYSAY NATIONAL HIGH SCHOOL</h1>
        <div class="school-info">Fort Magsaysay, Palayan City, Nueva Ecija • School ID: 305123</div>
    </div>

    <div class="report-title">OFFICIAL STUDENT PROGRESS REPORT CARD</div>

    <table class="info-table">
        <tr>
            <td class="label">NAME:</td>
            <td class="value">{{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }}</td>
            <td class="label" style="padding-left: 20px;">LRN:</td>
            <td class="value">{{ $student->lrn }}</td>
        </tr>
        <tr>
            <td class="label">GRADE:</td>
            <td class="value">{{ $student->section->grade_level }}</td>
            <td class="label" style="padding-left: 20px;">SECTION:</td>
            <td class="value">{{ $student->section->name }}</td>
        </tr>
        <tr>
            <td class="label">STRAND:</td>
            <td class="value">{{ $student->section->strand ?? 'N/A' }}</td>
            <td class="label" style="padding-left: 20px;">ADVISER:</td>
            <td class="value">
                {{ $student->section->advisor ? $student->section->advisor->first_name . ' ' . $student->section->advisor->last_name : '---' }}
            </td>
        </tr>
    </table>

    <table class="grades">
        <thead>
            <tr>
                <th>Learning Areas</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Final</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalAvg = 0; 
                $subjectCount = 0; 
            @endphp

            @foreach($subjects as $subject)
                @php
                    $q1 = $subject->grades->where('quarter', 1)->first()?->grade_value;
                    $q2 = $subject->grades->where('quarter', 2)->first()?->grade_value;
                    $q3 = $subject->grades->where('quarter', 3)->first()?->grade_value;
                    $q4 = $subject->grades->where('quarter', 4)->first()?->grade_value;
                    
                    $grades = collect([$q1, $q2, $q3, $q4])->filter();
                    $average = $grades->isNotEmpty() ? $grades->avg() : null;
                    
                    if($average) {
                        $totalAvg += $average;
                        $subjectCount++;
                    }
                @endphp
                <tr>
                    <td class="subject">{{ $subject->name }}</td>
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

            @php $gwa = $subjectCount > 0 ? $totalAvg / $subjectCount : 0; @endphp
            <tr class="summary-row">
                <td colspan="5" style="text-align: right; padding-right: 15px;">GENERAL WEIGHTED AVERAGE</td>
                <td>{{ $gwa > 0 ? number_format($gwa, 0) : '' }}</td>
                <td>{{ $gwa >= 75 ? 'PASSED' : ($gwa > 0 ? 'FAILED' : '') }}</td>
            </tr>
        </tbody>
    </table>

    [Image of an official school report card layout with DepEd header, student details, and a table of grades with signatures]

    <div class="signature-section">
        <div class="sig-box" style="float: left;">
            <div class="sig-line">
                {{ $student->section->advisor ? $student->section->advisor->first_name . ' ' . $student->section->advisor->last_name : '________________________' }}
            </div>
            <div class="sig-title">Class Adviser</div>
        </div>
        
        <div class="sig-box" style="float: right;">
            <div class="sig-line">MARIA LIZA R. CANLAS</div>
            <div class="sig-title">School Principal</div>
        </div>
    </div>

    <div class="footer">
        <p>This is a system-generated document based on official school records. Date Printed: {{ date('F d, Y') }}</p>
    </div>

</body>
</html>