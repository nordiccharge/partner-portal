<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Company;
use App\Models\Country;
use App\Models\Installer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pipeline;
use App\Models\Postal;
use App\Models\Product;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@nordiccharge.com',
            'password' => bcrypt('123seb'),
            'is_admin' => 1
        ]);

        Country::factory()->create();

        City::factory()->create();

        \App\Models\CompanyType::factory()->create([
            'name' => 'Installer',
        ]);

        \App\Models\CompanyType::factory()->create([
            'name' => 'Customer',
        ]);

        Company::factory()->create([
            'name' => 'GSR Teknik',
            'company_type_id' => 1
        ]);

        Installer::factory()->create();

        Postal::factory()->create();


        Company::factory()->create([
            'name' => 'EVDK',
        ]);

        Company::factory()->create([
            'name' => 'Strømlinet',
        ]);

        Team::factory()->create([
            'name' => 'EVDK',
            'company_id' => 2
        ])->users()->attach(1);

        Team::factory()->create([
            'name' => 'Strømlinet',
            'company_id' => 3
        ])->users()->attach(1);

        Brand::factory()->create([
           'name' => 'Easee'
        ]);

        Brand::factory()->create([
            'name' => 'Zaptec'
        ]);

        Category::factory()->create([
            'name' => 'AC Chargers'
        ]);

        Pipeline::factory()->create([
            'name' => 'Private Installation'
        ]);

        Stage::factory()->create([
            'name' => 'Order Created',
            'pipeline_id' => 1,
            'order' => 0,
            'state' => 'step'
        ]);

        Stage::factory()->create([
            'name' => 'Installation Confirmed',
            'pipeline_id' => 1,
            'order' => 1,
            'state' => 'milestone'
        ]);

        Stage::factory()->create([
            'name' => 'Online',
            'pipeline_id' => 1,
            'order' => 2,
            'state' => 'completed'
        ]);

        Pipeline::factory()->create([
            'name' => 'Purchase Order'
        ]);

        Product::factory()->create([
            'sku' => 'ECL001-black',
            'name' => 'Charge Lite',
            'brand_id' => 1,
            'category_id' => 1,
            'retail_price' => 0,
            'purchase_price' => 0,
            'delivery_information' => 'Test'
        ]);

        Product::factory()->create([
            'sku' => 'ZM000688',
            'name' => 'Zaptec Go',
            'brand_id' => 2,
            'category_id' => 1,
            'retail_price' => 0,
            'purchase_price' => 0
        ]);

        Inventory::factory()->create();

        Order::factory()->create();

        OrderItem::factory()->create([
            'inventory_id' => 1
        ]);

        Order::factory()->create([
            'team_id' => 1,
            'pipeline_id' => 1,
            'stage_id' => 1,
            'id' => 19483928171,
            'order_reference' => 'test_order_reference_2',
            'customer_first_name' => 'Sebastian Bak',
            'customer_last_name' => 'Lundahl',
            'customer_email' => 'sebastian@nordiccharge.com',
            'customer_phone' => '+45 22 22 93 70',
            'shipping_address' => 'Teglgårdstræde 4b',
            'postal_id' => 1,
            'city_id' => 1,
            'country_id' => 1,
            'tracking_code' => 1234,
            'wished_installation_date' => '2023-12-23',
            'installation_date' => '2023-12-24'
        ]);

        OrderItem::factory()->create([
            'order_id' => 19483928171,
            'inventory_id' => 1,
            'quantity' => 2
        ]);

    }
}
