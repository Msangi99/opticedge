<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BrandModelSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Samsung' => [
                'Galaxy S20', 'Galaxy S20+', 'Galaxy S20 Ultra', 'Galaxy S20 FE', 'Galaxy S21', 'Galaxy S21+',
                'Galaxy S21 Ultra', 'Galaxy S21 FE', 'Galaxy S22', 'Galaxy S22+', 'Galaxy S22 Ultra', 'Galaxy S23',
                'Galaxy S23+', 'Galaxy S23 Ultra', 'Galaxy S23 FE', 'Galaxy S24', 'Galaxy S24+', 'Galaxy S24 Ultra',
                'Galaxy S24 FE', 'Galaxy S25', 'Galaxy S25+', 'Galaxy S25 Ultra', 'Galaxy S26', 'Galaxy S26+',
                'Galaxy S26 Ultra', 'Galaxy Note 20', 'Galaxy Note 20 Ultra', 'Galaxy Z Flip', 'Galaxy Z Flip 5G',
                'Galaxy Z Fold 2', 'Galaxy Z Fold 3', 'Galaxy Z Flip 3', 'Galaxy Z Fold 4', 'Galaxy Z Flip 4',
                'Galaxy Z Fold 5', 'Galaxy Z Flip 5', 'Galaxy Z Fold 6', 'Galaxy Z Flip 6', 'Galaxy Z Fold 7',
                'Galaxy Z Flip 7', 'Galaxy Z Flip 7 FE', 'Galaxy Z TriFold', 'Galaxy A01', 'Galaxy A01 Core',
                'Galaxy A11', 'Galaxy A21', 'Galaxy A21s', 'Galaxy A31', 'Galaxy A41', 'Galaxy A51 5G',
                'Galaxy A51 5G UW', 'Galaxy A71', 'Galaxy A71 5G', 'Galaxy A71 5G UW', 'Galaxy A42 5G',
                'Galaxy A12', 'Galaxy A Quantum', 'Galaxy A02', 'Galaxy A02s', 'Galaxy A03', 'Galaxy A03s',
                'Galaxy A03 Core', 'Galaxy A12 Nacho', 'Galaxy A13 5G', 'Galaxy A22 4G', 'Galaxy A22 5G',
                'Galaxy A32 4G', 'Galaxy A32 5G', 'Galaxy A52 4G', 'Galaxy A52 5G', 'Galaxy A52s 5G', 'Galaxy A72',
                'Galaxy A04', 'Galaxy A04e', 'Galaxy A04s', 'Galaxy A13 4G', 'Galaxy A23', 'Galaxy A23 5G',
                'Galaxy A33 5G', 'Galaxy A53 5G', 'Galaxy A73 5G', 'Galaxy A14 4G', 'Galaxy A14 5G', 'Galaxy A24 4G',
                'Galaxy A24 5G', 'Galaxy A34 5G', 'Galaxy A54 5G', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A15',
                'Galaxy A16', 'Galaxy A25 5G', 'Galaxy A35 5G', 'Galaxy A55 5G', 'Galaxy A06', 'Galaxy A26 5G',
                'Galaxy A36 5G', 'Galaxy A56 5G', 'Galaxy M01', 'Galaxy M11', 'Galaxy M21', 'Galaxy M31', 'Galaxy M51',
                'Galaxy M02', 'Galaxy M12', 'Galaxy M22', 'Galaxy M32', 'Galaxy M52 5G', 'Galaxy M13', 'Galaxy M23 5G',
                'Galaxy M33 5G', 'Galaxy M53 5G', 'Galaxy M14 5G', 'Galaxy M34 5G', 'Galaxy M54 5G', 'Galaxy M15 5G',
                'Galaxy M35 5G', 'Galaxy M55 5G', 'Galaxy F41', 'Galaxy F62', 'Galaxy F23 5G', 'Galaxy F54 5G',
                'Galaxy F55 5G', 'Galaxy Tab S6 Lite', 'Galaxy Tab S7', 'Galaxy Tab S7+', 'Galaxy Tab S7 FE',
                'Galaxy Tab A7', 'Galaxy Tab A7 Lite', 'Galaxy Tab S8', 'Galaxy Tab S8+', 'Galaxy Tab S8 Ultra',
                'Galaxy Tab S9', 'Galaxy Tab S9+', 'Galaxy Tab S9 Ultra', 'Galaxy Tab S9 FE', 'Galaxy Tab S10',
                'Galaxy Tab S10+', 'Galaxy Tab S10 Ultra', 'Galaxy Tab S10 FE', 'Galaxy Tab S10 Edge', 'Galaxy Watch 3',
                'Galaxy Watch Active2', 'Galaxy Watch 4', 'Galaxy Watch 4 Classic', 'Galaxy Watch 5', 'Galaxy Watch 5 Pro',
                'Galaxy Watch 6', 'Galaxy Watch 6 Classic', 'Galaxy Watch 7', 'Galaxy Watch Ultra', 'Galaxy Watch 8',
                'Galaxy Watch 8 Classic', 'Galaxy Watch 8 Ultra',
            ],
            'iPhone' => [
                'iPhone 12 mini', 'iPhone 12', 'iPhone 12 Pro', 'iPhone 12 Pro Max', 'iPhone 13 mini', 'iPhone 13',
                'iPhone 13 Pro', 'iPhone 13 Pro Max', 'iPhone SE (3rd generation)', 'iPhone 14', 'iPhone 14 Plus',
                'iPhone 14 Pro', 'iPhone 14 Pro Max', 'iPhone 15', 'iPhone 15 Plus', 'iPhone 15 Pro',
                'iPhone 15 Pro Max', 'iPhone 16', 'iPhone 16 Plus', 'iPhone 16 Pro', 'iPhone 16 Pro Max', 'iPhone 17',
                'iPhone 17 Air', 'iPhone 17 Pro', 'iPhone 17 Pro Max', 'iPhone 17e',
            ],
            'Tecno' => [
                'Camon 15', 'Camon 15 Premier', 'Spark 5', 'Spark 5 Pro', 'Spark 5 Air', 'Spark Go 2020', 'Pova',
                'Pop 4', 'Pop 4 Pro', 'Pop 4 LTE', 'Spark 6', 'Spark 6 Go', 'Spark 6 Air', 'Camon 16',
                'Camon 16 Premier', 'Camon 16 SE', 'Spark 7', 'Spark 7 Pro', 'Spark 7T', 'Spark 7P', 'Pova 2',
                'Pop 5', 'Pop 5 Go', 'Pop 5 LTE', 'Pop 5 Pro', 'Phantom X', 'Camon 17', 'Camon 17 Pro', 'Camon 17P',
                'Spark 8', 'Spark 8 Pro', 'Spark 8C', 'Spark 8T', 'Spark 8P', 'Pova 3', 'Pop 6', 'Pop 6 Go',
                'Pop 6 Pro', 'Phantom X2', 'Phantom X2 Pro', 'Camon 19', 'Camon 19 Pro', 'Camon 19 Neo',
                'Camon 19 Pro 5G', 'Spark 9', 'Spark 9 Pro', 'Spark 9T', 'Spark 9 Star', 'Pova 4', 'Pova 4 Pro',
                'Pop 7', 'Pop 7 Pro', 'Phantom V Fold', 'Phantom V Flip', 'Camon 20', 'Camon 20 Pro', 'Camon 20 Pro 5G',
                'Camon 20 Premier 5G', 'Spark 10', 'Spark 10 Pro', 'Spark 10C', 'Spark 10 5G', 'Pova 5', 'Pova 5 Pro',
                'Pop 8', 'Pop 8 Pro', 'Phantom V Fold2', 'Phantom V Flip2', 'Camon 30', 'Camon 30 Pro', 'Camon 30 Pro 5G',
                'Camon 30 Premier 5G', 'Camon 30S', 'Spark 30', 'Spark 30 Pro', 'Spark 30 5G', 'Spark 30C',
                'Spark 30C 5G', 'Spark Go 1', 'Spark Go 1S', 'Pova 6', 'Pova 6 Neo 5G', 'Pop 9', 'Pop 9 4G',
                'Megapad', 'Megapad 11', 'Camon 40', 'Camon 40 Pro 4G', 'Camon 40 Pro 5G', 'Camon 40 Premier',
                'Camon 50 4G', 'Camon 50 Pro 4G', 'Camon 50 Pro 5G', 'Camon 50 Ultra 5G', 'Spark 40', 'Spark 40 Pro',
                'Spark 40 Pro+', 'Spark 40C', 'Spark Go 2', 'Spark Go 3', 'Spark Go 5G', 'Spark Slim', 'Pova 7 4G',
                'Pova 7 5G', 'Pova 7 Pro 5G', 'Pova 7 Ultra 5G', 'Pova Curve', 'Pova Curve 2', 'Pova Slim',
                'Megapad Pro', 'Megapad SE',
            ],
            'Infinix' => [
                'Hot 9', 'Hot 9 Play', 'Hot 9 Pro', 'Hot 10', 'Hot 10 Play', 'Hot 10 Lite', 'Hot 10S', 'Hot 10T',
                'Note 7', 'Note 7 Lite', 'Note 8', 'Note 8i', 'Zero 8', 'Smart 5', 'Smart 5 Pro', 'S5 Pro', 'Hot 11',
                'Hot 11S', 'Hot 11S NFC', 'Hot 11 Play', 'Hot 11 2022', 'Note 11', 'Note 11 Pro', 'Note 11S',
                'Note 11S NFC', 'Zero X', 'Zero X Pro', 'Zero X Neo', 'Smart 6', 'Smart 6 Plus', 'Smart 6 HD',
                'Hot 12', 'Hot 12 Play', 'Hot 12 Play NFC', 'Hot 12i', 'Hot 12 Pro', 'Hot 20', 'Hot 20 5G', 'Hot 20i',
                'Hot 20 Play', 'Hot 20S', 'Note 12', 'Note 12 Pro', 'Note 12 Pro 5G', 'Note 12 VIP', 'Note 12 G96',
                'Note 12 2023', 'Zero 20', 'Smart 7', 'Smart 7 HD', 'Smart 7 Plus', 'Hot 30', 'Hot 30 Play',
                'Hot 30 5G', 'Hot 30i', 'Hot 40', 'Hot 40 Pro', 'Hot 40i', 'Note 30', 'Note 30 Pro', 'Note 30 VIP',
                'Note 30 5G', 'Zero 30', 'Zero 30 4G', 'GT 10 Pro', 'Smart 8', 'Smart 8 HD', 'Smart 8 Plus',
                'Smart 8 Pro', 'Smart 8 (India)', 'Note 40', 'Note 40 Pro 4G', 'Note 40 Pro', 'Note 40 Pro+',
                'Note 40 5G', 'Note 40S', 'Note 40X 5G', 'Hot 50', 'Hot 50 4G', 'Hot 50 Pro 4G', 'Hot 50 Pro+ 4G',
                'Hot 50i', 'Zero 40', 'Zero 40 4G', 'Zero Flip', 'GT 20 Pro', 'Smart 9', 'Smart 9 HD', 'Xpad',
                'Note 50 4G', 'Note 50 Pro 4G', 'Note 50 Pro+', 'Note 50x', 'Note 50s 5G', 'Hot 60', 'Hot 60 5G',
                'Hot 60 Pro', 'Hot 60 Pro+', 'Hot 60i 4G', 'Hot 60i 5G', 'GT 30', 'GT 30 Pro', 'Smart 10',
                'Smart 10 Plus', 'Smart 10 HD', 'Xpad 20', 'Xpad 20 Pro', 'Xpad GT', 'Note Edge', 'Smart 20',
            ],
            'Google Pixel' => [
                'Pixel 4a', 'Pixel 5', 'Pixel 5a', 'Pixel 4a 5G', 'Pixel 6', 'Pixel 6 Pro', 'Pixel 6a', 'Pixel 7',
                'Pixel 7 Pro', 'Pixel 7a', 'Pixel 7 Pro Fold', 'Pixel 8', 'Pixel 8 Pro', 'Pixel 8a', 'Pixel 8 Pro Fold',
                'Pixel 9', 'Pixel 9 Pro', 'Pixel 9 Pro XL', 'Pixel 9 Pro Fold', 'Pixel 9a', 'Pixel 10', 'Pixel 10 Pro',
                'Pixel 10 Pro XL', 'Pixel 10 Pro Fold', 'Pixel 10a', 'Pixel Tablet',
            ],
        ];

        foreach ($catalog as $brandName => $models) {
            $brand = Category::query()->firstOrCreate(['name' => $brandName]);

            foreach ($models as $modelName) {
                Product::query()->firstOrCreate(
                    [
                        'category_id' => $brand->id,
                        'name' => $modelName,
                    ],
                    [
                        'brand' => $brandName,
                        'price' => 0,
                        'rating' => 5.0,
                        'stock_quantity' => 0,
                        'description' => null,
                        'images' => [],
                    ]
                );
            }
        }
    }
}
