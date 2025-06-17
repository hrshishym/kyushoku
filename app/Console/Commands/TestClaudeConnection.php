<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ClaudeApiService;

class TestClaudeConnection extends Command
{
    protected $signature = 'claude:test';
    protected $description = 'Test Claude API connection';

    public function handle(ClaudeApiService $claudeService)
    {
        $this->info('Testing Claude API connection...');
        
        if ($claudeService->testConnection()) {
            $this->info('✅ Claude API connection successful!');
        } else {
            $this->error('❌ Claude API connection failed. Please check your API key and configuration.');
        }
    }
}