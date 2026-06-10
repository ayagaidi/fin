<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LedgerFile;
use App\Models\LedgerAccount;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LedgerAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = LedgerFile::query();

        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('original_name', 'like', "%{$search}%");
        }

        $files = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.files', compact('files'));
    }

    public function upload(Request $request)
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

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . $originalName;
            $file->storeAs('', $filename, 'ledger');

            $path = storage_path('app/ledger_files/' . $filename);
            $reportDate = $this->extractDateFromFile($path);
            $totals = $this->parseTotals($path);

            $ledgerFile = LedgerFile::create([
                'filename' => $filename,
                'original_name' => $originalName,
                'report_date' => $reportDate,
                'total_assets' => $totals['assets'],
                'total_liabilities' => $totals['liabilities'],
            ]);

            $reconciliation = $this->parseAndReconcile($path, $reportDate);
            $reconciliation['file'] = $ledgerFile;

            return view('admin.upload', $reconciliation);
        }

        return view('admin.upload');
    }

    public function view(LedgerFile $file)
    {
        $path = storage_path('app/ledger_files/' . $file->filename);
        if (!file_exists($path)) {
            abort(404, 'الملف غير موجود');
        }
        $content = file_get_contents($path);
        return view('admin.view', compact('file', 'content'));
    }

    private function extractDateFromFile(string $path): ?string
    {
        $content = file_get_contents($path);
        if (preg_match('/AS AT CLOSE OF (\d{2} [A-Z]{3} \d{4})/', $content, $matches)) {
            $dateStr = $matches[1];
            $date = \DateTime::createFromFormat('d M Y', $dateStr);
            return $date ? $date->format('Y-m-d') : null;
        }
        return null;
    }

    private function parseTotals(string $path): array
    {
        $content = file_get_contents($path);
        $assets = 0;
        $liabilities = 0;

        if (preg_match('/"0010\s+ASSETS.*?([-]?\d{1,3}(?:,\d{3})*?\.\d{3})"/', $content, $m)) {
            $assets = (float) str_replace(',', '', $m[1] ?? 0);
        }
        if (preg_match('/"5685\s+LIABILITIES.*?([-]?\d{1,3}(?:,\d{3})*?\.\d{3})"/', $content, $m)) {
            $liabilities = (float) str_replace(',', '', $m[1] ?? 0);
        }

        return ['assets' => $assets, 'liabilities' => $liabilities];
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

    private function extractCurrency(string $accountCode): string
    {
        $parts = explode('.', $accountCode);
        return $parts[3] ?? 'LYD';
    }

    private function parseAndReconcile(string $path, ?string $reportDate): array
    {
        $content = file_get_contents($path);
        $content = preg_replace('/\x00.*$/', '', $content);

        $assets = $this->parseSection($content, '0010  ASSETS', '5685  LIABILITIES');
        $liabilities = $this->parseSection($content, '5685  LIABILITIES', 'END OF REPORT');

        $totalAssets = 0;
        $totalLiabilities = 0;
        if (preg_match('/"5680\s+TOTAL ASSETS\s+([-]?\d{1,3}(?:,\d{3})*?\.\d{3})"/', $content, $m)) {
            $totalAssets = (float) str_replace(',', '', $m[1]);
        }
        if (preg_match('/"7295\s+TOTAL LIABILITIES\s+([-]?\d{1,3}(?:,\d{3})*?\.\d{3})"/', $content, $m)) {
            $totalLiabilities = (float) str_replace(',', '', $m[1]);
        }

        $positiveAssets = array_filter($assets, fn($b) => $b > 0);
        $negativeLiabilities = array_filter($liabilities, fn($b) => $b < 0);

        if ($reportDate) {
            $now = now();
            $records = [];

            foreach ($assets as $account => $balance) {
                $records[] = [
                    'account_code' => $account,
                    'account_name' => null,
                    'section' => 'assets',
                    'balance' => $balance,
                    'currency' => $this->extractCurrency($account),
                    'report_date' => $reportDate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ($liabilities as $account => $balance) {
                $records[] = [
                    'account_code' => $account,
                    'account_name' => null,
                    'section' => 'liabilities',
                    'balance' => $balance,
                    'currency' => $this->extractCurrency($account),
                    'report_date' => $reportDate,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($records)) {
                LedgerAccount::insert($records);
            }
        }

        return compact('assets', 'liabilities', 'totalAssets', 'totalLiabilities', 'positiveAssets', 'negativeLiabilities', 'reportDate');
    }

    private function parseAccounts(string $content): array
    {
        $lines = explode("\n", $content);
        $accounts = [];
        $currentSection = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (strpos($trimmed, '0010  ASSETS') !== false) {
                $currentSection = 'assets';
            } elseif (strpos($trimmed, '5685  LIABILITIES') !== false) {
                $currentSection = 'liabilities';
            } elseif (preg_match('/^(\d{4})\s+(.+?)\s+(-?[\d,]+\.\d{3})$/', $trimmed, $m)) {
                $accounts[] = [
                    'line' => $m[1],
                    'description' => $m[2],
                    'balance' => (float) str_replace(',', '', $m[3]),
                    'section' => $currentSection,
                ];
            }
        }

        return $accounts;
    }
}