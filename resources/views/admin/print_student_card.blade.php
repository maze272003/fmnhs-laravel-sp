<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Report Card - {{ $student->last_name }}</title>
    <style>
        /* General Reset */
        body { 
            font-family: 'Arial', sans-serif; 
            color: #000; 
            line-height: 1.3; 
            background: #e5e7eb; /* Grey background for screen */
            margin: 0;
            padding: 20px;
        }

        /* Paper Simulation (Screen View) */
        .page-container {
            background: white;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            margin: 0 auto;
            padding: 15mm; /* Margins */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            box-sizing: border-box;
            position: relative;
        }

        /* Screen-only Controls */
        .screen-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .btn-print { background: #1e3a8a; color: white; }
        .btn-back { background: #fff; color: #333; }

        /* --- YOUR DESIGN STYLES --- */
        .school-header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; }
        .deped-tag { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #444; margin-bottom: 2px; }
        .school-name { font-size: 18px; font-weight: 900; color: #1e3a8a; margin: 0; text-transform: uppercase; }
        .school-info { font-size: 10px; color: #444; margin-top: 2px; }
        
        .report-title { 
            text-align: center; font-size: 12px; font-weight: bold; 
            background: #f3f4f6; padding: 6px; margin: 15px 0; 
            border: 1px solid #ccc; text-transform: uppercase; letter-spacing: 1px; 
        }

        /* Student Info Grid */
        .info-table { width: 100%; margin-bottom: 20px; font-size: 11px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; }
        .label { font-weight: bold; color: #444; width: 80px; }
        .value { border-bottom: 1px solid #000; padding-left: 5px; font-weight: bold; color: #000; }

        /* Grades Table */
        table.grades { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        table.grades th { 
            background-color: #1e3a8a; color: white; padding: 8px; 
            text-transform: uppercase; border: 1px solid #000; 
            font-size: 10px; -webkit-print-color-adjust: exact; 
        }
        table.grades td { border: 1px solid #000; padding: 6px; text-align: center; }
        table.grades td.subject { text-align: left; font-weight: bold; padding-left: 10px; width: 40%; }
        
        .passed { color: #000; font-weight: bold; } /* Standard black for print legibility */
        .failed { color: #dc2626; font-weight: bold; }

        .summary-row td { background-color: #f9fafb; font-weight: bold; border-top: 2px solid #000; }

        /* Signatures */
        .signature-section { margin-top: 50px; width: 100%; page-break-inside: avoid; }
        .sig-table { width: 100%; }
        .sig-table td { width: 50%; text-align: center; vertical-align: bottom; padding: 0 20px; }
        .sig-line { 
            border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; 
            font-weight: bold; font-size: 11px; text-transform: uppercase; 
        }
        .sig-title { font-size: 10px; color: #444; }

        .footer { 
            position: absolute; bottom: 15mm; left: 0; right: 0; 
            text-align: center; font-size: 9px; color: #666; 
            border-top: 1px solid #eee; padding-top: 5px; 
        }

        /* PRINT MEDIA QUERY (Crucial) */
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page-container { width: 100%; margin: 0; padding: 0; box-shadow: none; border: none; }
            .screen-controls { display: none !important; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="screen-controls">
        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-back">Back</a>
        <button onclick="window.print()" class="btn btn-print">Print Card</button>
    </div>

    <div class="page-container">
        
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
                <td class="label" style="padding-left: 20px; width: 40px;">LRN:</td>
                <td class="value">{{ $student->lrn }}</td>
            </tr>
            <tr>
                <td class="label">GRADE:</td>
                <td class="value">{{ $student->section->grade_level ?? 'N/A' }}</td>
                <td class="label" style="padding-left: 20px;">SECTION:</td>
                <td class="value">{{ $student->section->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">STRAND:</td>
                <td class="value">{{ $student->section->strand ?? 'N/A' }}</td>
                <td class="label" style="padding-left: 20px;">SY:</td>
                <td class="value">{{ $schoolYear->school_year ?? 'N/A' }}</td>
            </tr>
        </table>

        <table class="grades">
            <thead>
                <tr>
                    <th>Learning Areas</th>
                    <th width="8%">Q1</th>
                    <th width="8%">Q2</th>
                    <th width="8%">Q3</th>
                    <th width="8%">Q4</th>
                    <th width="10%">Final</th>
                    <th width="15%">Remarks</th>
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
                        
                        $grades = collect([$q1, $q2, $q3, $q4])->filter(fn($v) => !is_null($v) && $v !== '');
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
                    <td>
                        @if($gwa > 0)
                            <span class="{{ $gwa >= 75 ? 'passed' : 'failed' }}">
                                {{ $gwa >= 75 ? 'PASSED' : 'FAILED' }}
                            </span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <table class="sig-table">
                <tr>
                    <td>
                        <div class="sig-line">
                            {{ $student->section->advisor ? $student->section->advisor->first_name . ' ' . $student->section->advisor->last_name : '________________________' }}
                        </div>
                        <div class="sig-title">Class Adviser</div>
                    </td>
                    <td>
                        <div class="sig-line">MARIA LIZA R. CANLAS</div>
                        <div class="sig-title">School Principal IV</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>This is a system-generated document based on official school records. Date Printed: {{ date('F d, Y') }} | FMNHS SIS</p>
        </div>

    </div>

</body>
</html>