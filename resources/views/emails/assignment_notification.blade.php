<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f8f9fa; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 20px 0 30px 0;">
                <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                    
                    <tr>
                        <td bgcolor="#1e8e3e" style="padding: 24px 32px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <h1 style="color: #ffffff; font-size: 22px; margin: 0; font-weight: 500;">
                                            {{ $assignment->subject?->name ?? 'New Class Update' }}
                                        </h1>
                                        <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 4px 0 0 0;">
                                            {{ $assignment->subject?->code ?? '' }} — Grade {{ $assignment->section?->grade_level ?? '' }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px;">
                            <table width="100%">
                                <tr>
                                    <td style="display: flex; align-items: center; margin-bottom: 24px;">
                                        <div style="width: 40px; height: 40px; background-color: #e8f0fe; color: #1967d2; border-radius: 50%; display: inline-block; text-align: center; line-height: 40px; font-weight: bold; margin-right: 12px; font-size: 16px;">
                                            {{ substr($assignment->teacher->first_name, 0, 1) }}
                                        </div>
                                        <div style="display: inline-block; vertical-align: middle;">
                                            <p style="margin: 0; font-size: 15px; color: #3c4043;">
                                                <strong>{{ $assignment->teacher->first_name }} {{ $assignment->teacher->last_name }}</strong> posted a new assignment.
                                            </p>
                                            <p style="margin: 0; font-size: 12px; color: #70757a;">
                                                {{ \Carbon\Carbon::now()->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 10px;">
                                        <h2 style="font-size: 20px; color: #1e8e3e; margin: 0 0 12px 0; font-weight: 500;">
                                            {{ $assignment->title }}
                                        </h2>
                                        <p style="font-size: 14px; color: #3c4043; line-height: 1.6; margin: 0 0 20px 0;">
                                            {{ Str::limit($assignment->description, 200) }}
                                        </p>
                                        
                                        <table border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 24px;">
                                            <tr>
                                                <td style="background-color: #fce8e6; color: #d93025; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; border: 1px solid #fad2cf;">
                                                    DUE: {{ \Carbon\Carbon::parse($assignment->deadline)->format('M d, h:i A') }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 20px; border-top: 1px solid #f1f3f4;">
                                        <a href="{{ url('/student/assignments') }}" style="display: inline-block; padding: 10px 24px; background-color: #1e8e3e; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: 500; font-size: 14px; box-shadow: 0 1px 2px 0 rgba(60,64,67,0.302), 0 1px 3px 1px rgba(60,64,67,0.149);">
                                            Open Assignment
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px 32px; background-color: #f1f3f4; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #70757a;">
                                This is an automated notification from <strong>Fort Magsaysay National High School SIS</strong>.
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 11px; color: #9aa0a6;">
                                &copy; 2026 Student Information System v2.0
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>