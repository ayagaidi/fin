<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerAccount;

class LedgerReconcileController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'file' => ['required', 'file', function ($attr, $file, $fail) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    if (!in_array($ext, ['csv', 'txt'])) {
                        $fail("The file field must be a file of type: csv, txt.");
                    }
                }]
            ]);
            
            $path = $request->file('file')->getRealPath();
            $results = $this->parseAndReconcile($path);
            
            return view('reconciliation.results', $results);
        }
        
        return view('reconciliation.upload');
    }
    
    private function parseAndReconcile(string $path): array
    {
        $content = file_get_contents($path);
        
        $assets = $this->parseSection($content, '0010  ASSETS', '5685  LIABILITIES');
        $liabilities = $this->parseSection($content, '5685  LIABILITIES', 'END OF REPORT');
        
        $totalAssets = $this->extractTotal($content, '5680');
        $totalLiabilities = $this->extractTotal($content, '7295');
        
        $positiveAssets = array_filter($assets, fn($b) => $b > 0);
        $negativeLiabilities = array_filter($liabilities, fn($b) => $b < 0);
        
        return compact('assets', 'liabilities', 'totalAssets', 'totalLiabilities', 'positiveAssets', 'negativeLiabilities');
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
                if (preg_match('/(AC\.\d+\.TR\.[^\s]+)\s+(.+?)$/', $trimmed, $matches)) {
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
}