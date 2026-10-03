<?php

namespace Database\Seeders;

use App\Models\AirConditioner;
use Illuminate\Database\Seeder;

class AirConditionerSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Điều hòa Casper Inverter 1 chiều 9000 BTU',
                'brand' => 'Casper',
                'price' => 7490000,
                'description' => 'Điều hòa Casper Inverter tiết kiệm điện, làm lạnh nhanh, phù hợp phòng ngủ và phòng làm việc nhỏ.',
                'origin' => 'Thái Lan',
                'warranty' => '3 năm',
                'variant' => [
                    'capacity_name' => '9.000 BTU (1 HP)',
                    'price' => 7490000,
                    'stock' => 20,
                    'weight' => 25000,
                    'specifications' => [
                        'room_size' => 'Dưới 15 m2',
                        'inverter_type' => 'Inverter',
                        'type' => '1 chiều',
                        'energy_rating' => '5 sao',
                        'cooling_feature' => 'Làm lạnh nhanh Turbo',
                    ],
                ],
            ],
            [
                'name' => 'Điều hòa Daikin Inverter 1 chiều 12000 BTU',
                'brand' => 'Daikin',
                'price' => 11990000,
                'description' => 'Điều hòa Daikin Inverter vận hành êm ái, làm lạnh ổn định, phù hợp phòng khách hoặc phòng ngủ rộng.',
                'origin' => 'Việt Nam',
                'warranty' => '1 năm',
                'variant' => [
                    'capacity_name' => '12.000 BTU (1.5 HP)',
                    'price' => 11990000,
                    'stock' => 15,
                    'weight' => 32000,
                    'specifications' => [
                        'room_size' => 'Từ 15 đến 20 m2',
                        'inverter_type' => 'Inverter',
                        'type' => '1 chiều',
                        'energy_rating' => '5 sao',
                        'cooling_feature' => 'Làm lạnh nhanh',
                    ],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $variantData = $productData['variant'];
            unset($productData['variant']);

            $product = AirConditioner::updateOrCreate(
                ['name' => $productData['name']],
                $productData
            );

            $product->variants()->updateOrCreate(
                ['capacity_name' => $variantData['capacity_name']],
                $variantData
            );
        }
    }
}
