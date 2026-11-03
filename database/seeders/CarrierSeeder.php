<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $carriers = [
            ['name' => 'Samudra Line', 'code' => 'SML', 'contact_email' => 'ops@samudraline.example'],
            ['name' => 'Pacific Star Shipping', 'code' => 'PSS', 'contact_email' => 'ops@pacificstar.example'],
            ['name' => 'Nordic Freight', 'code' => 'NFR', 'contact_email' => 'ops@nordicfreight.example'],
            ['name' => 'TransAsia Logistics', 'code' => 'TAL', 'contact_email' => 'ops@transasia.example'],
            ['name' => 'Global Air Cargo', 'code' => 'GAC', 'contact_email' => 'ops@globalair.example'],
        ];

        foreach ($carriers as $data) {
            Carrier::updateOrCreate(['code' => $data['code']], $data + ['is_active' => true]);
        }
    }
}