<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClaudeApiService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.claude.api_key');
        $this->baseUrl = config('services.claude.base_url', 'https://api.anthropic.com');
    }

    public function parseMonthlyMenuFromPdf(string $pdfPath, int $year, int $month): array
    {
        try {
            // PDFファイルをBase64エンコード
            $pdfData = base64_encode(file_get_contents($pdfPath));
            
            $prompt = $this->buildMonthlyMenuParsingPrompt($year, $month);
            $response = $this->callClaudeApiWithPdf($prompt, $pdfData);
            
            return $this->parseMonthlyJsonResponse($response, $year, $month);
            
        } catch (\Exception $e) {
            Log::error('Claude API monthly menu parsing failed', [
                'error' => $e->getMessage(),
                'year' => $year,
                'month' => $month,
                'pdf_path' => $pdfPath
            ]);
            
            throw $e;
        }
    }

    private function buildMonthlyMenuParsingPrompt(int $year, int $month): string
    {
        $monthName = Carbon::create($year, $month, 1)->format('Y年m月');
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $dateFormat = sprintf('%d-%02d-DD', $year, $month);
        $exampleDate = sprintf('%d-%02d-01', $year, $month);
        
        return "このPDFは{$monthName}の小学校給食献立表です。PDFから1ヶ月分の献立情報を解析し、以下のJSON形式で返してください。

**重要な指示:**
1. PDFから各日付の献立を正確に読み取ってください
2. 土日や休校日は献立がない場合があります（その場合は除外）
3. 日付は「{$dateFormat}」形式で統一してください
4. 献立項目は以下に分類してください：
   - main_dish: 主菜（メインおかず）
   - side_dish: 副菜（サブおかず）
   - soup: 汁物（みそ汁、スープなど）
   - rice: ご飯類（白ご飯、炊き込みご飯など）
   - drink: 飲み物（牛乳など）
   - dessert: デザート（果物、ヨーグルトなど）
   - other: その他（カロリー、アレルギー情報など）

**期待するJSON形式:**
```json
{
  \"status\": \"success\",
  \"total_days\": 献立のある日数,
  \"menus\": [
    {
      \"date\": \"{$exampleDate}\",
      \"main_dish\": \"主菜名\",
      \"side_dish\": \"副菜名\",
      \"soup\": \"汁物名\",
      \"rice\": \"ご飯名\",
      \"drink\": \"飲み物名\",
      \"dessert\": \"デザート名\",
      \"other\": \"その他の情報\"
    }
  ],
  \"notes\": \"解析時の注意点や特記事項\"
}
```

**注意事項:**
- 該当する項目がない場合はnullを設定
- 日本語の献立名をそのまま使用
- レスポンスは有効なJSONのみ（説明文不要）
- 1日に複数の献立がある場合は最初のもので統一";
    }

    private function callClaudeApiWithPdf(string $prompt, string $pdfDataBase64): string
    {
        $response = $this->client->post($this->baseUrl . '/v1/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ],
            'json' => [
                'model' => 'claude-sonnet-4-20250514', // PDFには Sonnet を推奨
                'max_tokens' => 4000, // 1ヶ月分なので大きめに設定
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt
                            ],
                            [
                                'type' => 'document',
                                'source' => [
                                    'type' => 'base64',
                                    'media_type' => 'application/pdf',
                                    'data' => $pdfDataBase64
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'timeout' => 120, // PDFの場合は長めに設定
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        
        if (!isset($responseData['content'][0]['text'])) {
            throw new \Exception('Unexpected Claude API response format');
        }

        return $responseData['content'][0]['text'];
    }

    private function parseMonthlyJsonResponse(string $response, int $year, int $month): array
    {
        // JSONの前後の説明文を除去
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}') + 1;
        
        if ($jsonStart === false || $jsonEnd === false) {
            throw new \Exception('No valid JSON found in Claude response');
        }
        
        $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart);
        $decoded = json_decode($jsonString, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON in Claude response: ' . json_last_error_msg());
        }

        // レスポンス形式の検証
        if (!isset($decoded['menus']) || !is_array($decoded['menus'])) {
            throw new \Exception('Invalid menu data structure in Claude response');
        }

        // 各献立データの検証と整形
        $validatedMenus = [];
        foreach ($decoded['menus'] as $menu) {
            if (!isset($menu['date'])) {
                continue; // 日付がない場合はスキップ
            }

            // 日付の検証
            try {
                $date = Carbon::parse($menu['date']);
                if ($date->year !== $year || $date->month !== $month) {
                    continue; // 対象月以外はスキップ
                }
            } catch (\Exception $e) {
                continue; // 無効な日付はスキップ
            }

            $validatedMenus[] = [
                'date' => $menu['date'],
                'main_dish' => $menu['main_dish'] ?? null,
                'side_dish' => $menu['side_dish'] ?? null,
                'soup' => $menu['soup'] ?? null,
                'rice' => $menu['rice'] ?? null,
                'drink' => $menu['drink'] ?? null,
                'dessert' => $menu['dessert'] ?? null,
                'other' => $menu['other'] ?? null,
            ];
        }

        return [
            'status' => $decoded['status'] ?? 'success',
            'total_days' => count($validatedMenus),
            'menus' => $validatedMenus,
            'notes' => $decoded['notes'] ?? null,
        ];
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->client->post($this->baseUrl . '/v1/messages', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                ],
                'json' => [
                    'model' => 'claude-3-haiku-20240307',
                    'max_tokens' => 100,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Hello, this is a test message. Please respond with "Connection successful".'
                        ]
                    ]
                ],
                'timeout' => 30,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            $responseText = $responseData['content'][0]['text'] ?? '';
            
            return strpos($responseText, 'Connection successful') !== false;
        } catch (\Exception $e) {
            Log::error('Claude API connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}