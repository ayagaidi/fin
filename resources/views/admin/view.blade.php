<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>عرض ملف - View File</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .nav { margin-bottom: 1rem; }
        .nav a { margin-left: 1rem; text-decoration: none; color: #4f46e5; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.5rem; text-align: right; border: 1px solid #ddd; }
        th { background: #4f46e5; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .positive { color: #059669; }
        .negative { color: #dc2626; }
        pre { background: #f5f5f5; padding: 1rem; border-radius: 4px; overflow-x: auto; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('admin.files') }}">العودة للملفات</a>
        </div>

        <h1>عرض الملف: {{ $file->original_name }}</h1>

        <div style="margin-bottom: 1rem;">
            <strong>تاريخ التقرير:</strong> {{ $file->report_date ? $file->report_date->format('Y-m-d') : '-' }}<br>
            <strong>إجمالي الأصول:</strong> {{ number_format($file->total_assets, 3) }}<br>
            <strong>إجمالي الخصوم:</strong> {{ number_format($file->total_liabilities, 3) }}
        </div>

        <h2>المحتوى - Raw Content</h2>
        <pre>{{ $content }}</pre>
    </div>
</body>
</html>