<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Symfony\Component\Intl\Currencies;
use Throwable;

/**
 * Kurs diambil dari open.er-api.com (kurs terhadap USD), disimpan di tabel currencies,
 * lalu dipakai untuk konversi lewat USD sebagai mata uang perantara.
 * Layanan ini mewajibkan atribusi dan membatasi permintaan, jadi kurs di-cache di database.
 */
class ExchangeRateService
{
    public const ENDPOINT = 'https://open.er-api.com/v6/latest/USD';
    public const MAX_AGE_HOURS = 24;
    private const RETRY_AFTER_MINUTES = 60;

    private ?array $rates = null;

    /** Mengambil kurs dari API. $quick = true untuk pemanggilan saat halaman dibuka (timeout pendek). */
    public function refresh(bool $quick = false): array
    {
        try {
            $request = Http::acceptJson()->timeout($quick ? 4 : 10);
            if (! $quick) {
                $request = $request->retry(2, 400, throw: false);
            }
            $response = $request->get(self::ENDPOINT);
        } catch (Throwable $e) {
            return $this->fail('Tidak bisa terhubung ke layanan kurs. Kurs yang tersimpan tetap dipakai.', $e);
        }

        if (! $response->successful()) {
            return $this->fail('Layanan kurs menolak permintaan (status ' . $response->status() . '). Kurs yang tersimpan tetap dipakai.');
        }

        $data = $response->json();
        if (! is_array($data)) {
            $data = [];
        }

        // Hanya terima kode 3 huruf dengan nilai angka positif.
        $rates = collect($data['rates'] ?? [])
            ->filter(fn ($rate, $code) => is_string($code) && preg_match('/^[A-Z]{3}$/', $code) === 1 && is_numeric($rate) && $rate > 0);

        // Respons yang tidak lengkap tidak boleh menimpa kurs lama.
        if (($data['result'] ?? null) !== 'success' || $rates->count() < 50 || ! $rates->has('USD')) {
            return $this->fail('Data kurs dari layanan tidak lengkap. Kurs yang tersimpan tetap dipakai.');
        }

        $now = now()->toDateTimeString();

        DB::transaction(function () use ($rates, $now) {
            Currency::upsert(
                $rates->map(fn ($rate, $code) => ['code' => $code, 'rate' => $rate, 'fetched_at' => $now])->values()->all(),
                ['code'],
                ['rate', 'fetched_at'],
            );
        });

        $this->rates = null;
        Cache::forget('currency_options');

        return ['ok' => true, 'message' => "Kurs diperbarui: {$rates->count()} mata uang."];
    }

    /** Memperbarui kurs hanya jika sudah lebih dari 24 jam, dan paling sering sekali per jam. */
    public function ensureFresh(): void
    {
        if (! $this->status()['stale']) {
            return;
        }

        // Cache::add hanya berhasil jika kuncinya belum ada: satu percobaan per jam,
        // supaya layanan yang membatasi permintaan tidak ditekan terus saat gagal.
        if (! Cache::add('rates:attempt', true, now()->addMinutes(self::RETRY_AFTER_MINUTES))) {
            return;
        }

        $this->refresh(quick: true);
    }

    public function status(): array
    {
        $last = Currency::max('fetched_at');
        $updatedAt = $last ? Carbon::parse($last) : null;

        return [
            'count' => Currency::count(),
            'updated_at' => $updatedAt,
            'stale' => ! $updatedAt || $updatedAt->lt(now()->subHours(self::MAX_AGE_HOURS)),
        ];
    }

    /** Konversi lewat USD: jumlah / kurs asal * kurs tujuan. Null jika salah satu kurs tidak ada. */
    public function convert(float $amount, string $from, string $to): ?float
    {
        $rates = $this->rates();

        if (! isset($rates[$from], $rates[$to])) {
            return null;
        }

        return $amount / $rates[$from] * $rates[$to];
    }

    /** Nilai asli dan hasil konversi ke beberapa mata uang tujuan, sudah diformat. */
    public function describe(float $amount, string $from, array $targets): array
    {
        $rows = [];

        foreach (array_unique($targets) as $code) {
            if ($code === $from) {
                continue;
            }

            $converted = $this->convert($amount, $from, $code);

            $rows[] = [
                'code' => $code,
                'name' => $this->nameOf($code),
                'text' => $converted === null ? null : $this->format($converted, $code),
            ];
        }

        return [
            'original' => $this->format($amount, $from),
            'from' => $from,
            'from_name' => $this->nameOf($from),
            'conversions' => $rows,
        ];
    }

    public function format(float $amount, string $code): string
    {
        return Number::currency($amount, in: $code, locale: 'id');
    }

    public function nameOf(string $code): string
    {
        try {
            return Currencies::exists($code) ? Currencies::getName($code, 'id') : $code;
        } catch (Throwable) {
            return $code;
        }
    }

    /** Daftar pilihan untuk dropdown: kode => "KODE - Nama". */
    public function options(): array
    {
        return Cache::remember('currency_options', 3600, fn () => Currency::orderBy('code')
            ->pluck('code')
            ->mapWithKeys(fn ($code) => [$code => $code . ' - ' . $this->nameOf($code)])
            ->all());
    }

    private function rates(): array
    {
        return $this->rates ??= Currency::pluck('rate', 'code')->map(fn ($rate) => (float) $rate)->all();
    }

    private function fail(string $message, ?Throwable $e = null): array
    {
        Log::warning('Gagal memperbarui kurs: ' . $message, $e ? ['exception' => $e->getMessage()] : []);

        return ['ok' => false, 'message' => $message];
    }
}