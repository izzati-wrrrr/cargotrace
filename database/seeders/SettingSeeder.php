<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['delay_low_hours', '6', 'Delay sampai nilai ini (jam) dianggap LOW'],
            ['delay_medium_hours', '24', 'Delay sampai nilai ini (jam) dianggap MEDIUM, di atasnya HIGH'],
        ];

        foreach ($settings as [$key, $value, $description]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'description' => $description]
            );
        }
    }
}