<!DOCTYPE html>
<html>
<head>
    <title>New Announcement</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;">

    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px;">
        <h2 style="color: #2d3748;">📢 New Announcement from Admin</h2>
        
        <h3 style="color: #047857;">{{ $announcement->title }}</h3>
        
        <p style="white-space: pre-wrap; color: #4a5568;">{{ $announcement->content }}</p>

        @if($announcement->image)
            <div style="margin-top: 20px; text-align: center;">
                <p style="font-weight: bold; font-size: 12px; color: #718096; text-transform: uppercase;">Attached Media:</p>
                
                @php
                    // Kunin ang direct URL galing sa S3
                    $mediaUrl = \Illuminate\Support\Facades\Storage::disk('s3')->url($announcement->image);
                    $extension = pathinfo($announcement->image, PATHINFO_EXTENSION);
                    $isVideo = in_array(strtolower($extension), ['mp4', 'mov', 'avi']);
                @endphp

                @if($isVideo)
                    {{-- 
                       NOTE: Email clients DO NOT support embedded video players tags.
                       Ang best practice ay maglagay ng Link button.
                    --}}
                    <div style="background: #edf2f7; padding: 20px; border-radius: 8px;">
                        <p>📹 <strong>This announcement includes a video.</strong></p>
                        <a href="{{ $mediaUrl }}" style="background-color: #047857; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                            Watch Video Here
                        </a>
                    </div>
                @else
                    {{-- Images work fine in emails --}}
                    <img src="{{ $mediaUrl }}" alt="Announcement Image" style="max-width: 100%; border-radius: 8px; border: 1px solid #e2e8f0;">
                @endif
            </div>
        @endif

        <hr style="border: none; border-top: 1px solid #eee; margin-top: 30px;">
        <p style="font-size: 12px; color: #aaa; text-align: center;">Fort Magsaysay National High School Portal</p>
    </div>

</body>
</html>