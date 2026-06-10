<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نتائج التسوية - Reconciliation Results</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>=== مرجع محاسبة - Reconciliation Report ===</h1>
        <p>التاريخ: {{ now()->format('Y-m-d') }}</p>
        
        <div class="summary">
            <div class="card assets">
                <h3>ملخص الأصول - Assets Summary</h3>
                <p><strong>إجمالي الأصول:</strong> {{ number_format($totalAssets, 3) }}</p>
                <p><strong>مجموع الحسابات:</strong> {{ number_format(array_sum($assets), 3) }}</p>
                <p><strong>عدد الحسابات:</strong> {{ count($assets) }}</p>
                <p><strong>فرق:</strong> {{ number_format($totalAssets - array_sum($assets), 3) }}</p>
            </div>
            
            <div class="card liabilities">
                <h3>ملخص الخصوم - Liabilities Summary</h3>
                <p><strong>إجمالي الخصوم:</strong> {{ number_format($totalLiabilities, 3) }}</p>
                <p><strong>مجموع الحسابات:</strong> {{ number_format(array_sum($liabilities), 3) }}</p>
                <p><strong>عدد الحسابات:</strong> {{ count($liabilities) }}</p>
            </div>
            
            <div class="card discrepancy">
                <h3>فحص التوازن - Balance Check</h3>
                <p><strong>الفرق بين الأصول والخصوم:</strong> {{ number_format($totalAssets + $totalLiabilities, 3) }}</p>
            </div>
        </div>
        
        @if(count($positiveAssets) > 0)
            <div class="error">
                <h3>⚠️ مشاكل في حسابات الأصول</h3>
                <p>عدد حسابات الأصول الإيجابية (غير عادلة): {{ count($positiveAssets) }}</p>
                <p>مجموع الحسابات الإيجابية: {{ number_format(array_sum($positiveAssets), 3) }}</p>
            </div>
            
            <h3>أبرز الحسابات الإيجابية - Positive Asset Accounts (Top 10)</h3>
            <table>
                <thead>
                    <tr><th>الحساب - Account</th><th>الرصيد - Balance</th></tr>
                </thead>
                <tbody>
                    @foreach(collect($positiveAssets)->sortByDesc()->take(10) as $account => $balance)
                        <tr><td>{{ $account }}</td><td class="positive">{{ number_format($balance, 3) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="success">
                <h3>✅ جميع حسابات الأصول سالبة أو صفر</h3>
            </div>
        @endif
        
        @if(count($negativeLiabilities) > 0)
            <div class="warning">
                <h3>⚠️ حسابات خصوم سالبة - Negative Liability Accounts</h3>
                <p>عدد الحسابات السالبة: {{ count($negativeLiabilities) }}</p>
            </div>
        @endif
        
        <h3>أكبر حسابات الأصول - Top Asset Accounts (20)</h3>
        <table>
            <thead>
                <tr><th>الحساب - Account</th><th>الرصيد - Balance</th></tr>
            </thead>
            <tbody>
                @foreach(collect($assets)->sortByDesc(fn($b) => abs($b))->take(20) as $account => $balance)
                    <tr><td>{{ $account }}</td><td class="{{ $balance >= 0 ? 'positive' : 'negative' }}">{{ number_format($balance, 3) }}</td></tr>
                @endforeach
            </tbody>
        </table>
        
        <h3>أكبر حسابات الخصوم - Top Liability Accounts (20)</h3>
        <table>
            <thead>
                <tr><th>الحساب - Account</th><th>الرصيد - Balance</th></tr>
            </thead>
            <tbody>
                @foreach(collect($liabilities)->sortByDesc(fn($b) => abs($b))->take(20) as $account => $balance)
                    <tr><td>{{ $account }}</td><td class="{{ $balance >= 0 ? 'positive' : 'negative' }}">{{ number_format($balance, 3) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>