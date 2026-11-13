<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class RefreshExchangeRates extends Command
{
    protected $signature = 'rates:refresh';

    protected $description = 'Ambil kurs terbaru dari API eksternal dan simpan ke database';

    public function handle(ExchangeRateService $rates): int
    {
        $result = $rates->refresh();

        $result['ok'] ? $this->info($result['message']) : $this->error($result['message']);

        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }
}