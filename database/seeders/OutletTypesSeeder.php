<?php

namespace Database\Seeders;

use App\Models\OutletType;
use Illuminate\Database\Seeder;

class OutletTypesSeeder extends Seeder
{
    public function run(): void
    {
        // is_active=false → disembunyikan dari form create (data lama tetap aman)
        $types = [
            ['name' => 'Warung Makan',      'slug' => 'warung_makan',  'route_prefix' => 'fnb',     'type_code' => '01', 'icon' => 'fa-utensils',        'description' => 'Warung nasi, warteg, rumah makan',     'requires_opening_stock' => true,  'track_cogs' => false, 'sort_order' => 1,  'is_active' => true],
            ['name' => 'Kafe / Coffee Shop', 'slug' => 'kafe',          'route_prefix' => 'fnb',     'type_code' => '02', 'icon' => 'fa-mug-hot',         'description' => 'Kedai kopi, cafe, minuman kekinian',    'requires_opening_stock' => true,  'track_cogs' => false, 'sort_order' => 2,  'is_active' => true],
            // Retail — dikelola sebagai satu jenis dengan bidang_usaha bebas
            ['name' => 'Retail',             'slug' => 'retail',        'route_prefix' => 'retail',  'type_code' => '10', 'icon' => 'fa-shop',            'description' => 'Toko retail — kelontong, pakaian, elektronik, dll.', 'requires_opening_stock' => false, 'track_cogs' => true, 'sort_order' => 3, 'is_active' => true],
            // Legacy retail sub-types — disembunyikan dari create, tetap aktif untuk outlet lama
            ['name' => 'Toko Kelontong',     'slug' => 'kelontong',     'route_prefix' => 'retail',  'type_code' => '03', 'icon' => 'fa-basket-shopping', 'description' => 'Toko sembako, minimarket kecil',        'requires_opening_stock' => true,  'track_cogs' => true,  'sort_order' => 31, 'is_active' => false],
            ['name' => 'Toko Pakaian',       'slug' => 'pakaian',       'route_prefix' => 'retail',  'type_code' => '05', 'icon' => 'fa-shirt',           'description' => 'Butik, distro, toko baju & aksesoris', 'requires_opening_stock' => false, 'track_cogs' => true,  'sort_order' => 32, 'is_active' => false],
            ['name' => 'Toko Elektronik',    'slug' => 'elektronik',    'route_prefix' => 'retail',  'type_code' => '06', 'icon' => 'fa-mobile-screen',   'description' => 'Gadget, aksesoris, elektronik',         'requires_opening_stock' => false, 'track_cogs' => true,  'sort_order' => 33, 'is_active' => false],
            ['name' => 'Salon & Barbershop',  'slug' => 'salon',         'route_prefix' => 'salon',   'type_code' => '07', 'icon' => 'fa-scissors',        'description' => 'Salon, barbershop, spa, perawatan rambut & kecantikan',     'requires_opening_stock' => false, 'track_cogs' => false, 'sort_order' => 4,  'is_active' => true],
            ['name' => 'Laundry',            'slug' => 'laundry',       'route_prefix' => 'laundry', 'type_code' => '08', 'icon' => 'fa-soap',            'description' => 'Jasa laundry kiloan dan satuan',        'requires_opening_stock' => false, 'track_cogs' => false, 'sort_order' => 5,  'is_active' => true],
            ['name' => 'Toko Buku',          'slug' => 'buku',          'route_prefix' => 'fnb',     'type_code' => '09', 'icon' => 'fa-book',            'description' => 'Toko buku, alat tulis, percetakan',     'requires_opening_stock' => false, 'track_cogs' => false, 'sort_order' => 6,  'is_active' => false],
        ];

        foreach ($types as $data) {
            OutletType::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
