<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReconcileLedger extends Command
{
    protected $signature = 'ledger:reconcile {file? : Path to the CSV file}';
    protected $description = 'Reconcile assets and liabilities from NUB_FT_FINANCE CSV - Identifies discrepancies between accounts';

    public function handle()
    {
        $file = $this->argument('file');
        if (!$file) {
            $file = public_path('NUB_FT_FINANCE (2).csv');
        }
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $content = file_get_contents($file);
        
        $assetsSection = $this->parseSection($content, '0010  ASSETS', '5685  LIABILITIES');
        $liabilitiesSection = $this->parseSection($content, '5685  LIABILITIES', 'END OF REPORT');
        
        $totalAssets = $this->extractTotal($content, '5680');
        $totalLiabilities = $this->extractTotal($content, '7295');
        
        $this->outputResults($assetsSection, $liabilitiesSection, $totalAssets, $totalLiabilities);
        
        return 0;
    }
    
    private function parseSection(string $content, string $startMarker, string $endMarker): array
    {
        $lines = explode("\n", $content);
        $accounts = [];
        $inSection = false;
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            if (strpos($trimmed, $startMarker) !== false) {
                $inSection = true;
            } elseif (strpos($trimmed, $endMarker) !== false && $inSection) {
                break;
            }
            
            if ($inSection) {
                if (preg_match('/(AC\.\d+\.TR\.[^\s]+)/', $trimmed, $matches)) {
                    $fullAccount = $matches[1];
                    
                    if (preg_match('/([-]?\d{1,3}(?:,\d{3})*?\.\d{3})["\s]*$/', $trimmed, $balanceMatch)) {
                        $balance = (float) str_replace(',', '', $balanceMatch[1]);
                        $accounts[$fullAccount] = $balance;
                    }
                }
            }
        }
        
        return $accounts;
    }
    
    private function extractTotal(string $content, string $lineCode): float
    {
        if (preg_match('/"' . $lineCode . '\s+.*?([-]?\d{1,3}(?:,\d{3})*?\.\d{3})"/', $content, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }
        return 0;
    }
    
    private function outputResults(array $assets, array $liabilities, float $totalAssets, float $totalLiabilities): void
    {
        $sumAssets = array_sum($assets);
        $sumLiabilities = array_sum($liabilities);
        
        $this->info("=== مرجع محاسبة - Reconciliation Report ===");
        $this->info("التاريخ: " . now()->format('Y-m-d'));
        $this->info("");
        
        $this->info("ملخص الأصول - Assets Summary:");
        $this->info("  إجمالي الأصول (من الملف): " . number_format($totalAssets, 3));
        $this->info("  مجموع الحسابات الفردية: " . number_format($sumAssets, 3));
        $this->info("  عدد الحسابات: " . count($assets));
        $this->info("  فرق: " . number_format($totalAssets - $sumAssets, 3));
        $this->info("");
        
        $this->info("ملخص الخصوم - Liabilities Summary:");
        $this->info("  إجمالي الخصوم (من الملف): " . number_format($totalLiabilities, 3));
        $this->info("  مجموع الحسابات الفردية: " . number_format($sumLiabilities, 3));
        $this->info("  عدد الحسابات: " . count($liabilities));
        $this->info("");
        
        $this->checkDiscrepancies($assets, $liabilities, $totalAssets, $totalLiabilities);
    }
    
    private function checkDiscrepancies(array $assets, array $liabilities, float $totalAssets, float $totalLiabilities): void
    {
        $this->info("=== النتائج - Reconciliation Check ===");
        
        // Check for positive asset accounts (should be negative or zero)
        $positiveAssets = array_filter($assets, fn($b) => $b > 0);
        if (count($positiveAssets) > 0) {
            $this->error("  عدد حسابات الأصول الإيجابية (غير عادلة): " . count($positiveAssets));
            $this->error("  مجموع الحسابات الإيجابية: " . number_format(array_sum($positiveAssets), 3));
            $this->error("  قد تكون مشاكل في:");
            $this->error("    - إدخال البيانات");
            $this->error("    - تصنيف الحساب");
            
            arsort($positiveAssets);
            $this->info("  أبرز الحسابات الإيجابية (الأعلى 10):");
            $count = 0;
            foreach ($positiveAssets as $account => $balance) {
                if ($count++ >= 10) break;
                $this->line("    {$account}: " . number_format($balance, 3));
            }
        } else {
            $this->info("  ✓ جميع حسابات الأصول سالبة أو صفر (متوازن)");
        }
        
        // Check liability section for negative accounts that should be positive
        $negativeLiabilities = array_filter($liabilities, fn($b) => $b < 0);
        if (count($negativeLiabilities) > 0) {
            $this->warn("  عدد حسابات الخصوم السالبة (ربما مشكلة): " . count($negativeLiabilities));
            $this->warn("  مجموع الحسابات السالبة: " . number_format(array_sum($negativeLiabilities), 3));
            
            asort($negativeLiabilities);
            $this->info("  أبرز الحسابات السالبة (أقل 5):");
            $count = 0;
            foreach ($negativeLiabilities as $account => $balance) {
                if ($count++ >= 5) break;
                $this->line("    {$account}: " . number_format($balance, 3));
            }
        }
        
        // Find accounts that appear in both sections with wrong signs
        $this->checkCrossSectionAccounts($assets, $liabilities);
        
        $this->info("");
        $this->info("=== أكبر الحسابات - Top Accounts ===");
        $this->showTopAccounts($assets, $liabilities);
    }
    
    private function checkCrossSectionAccounts(array $assets, array $liabilities): void
    {
        $this->info("");
        $this->info("=== الفحص المتقاطع - Cross-Section Analysis ===");
        
        // Find asset accounts that also appear as positive liabilities (customer deposits)
        $crossMatched = [];
        foreach ($assets as $account => $aBalance) {
            if (isset($liabilities[$account]) && $liabilities[$account] > 0) {
                $crossMatched[] = [
                    'account' => $account,
                    'asset_balance' => $aBalance,
                    'liability_balance' => $liabilities[$account]
                ];
            }
        }
        
        if (count($crossMatched) > 0) {
            $this->warn("  عدد الحسابات المشتركة بين الأصول والخصوم (موجب): " . count($crossMatched));
            
            usort($crossMatched, fn($a, $b) => abs($b['liability_balance']) <=> abs($a['liability_balance']));
            $this->info("  أبرز 5 حسابات مشتركة:");
            foreach (array_slice($crossMatched, 0, 5) as $item) {
                $this->line("    {$item['account']}: أصول=" . number_format($item['asset_balance'], 3) . ", خصوم=" . number_format($item['liability_balance'], 3));
            }
        }
        
        // Find accounts only in assets, not in liabilities
        $onlyAssets = array_diff_key($assets, $liabilities);
        $this->info("  حسابات موجودة في الأصول فقط (غير مُطابقة): " . count($onlyAssets));
        
        // Find accounts only in liabilities, not in assets
        $onlyLiabilities = array_diff_key($liabilities, $assets);
        $this->info("  حسابات موجودة في الخصوم فقط (غير مُطابقة): " . count($onlyLiabilities));
    }
    
    private function showTopAccounts(array $assets, array $liabilities): void
    {
        $sortedAssets = $assets;
        uasort($sortedAssets, fn($a, $b) => abs($b) <=> abs($a));
        
        $this->info("");
        $this->info("أكبر حسابات الأصول - Top Asset Accounts (15):");
        foreach (array_slice($sortedAssets, 0, 15) as $account => $balance) {
            $this->line("  {$account}: " . number_format($balance, 3));
        }

        $sortedLiabilities = $liabilities;
        uasort($sortedLiabilities, fn($a, $b) => abs($b) <=> abs($a));
        
        $this->info("");
        $this->info("أكبر حسابات الخصوم - Top Liability Accounts (15):");
        foreach (array_slice($sortedLiabilities, 0, 15) as $account => $balance) {
            $this->line("  {$account}: " . number_format($balance, 3));
        }
    }
}