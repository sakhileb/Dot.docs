<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }
        .page { padding: 40px 50px; }
        h1.doc-title {
            font-size: 22pt;
            font-weight: bold;
            margin-bottom: 4px;
            color: #1e1b4b;
        }
        .meta {
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .content h1 { font-size: 18pt; margin-top: 20px; }
        .content h2 { font-size: 15pt; margin-top: 16px; }
        .content h3 { font-size: 13pt; margin-top: 12px; }
        .content p  { margin: 8px 0; }
        .content ul, .content ol { margin: 8px 0 8px 20px; }
        .content blockquote {
            border-left: 3px solid #c7d2fe;
            padding-left: 12px;
            color: #4b5563;
            margin: 10px 0;
        }
        .content code {
            background: #f3f4f6;
            padding: 1px 4px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 10pt;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 10pt;
        }
        .content table th, .content table td {
            border: 1px solid #d1d5db;
            padding: 5px 8px;
        }
        .content table th { background: #f3f4f6; font-weight: bold; }
        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="page">
        <h1 class="doc-title">{{ $document->title }}</h1>
        <div class="meta">
            Exported from Dot.docs · {{ now()->format('F j, Y') }}
            · Version {{ $document->version }}
            @if($document->owner) · {{ $document->owner->name }} @endif
        </div>
        <div class="content">
            {!! $document->content !!}
        </div>
    </div>
    <div class="footer">Dot.docs — {{ $document->title }}</div>
</body>
</html>
