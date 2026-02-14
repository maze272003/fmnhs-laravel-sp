<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - {{ $portfolio->student->first_name ?? 'Student' }} {{ $portfolio->student->last_name ?? '' }}</title>
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
        .portfolio-info {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        .portfolio-info h3 {
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
        .portfolio-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .portfolio-item h4 {
            font-size: 13pt;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .portfolio-item .date {
            font-size: 10pt;
            color: #718096;
            margin-bottom: 10px;
        }
        .portfolio-item .content {
            font-size: 11pt;
            color: #4a5568;
        }
        .portfolio-item .reflection {
            background: #f7fafc;
            padding: 10px;
            border-left: 3px solid #1a365d;
            margin-top: 10px;
            font-style: italic;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10pt;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .no-items {
            text-align: center;
            padding: 40px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Portfolio</h1>
        <h2>FMNHS Student Information System</h2>
    </div>

    <div class="portfolio-info">
        <h3>Portfolio Details</h3>
        <div class="info-grid">
            <div class="info-item"><span class="info-label">Student Name:</span> {{ $portfolio->student->first_name ?? '' }} {{ $portfolio->student->last_name ?? '' }}</div>
            <div class="info-item"><span class="info-label">Title:</span> {{ $portfolio->title ?? 'Untitled' }}</div>
            <div class="info-item"><span class="info-label">Description:</span> {{ $portfolio->description ?? 'No description' }}</div>
            <div class="info-item"><span class="info-label">Created:</span> {{ $portfolio->created_at->format('M d, Y') ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="section">
        <h3 class="section-title">Portfolio Items</h3>
        
        @if($portfolio->items->isNotEmpty())
            @foreach($portfolio->items as $item)
            <div class="portfolio-item">
                <h4>{{ $item->title ?? 'Untitled Item' }}</h4>
                <div class="date">Added: {{ $item->created_at->format('F d, Y') ?? 'N/A' }}</div>
                <div class="content">
                    {{ $item->content ?? 'No content available.' }}
                </div>
                @if($item->reflection)
                <div class="reflection">
                    <strong>Reflection:</strong> {{ $item->reflection }}
                </div>
                @endif
            </div>
            @endforeach
        @else
            <div class="no-items">
                <p>No portfolio items have been added yet.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('F d, Y \a\t h:i A') ?? now()->format('F d, Y \a\t h:i A') }}</p>
        <p>This portfolio was exported from the FMNHS Student Information System</p>
    </div>
</body>
</html>
