<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>رفع ملف - Upload File</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4f46e5; padding-bottom: 0.5rem; }
        h2 { color: #4f46e5; margin-top: 1.5rem; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.5rem; text-align: right; border: 1px solid #ddd; }
        th { background: #4f46e5; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .positive { color: #059669; }
        .negative { color: #dc2626; }
        .warning { background: #fffbeb; padding: 1rem; border-radius: 4px; border-left: 4px solid #f59e0b; margin: 1rem 0; }
        .error { background: #fef2f2; padding: 1rem; border-radius: 4px; border-left: 4px solid #dc2626; margin: 1rem 0; }
        .success { background: #ecfdf5; padding: 1rem; border-radius: 4px; border-left: 4px solid #10b981; margin: 1rem 0; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin: 1rem 0; }
        .card { padding: 1rem; border-radius: 8px; }
        .card.assets { background: #fef2f2; }
        .card.liabilities { background: #eff6ff; }
        .card.discrepancy { background: #fffbeb; }
        .upload-form { max-width: 600px; margin: 0 auto; background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .form-group { margin: 1rem 0; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input[type="file"] { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; width: 100%; }
        button { background: #4f46e5; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #4338ca; }
        .nav { margin-bottom: 1rem; }
        .nav a { margin-left: 1rem; text-decoration: none; color: #4f46e5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav">
            <a href="{{ route('admin.files') }}">العودة للملفات</a>
        </div>

        <div class="upload-form">
            <h1>رفع ملف مالي - Upload Ledger File</h1>

            @if($errors->any())
                <div class="error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file">اختر ملف CSV أو TXT</label>
                    <input type="file" name="file" id="file" accept=".csv,.txt" required>
                </div>
                <button type="submit">رفع الملف</button>
            </form>
        </div>

        @isset($totalAssets, $totalLiabilities, $assets, $liabilities, $positiveAssets, $negativeLiabilities)
            <h1>=== مرجع محاسبة - Reconciliation Report ===</h1>
            <p>التاريخ: {{ $reportDate ?? now()->format('Y-m-d') }}</p>

            <div class="summary">
                <div class="card assets">
                    <h3>ملخص الأصول - Assets Summary</h3>
                    <p><strong>إجمالي الأصول (من الملف):</strong> {{ number_format($totalAssets ?? 0, 3) }}</p>
                    <p><strong>مجموع الحسابات الفردية:</strong> {{ number_format(array_sum($assets ?? []), 3) }}</p>
                    <p><strong>عدد الحسابات:</strong> {{ count($assets ?? []) }}</p>
                    <p><strong>فرق:</strong> {{ number_format(($totalAssets ?? 0) - array_sum($assets ?? []), 3) }}</p>
                </div>

                <div class="card liabilities">
                    <h3>ملخص الخصوم - Liabilities Summary</h3>
                    <p><strong>إجمالي الخصوم (من الملف):</strong> {{ number_format($totalLiabilities ?? 0, 3) }}</p>
                    <p><strong>مجموع الحسابات الفردية:</strong> {{ number_format(array_sum($liabilities ?? []), 3) }}</p>
                    <p><strong>عدد الحسابات:</strong> {{ count($liabilities ?? []) }}</p>
                </div>
            </div>

            @if(count($positiveAssets ?? []) > 0)
                <div class="error">
                    <h3>عدد حسابات الأصول الإيجابية (غير عادلة): {{ count($positiveAssets) }}</h3>
                    <p>مجموع الحسابات الإيجابية: {{ number_format(array_sum($positiveAssets), 3) }}</p>
                </div>

                <h3>أبرز الحسابات الإيجابية - Positive Asset Accounts (Top 10)</h3>
                <table>
                    <thead>
                        <tr><th>الحساب - Account Code</th><th>العمله - Currency</th><th>الرصيد - Balance</th></tr>
                    </thead>
                    <tbody>
                        @foreach(collect($positiveAssets)->sortByDesc(fn($b) => $b)->take(10) as $account => $balance)
                            @php $parts = explode('.', $account); @endphp
                            <tr><td>{{ $parts[1] ?? $account }}</td><td>{{ $parts[3] ?? '' }}</td><td class="positive">{{ number_format($balance, 3) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="success">
                    <h3>جميع حسابات الأصول سالبة أو صفر</h3>
                </div>
            @endif

            @if(count($negativeLiabilities ?? []) > 0)
                <div class="warning">
                    <h3>حسابات خصوم سالبة - Negative Liability Accounts</h3>
                    <p>عدد الحسابات السالبة: {{ count($negativeLiabilities) }}</p>
                    <p>مجموع الحسابات السالبة: {{ number_format(array_sum($negativeLiabilities), 3) }}</p>
                </div>

                <h3>أبرز الحسابات السالبة - Top Negative Liability Accounts (Top 5)</h3>
                <table>
                    <thead>
                        <tr><th>الحساب - Account Code</th><th>العمله - Currency</th><th>الرصيد - Balance</th></tr>
                    </thead>
                    <tbody>
                        @foreach(collect($negativeLiabilities)->sortBy(fn($b) => $b)->take(5) as $account => $balance)
                            @php $parts = explode('.', $account); @endphp
                            <tr><td>{{ $parts[1] ?? $account }}</td><td>{{ $parts[3] ?? '' }}</td><td class="negative">{{ number_format($balance, 3) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <h3>أكبر حسابات الأصول - Top Asset Accounts (15)</h3>
            <table>
                <thead>
                    <tr><th>الحساب - Account Code</th><th>العمله - Currency</th><th>الرصيد - Balance</th></tr>
                </thead>
                <tbody>
                    @foreach(collect($assets ?? [])->sortByDesc(fn($b) => abs($b))->take(15) as $account => $balance)
                        @php $parts = explode('.', $account); @endphp
                        <tr><td>{{ $parts[1] ?? $account }}</td><td>{{ $parts[3] ?? '' }}</td><td class="{{ $balance >= 0 ? 'positive' : 'negative' }}">{{ number_format($balance, 3) }}</td></tr>
                    @endforeach
                </tbody>
            </table>

            <h3>أكبر حسابات الخصوم - Top Liability Accounts (15)</h3>
            <table>
                <thead>
                    <tr><th>الحساب - Account Code</th><th>العمله - Currency</th><th>الرصيد - Balance</th></tr>
                </thead>
                <tbody>
                    @foreach(collect($liabilities ?? [])->sortByDesc(fn($b) => abs($b))->take(15) as $account => $balance)
                        @php $parts = explode('.', $account); @endphp
                        <tr><td>{{ $parts[1] ?? $account }}</td><td>{{ $parts[3] ?? '' }}</td><td class="{{ $balance >= 0 ? 'positive' : 'negative' }}">{{ number_format($balance, 3) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endisset
    </div>
</body>
</html>
