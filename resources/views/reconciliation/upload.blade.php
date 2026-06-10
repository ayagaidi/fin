<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسوية الميزانية - Reconciliation</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #4f46e5; padding-bottom: 0.5rem; }
        .form-group { margin: 1rem 0; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input[type="file"] { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4f46e5; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #4338ca; }
        .error { color: #dc2626; background: #fef2f2; padding: 1rem; border-radius: 4px; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>تسوية الميزانية - Reconciliation Upload</h1>
        
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
                <label for="file">اختر ملف CSV - Select CSV File</label>
                <input type="file" name="file" id="file" accept=".csv,.txt" required>
            </div>
            <button type="submit">بدء التسوية - Start Reconciliation</button>
        </form>
        
        <p style="margin-top: 2rem; color: #666;">
            <strong>تعليمات:</strong> الملف يجب أن يكون بصيغة NUB_FT_FINANCE<br>
            <strong>Instructions:</strong> File must be in NUB_FT_FINANCE format
        </p>
    </div>
</body>
</html>