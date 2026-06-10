# مرجع محاسبة - Ledger Reconciliation System

نظام لتسوية وتحليل ملفات NUB_FT_FINANCE للميزانية العامة.

## البنية - File Structure

```
app/
├── Console/Commands/
│   └── ReconcileLedger.php    # أمر الكونسول لتسوية الميزانية
├── Http/Controllers/
│   └── LedgerReconcileController.php  # وحدة التحكم للويب
└── Models/
    └── LedgerAccount.php      # نموذج الحسابات

database/
└── migrations/
    └── 2026_06_10_095447_create_ledger_accounts_table.php  # جدول الحسابات

resources/views/
└── reconciliation/
    ├── upload.blade.php      # نموذج رفع الملف
    └── results.blade.php     # عرض النتائج
```

## الاستخدام - Usage

### أمر الكونسول
```bash
php artisan ledger:reconcile [file]
```

### الويب
```
GET|POST /reconciliation
```

## مخرجات التسوية - Reconciliation Output

- ملخص الأصول والخصوم مع المجاميع
- الفرق بين المجاميع الإجمالية والحسابات الفردية
- اكتشاف الحسابات ذات الرصيد الإيجابي في الأصول (غير عادلة)
- الفحص المتقاطع للحسابات المشتركة بين الأصول والخصوم
- أكبر 15 حساب في كل قسم

## المشاكل المكتشفة - Detected Issues

1. **الأصول ذات الرصيد الإيجابي**: 18 حساب (يجب أن تكون سالبة)
2. **الخصوم السالبة**: 7 حسابات (قد تكون مشكلة في التصنيف)
3. **الحسابات المشتركة**: 178 حساب موجود في كلا القسمين (وديون العملاء)
4. **الفرق الكلي**: 1,747,400.761 بين المجاميع