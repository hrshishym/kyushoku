<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MonthlyPdf;
use App\Services\ClaudeApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    protected $claudeService;

    public function __construct(ClaudeApiService $claudeService)
    {
        $this->claudeService = $claudeService;
    }

    public function index()
    {
        $todayMenu = Menu::getTodaysMenu();
        $tomorrowMenu = Menu::getTomorrowsMenu();
        
        // 最近アップロードされた月次PDFを取得（ユーザー固有）
        $recentPdfs = MonthlyPdf::where('user_id', auth()->id())
                                ->orderBy('year', 'desc')
                                ->orderBy('month', 'desc')
                                ->limit(3)
                                ->get();
        
        return view('menus.index', compact('todayMenu', 'tomorrowMenu', 'recentPdfs'));
    }

    public function uploadForm()
    {
        return view('menus.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:20480', // 20MB max（1ヶ月分は大きい可能性）
            'target_year' => 'required|integer|min:2020|max:2030',
            'target_month' => 'required|integer|min:1|max:12',
        ]);

        DB::beginTransaction();
        
        try {
            $year = $request->target_year;
            $month = $request->target_month;

            // 既存の同月データをチェック
            $existingPdf = MonthlyPdf::findByYearMonth($year, $month);
            if ($existingPdf) {
                // 既存データを削除
                $this->deleteExistingMonthData($existingPdf);
            }

            // PDFファイルを保存
            $file = $request->file('pdf_file');
            $fileName = sprintf('%04d%02d_%s', $year, $month, time() . '_' . $file->getClientOriginalName());
            $filePath = $file->storeAs('monthly_menus', $fileName, 'public');

            // MonthlyPdfレコードを作成
            $monthlyPdf = MonthlyPdf::create([
                'user_id' => auth()->id(),
                'year' => $year,
                'month' => $month,
                'pdf_path' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
                'total_days_parsed' => 0,
                'parsing_status' => [],
            ]);

            // Claude APIで1ヶ月分の献立を解析
            $pdfFullPath = storage_path('app/public/' . $filePath);
            $parseResult = $this->claudeService->parseMonthlyMenuFromPdf($pdfFullPath, $year, $month);

            // 解析結果をデータベースに保存
            $savedMenus = $this->saveMonthlyMenus($parseResult['menus']);
            
            // MonthlyPdfの解析状況を更新
            $monthlyPdf->update([
                'total_days_parsed' => $parseResult['total_days'],
                'parsing_status' => $this->buildParsingStatus($parseResult['menus']),
            ]);

            DB::commit();

            return redirect()->route('menu.index')
                ->with('success', sprintf(
                    '%d年%d月の献立が正常にアップロードされました。%d日分の献立を解析しました。',
                    $year, $month, $parseResult['total_days']
                ));

        } catch (\Exception $e) {
            DB::rollBack();
            
            // エラー時はアップロードしたファイルを削除
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return redirect()->back()
                ->with('error', 'エラーが発生しました: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function showMonthly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $menus = Menu::getMenusForMonth($year, $month);
        $monthlyPdf = MonthlyPdf::findByYearMonth($year, $month);

        return view('menus.monthly', compact('menus', 'monthlyPdf', 'year', 'month'));
    }

    public function showPdf($id)
    {
        $monthlyPdf = MonthlyPdf::where('user_id', auth()->id())->findOrFail($id);
        
        if (!Storage::disk('public')->exists($monthlyPdf->pdf_path)) {
            abort(404, 'PDFファイルが見つかりません。');
        }

        return response()->file(storage_path('app/public/' . $monthlyPdf->pdf_path));
    }

    private function deleteExistingMonthData(MonthlyPdf $monthlyPdf)
    {
        // 該当月の献立データを削除（ユーザー固有）
        $startDate = Carbon::create($monthlyPdf->year, $monthlyPdf->month, 1)->startOfMonth();
        $endDate = Carbon::create($monthlyPdf->year, $monthlyPdf->month, 1)->endOfMonth();
        
        Menu::where('user_id', auth()->id())
            ->whereBetween('date', [$startDate, $endDate])
            ->delete();

        // PDFファイルを削除
        if (Storage::disk('public')->exists($monthlyPdf->pdf_path)) {
            Storage::disk('public')->delete($monthlyPdf->pdf_path);
        }

        // MonthlyPdfレコードを削除
        $monthlyPdf->delete();
    }

    private function saveMonthlyMenus(array $menus): int
    {
        $savedCount = 0;
        
        foreach ($menus as $menuData) {
            try {
                Menu::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'date' => $menuData['date']
                    ],
                    [
                        'main_dish' => $menuData['main_dish'],
                        'side_dish' => $menuData['side_dish'],
                        'soup' => $menuData['soup'],
                        'rice' => $menuData['rice'],
                        'drink' => $menuData['drink'],
                        'dessert' => $menuData['dessert'],
                        'other' => $menuData['other'],
                    ]
                );
                $savedCount++;
            } catch (\Exception $e) {
                // 個別の保存エラーはログに記録して続行
                \Log::warning('Menu save failed for date: ' . $menuData['date'], [
                    'error' => $e->getMessage(),
                    'menu_data' => $menuData
                ]);
            }
        }

        return $savedCount;
    }

    private function buildParsingStatus(array $menus): array
    {
        $status = [];
        foreach ($menus as $menu) {
            $status[$menu['date']] = [
                'success' => true,
                'items_count' => count(array_filter([
                    $menu['main_dish'],
                    $menu['side_dish'],
                    $menu['soup'],
                    $menu['rice'],
                    $menu['drink'],
                    $menu['dessert']
                ]))
            ];
        }
        return $status;
    }
}
