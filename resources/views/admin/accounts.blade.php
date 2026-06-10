<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الحسابات - Ledger Accounts</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .nav { margin-bottom: 1rem; }
        .nav a { margin-left: 1rem; text-decoration: none; color: #4f46e5; }
        .filters { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; margin: 1rem 0; padding: 1rem; background: #f9fafb; border-radius: 8px; }
        .filters input, .filters select { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .filters button { background: #4f46e5; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 0.9rem; }
        th, td { padding: 0.75rem; text-align: right; border: 1px solid #ddd; }
        th { background: #4f46e5; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background: #f9f9f9; }
        .positive { color: #059669; font-weight: bold; }
        .negative { color: #dc2626; font-weight: bold; }
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1rem; }
        .pagination a, .pagination span { padding: 0.5rem 0.75rem; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; }
        .pagination .current { background: #4f46e5; color: white; border-color: #4f46e5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('admin.files') }}">الملفات</a>
            <a href="{{ route('admin.upload') }}">رفع ملف جديد</a>
            <a href="{{ route('admin.accounts') }}">الحسابات</a>
        </div>

        <h1>بحث وعرض الحسابات</h1>

        <form method="GET" class="filters">
            <select name="file_id">
                <option value="">كل الملفات</option>
                @foreach($files as $f)
                    <option value="{{ $f->id }}" {{ request('file_id') == $f->id ? 'selected' : '' }}>
                        {{ $f->original_name }} ({{ $f->report_date ? $f->report_date->format('Y-m-d') : '' }})
                    </option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="من تاريخ">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="إلى تاريخ">
            <select name="section">
                <option value="">كل الأقسام</option>
                <option value="assets" {{ request('section') == 'assets' ? 'selected' : '' }}>أصول</option>
                <option value="liabilities" {{ request('section') == 'liabilities' ? 'selected' : '' }}>خصوم</option>
            </select>
            <select name="currency">
                <option value="">كل العملات</option>
                @php $currencies = $accounts->getCollection()->pluck('currency')->unique()->sort(); @endphp
                @foreach($currencies as $curr)
                    <option value="{{ $curr }}" {{ request('currency') == $curr ? 'selected' : '' }}>{{ $curr }}</option>
                @endforeach
            </select>
            <input type="text" name="account_code" value="{{ request('account_code') }}" placeholder="بحث بكود الحساب">
            <button type="submit">بحث</button>
            <a href="{{ route('admin.accounts') }}" style="padding: 0.5rem 1rem; background: #6b7280; color: white; border-radius: 4px; text-decoration: none; margin-right: 0.5rem;">إعادة تعيين</a>
        </form>

        <p style="margin: 0.5rem 0;">عدد النتائج: {{ $accounts->total() }}</p>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الملف</th>
                        <th>تاريخ التقرير</th>
                        <th>كود الحساب</th>
                        <th>القسم</th>
                        <th>العملة</th>
                        <th>الرصيد</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>{{ $account->id }}</td>
                            <td>{{ $account->ledgerFile->original_name ?? '-' }}</td>
                            <td>{{ $account->report_date ? $account->report_date->format('Y-m-d') : '-' }}</td>
                            <td>{{ $account->account_code }}</td>
                            <td>{{ $account->section == 'assets' ? 'أصول' : 'خصوم' }}</td>
                            <td>{{ $account->currency }}</td>
                            <td class="{{ $account->balance >= 0 ? 'positive' : 'negative' }}">
                                {{ number_format($account->balance, 3) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">لا توجد نتائج</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $accounts->links() }}
    </div>
</body>
</html>