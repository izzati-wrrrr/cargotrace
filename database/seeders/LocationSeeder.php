<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Koordinat perkiraan, cukup untuk demo peta.
        $locations = [
            ['Tanjung Priok', 'Jakarta', 'ID', 'PORT', -6.1040, 106.8800],
            ['Belawan', 'Medan', 'ID', 'PORT', 3.7840, 98.6810],
            ['Singapore Port', 'Singapore', 'SG', 'PORT', 1.2644, 103.8222],
            ['Port Klang', 'Klang', 'MY', 'PORT', 3.0000, 101.3833],
            ['Shanghai Port', 'Shanghai', 'CN', 'PORT', 31.2304, 121.4737],
            ['Hong Kong Port', 'Hong Kong', 'HK', 'PORT', 22.3193, 114.1694],
            ['Busan Port', 'Busan', 'KR', 'PORT', 35.1028, 129.0403],
            ['Yokohama Port', 'Yokohama', 'JP', 'PORT', 35.4437, 139.6380],
            ['Jebel Ali', 'Dubai', 'AE', 'PORT', 25.0118, 55.0617],
            ['Port of Rotterdam', 'Rotterdam', 'NL', 'PORT', 51.9244, 4.4777],
            ['Port of Hamburg', 'Hamburg', 'DE', 'PORT', 53.5511, 9.9937],
            ['Port of Los Angeles', 'Los Angeles', 'US', 'PORT', 33.7292, -118.2620],
            ['Port of New York', 'New York', 'US', 'PORT', 40.7128, -74.0060],
            ['Port of Santos', 'Santos', 'BR', 'PORT', -23.9608, -46.3336],
            ['Port of Durban', 'Durban', 'ZA', 'PORT', -29.8587, 31.0218],
            ['Port Botany', 'Sydney', 'AU', 'PORT', -33.9500, 151.2100],
            ['Gudang Cikarang', 'Bekasi', 'ID', 'WAREHOUSE', -6.2615, 107.1520],
            ['Rotterdam Warehouse', 'Rotterdam', 'NL', 'WAREHOUSE', 51.9000, 4.4000],
        ];

        foreach ($locations as [$name, $city, $country, $type, $lat, $lng]) {
            Location::updateOrCreate(
                ['name' => $name, 'country_code' => $country],
                ['city' => $city, 'type' => $type, 'latitude' => $lat, 'longitude' => $lng]
            );
        }
    }
}