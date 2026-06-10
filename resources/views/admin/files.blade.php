<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إدارة الملفات - Ledger Admin</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .nav { margin-bottom: 1rem; }
        .nav a { margin-left: 1rem; text-decoration: none; color: #4f46e5; }
        .search-form { display: flex; gap: 0.5rem; margin: 1rem 0; flex-wrap: wrap; }
        .search-form input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .search-form button { background: #4f46e5; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; }
        .btn-upload { background: #059669; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.75rem; text-align: right; border: 1px solid #ddd; }
        th { background: #4f46e5; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .success { background: #10b981; color: white; padding: 0.75rem; border-radius: 4px; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('admin.files') }}">الملفات</a>
            <a href="{{ route('admin.upload') }}">رفع ملف جديد</a>
        </div>

        <h1>إدارة الملفات - Ledger Files</h1>

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="search-form">
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="من تاريخ">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="إلى تاريخ">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث في اسم الملف">
            <button type="submit">بحث</button>
            <a href="{{ route('admin.files') }}" style="padding: 0.5rem 1rem; background: #6b7280; color: white; border-radius: 4px; text-decoration: none;">إعادة تعيين</a>
        </form>

        <a href="{{ route('admin.upload') }}" class="btn-upload">رفع ملف جديد</a>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الملف</th>
                    <th>تاريخ التقرير</th>
                    <th>إجمالي الأصول</th>
                    <th>إجمالي الخصوم</th>
                    <th>تاريخ الرفع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                    <tr>
                        <td>{{ $file->id }}</td>
                        <td>{{ $file->original_name }}</td>
                        <td>{{ $file->report_date ? $file->report_date->format('Y-m-d') : '-' }}</td>
                        <td>{{ number_format($file->total_assets, 3) }}</td>
                        <td>{{ number_format($file->total_liabilities, 3) }}</td>
                        <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.view', $file) }}">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">لا توجد ملفات</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $files->links() }}
    </div>
</body>
</html>